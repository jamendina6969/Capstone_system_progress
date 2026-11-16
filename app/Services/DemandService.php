<?php

namespace App\Services;

use App\Models\DemandRecord;
use App\Models\SalesRecord;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemandService
{
    public function syncFromSales(Carbon $from, Carbon $to): int
    {
        $fromDate = $from->toDateString();
        $toDate   = $to->toDateString();

        return DB::transaction(function () use ($fromDate, $toDate) {
            DemandRecord::where('source', 'sale')
                ->whereBetween('record_date', [$fromDate, $toDate])
                ->delete();

            $aggregates = SalesRecord::query()
                ->select('product_id', 'record_date', DB::raw('SUM(quantity) as quantity'))
                ->whereBetween('record_date', [$fromDate, $toDate])
                ->groupBy('product_id', 'record_date')
                ->get();

            if ($aggregates->isEmpty()) {
                return 0;
            }

            $now = now();
            $rows = $aggregates->map(fn ($a) => [
                'product_id'     => $a->product_id,
                'transaction_id' => null,
                'record_date'    => $a->record_date,
                'quantity'       => $a->quantity,
                'source'         => 'sale',
                'created_at'     => $now,
                'updated_at'     => $now,
            ])->all();

            DemandRecord::insert($rows);

            return count($rows);
        });
    }

    public function refreshForSale(SalesRecord $sale): ?DemandRecord
    {
        $date = $sale->record_date instanceof Carbon
            ? $sale->record_date->toDateString()
            : (string) $sale->record_date;

        $total = (float) SalesRecord::where('product_id', $sale->product_id)
            ->where('record_date', $date)
            ->sum('quantity');

        if ($total <= 0.0) {
            DemandRecord::where('product_id', $sale->product_id)
                ->where('record_date', $date)
                ->where('source', 'sale')
                ->delete();

            return null;
        }

        return DemandRecord::updateOrCreate(
            ['product_id' => $sale->product_id, 'record_date' => $date, 'source' => 'sale'],
            ['quantity' => $total, 'transaction_id' => null]
        );
    }

    public function totalDemand(Product $product, Carbon $from, Carbon $to): float
    {
        return (float) DemandRecord::where('product_id', $product->id)
            ->whereIn('source', ['sale', 'imported'])
            ->whereBetween('record_date', [$from->toDateString(), $to->toDateString()])
            ->sum('quantity');
    }
}