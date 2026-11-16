<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private const PER_PAGE = 9;

    public function index(Request $request)
    {
        // Load parent categories with their direct children and products
        $query = Category::whereNull('parent_id')->with(['children.products', 'products']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('children', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('main_group') && $request->main_group !== 'all') {
            $query->where('id', $request->main_group);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $categories = $query->orderBy('name')->paginate(self::PER_PAGE)->withQueryString();
        
        // Only fetch true top-level categories for the dropdown
        $mainCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        // =====================================================
        // AJAX / INFINITE SCROLL RESPONSE
        // =====================================================
        if ($request->ajax() || $request->expectsJson()) {
            $viewData = [
                'categories' => $categories,
                'mainCategories' => $mainCategories
            ];

            $rowsHtml = view('admin.categories.partials.rows', $viewData)->render();
            $cardsHtml = view('admin.categories.partials.cards', $viewData)->render();
            $modalsHtml = view('admin.categories.partials.modals', $viewData)->render();

            return response()->json([
                'rows_html' => $rowsHtml,
                'cards_html' => $cardsHtml,
                'modals_html' => $modalsHtml,

                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total_records' => $categories->total(),
                'first_item' => $categories->firstItem(),
                'last_item' => $categories->lastItem(),

                'next_page_url' => $categories->nextPageUrl(),
                'has_more' => $categories->hasMorePages(),
            ]);
        }

        return view('admin.categories.index', [
            'categories' => $categories,
            'mainCategories' => $mainCategories
        ]);
    }

    // ... (The rest of your controller methods remain exactly the same: show, showSubcategoryDetail, etc.)
    
    public function show(Category $category)
    {
        $category->load(['children.products', 'products']);
        
        return view('admin.categories.show', compact('category'));
    }

    public function showSubcategoryDetail(Request $request, Category $category)
    {
        $productQuery = $category->products();

        if ($request->filled('search')) {
            $search = $request->search;
            $productQuery->where(function($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_sku', 'like', "%{$search}%")
                  ->orWhere('approx_volume', 'like', "%{$search}%");
            });
        }

        $products = $productQuery->orderBy('product_name')->paginate(10)->withQueryString();
        $parentCategory = $category->parent;
        $mainCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.subcategory-detail', [
            'subcategory' => $category,
            'parentCategory' => $parentCategory,
            'products' => $products,
            'mainCategories' => $mainCategories
        ]);
    }

    private function generateUniqueCode($name)
    {
        $baseCode = strtoupper(Str::slug($name, ''));
        if (strlen($baseCode) > 6) {
            $baseCode = substr($baseCode, 0, 6);
        }
        if (empty($baseCode)) {
            $baseCode = 'CAT';
        }

        $code = $baseCode;
        $counter = 1;

        while (Category::where('code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }

        return $code;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'type' => ['required', 'in:product,service'],
            'description' => ['nullable', 'string'],
        ]);

        $parentId = $data['parent_id'] ?? null;

        $exists = Category::where('parent_id', $parentId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($data['name']))])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A category with this name already exists under this group.'])->withInput();
        }

        if ($parentId) {
            $parentCategory = Category::find($parentId);
            if ($parentCategory) {
                $data['type'] = $parentCategory->type;
            }
        }

        $data['code'] = $this->generateUniqueCode($data['name']);
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function storeSubcategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $exists = Category::where('parent_id', $category->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($data['name']))])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'This brand subcategory already exists under this category.'])->withInput();
        }

        $category->children()->create([
            'name' => $data['name'],
            'code' => $this->generateUniqueCode($data['name']),
            'type' => $category->type,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Brand subcategory added successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:product,service'],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function updateSubcategory(Request $request, Category $subcategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $exists = Category::where('parent_id', $subcategory->parent_id)
            ->where('id', '!=', $subcategory->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($data['name']))])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A brand with this name already exists under this group.'])->withInput();
        }

        $subcategory->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Brand subcategory updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete this main category because it still has products assigned to it.');
        }

        foreach ($category->children as $child) {
            if ($child->products()->exists()) {
                return back()->with('error', 'Cannot delete this category because its sub-category/brand ("' . $child->name . '") still has products inside.');
            }
        }

        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete a category that still has subcategories assigned.');
        }

        if ($category->attachment_path) {
            Storage::disk('public')->delete($category->attachment_path);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    public function destroySubcategory(Category $subcategory)
    {
        if ($subcategory->products()->exists()) {
            return back()->with('error', 'Cannot delete this brand subcategory because it still has products assigned to it.');
        }

        $subcategory->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Brand subcategory deleted successfully.');
    }
}