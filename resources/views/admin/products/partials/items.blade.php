{{-- resources/views/admin/products/partials/items.blade.php --}}

@php
    $render = $render ?? 'rows';
@endphp

{{-- =====================================================
     PRODUCT LIST ROWS
===================================================== --}}
@if ($render === 'rows')
    @forelse ($products as $product)
        @php
            $storeQty = $product->store_quantity ?? 0;
            $whQty = $product->warehouse_quantity ?? 0;
            $totalQty = $storeQty + $whQty;
            $reorder = $product->reorder_point ?? 10;

            $storeStatus = $product->store_status ?? ($storeQty <= 0 ? 'Out of Stock' : ($storeQty <= $reorder ? 'Low Stock' : 'In Stock'));
            $whStatus = $product->warehouse_status ?? ($whQty <= 0 ? 'Out of Stock' : ($whQty <= $reorder ? 'Low Stock' : 'In Stock'));
            $totalStatus = $product->total_status ?? ($totalQty <= 0 ? 'Out of Stock' : ($totalQty <= $reorder ? 'Low Stock' : 'In Stock'));

            $statusClasses = [
                'Out of Stock' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-rose-400 border-red-200 dark:border-rose-900',
                'Low Stock' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 border-amber-200 dark:border-amber-900',
                'In Stock' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900',
            ];
        @endphp

        <tr
            data-product-row="true"
            data-product-id="{{ $product->id }}"
            data-product-page="{{ $products->currentPage() }}"
            class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer"
            onclick="openProductDetail({{ $product->id }})"
        >
            <td class="px-6 py-4 align-middle min-w-[180px]">
                <div class="font-semibold text-gray-900 dark:text-white text-sm leading-5">
                    {{ $product->product_name }}
                </div>
                @if ($product->approx_volume)
                    <div class="text-[11px] text-gray-400 mt-0.5 leading-4">
                        {{ $product->approx_volume }}
                    </div>
                @endif
            </td>

            <td class="px-6 py-4 align-middle whitespace-nowrap font-mono text-xs font-semibold text-gray-800 dark:text-slate-200">
                {{ $product->product_sku ?? '—' }}
            </td>

            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 dark:text-slate-400 font-medium">
                {{ $product->category->name ?? '—' }}
            </td>

            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 dark:text-slate-400 font-medium">
                ₱{{ $product->purchase_cost !== null ? number_format($product->purchase_cost, 2) : '—' }}
            </td>

            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 dark:text-slate-400 font-medium">
                ₱{{ $product->selling_price !== null ? number_format($product->selling_price, 2) : '—' }}
            </td>

            <td class="px-6 py-4 align-middle text-sm text-gray-700 dark:text-slate-300 font-semibold">
                {{ $storeQty }}
            </td>

            <td class="px-6 py-4 align-middle text-sm text-gray-700 dark:text-slate-300 font-semibold">
                {{ $whQty }}
            </td>

            <td class="px-6 py-4 align-middle">
                <span class="inline-flex items-center whitespace-nowrap border text-[10px] leading-4 font-semibold px-3 py-1 rounded-full {{ $statusClasses[$storeStatus] ?? $statusClasses['In Stock'] }}">
                    {{ $storeStatus }}
                </span>
            </td>

            <td class="px-6 py-4 align-middle">
                <span class="inline-flex items-center whitespace-nowrap border text-[10px] leading-4 font-semibold px-3 py-1 rounded-full {{ $statusClasses[$whStatus] ?? $statusClasses['In Stock'] }}">
                    {{ $whStatus }}
                </span>
            </td>

            <td class="px-6 py-4 align-middle">
                <span class="inline-flex items-center whitespace-nowrap border text-[10px] leading-4 font-semibold px-3 py-1 rounded-full {{ $statusClasses[$totalStatus] ?? $statusClasses['In Stock'] }}">
                    {{ $totalStatus }}
                </span>
            </td>

            <td class="px-6 py-4 align-middle text-right whitespace-nowrap" onclick="event.stopPropagation()">
                <div class="flex justify-end items-center gap-2">
                    <button
                        type="button"
                        onclick="toggleEditProductModal({{ $product->id }}, true)"
                        class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs cursor-pointer"
                    >
                        Edit
                    </button>

                    <button
                        type="button"
                        onclick="toggleDeleteModal({{ $product->id }}, true)"
                        class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs cursor-pointer"
                    >
                        Delete
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr data-empty-row="true">
            <td colspan="11" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500">
                No products found.
            </td>
        </tr>
    @endforelse
@endif


{{-- =====================================================
     PRODUCT GRID CARDS
===================================================== --}}
@if ($render === 'cards')
    @forelse ($products as $product)
        @php
            $storeQty = $product->store_quantity ?? 0;
            $warehouseQty = $product->warehouse_quantity ?? 0;
            $totalQty = $storeQty + $warehouseQty;
            $reorder = $product->reorder_point ?? 10;

            if ($totalQty <= 0) {
                $statusClass = 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-900';
                $statusText = 'Out of Stock';
            } elseif ($totalQty <= $reorder) {
                $statusClass = 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-900';
                $statusText = 'Low (' . $totalQty . ')';
            } else {
                $statusClass = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900';
                $statusText = 'In Stock (' . $totalQty . ')';
            }
        @endphp

        <div
            data-product-row="true"
            data-product-id="{{ $product->id }}"
            data-product-page="{{ $products->currentPage() }}"
            class="product-grid-card w-full min-w-0 bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl overflow-hidden flex flex-col shadow-xs transition-all duration-200 {{ $totalQty <= 0 ? 'opacity-60' : '' }}"
        >
            {{-- CLICKABLE AREA: image + info --}}
            <div class="cursor-pointer" onclick="openProductDetail({{ $product->id }})">
                {{-- IMAGE (Matching Inventory exactly) --}}
                <div class="w-full h-32 bg-gray-100 dark:bg-[#10141D] flex items-center justify-center text-gray-300 dark:text-slate-600">
                    @if (!empty($product->product_image))
                        <img
                            src="{{ asset('storage/' . $product->product_image) }}"
                            alt="{{ $product->product_name }}"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    @else
                        <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    @endif
                </div>

                {{-- INFO --}}
                <div class="p-3">
                    <p class="font-bold text-gray-900 dark:text-white text-xs line-clamp-1">
                        {{ $product->product_name }}
                    </p>
                    <p class="text-[11px] font-mono text-gray-400 dark:text-slate-500 mt-0.5">
                        {{ $product->product_sku ?? '—' }}
                    </p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-400 mt-0.5 truncate">
                        {{ $product->category->name ?? '—' }}
                    </p>
                    <p class="text-[11px] text-gray-500 dark:text-slate-400 mt-1 font-medium">
                        Store {{ $storeQty }} · WH {{ $warehouseQty }}
                    </p>
                    <div class="mt-2">
                        <span class="inline-flex items-center text-[10px] font-semibold px-2 py-1 rounded-full border whitespace-nowrap {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- PRICE + ACTIONS (pinned to bottom) --}}
            <div class="mt-auto p-3 pt-0">
                <p class="text-sm font-extrabold text-gray-900 dark:text-slate-100 mb-2 truncate">
                    ₱{{ $product->selling_price !== null ? number_format($product->selling_price, 2) : '—' }}
                </p>

                <div class="flex gap-1.5 text-xs font-medium">
                    <button
                        type="button"
                        onclick="toggleEditProductModal({{ $product->id }}, true)"
                        class="flex-1 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-[10px] font-semibold px-2 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer text-center"
                    >
                        Edit
                    </button>
                    <button
                        type="button"
                        onclick="toggleDeleteModal({{ $product->id }}, true)"
                        class="flex-1 bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-[10px] font-semibold px-2 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer text-center"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full py-16 text-center text-gray-400 dark:text-slate-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-sm font-semibold">No products found.</p>
            <p class="text-xs mt-1">Try changing your search or filters.</p>
        </div>
    @endforelse
@endif


{{-- =====================================================
     EDIT AND DELETE MODALS
===================================================== --}}
@if ($render === 'modals')
    @foreach ($products as $product)

        {{-- EDIT MODAL --}}
        <div id="editProductModal-{{ $product->id }}" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-sm items-center justify-center p-4" onclick="if (event.target === this) toggleEditProductModal({{ $product->id }}, false)">
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-2xl shadow-2xl p-6 text-left" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200 dark:border-[#1e2536]">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Edit Product: {{ $product->product_name }}
                    </h3>
                    <button type="button" onclick="toggleEditProductModal({{ $product->id }}, false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] text-xl font-bold">
                        ×
                    </button>
                </div>

                <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Product SKU</label>
                        <input type="text" name="product_sku" value="{{ $product->product_sku }}" readonly class="w-full rounded-lg bg-gray-100 dark:bg-[#161B26]/60 border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-500 dark:text-slate-400 cursor-not-allowed font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Product Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="product_name" value="{{ $product->product_name }}" required class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Category</label>
                            <select name="category_id" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                                <option value="">Uncategorized (default)</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected($product->category_id == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                    @foreach ($category->children as $child)
                                        <option value="{{ $child->id }}" @selected($product->category_id == $child->id)>
                                            — {{ $child->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Approx. Volume</label>
                            <input type="text" name="approx_volume" value="{{ $product->approx_volume }}" placeholder="e.g. 20L, 5 gallons" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Purchase Cost</label>
                            <input type="number" step="0.01" min="0" name="purchase_cost" value="{{ $product->purchase_cost }}" required class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Selling Price</label>
                            <input type="number" step="0.01" min="0" name="selling_price" value="{{ $product->selling_price }}" required class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Store Quantity</label>
                            <input type="number" min="0" name="store_quantity" value="{{ $product->store_quantity }}" required class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Warehouse Quantity</label>
                            <input type="number" min="0" name="warehouse_quantity" value="{{ $product->warehouse_quantity }}" required class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Reorder Point</label>
                        <input type="number" min="0" name="reorder_point" value="{{ $product->reorder_point }}" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Description</label>
                        <textarea name="product_description" rows="2" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">{{ $product->product_description }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Replace Image</label>
                        <input type="file" name="product_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs text-gray-500 dark:text-slate-400">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-[#1e2536]">
                        <button type="button" onclick="toggleEditProductModal({{ $product->id }}, false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                        <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- DELETE MODAL --}}
        <div id="deleteModal-{{ $product->id }}" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-sm items-center justify-center p-4" onclick="if (event.target === this) toggleDeleteModal({{ $product->id }}, false)">
            <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-2xl shadow-2xl p-6 text-left" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200 dark:border-[#1e2536]">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Delete Product
                    </h3>
                    <button type="button" onclick="toggleDeleteModal({{ $product->id }}, false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl text-xl font-bold">
                        ×
                    </button>
                </div>

                <p class="text-sm text-gray-600 dark:text-slate-300 mb-5">
                    Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ $product->product_name }}</strong>? This action cannot be undone.
                </p>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleDeleteModal({{ $product->id }}, false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Delete Product</button>
                    </form>
                </div>
            </div>
        </div>

    @endforeach
@endif