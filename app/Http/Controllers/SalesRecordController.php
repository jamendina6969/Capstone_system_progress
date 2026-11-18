<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\SalesRecord;
use App\Models\Product;
use App\Models\Category;
use App\Services\DemandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SalesRecordController extends Controller
{
    private const PER_PAGE = 12;

    private function getCategoryAndDescendantIds($categoryId)
    {
        $categoryIds = [(int) $categoryId];
        $parentIds = [(int) $categoryId];

        while (!empty($parentIds)) {
            $childIds = Category::whereIn('parent_id', $parentIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $childIds = array_values(array_diff($childIds, $categoryIds));

            if (empty($childIds)) {
                break;
            }

            $categoryIds = array_merge($categoryIds, $childIds);
            $parentIds = $childIds;
        }

        return array_values(array_unique($categoryIds));
    }

    private function salesQuery()
    {
        $query = Transaction::whereIn('transaction_type', [0, 1]);

        // Restrict staff to only their own sales
        if (Auth::check() && Auth::user()->role === 'staff') {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = Transaction::with([
                'user',
                'transactionItems.product.category.parent'
            ])
            ->whereIn('transaction_type', [0, 1]);

        // Restrict staff to only their own sales
        if (Auth::user()->role === 'staff') {
            $query->where('user_id', Auth::id());
        }

        // Search transaction ID, product, category, OR deleted user's name.
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('cashier_name', 'like', "%{$search}%")
                    ->orWhereHas('transactionItems', function ($itemQuery) use ($search) {
                        $itemQuery->where('product_name', 'like', "%{$search}%")
                            ->orWhere('main_category_name', 'like', "%{$search}%")
                            ->orWhere('brand_category_name', 'like', "%{$search}%")
                            ->orWhereHas('product.category', function ($catQuery) use ($search) {
                                $catQuery->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // Include products under the selected category and its descendants.
        if ($request->filled('category_id')) {
            $categoryIds = $this->getCategoryAndDescendantIds(
                $request->category_id
            );

            $query->whereHas('transactionItems', function ($q) use ($categoryIds) {
                $q->whereHas('product', function ($subQ) use ($categoryIds) {
                    $subQ->whereIn('category_id', $categoryIds);
                });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Ignore unsupported source values instead of treating them as POS.
        if (in_array($request->input('source'), ['pos', 'imported'], true)) {
            $query->where(
                'transaction_type',
                $request->source === 'imported' ? 1 : 0
            );
        }

        $records = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $categories = Category::whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        // Sales summary cards.
        $todayTotal = $this->salesQuery()
            ->whereDate('created_at', today())
            ->sum('total_amount');

        $weekTotal = $this->salesQuery()
            ->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->sum('total_amount');

        $monthTotal = $this->salesQuery()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $yearTotal = $this->salesQuery()
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $overallTotal = $this->salesQuery()
            ->sum('total_amount');

        $yesterdayTotal = $this->salesQuery()
            ->whereDate('created_at', today()->subDay())
            ->sum('total_amount');

        $lastWeekTotal = $this->salesQuery()
            ->whereBetween('created_at', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            ])
            ->sum('total_amount');

        $lastMonth = now()->subMonth();

        $lastMonthTotal = $this->salesQuery()
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->sum('total_amount');

        $lastYearTotal = $this->salesQuery()
            ->whereYear('created_at', now()->subYear()->year)
            ->sum('total_amount');

        $calcChange = function ($current, $previous) {
            if ((float) $previous == 0) {
                return (float) $current > 0 ? 100.0 : 0.0;
            }

            return round(
                (($current - $previous) / $previous) * 100,
                1
            );
        };

        $summary = [
            'today' => $todayTotal,
            'today_change' => $calcChange($todayTotal, $yesterdayTotal),

            'week' => $weekTotal,
            'week_change' => $calcChange($weekTotal, $lastWeekTotal),

            'month' => $monthTotal,
            'month_change' => $calcChange($monthTotal, $lastMonthTotal),

            'year' => $yearTotal,
            'year_change' => $calcChange($yearTotal, $lastYearTotal),

            'overall' => $overallTotal,
        ];

        // AJAX requests return only the requested page's table rows.
        if ($request->ajax()) {
            return response()->json([
                'html' => view('sales.partials.rows', compact('records'))->render(),
                'next_page_url' => $records->nextPageUrl(),
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'total_records' => $records->total(),
                'per_page' => $records->perPage(),
                'first_item' => $records->firstItem(),
                'last_item' => $records->lastItem(),
                'has_more' => $records->hasMorePages(),
            ]);
        }

        return view('sales.records', compact(
            'records',
            'summary',
            'categories'
        ));
    }

    public function syncRecords()
    {
        $items = TransactionItem::where(function ($q) {
                $q->whereNull('main_category_name')
                    ->orWhereNull('product_sku')
                    ->orWhereNull('item_sku');
            })
            ->with('product.category.parent')
            ->get();

        $updatedCount = 0;

        foreach ($items as $item) {
            $updates = [];

            if (
                empty($item->main_category_name) &&
                $item->product &&
                $item->product->category
            ) {
                $cat = $item->product->category;

                $updates['main_category_name'] = $cat->parent
                    ? $cat->parent->name
                    : $cat->name;

                $updates['brand_category_name'] = $cat->parent
                    ? $cat->name
                    : null;
            }

            if (empty($item->product_sku) && $item->product) {
                $updates['product_sku'] = $item->product->product_sku;
            }

            if (empty($item->item_sku) && $item->product) {
                $updates['item_sku'] =
                    $item->product->product_sku . '-SYNC-' . uniqid();
            }

            if (!empty($updates)) {
                $item->update($updates);
                $updatedCount++;
            }
        }

        return back()->with(
            'success',
            "Successfully synced categories and SKUs for {$updatedCount} past transaction records."
        );
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'product_name',
                'quantity',
                'date',
                'purchase_cost',
                'selling_price',
                'main_category',
                'brand_category'
            ]);

            fputcsv($file, [
                'Sample Product A',
                '10',
                now()->format('Y-m-d'),
                '100.00',
                '150.00',
                'Electronics',
                'Samsung'
            ]);

            fputcsv($file, [
                'Sample Product B',
                '5',
                now()->format('Y-m-d'),
                '50.00',
                '99.50',
                'Groceries',
                'Nestle'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function showImport()
    {
        $categories = Category::where('type', 'product')
            ->orderBy('name')
            ->get();

        return view('sales.import', compact('categories'));
    }

    public function processImport(Request $request, DemandService $demandService)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'auto_create_products' => ['nullable', 'boolean'],
            'default_category_id' => [
                'nullable',
                'integer',
                'exists:categories,id'
            ],
        ]);

        $autoCreate = $request->boolean('auto_create_products');

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Unable to open the uploaded CSV file.');
        }

        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);

            return back()->with('error', 'The uploaded CSV file is empty.');
        }

        // Remove a possible UTF-8 BOM from the first column.
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

        $header = array_map(
            fn ($h) => strtolower(trim($h)),
            $header
        );

        $required = [
            'product_name',
            'quantity',
            'date',
            'purchase_cost',
            'selling_price'
        ];

        foreach ($required as $col) {
            if (!in_array($col, $header, true)) {
                fclose($handle);

                return back()->with(
                    'error',
                    "CSV is missing required column: {$col}. Please download the latest template."
                );
            }
        }

        $imported = 0;
        $skipped = [];
        $rowNum = 1;

        $fallbackCategoryId = $request->input('default_category_id');

        if (!$fallbackCategoryId && $autoCreate) {
            $fallbackCategory = Category::firstOrCreate(
                [
                    'name' => 'Uncategorized',
                    'parent_id' => null
                ],
                [
                    'type' => 'product',
                    'code' => 'UNCAT'
                ]
            );

            $fallbackCategoryId = $fallbackCategory->id;
        }

        $importerName = Auth::user()->firstname . ' ' . Auth::user()->lastname;

        try {
            DB::transaction(function () use (
                $handle,
                $header,
                $autoCreate,
                $fallbackCategoryId,
                &$imported,
                &$skipped,
                &$rowNum,
                $importerName,
                $demandService
            ) {
                $recordsToInsert = [];
                $importedSignatures = []; // Tracks duplicates within the CSV itself
                $minDate = null;
                $maxDate = null;

                while (($row = fgetcsv($handle)) !== false) {
                    $rowNum++;

                    if (count($row) !== count($header)) {
                        $skipped[] = "Row {$rowNum}: column count does not match the CSV header";
                        continue;
                    }

                    $data = array_combine($header, $row);

                    $productName = trim($data['product_name'] ?? '');
                    $quantityRaw = trim($data['quantity'] ?? '');
                    $dateRaw = trim($data['date'] ?? '');

                    $purchaseCostRaw = trim($data['purchase_cost'] ?? '');
                    $sellingPriceRaw = trim($data['selling_price'] ?? '');

                    $mainCatName = trim($data['main_category'] ?? '');
                    $brandCatName = trim($data['brand_category'] ?? '');

                    if (
                        $productName === '' ||
                        !is_numeric($quantityRaw) ||
                        (int) $quantityRaw <= 0 ||
                        (string) (int) $quantityRaw !== $quantityRaw ||
                        $dateRaw === '' ||
                        !is_numeric($purchaseCostRaw) ||
                        !is_numeric($sellingPriceRaw) ||
                        (float) $purchaseCostRaw < 0 ||
                        (float) $sellingPriceRaw < 0
                    ) {
                        $skipped[] = "Row {$rowNum}: missing or invalid data";
                        continue;
                    }

                    $quantity = (int) $quantityRaw;
                    $purchaseCost = (float) $purchaseCostRaw;
                    $sellingPrice = (float) $sellingPriceRaw;

                    try {
                        if (str_contains($dateRaw, '/')) {
                            $parts = explode('/', $dateRaw);
                            if (count($parts) === 3) {
                                if ((int)$parts[0] > 12) {
                                    $date = Carbon::createFromFormat('d/m/Y', $dateRaw);
                                } else {
                                    try {
                                        $date = Carbon::createFromFormat('d/m/Y', $dateRaw);
                                    } catch (\Exception $ex) {
                                        $date = Carbon::createFromFormat('m/d/Y', $dateRaw);
                                    }
                                }
                            } else {
                                $date = Carbon::parse($dateRaw);
                            }
                        } else {
                            $date = Carbon::parse($dateRaw);
                        }
                    } catch (\Exception $e) {
                        $skipped[] = "Row {$rowNum}: invalid date '{$dateRaw}'";
                        continue;
                    }

                    $product = Product::whereRaw(
                        'LOWER(TRIM(product_name)) = ?',
                        [strtolower($productName)]
                    )->first();

                    if (!$product) {
                        if (!$autoCreate) {
                            $skipped[] = "Row {$rowNum}: product '{$productName}' not found";
                            continue;
                        }

                        $categoryId = $fallbackCategoryId;

                        if ($mainCatName !== '') {
                            $mainCat = Category::firstOrCreate(
                                [
                                    'name' => $mainCatName,
                                    'parent_id' => null
                                ],
                                [
                                    'type' => 'product',
                                    'code' => strtoupper(substr(md5($mainCatName), 0, 6))
                                ]
                            );

                            if ($brandCatName !== '') {
                                $brandCat = Category::firstOrCreate(
                                    [
                                        'name' => $brandCatName,
                                        'parent_id' => $mainCat->id
                                    ],
                                    [
                                        'type' => 'product',
                                        'code' => strtoupper(
                                            substr(md5($mainCatName . $brandCatName), 0, 6)
                                        )
                                    ]
                                );

                                $categoryId = $brandCat->id;
                            } else {
                                $categoryId = $mainCat->id;
                            }
                        }

                        $prefix = strtoupper(
                            substr(
                                preg_replace('/[^A-Za-z]/', '', $productName),
                                0,
                                3
                            )
                        );

                        if (strlen($prefix) < 3) {
                            $prefix = str_pad($prefix, 3, 'X');
                        }

                        do {
                            $sku = $prefix . '-' .
                                strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
                        } while (Product::where('product_sku', $sku)->exists());

                        $product = Product::create([
                            'product_sku' => $sku,
                            'product_name' => $productName,
                            'product_description' =>
                                'Auto-created from sales import on ' . now()->format('M d, Y'),
                            'purchase_cost' => $purchaseCost,
                            'selling_price' => $sellingPrice,
                            'store_quantity' => 0,
                            'warehouse_quantity' => 0,
                            'category_id' => $categoryId,
                        ]);
                    }

                    $mainCategorySnapshot = $mainCatName;
                    $brandCategorySnapshot = $brandCatName;

                    if (
                        $mainCategorySnapshot === '' &&
                        $product->category
                    ) {
                        $productCategory = $product->category;

                        $mainCategorySnapshot = $productCategory->parent
                            ? $productCategory->parent->name
                            : $productCategory->name;

                        $brandCategorySnapshot = $productCategory->parent
                            ? $productCategory->name
                            : '';
                    }

                    $subtotal = $sellingPrice * $quantity;

                    // === DEDUP: Check memory first, then DB ===
                    $signature = $product->id . '|' . $date->toDateString() . '|' . $quantity;
                    
                    if (isset($importedSignatures[$signature])) {
                        $skipped[] = "Row {$rowNum}: duplicate entry within this CSV file";
                        continue;
                    }

                    $alreadyImported = SalesRecord::where('product_id', $product->id)
                        ->where('record_date', $date->toDateString())
                        ->where('quantity', $quantity)
                        ->where('source', 'imported')
                        ->exists();

                    if ($alreadyImported) {
                        $skipped[] = "Row {$rowNum}: possible duplicate — {$productName} × {$quantity} on {$date->format('Y-m-d')} already imported";
                        continue;
                    }

                    // Mark as seen in memory
                    $importedSignatures[$signature] = true;

                    // Track date range for bulk sync
                    if ($minDate === null || $date->lt($minDate)) {
                        $minDate = $date->copy();
                    }
                    if ($maxDate === null || $date->gt($maxDate)) {
                        $maxDate = $date->copy();
                    }

                    $transaction = new Transaction();
                    $transaction->user_id = Auth::id();
                    $transaction->cashier_name = $importerName;
                    $transaction->transaction_id = 'IMP-' . $date->format('Ymd') . '-' . uniqid();
                    $transaction->transaction_type = 1;
                    $transaction->total_amount = $subtotal;
                    $transaction->amount_paid = $subtotal;
                    $transaction->amount_change = 0;
                    $transaction->created_at = $date;
                    $transaction->updated_at = $date;
                    $transaction->save();

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'product_sku' => $product->product_sku,
                        'item_sku' => $product->product_sku . '-IMP-' . uniqid(),
                        'product_name' => $product->product_name,
                        'main_category_name' => $mainCategorySnapshot,
                        'brand_category_name' => $brandCategorySnapshot,
                        'selling_price' => $sellingPrice,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal,
                    ]);

                    $recordsToInsert[] = [
                        'product_id' => $product->id,
                        'transaction_id' => $transaction->id,
                        'record_date' => $date->toDateString(),
                        'quantity' => $quantity,
                        'source' => 'imported',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $imported++;
                }

                if (!empty($recordsToInsert)) {
                    SalesRecord::withoutEvents(function () use ($recordsToInsert) {
                        foreach (array_chunk($recordsToInsert, 500) as $chunk) {
                            SalesRecord::insert($chunk);
                        }
                    });

                    if ($minDate && $maxDate) {
                        $demandService->syncFromSales(
                            $minDate->startOfDay(),
                            $maxDate->endOfDay()
                        );
                    }
                }
            });
        } catch (\Throwable $e) {
            fclose($handle);
            throw $e;
        }

        fclose($handle);

        $message = "{$imported} sales record(s) imported successfully.";

        if (count($skipped) > 0) {
            $message .= ' ' . count($skipped) . ' row(s) skipped.';
        }

        return redirect()
            ->route('sales.records')
            ->with('success', $message)
            ->with('skipped', $skipped);
    }
}