<?php

namespace App\Console\Commands;

use App\Models\DemandRecord;
use App\Models\SalesRecord;
use App\Services\DemandService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RebuildImportDemand extends Command
{
    protected $signature = 'demand:rebuild-imports
                            {--dry-run : Preview what would be migrated without making changes}
                            {--force   : Skip the confirmation prompt (for automated runs)}';

    protected $description = 'Move legacy imported demand_records into sales_records, then rebuild all sale-derived aggregates';

    public function handle(DemandService $demandService): int
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║  Demand Records Rebuild — imports → sales_records            ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // ─── 1. Snapshot pre-migration state ────────────────────────────────
        $imported    = DemandRecord::where('source', 'imported')->orderBy('id')->get();
        $manual      = DemandRecord::where('source', 'manual')->count();
        $count       = $imported->count();
        $totalQty    = (int) $imported->sum('quantity');

        $this->line('Pre-migration snapshot:');
        $this->table(
            ['Source', 'Rows', 'Total Quantity'],
            [
                ['demand_records (imported)', $count,  $totalQty],
                ['demand_records (manual)',   $manual, '—'],
                ['sales_records (imported)',  SalesRecord::where('source', 'imported')->count(), '—'],
                ['sales_records (pos)',       SalesRecord::where('source', 'pos')->count(), '—'],
                ['demand_records (sale)',     DemandRecord::where('source', 'sale')->count(), (int) DemandRecord::where('source', 'sale')->sum('quantity')],
            ]
        );
        $this->newLine();

        // ─── 2. Nothing to do? ──────────────────────────────────────────────
        if ($count === 0 && $manual === 0) {
            $this->info('Nothing to migrate — no imported or manual demand records found.');
            return self::SUCCESS;
        }

        // ─── 3. Dry-run mode ────────────────────────────────────────────────
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN — no changes will be made.');
            $this->newLine();

            if ($count > 0) {
                $this->line("First 20 imported demand rows that would move to sales_records:");
                $this->table(
                    ['ID', 'product_id', 'record_date', 'quantity', 'transaction_id'],
                    $imported->take(20)->map(fn ($r) => [
                        $r->id,
                        $r->product_id,
                        $r->record_date->toDateString(),
                        $r->quantity,
                        $r->transaction_id ?? '—',
                    ])->all()
                );
                if ($count > 20) {
                    $this->line("… and " . ($count - 20) . " more row(s).");
                }
            }

            if ($manual > 0) {
                $this->newLine();
                $this->warn("{$manual} manual demand row(s) would be deleted (source='manual').");
            }

            $this->newLine();
            $this->info('Dry run complete — nothing was changed.');
            return self::SUCCESS;
        }

        // ─── 4. Confirm ────────────────────────────────────────────────────
        if (!$this->option('force')) {
            $this->warn('This operation will:');
            $this->line("  1. Insert {$count} row(s) into sales_records (source='imported')");
            $this->line("  2. Delete ALL demand_records with source='imported' ({$count} rows)");
            $this->line("  3. Delete ALL demand_records with source='manual' ({$manual} rows)");
            $this->line("  4. Rebuild ALL demand_records with source='sale' from sales_records");
            $this->newLine();
            $this->warn('This is not reversible without a database backup.');

            if (!$this->confirm('Proceed with migration?', false)) {
                $this->error('Migration aborted. No changes were made.');
                return self::FAILURE;
            }
        }

        // ─── 5. Run migration inside a transaction ─────────────────────────
        $this->newLine();
        $bar = $this->output->createProgressBar(4);
        $bar->setFormat(' %current%/%max% [%bar%] %message%');
        $bar->setMessage('Starting…');
        $bar->start();

        try {
            DB::transaction(function () use ($imported, $demandService, $bar) {
                // Step 1: copy imported demand rows into sales_records
                // Observer is suppressed so the aggregate rebuild happens in one
                // deterministic pass at the end (not incrementally per row).
                $bar->setMessage('Copying imported demand → sales_records');
                $bar->advance();

                SalesRecord::withoutEvents(function () use ($imported) {
                    $now = now();
                    $rows = $imported->map(fn ($r) => [
                        'product_id'     => $r->product_id,
                        'transaction_id' => $r->transaction_id,
                        'record_date'    => $r->record_date->toDateString(),
                        'quantity'       => $r->quantity,
                        'source'         => 'imported',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ])->all();

                    foreach (array_chunk($rows, 500) as $chunk) {
                        SalesRecord::insert($chunk);
                    }
                });

                // Step 2: delete legacy imported demand rows
                $bar->setMessage('Deleting legacy imported demand rows');
                $bar->advance();
                DemandRecord::where('source', 'imported')->delete();

                // Step 3: delete manual demand rows (Q1 decision — yes)
                $bar->setMessage('Deleting manual demand rows');
                $bar->advance();
                DemandRecord::where('source', 'manual')->delete();

                // Step 4: rebuild all sale-derived demand from sales_records
                $bar->setMessage('Rebuilding sale aggregates');
                $bar->advance();
                $demandService->syncFromSales(
                    Carbon::parse('2000-01-01'),
                    Carbon::today()->addDay()
                );
            });

            $bar->setMessage('Done.');
            $bar->finish();
            $this->newLine(2);

        } catch (\Throwable $e) {
            $bar->finish();
            $this->newLine(2);
            $this->error('Migration FAILED — transaction rolled back. No changes were committed.');
            $this->error($e->getMessage());
            report($e);
            return self::FAILURE;
        }

        // ─── 6. Post-migration verification ────────────────────────────────
        $this->info('Migration complete.');
        $this->newLine();

        $postImported = DemandRecord::where('source', 'imported')->count();
        $postManual   = DemandRecord::where('source', 'manual')->count();
        $postSales    = DemandRecord::where('source', 'sale')->count();
        $postSalesQty = (int) DemandRecord::where('source', 'sale')->sum('quantity');

        $rawSalesImported = SalesRecord::where('source', 'imported')->count();
        $rawSalesPos      = SalesRecord::where('source', 'pos')->count();
        $rawSalesTotalQty = (int) SalesRecord::sum('quantity');

        $checks = [
            [
                'check'    => "demand_records (imported) = 0",
                'value'    => $postImported,
                'expected' => '0',
                'pass'     => $postImported === 0,
            ],
            [
                'check'    => "demand_records (manual) = 0",
                'value'    => $postManual,
                'expected' => '0',
                'pass'     => $postManual === 0,
            ],
            [
                'check'    => "sales_records (imported) — informational",
                'value'    => $rawSalesImported,
                'expected' => '—',
                'pass'     => true,   // always PASS, just display
            ],
            [
                'check'    => "demand_records (sale) total qty = sales_records total qty",
                'value'    => "demand={$postSalesQty} / raw={$rawSalesTotalQty}",
                'expected' => 'match',
                'pass'     => $postSalesQty === $rawSalesTotalQty,
            ],
        ];

        $this->table(
            ['Verification', 'Actual', 'Expected', 'Result'],
            array_map(fn ($c) => [
                $c['check'],
                $c['value'],
                $c['expected'],
                $c['pass'] ? '<fg=green>PASS</>' : '<fg=red>FAIL</>',
            ], $checks)
        );

        $allPassed = !in_array(false, array_column($checks, 'pass'), true);

        $this->newLine();
        $this->line('Final state:');
        $this->table(
            ['Table', 'Source', 'Rows', 'Total Quantity'],
            [
                ['sales_records',  'pos',        $rawSalesPos,       '—'],
                ['sales_records',  'imported',   $rawSalesImported,  '—'],
                ['demand_records', 'sale',       $postSales,         $postSalesQty],
                ['demand_records', 'imported',   $postImported,      '—'],
                ['demand_records', 'manual',     $postManual,        '—'],
            ]
        );

        if ($allPassed) {
            $this->newLine();
            $this->info('✓ All verification checks passed.');
            $this->line('  sales_records is now the complete raw event log (POS + imported).');
            $this->line('  demand_records is now a pure derived aggregate (source=sale).');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->error('⚠ One or more verification checks FAILED.');
        $this->error('  Investigate before proceeding. Restore the DB backup if data is corrupted.');
        return self::FAILURE;
    }
}