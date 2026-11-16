@forelse ($categories as $category)
    <tr data-category-row="true" data-category-id="{{ $category->id }}" data-category-page="{{ $categories->currentPage() }}" class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors align-top">
        <td class="px-8 py-4 space-y-2 cursor-pointer hover:bg-gray-100/60 dark:hover:bg-[#1e2536]/60 transition-colors" onclick="window.location='{{ route('admin.categories.show', $category) }}'">
            <div class="flex items-center gap-2">
                <span class="font-bold text-gray-900 dark:text-white uppercase tracking-wider hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $category->name }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">MAIN CATEGORY</span>
            </div>
            @if($category->children->count() > 0)
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="text-xs font-semibold text-gray-400 dark:text-slate-500 mr-1">BRANDS:</span>
                    @foreach($category->children as $child)
                        <div class="group inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] text-gray-700 dark:text-slate-300 shadow-2xs hover:border-slate-400 dark:hover:border-slate-500 transition-colors" onclick="event.stopPropagation()">
                            <a href="{{ route('admin.categories.subcategories.show', $child) }}" class="hover:text-blue-600 dark:hover:text-blue-400 hover:underline flex items-center gap-1" title="View Products">
                                {{ $child->name }}
                                @if($child->code) <span class="text-[10px] text-gray-400 dark:text-slate-500 font-mono">({{ $child->code }})</span> @endif
                            </a>
                            <div class="flex items-center gap-1 ml-1 border-l border-gray-300 dark:border-gray-600 pl-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" onclick="toggleEditSubcategoryModal({{ $child->id }}, true)" class="text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer" title="Edit"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                <button type="button" onclick="toggleDeleteSubcategoryModal({{ $child->id }}, true)" class="text-gray-500 hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer" title="Delete"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </td>
        <td class="px-8 py-4 font-mono text-xs text-gray-600 dark:text-slate-400">{{ $category->code ?? '—' }}</td>
        <td class="px-8 py-4">
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $category->type === 'service' ? 'bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900' : 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 border border-gray-200 dark:border-slate-700' }}">
                {{ ucfirst($category->type) }}
            </span>
        </td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-semibold">{{ $category->products->count() + $category->children->sum(fn($child) => $child->products->count()) }}</td>
        <td class="px-8 py-4 font-semibold text-gray-900 dark:text-slate-100">
            @php
                $totalValuation = $category->products->sum(fn($p) => (($p->store_quantity ?? 0) + ($p->warehouse_quantity ?? 0)) * ($p->purchase_cost ?? 0)) + $category->children->sum(fn($sub) => $sub->products->sum(fn($p) => (($p->store_quantity ?? 0) + ($p->warehouse_quantity ?? 0)) * ($p->purchase_cost ?? 0)));
            @endphp
            ₱{{ number_format($totalValuation, 2) }}
        </td>
        <td class="px-8 py-4 text-right">
            <div class="flex justify-end items-center gap-2">
                <button type="button" onclick="toggleAddBrandModal({{ $category->id }}, true)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-3.5 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer whitespace-nowrap">Brand</button>
                <button type="button" onclick="toggleEditCategoryModal({{ $category->id }}, true)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-3.5 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Edit</button>
                <button type="button" onclick="toggleDeleteCategoryModal({{ $category->id }}, true)" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-3.5 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Delete</button>
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true"><td colspan="6" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500 font-medium">No categories found.</td></tr>
@endforelse