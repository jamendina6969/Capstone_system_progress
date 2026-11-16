<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\EoqSetting;
use App\Models\ProductOrderingCost;
use App\Services\DemandForecastService;
use App\Services\EoqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EoqCalibrationController extends Controller
{
    private const PER_PAGE = 16;

    public function index(Request $request, DemandForecastService $forecastService, EoqService $eoqService)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $searchTerms = array_filter(explode(' ', trim($request->search)));
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQuery) use ($term) {
                        $subQuery->where('product_name', 'like', '%' . $term . '%')
                                ->orWhereHas('category', function ($catQuery) use ($term) {
                                    $catQuery->where('name', 'like', '%' . $term . '%');
                                });
                    });
                }
            });
        }

        if ($request->filled('category_id')) {
            $selectedCategoryId = $request->category_id;
            
            // Check if the selected category is a parent (Main Category)
            $hasChildren = Category::where('parent_id', $selectedCategoryId)->exists();

            if ($hasChildren) {
                // If it's a main category, include products from the main category AND its subcategories
                $childIds = Category::where('parent_id', $selectedCategoryId)->pluck('id')->toArray();
                $query->whereIn('category_id', array_merge([$selectedCategoryId], $childIds));
            } else {
                // If it's a subcategory (Brand), just filter by that specific ID
                $query->where('category_id', $selectedCategoryId);
            }
        }

        // Get the selected timeframe (defaults to annual)
        $timeframe = $request->get('timeframe', 'annual');

        $products = $query->whereHas('category', fn($q) => $q->where('type', 'product'))
            ->orderBy('product_name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $products->getCollection()->transform(function ($product) use ($forecastService, $eoqService, $timeframe) {
            // Calculate baseline annual demand
            $annualDemand = $forecastService->projectedAnnualDemand($product);
            
            // Adjust based on selected timeframe
            $periodDemand = match($timeframe) {
                'daily' => $annualDemand / 365,
                'weekly' => $annualDemand / 52,
                'monthly' => $annualDemand / 12,
                'quarterly' => $annualDemand / 4,
                default => $annualDemand,
            };

            $product->display_demand = $periodDemand;
            // Calculate EOQ for this specific timeframe
            $product->display_eoq = $eoqService->calculate($product, $periodDemand);
            
            // Keep original metrics for reference if needed
            $product->computed_annual_demand = $annualDemand;
            $product->computed_ordering_cost = $eoqService->orderingCost($product);
            $product->computed_holding_cost = $eoqService->holdingCost($product);

            return $product;
        });

        $categories = Category::where('type', 'product')->orderBy('name')->get();
        $settings = EoqSetting::first();

        // =====================================================
        // AJAX / INFINITE SCROLL RESPONSE
        // =====================================================
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('forecast.partials.eoqTable', compact('products', 'timeframe'))->render(),
                'next_page_url' => $products->nextPageUrl(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total_records' => $products->total(),
                'per_page' => $products->perPage(),
                'first_item' => $products->firstItem(),
                'last_item' => $products->lastItem(),
                'has_more' => $products->hasMorePages(),
            ]);
        }

        return view('forecast.eoqCalibration', compact('products', 'categories', 'settings', 'timeframe'));
    }

    public function applyBatch(Request $request)
    {
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['exists:products,id'],
            'ordering_cost' => ['nullable', 'numeric', 'min:0'],
            'holding_cost_override' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (empty($data['ordering_cost']) && empty($data['holding_cost_override'])) {
            return back()->with('error', 'Enter at least one value (ordering cost or holding cost) to apply.');
        }

        DB::transaction(function () use ($data) {
            foreach ($data['product_ids'] as $productId) {
                ProductOrderingCost::create([
                    'product_id' => $productId,
                    'ordering_cost' => $data['ordering_cost'] ?? null,
                    'holding_cost_override' => $data['holding_cost_override'] ?? null,
                    'set_by' => Auth::id(),
                ]);
            }
        });

        $count = count($data['product_ids']);
        return back()->with('success', "Calibration applied to {$count} product(s).");
    }
}