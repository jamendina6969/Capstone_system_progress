<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductItemSku;
use App\Models\Category;
use App\Services\ItemSkuService;
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
    public function transfer(Request $request)
    {
        $request->validate([
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['exists:products,id'],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        Product::whereIn('id', $request->product_ids)->update([
            'category_id' => $request->category_id,
        ]);

        return back()->with('success', 'Products transferred successfully.');
    }

    private function ensureProductImageSchema(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('products') && !\Illuminate\Support\Facades\Schema::hasColumn('products', 'product_image_data')) {
            \Illuminate\Support\Facades\Schema::table('products', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->longText('product_image_data')->nullable()->after('product_image');
                $table->string('product_image_mime', 64)->nullable()->after('product_image_data');
            });
        }
    }

    public function store(Request $request)
    {
        $this->ensureProductImageSchema();
        $request->validate([
            'product_sku' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\-]+$/', 'unique:products,product_sku'],
            'product_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s\-\/#]+$/'],
            'product_description' => ['nullable', 'string', 'regex:/^[A-Za-z0-9\s\.,\-\/#\r\n]+$/'],
            'approx_volume' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s\.,\-\/]+$/'],
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
        ], [
            'product_name.regex' => 'Product name may only contain letters, numbers, spaces, hyphens (-), slashes (/), and # (no emojis or other special characters).',
            'product_sku.regex' => 'Product SKU may only contain letters, numbers, and hyphens (-).',
            'approx_volume.regex' => 'Approx. volume may not contain emojis or special characters.',
            'product_description.regex' => 'Description may not contain emojis or special characters.',
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
            $file = $request->file('product_image');
            [$imageData, $imageMime] = $this->encodeProductImage($file);
            $product->product_image_data = $imageData;
            $product->product_image_mime = $imageMime;
            try {
                $product->product_image = $file->store('products', 'public');
            } catch (\Throwable $e) {
                $product->product_image = 'db:' . $productSku;
            }
        }

        $product->save();

        // Auto-generate serialized unit item_skus for initial Store and Warehouse stock
        $skuService = app(ItemSkuService::class);
        if ((int) $product->store_quantity > 0) {
            $skuService->generateUnits($product, (int) $product->store_quantity, 'store');
        }
        if ((int) $product->warehouse_quantity > 0) {
            $skuService->generateUnits($product, (int) $product->warehouse_quantity, 'warehouse');
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully with serialized item SKUs.');
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
        $this->ensureProductImageSchema();
        $request->validate([
            'product_sku' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9\-]+$/',
                'unique:products,product_sku,' . $product->id,
            ],
            'product_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s\-\/#]+$/'],
            'product_description' => ['nullable', 'string', 'regex:/^[A-Za-z0-9\s\.,\-\/#\r\n]+$/'],
            'approx_volume' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s\.,\-\/]+$/'],
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
        ], [
            'product_name.regex' => 'Product name may only contain letters, numbers, spaces, hyphens (-), slashes (/), and # (no emojis or other special characters).',
            'product_sku.regex' => 'Product SKU may only contain letters, numbers, and hyphens (-).',
            'approx_volume.regex' => 'Approx. volume may not contain emojis or special characters.',
            'product_description.regex' => 'Description may not contain emojis or special characters.',
        ]);

        $categoryId = $request->input('category_id');
        if (empty($categoryId)) {
            $defaultCategory = Category::where('name', 'like', '%Uncategorized%')->first() ?? Category::first();
            $categoryId = $defaultCategory ? $defaultCategory->id : null;
        }

        $oldStoreQty = (int) $product->store_quantity;
        $oldWhQty = (int) $product->warehouse_quantity;

        if ($request->hasFile('product_image')) {
            if (
                $product->product_image &&
                !str_starts_with($product->product_image, 'db:') &&
                Storage::disk('public')->exists($product->product_image)
            ) {
                Storage::disk('public')->delete($product->product_image);
            }

            $file = $request->file('product_image');
            [$imageData, $imageMime] = $this->encodeProductImage($file);
            $product->product_image_data = $imageData;
            $product->product_image_mime = $imageMime;
            try {
                $product->product_image = $file->store('products', 'public');
            } catch (\Throwable $e) {
                $product->product_image = 'db:' . $product->id;
            }
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

        // Keep serialized unit item_skus synchronized with manual stock edits
        $skuService = app(ItemSkuService::class);
        $storeDelta = (int) $product->store_quantity - $oldStoreQty;
        $whDelta = (int) $product->warehouse_quantity - $oldWhQty;
        if ($storeDelta !== 0) {
            $skuService->adjustLocationCount($product, 'store', $storeDelta);
        }
        if ($whDelta !== 0) {
            $skuService->adjustLocationCount($product, 'warehouse', $whDelta);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if (
            $product->product_image &&
            !str_starts_with($product->product_image, 'db:') &&
            Storage::disk('public')->exists($product->product_image)
        ) {
            Storage::disk('public')->delete($product->product_image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Serve persistent DB-backed product image (Wasmer-proof, never disappears on redeploy).
     */
    public function serveImage(Product $product)
    {
        if (!empty($product->product_image_data)) {
            $binary = base64_decode($product->product_image_data, true);
            if ($binary !== false) {
                $mime = $product->product_image_mime ?: 'image/jpeg';
                return response($binary, 200)
                    ->header('Content-Type', $mime)
                    ->header('Cache-Control', 'public, max-age=86400');
            }
        }

        if (
            $product->product_image &&
            !str_starts_with($product->product_image, 'db:') &&
            Storage::disk('public')->exists($product->product_image)
        ) {
            return response()->file(Storage::disk('public')->path($product->product_image), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort(404);
    }

    /**
     * JSON endpoint for the "View Item SKUs" inspector modal.
     */
    public function itemSkus(Request $request, Product $product)
    {
        $status = $request->input('status');
        $location = $request->input('location');
        $search = trim((string) $request->input('search', ''));

        $query = ProductItemSku::with(['purchaseOrder:id,po_number', 'transaction:id,transaction_id', 'supplierReturn:id,return_number'])
            ->where('product_id', $product->id);

        if ($status && in_array($status, ['in_stock', 'sold', 'returned_defective', 'returned_to_supplier'], true)) {
            $query->where('status', $status);
        }
        if ($location && in_array($location, ['store', 'warehouse'], true)) {
            $query->where('location', $location);
        }
        if ($search !== '') {
            $query->where('item_sku', 'like', "%{$search}%");
        }

        $skus = $query->orderBy('id')->limit(200)->get();

        $counts = [
            'store_in_stock' => ProductItemSku::where('product_id', $product->id)->where('location', 'store')->where('status', 'in_stock')->count(),
            'warehouse_in_stock' => ProductItemSku::where('product_id', $product->id)->where('location', 'warehouse')->where('status', 'in_stock')->count(),
            'sold' => ProductItemSku::where('product_id', $product->id)->where('status', 'sold')->count(),
            'returned_defective' => ProductItemSku::where('product_id', $product->id)->where('status', 'returned_defective')->count(),
            'returned_to_supplier' => ProductItemSku::where('product_id', $product->id)->where('status', 'returned_to_supplier')->count(),
        ];

        return response()->json([
            'product' => [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'product_sku' => $product->product_sku,
                'store_quantity' => (int) $product->store_quantity,
                'warehouse_quantity' => (int) $product->warehouse_quantity,
            ],
            'counts' => $counts,
            'skus' => $skus->map(fn ($u) => [
                'id' => $u->id,
                'item_sku' => $u->item_sku,
                'location' => $u->location,
                'status' => $u->status,
                'po_number' => $u->purchaseOrder?->po_number,
                'transaction_id' => $u->transaction?->transaction_id,
                'supplier_return_number' => $u->supplierReturn?->return_number,
                'updated_at' => $u->updated_at?->format('M d, Y h:i A'),
            ]),
        ]);
    }

    /**
     * Resize & compress uploaded product image to max 400x400px JPEG for lightweight DB persistence.
     */
    private function encodeProductImage($file): array
    {
        $realPath = $file->getRealPath();
        $rawMime = $file->getMimeType() ?: 'image/jpeg';

        if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            try {
                $rawBytes = file_get_contents($realPath);
                $src = @imagecreatefromstring($rawBytes);
                if ($src !== false) {
                    $origW = imagesx($src);
                    $origH = imagesy($src);
                    $maxDim = 400;

                    if ($origW > $maxDim || $origH > $maxDim) {
                        $scale = min($maxDim / $origW, $maxDim / $origH);
                        $newW = max(1, (int) round($origW * $scale));
                        $newH = max(1, (int) round($origH * $scale));
                    } else {
                        $newW = $origW;
                        $newH = $origH;
                    }

                    $dst = imagecreatetruecolor($newW, $newH);
                    $white = imagecolorallocate($dst, 255, 255, 255);
                    imagefill($dst, 0, 0, $white);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

                    ob_start();
                    imagejpeg($dst, null, 80);
                    $compressed = ob_get_clean();

                    imagedestroy($src);
                    imagedestroy($dst);

                    if ($compressed !== false && strlen($compressed) > 0) {
                        return [base64_encode($compressed), 'image/jpeg'];
                    }
                }
            } catch (\Throwable $e) {
                // Fall back to raw file bytes below
            }
        }

        return [base64_encode(file_get_contents($realPath)), $rawMime];
    }
}