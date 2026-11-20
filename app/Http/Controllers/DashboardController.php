<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DemandForecastService;
use App\Services\EoqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index(Request $request, DemandForecastService $forecastService, EoqService $eoqService)
    {
        $user = Auth::user();

        if ($user->role === 'staff') {
            return redirect()->route('pos');
        }

        if ($user->role === 'warehouse_staff') {
            return redirect()->route('inventory.index');
        }

        $data = $this->sharedMetrics();

        if (in_array($user->role, ['admin', 'manager'])) {
            $data = array_merge($data, $this->managementMetrics($request, $forecastService, $eoqService));
        }

        // Fast/Slow filter parameters
        $categoryId = $request->get('product_category');
        $range      = $request->get('fast_slow_range', '3months');
        $dateFrom   = $request->get('fast_slow_date_from');
        $dateTo     = $request->get('fast_slow_date_to');

        if ($range && $range !== 'custom') {
            $dateTo   = now()->toDateString();
            $dateFrom = match ($range) {
                '7days'   => now()->subDays(7)->toDateString(),
                '14days'  => now()->subDays(14)->toDateString(),
                '1month'  => now()->subMonth()->toDateString(),
                '6months' => now()->subMonths(6)->toDateString(),
                '1yr'     => now()->subYear()->toDateString(),
                default   => now()->subMonths(3)->toDateString(),
            };
        }

        // Forecast filter parameters
        $forecastRange    = $request->get('forecast_range', '3months');
        $forecastDateFrom = $request->get('forecast_date_from');
        $forecastDateTo   = $request->get('forecast_date_to');
        $forecastCategory = $request->get('forecast_category');
        $forecastProduct  = $request->get('forecast_product');

        if ($forecastRange && $forecastRange !== 'custom') {
            $forecastDateTo   = now()->toDateString();
            $forecastDateFrom = match ($forecastRange) {
                '7days'   => now()->subDays(7)->toDateString(),
                '14days'  => now()->subDays(14)->toDateString(),
                '1month'  => now()->subMonth()->toDateString(),
                '6months' => now()->subMonths(6)->toDateString(),
                '1yr'     => now()->subYear()->toDateString(),
                default   => now()->subMonths(3)->toDateString(),
            };
        }

        // Chart data (per-product or whole-catalog aggregate)
        $data['forecastChart'] = $forecastService->chartData(
            $forecastProduct ? (int) $forecastProduct : null,
            $forecastRange,
            $forecastRange === 'custom' ? $forecastDateFrom : null,
            $forecastRange === 'custom' ? $forecastDateTo : null
        );

        // Chart subtitle: which product / category is being shown
        $data['forecastProductName'] = 'All Products (Aggregate)';
        if ($forecastProduct) {
            $p = Product::find($forecastProduct);
            if ($p) {
                $data['forecastProductName'] = $p->product_name
                    . ($p->product_sku ? ' (' . $p->product_sku . ')' : '');
            }
        }

        $data['forecastCategoryName'] = $forecastCategory
            ? optional(Category::find($forecastCategory))->name
            : null;

        // Cascading dropdown data
        $allCategories = Category::orderBy('name')->get();
        $data['mainCategories'] = $allCategories->whereNull('parent_id');
        $data['allCategories']  = $allCategories;

        $forecastProductsQuery = Product::orderBy('product_name');
        if ($forecastCategory) {
            $categoryIds = $this->descendantCategoryIds((int) $forecastCategory);
            $forecastProductsQuery->whereIn('category_id', $categoryIds);
        }
        $data['forecastProducts'] = $forecastProductsQuery->get();

        $data['selectedForecastCategory'] = $forecastCategory;
        $data['selectedForecastProduct']  = $forecastProduct;
        $data['forecastRange']            = $forecastRange;
        $data['forecastDateFrom']         = ($forecastRange === 'custom') ? $forecastDateFrom : '';
        $data['forecastDateTo']           = ($forecastRange === 'custom') ? $forecastDateTo   : '';

        // Fast/Slow products
        $data['fastMoving'] = $this->getFastMovingProducts($categoryId, $dateFrom, $dateTo);
        $data['slowMoving'] = $this->getSlowMovingProducts($categoryId, $dateFrom, $dateTo);

        $data['selectedCategory'] = $categoryId;
        $data['fastSlowRange']    = $range;
        $data['fastSlowDateFrom'] = ($range === 'custom') ? $dateFrom : '';
        $data['fastSlowDateTo']   = ($range === 'custom') ? $dateTo   : '';

        if ($user->role === 'admin') {
            $data['totalUsers'] = User::count();
        }

        // =====================================================
        // AJAX Response for Inventory Alerts Infinite Scroll
        // =====================================================
        if ($request->ajax() && $request->has('alert_ajax')) {
            $alerts = $data['lowStockProducts'];
            return response()->json([
                'html' => view('dashboard.partials.alert-rows', ['lowStockProducts' => $alerts])->render(),
                'next_page_url' => $alerts->nextPageUrl(),
                'current_page' => $alerts->currentPage(),
                'last_page' => $alerts->lastPage(),
                'total_records' => $alerts->total(),
                'first_item' => $alerts->firstItem(),
                'last_item' => $alerts->lastItem(),
            ]);
        }

        return view('home', $data);
    }

    private function descendantCategoryIds(int $categoryId): array
    {
        $ids    = [$categoryId];
        $parent = [$categoryId];

        while (!empty($parent)) {
            $children = Category::whereIn('parent_id', $parent)->pluck('id')->all();
            $children = array_values(array_diff($children, $ids));
            if (empty($children)) {
                break;
            }
            $ids    = array_merge($ids, $children);
            $parent = $children;
        }

        return $ids;
    }

    private function sharedMetrics(): array
    {
        $restockNeededCount = Product::where(function ($q) {
            $q->whereRaw('COALESCE(store_quantity, 0) = 0')
              ->orWhereRaw('COALESCE(store_quantity, 0) <= COALESCE(reorder_point, 10)')
              ->orWhereRaw('COALESCE(warehouse_quantity, 0) = 0')
              ->orWhereRaw('COALESCE(warehouse_quantity, 0) <= COALESCE(reorder_point, 10)')
              ->orWhereRaw('(COALESCE(store_quantity, 0) + COALESCE(warehouse_quantity, 0)) <= COALESCE(reorder_point, 10)');
        })->count();

        return [
            'todaySales'         => Transaction::whereDate('created_at', today())->sum('total_amount'),
            'overallSales'       => Transaction::sum('total_amount'),
            // Only count actual POS checkouts (transaction_type = 0) to avoid counting imported CSV rows as daily orders
            'todayTransactions'  => Transaction::whereDate('created_at', today())->where('transaction_type', 0)->count(),
            'totalProducts'      => Product::count(),
            'restockNeededCount' => $restockNeededCount,
        ];
    }

    private function managementMetrics(Request $request, DemandForecastService $forecastService, EoqService $eoqService): array
    {
        $products = Product::with('category')
            ->whereHas('category', fn ($q) => $q->where('type', 'product'))
            ->get();

        $totalProjectedDemand = 0;
        $totalEoq             = 0;

        foreach ($products as $p) {
            $demand               = $forecastService->projectedAnnualDemand($p);
            $totalProjectedDemand += $demand;
            $totalEoq             += $eoqService->calculate($p, $demand);
        }

        $lowStockQuery = Product::with('category')->where(function ($q) {
            $q->whereRaw('COALESCE(store_quantity, 0) = 0')
              ->orWhereRaw('COALESCE(store_quantity, 0) <= COALESCE(reorder_point, 10)')
              ->orWhereRaw('COALESCE(warehouse_quantity, 0) = 0')
              ->orWhereRaw('COALESCE(warehouse_quantity, 0) <= COALESCE(reorder_point, 10)')
              ->orWhereRaw('(COALESCE(store_quantity, 0) + COALESCE(warehouse_quantity, 0)) <= COALESCE(reorder_point, 10)');
        })->get();

        $alertPage    = $request->get('alert_page', 1);
        $alertPerPage = 4;
        $paginatedAlerts = new LengthAwarePaginator(
            $lowStockQuery->forPage($alertPage, $alertPerPage)->values(),
            $lowStockQuery->count(),
            $alertPerPage,
            $alertPage,
            ['path' => $request->url(), 'pageName' => 'alert_page']
        );
        $paginatedAlerts->appends($request->query());

        return [
            'lowStockProducts'     => $paginatedAlerts,
            'totalProjectedDemand' => $totalProjectedDemand,
            'totalEoq'             => $totalEoq,
        ];
    }

    private function getFastMovingProducts($categoryId = null, $dateFrom = null, $dateTo = null)
    {
        $query = Product::with('category');

        if ($categoryId) {
            $categoryIds = $this->descendantCategoryIds((int) $categoryId);
            $query->whereIn('category_id', $categoryIds);
        }

        $products = $query->get()->map(function ($product) use ($dateFrom, $dateTo) {
            $soldQuery = DB::table('transaction_items')->where('product_id', $product->id);
            if ($dateFrom) $soldQuery->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo)   $soldQuery->whereDate('created_at', '<=', $dateTo);

            $soldCount  = (int) $soldQuery->sum('quantity');
            $totalStock = ($product->store_quantity ?? 0) + ($product->warehouse_quantity ?? 0);
            $productSku = $product->product_sku ?? $product->code ?? $product->sku ?? 'N/A';

            return [
                'id'    => $product->id,
                'name'  => $product->product_name ?? 'Sample Product',
                'sku'   => $productSku,
                'sold'  => $soldCount,
                'stock' => $totalStock,
            ];
        })->sortByDesc('sold')->values();

        return $products->take(15)->values()->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });
    }

    private function getSlowMovingProducts($categoryId = null, $dateFrom = null, $dateTo = null)
    {
        $query = Product::with('category');

        if ($categoryId) {
            $categoryIds = $this->descendantCategoryIds((int) $categoryId);
            $query->whereIn('category_id', $categoryIds);
        }

        $products = $query->get()->map(function ($product) use ($dateFrom, $dateTo) {
            $soldQuery = DB::table('transaction_items')->where('product_id', $product->id);
            if ($dateFrom) $soldQuery->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo)   $soldQuery->whereDate('created_at', '<=', $dateTo);

            $soldCount  = (int) $soldQuery->sum('quantity');
            $totalStock = ($product->store_quantity ?? 0) + ($product->warehouse_quantity ?? 0);
            $productSku = $product->product_sku ?? $product->code ?? $product->sku ?? 'N/A';

            return [
                'id'          => $product->id,
                'name'        => $product->product_name ?? 'Slow Product',
                'sku'         => $productSku,
                'sold'        => $soldCount,
                'stock'       => $totalStock,
                'supply_days' => $soldCount > 0 ? 'Active movement' : '180+ days supply',
            ];
        })->sortBy('sold')->values();

        return $products->take(15)->values()->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });
    }
} 