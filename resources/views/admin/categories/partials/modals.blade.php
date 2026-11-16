{{-- resources/views/admin/categories/partials/modals.blade.php --}}

{{-- ================= RENDER ADD BRAND MODALS FOR ALL CATEGORIES ================= --}}
@foreach ($categories as $category)
    <div id="addBrandModal-{{ $category->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <!-- ... paste the rest of your Add Brand modal here ... -->
    </div>
@endforeach

{{-- ================= RENDER EDIT MODALS FOR ALL CATEGORIES ================= --}}
@foreach ($categories as $category)
    <div id="editCategoryModal-{{ $category->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <!-- ... paste the rest of your Edit Category modal here ... -->
    </div>
@endforeach

{{-- ================= RENDER DELETE MODALS FOR ALL CATEGORIES ================= --}}
@foreach ($categories as $category)
    <div id="deleteCategoryModal-{{ $category->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <!-- ... paste the rest of your Delete Category modal here ... -->
    </div>
@endforeach

{{-- ================= RENDER EDIT SUB-CATEGORY MODALS FOR ALL CHILDREN ================= --}}
@foreach ($categories as $category)
    @foreach ($category->children as $child)
        <div id="editSubcategoryModal-{{ $child->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <!-- ... paste the rest of your Edit Subcategory modal here ... -->
        </div>
    @endforeach
@endforeach

{{-- ================= RENDER DELETE SUB-CATEGORY MODALS FOR ALL CHILDREN ================= --}}
@foreach ($categories as $category)
    @foreach ($category->children as $child)
        <div id="deleteSubcategoryModal-{{ $child->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <!-- ... paste the rest of your Delete Subcategory modal here ... -->
        </div>
    @endforeach
@endforeach