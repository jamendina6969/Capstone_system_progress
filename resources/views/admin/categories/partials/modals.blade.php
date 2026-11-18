{{-- resources/views/admin/categories/partials/modals.blade.php --}}

{{-- ================= RENDER ADD BRAND MODALS FOR ALL CATEGORIES ================= --}}
@foreach ($categories as $category)
    <div id="addBrandModal-{{ $category->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-6 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add Brand under {{ $category->name }}</h3>
                <button type="button" onclick="toggleAddBrandModal({{ $category->id }}, false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $category->id }}">
                <input type="hidden" name="type" value="{{ $category->type }}">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Brand Name</label>
                    <input type="text" name="name" required placeholder="e.g. Samsung, AquaFlask" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Brand description..." class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400"></textarea>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleAddBrandModal({{ $category->id }}, false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all shadow-xs cursor-pointer">Save Brand</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- ================= RENDER EDIT MODALS FOR ALL CATEGORIES ================= --}}
@foreach ($categories as $category)
    <div id="editCategoryModal-{{ $category->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-6 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Category: {{ $category->name }}</h3>
                <button type="button" onclick="toggleEditCategoryModal({{ $category->id }}, false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Category Name</label>
                    <input type="text" name="name" value="{{ $category->name }}" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Type</label>
                    <select name="type" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                        <option value="product" {{ $category->type === 'product' ? 'selected' : '' }}>Product</option>
                        <option value="service" {{ $category->type === 'service' ? 'selected' : '' }}>Service</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="description" rows="3" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">{{ $category->description }}</textarea>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleEditCategoryModal({{ $category->id }}, false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all shadow-xs cursor-pointer">Update Category</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- ================= RENDER DELETE MODALS FOR ALL CATEGORIES ================= --}}
@foreach ($categories as $category)
    <div id="deleteCategoryModal-{{ $category->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-4 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Delete Category</h3>
                <button type="button" onclick="toggleDeleteCategoryModal({{ $category->id }}, false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <p class="text-sm text-gray-600 dark:text-slate-300 mb-6 font-medium">
                Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ $category->name }}</strong>? This will also affect associated subcategories and products.
            </p>
            <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="toggleDeleteCategoryModal({{ $category->id }}, false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors cursor-pointer">Cancel</button>
                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all shadow-xs cursor-pointer">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
@endforeach

{{-- ================= RENDER EDIT SUB-CATEGORY MODALS FOR ALL CHILDREN ================= --}}
@foreach ($categories as $category)
    @foreach ($category->children as $child)
        <div id="editSubcategoryModal-{{ $child->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative w-full max-w-xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between pb-6 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Brand: {{ $child->name }}</h3>
                    <button type="button" onclick="toggleEditSubcategoryModal({{ $child->id }}, false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors text-xl font-bold">&times;</button>
                </div>
                <form action="{{ route('admin.categories.subcategories.update', $child) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Brand Name</label>
                        <input type="text" name="name" value="{{ $child->name }}" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea name="description" rows="3" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">{{ $child->description }}</textarea>
                    </div>
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                        <button type="button" onclick="toggleEditSubcategoryModal({{ $child->id }}, false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all shadow-xs cursor-pointer">Save Brand</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endforeach

{{-- ================= RENDER DELETE SUB-CATEGORY MODALS FOR ALL CHILDREN ================= --}}
@foreach ($categories as $category)
    @foreach ($category->children as $child)
        <div id="deleteSubcategoryModal-{{ $child->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between pb-4 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Delete Brand</h3>
                    <button type="button" onclick="toggleDeleteSubcategoryModal({{ $child->id }}, false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors text-xl font-bold cursor-pointer">&times;</button>
                </div>
                <p class="text-sm text-gray-600 dark:text-slate-300 mb-6 font-medium">
                    Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ $child->name }}</strong>? This action cannot be undone.
                </p>
                <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleDeleteSubcategoryModal({{ $child->id }}, false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors cursor-pointer">Cancel</button>
                    <form action="{{ route('admin.categories.subcategories.destroy', $child) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all shadow-xs cursor-pointer">Delete Brand</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endforeach