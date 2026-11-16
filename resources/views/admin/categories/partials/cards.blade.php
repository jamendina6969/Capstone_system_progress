@forelse ($categories as $category)
    <div data-category-row="true" data-category-id="{{ $category->id }}" data-category-page="{{ $categories->currentPage() }}" class="w-full border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl overflow-hidden bg-white dark:bg-[#161B26] flex flex-col shadow-xs p-5 justify-between cursor-pointer hover:border-blue-500/50 transition-all" onclick="window.location='{{ route('admin.categories.show', $category) }}'">
        <div>
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-wider">{{ $category->name }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">MAIN</span>
            </div>
            <p class="text-xs text-gray-400 dark:text-slate-400 font-mono mb-2">Code: {{ $category->code ?? '—' }}</p>
            <div class="mb-3 flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $category->type === 'service' ? 'bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900' : 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 border border-gray-200 dark:border-slate-700' }}">{{ ucfirst($category->type) }}</span>
                <span class="text-xs font-semibold text-gray-500 dark:text-slate-400">Total Products: {{ $category->products->count() + $category->children->sum(fn($child) => $child->products->count()) }}</span>
            </div>
            <div class="text-xs font-semibold text-gray-700 dark:text-slate-300 mb-4">
                Valuation: 
                @php
                    $gridValuation = $category->products->sum(fn($p) => (($p->store_quantity ?? 0) + ($p->warehouse_quantity ?? 0)) * ($p->purchase_cost ?? 0)) + $category->children->sum(fn($sub) => $sub->products->sum(fn($p) => (($p->store_quantity ?? 0) + ($p->warehouse_quantity ?? 0)) * ($p->purchase_cost ?? 0)));
                @endphp
                <span class="text-gray-900 dark:text-white font-bold">₱{{ number_format($gridValuation, 2) }}</span>
            </div>
            @if($category->children->count() > 0)
                <div class="flex flex-wrap items-center gap-1 mb-4" onclick="event.stopPropagation()">
                    <span class="text-[11px] font-semibold text-gray-400 dark:text-slate-500 mr-1">BRANDS:</span>
                    @foreach($category->children as $child)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-50 dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] text-gray-700 dark:text-slate-300">{{ $child->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="pt-3 border-t-2 border-gray-100 dark:border-[#1e2536] flex items-center gap-1.5" onclick="event.stopPropagation()">
            <button type="button" onclick="toggleAddBrandModal({{ $category->id }}, true)" class="flex-1 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-[10px] font-semibold px-2 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer text-center">Brand</button>
            <button type="button" onclick="toggleEditCategoryModal({{ $category->id }}, true)" class="flex-1 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-[10px] font-semibold px-2 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer text-center">Edit</button>
            <button type="button" onclick="toggleDeleteCategoryModal({{ $category->id }}, true)" class="flex-1 bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-[10px] font-semibold px-2 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer text-center">Delete</button>
        </div>
    </div>
@empty
    <p class="col-span-full text-center text-gray-400 dark:text-slate-500 py-12 font-medium">No categories found.</p>
@endforelse