<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private const PER_PAGE = 24;

    /**
     * Return the given category ID plus all descendant category IDs.
     */
    private function getCategoryAndDescendantIds($categoryId): array
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

    /**
     * Top-level product categories with ordered children.
     */
    private function getProductCategories()
    {
        return Category::whereNull('parent_id')
            ->where('type', 'product')
            ->with(['children' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();
    }

    /**
     * Render the product index page OR return a JSON payload for the
     * infinite-scroll / load-more client.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // SEARCH
        if ($request->filled('search')) {
            $searchTerm = trim($request->input('search'));

            $query->where(function ($q) use ($searchTerm) {
                $q->where('product_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('product_sku', 'like', '%' . $searchTerm . '%');
            });
        }

        // CATEGORY FILTER INCLUDING DESCENDANTS
        if ($request->filled('category_id')) {
            $categoryIds = $this->getCategoryAndDescendantIds(
                $request->input('category_id')
            );

            $query->whereIn('category_id', $categoryIds);
        }

        // --- STATUS FILTERS ---
        if ($request->filled('status')) {
            $reorder = 'COALESCE(reorder_point, 10)';
            $store = 'COALESCE(store_quantity, 0)';
            $wh = 'COALESCE(warehouse_quantity, 0)';
            $total = "($store + $wh)";

            if ($request->status === 'store_out') {
                $query->whereRaw("$store <= 0");
            } elseif ($request->status === 'store_low') {
                $query->whereRaw("$store > 0")->whereRaw("$store <= $reorder");
            } elseif ($request->status === 'wh_out') {
                $query->whereRaw("$wh <= 0");
            } elseif ($request->status === 'wh_low') {
                $query->whereRaw("$wh > 0")->whereRaw("$wh <= $reorder");
            } elseif ($request->status === 'total_out') {
                $query->whereRaw("$total <= 0");
            } elseif ($request->status === 'total_low') {
                $query->whereRaw("$total > 0")->whereRaw("$total <= $reorder");
            }
        }

        // ALLOWED SORT COLUMNS
        $allowedSorts = [
            'product_name',
            'product_sku',
            'store_quantity',
            'warehouse_quantity',
            'selling_price',
            'purchase_cost',
            'created_at',
        ];

        $sortField = $request->input('sort', 'product_name');
        $sortDirection = $request->input('direction', 'asc');

        if (!in_array($sortField, $allowedSorts, true)) {
            $sortField = 'product_name';
        }

        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

        // STABLE SORTING
        $query->orderBy($sortField, $sortDirection)
            ->orderBy('id', $sortDirection);

        // PAGINATION
        $products = $query
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $categories = $this->getProductCategories();

        // AJAX / INFINITE SCROLL RESPONSE
        if ($request->ajax() || $request->expectsJson()) {
            $viewData = [
                'products' => $products,
                'categories' => $categories,
            ];

            $rowsHtml = view(
                'admin.products.partials.items',
                array_merge($viewData, ['render' => 'rows'])
            )->render();

            $cardsHtml = view(
                'admin.products.partials.items',
                array_merge($viewData, ['render' => 'cards'])
            )->render();

            $modalsHtml = view(
                'admin.products.partials.items',
                array_merge($viewData, ['render' => 'modals'])
            )->render();

            return response()->json([
                'rows_html' => $rowsHtml,
                'cards_html' => $cardsHtml,
                'modals_html' => $modalsHtml,

                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total_records' => $products->total(),
                'first_item' => $products->firstItem(),
                'last_item' => $products->lastItem(),

                'next_page_url' => $products->nextPageUrl(),
                'has_more' => $products->hasMorePages(),
            ]);
        }

        // FULL PAGE RESPONSE
        $autoProductSku = 'PRD-' . strtoupper(substr(uniqid(), -6));

        return view('admin.products.index', compact(
            'products',
            'categories',
            'autoProductSku'
        ));
    }

    public function create()
    {
        $categories = $this->getProductCategories();

        $autoProductSku = 'PRD-' . strtoupper(substr(uniqid(), -6));

        return view('admin.products.create', compact(
            'categories',
            'autoProductSku'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_sku' => ['nullable', 'string', 'max:255', 'unique:products,product_sku'],
            'product_name' => ['required', 'string', 'max:255'],
            'product_description' => ['nullable', 'string'],
            'approx_volume' => ['nullable', 'string', 'max:255'],
            'purchase_cost' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'store_quantity' => ['required', 'integer', 'min:0'],
            'warehouse_quantity' => ['required', 'integer', 'min:0'],
            'reorder_point' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'product_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
            ],
        ]);

        // Fallback category logic: if empty, pick "Uncategorized" or the first available category
        $categoryId = $request->input('category_id');
        if (empty($categoryId)) {
            $defaultCategory = Category::where('name', 'like', '%Uncategorized%')->first() ?? Category::first();
            $categoryId = $defaultCategory ? $defaultCategory->id : null;
        }

        // SKU generation logic: use user input if filled, otherwise auto-generate
        if ($request->filled('product_sku')) {
            $productSku = trim($request->product_sku);
        } else {
            $prefix = 'PRD';

            if ($categoryId) {
                $category = Category::find($categoryId);

                if ($category) {
                    $cleaned = strtoupper(
                        preg_replace('/[^a-zA-Z]/', '', $category->name)
                    );

                    if (strlen($cleaned) >= 3) {
                        $prefix = substr($cleaned, 0, 3);
                    } elseif (strlen($cleaned) > 0) {
                        $prefix = $cleaned;
                    }
                }
            }

            $productSku = $prefix . '-' . strtoupper(substr(uniqid(), -6));
        }

        $product = new Product();

        $product->product_sku = $productSku;
        $product->product_name = $request->product_name;
        $product->product_description = $request->product_description ?? '';
        $product->approx_volume = $request->approx_volume ?? '';
        $product->purchase_cost = $request->purchase_cost;
        $product->selling_price = $request->selling_price;
        $product->store_quantity = $request->store_quantity;
        $product->warehouse_quantity = $request->warehouse_quantity;
        $product->reorder_point = $request->reorder_point ?? 0;
        $product->category_id = $categoryId; // Safe fallback ID preventing null constraint errors

        if ($request->hasFile('product_image')) {
            $product->product_image = $request
                ->file('product_image')
                ->store('products', 'public');
        }

        $product->save();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = $this->getProductCategories();

        return view('admin.products.edit', compact(
            'product',
            'categories'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_sku' => [
                'required',
                'string',
                'max:255',
                'unique:products,product_sku,' . $product->id,
            ],
            'product_name' => ['required', 'string', 'max:255'],
            'product_description' => ['nullable', 'string'],
            'approx_volume' => ['nullable', 'string', 'max:255'],
            'purchase_cost' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'store_quantity' => ['required', 'integer', 'min:0'],
            'warehouse_quantity' => ['required', 'integer', 'min:0'],
            'reorder_point' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'product_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
            ],
        ]);

        $categoryId = $request->input('category_id');
        if (empty($categoryId)) {
            $defaultCategory = Category::where('name', 'like', '%Uncategorized%')->first() ?? Category::first();
            $categoryId = $defaultCategory ? $defaultCategory->id : null;
        }

        if ($request->hasFile('product_image')) {
            if (
                $product->product_image &&
                Storage::disk('public')->exists($product->product_image)
            ) {
                Storage::disk('public')->delete($product->product_image);
            }

            $product->product_image = $request
                ->file('product_image')
                ->store('products', 'public');
        }

        $product->product_sku = $request->product_sku;
        $product->product_name = $request->product_name;
        $product->product_description = $request->product_description ?? '';
        $product->approx_volume = $request->approx_volume ?? '';
        $product->purchase_cost = $request->purchase_cost;
        $product->selling_price = $request->selling_price;
        $product->store_quantity = $request->store_quantity;
        $product->warehouse_quantity = $request->warehouse_quantity;
        $product->reorder_point = $request->reorder_point ?? 0;
        $product->category_id = $categoryId;

        $product->save();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if (
            $product->product_image &&
            Storage::disk('public')->exists($product->product_image)
        ) {
            Storage::disk('public')->delete($product->product_image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}