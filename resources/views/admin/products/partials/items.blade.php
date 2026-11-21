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
                <div class="inline-flex justify-end items-center" data-row-actions>
                    <button type="button" onclick="openRowActionMenu(event, this)"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 transition-all shadow-xs cursor-pointer"
                            aria-label="Actions" title="Actions">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </button>
                    <div class="row-action-items hidden">
                        <button type="button" data-tone="blue" data-label="View Item SKUs"
                                onclick="openItemSkusModal({{ $product->id }})">View Item SKUs</button>
                        <button type="button" data-tone="default" data-label="Edit Product"
                                onclick="toggleEditProductModal({{ $product->id }}, true)">Edit Product</button>
                        <button type="button" data-tone="rose" data-label="Delete Product"
                                onclick="toggleDeleteModal({{ $product->id }}, true)">Delete Product</button>
                    </div>
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
     PRODUCT GRID CARDS (Official Hydrotech Executive Card)
===================================================== --}}
@if ($render === 'cards')
    @forelse ($products as $product)
        @php
            $storeQty = (int) ($product->store_quantity ?? 0);
            $warehouseQty = (int) ($product->warehouse_quantity ?? 0);
            $totalQty = $storeQty + $warehouseQty;
            $reorder = (int) ($product->reorder_point ?? 10);

            if ($totalQty <= 0) {
                $statusClass = 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-900';
                $statusText = 'OUT OF STOCK';
            } elseif ($totalQty <= $reorder) {
                $statusClass = 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-900';
                $statusText = 'LOW STOCK';
            } else {
                $statusClass = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900';
                $statusText = 'IN STOCK';
            }
        @endphp

        <div
            data-product-row="true"
            data-product-id="{{ $product->id }}"
            data-product-page="{{ $products->currentPage() }}"
            onclick="openProductDetail({{ $product->id }})"
            class="product-grid-card w-full min-w-0 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-4 sm:p-5 flex flex-col justify-between shadow-sm hover:shadow-md hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 cursor-pointer"
        >
            {{-- Top Section: 64x64 Thumbnail & Stock Summary --}}
            <div class="flex items-start gap-4 mb-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#e0f2fe] dark:bg-[#161B26] border border-transparent dark:border-[#1e2536] flex items-center justify-center overflow-hidden shrink-0">
                    @if ($product->image_url)
                        <img
                            src="{{ $product->image_url }}"
                            alt="{{ $product->product_name }}"
                            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                        <svg class="hidden w-9 h-9 text-sky-200 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    @else
                        <svg class="w-9 h-9 text-sky-200 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    @endif
                </div>

                <div class="flex flex-col items-end flex-1 min-w-0 pt-0.5">
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border {{ $statusClass }} tracking-wide uppercase whitespace-nowrap">
                        {{ $statusText }}
                    </span>
                    <div class="text-2xl sm:text-3xl font-black {{ $totalQty <= 0 ? 'text-gray-400' : 'text-gray-900 dark:text-white' }} leading-none tracking-tight mt-1.5">
                        {{ $totalQty }}
                    </div>
                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-1.5 uppercase tracking-wide whitespace-nowrap">
                        STORE: {{ $storeQty }} • WH: {{ $warehouseQty }}
                    </div>
                </div>
            </div>

            {{-- Middle Section: Name, SKU, Category --}}
            <div class="mb-3.5 flex-1">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm line-clamp-1">
                    {{ $product->product_name }}
                </h3>
                <p class="text-[11px] font-mono font-semibold text-gray-500 dark:text-slate-400 mt-0.5 truncate">
                    {{ $product->product_sku ?? '—' }}
                </p>
                <p class="text-[11px] text-gray-400 dark:text-slate-500 truncate mt-0.5 uppercase tracking-wide">
                    {{ $product->category->parent->name ?? $product->category->name ?? 'UNCATEGORIZED' }}
                    @if(isset($product->category->parent))
                        <span class="mx-0.5">›</span> {{ $product->category->name }}
                    @endif
                </p>
            </div>

            {{-- Bottom Section: Price/Cost + Unified Ellipsis Menu --}}
            <div class="pt-3.5 border-t border-gray-100 dark:border-[#1e2536] flex items-end justify-between gap-3 mt-auto">
                <div class="flex flex-col min-w-0">
                    <span class="text-[10px] font-black text-gray-900 dark:text-white uppercase tracking-wider">PRICE:</span>
                    <span class="text-base sm:text-lg font-black text-gray-900 dark:text-white leading-tight truncate">
                        ₱{{ $product->selling_price !== null ? number_format($product->selling_price, 2) : '0.00' }}
                    </span>
                    <span class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 mt-0.5 truncate">
                        Cost: ₱{{ $product->purchase_cost !== null ? number_format($product->purchase_cost, 2) : '0.00' }}
                    </span>
                </div>

                <div class="inline-flex items-center justify-end shrink-0" onclick="event.stopPropagation()" data-row-actions>
                    <button type="button" onclick="openRowActionMenu(event, this)"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 transition-all shadow-xs cursor-pointer"
                            aria-label="Product Actions" title="Actions">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </button>
                    <div class="row-action-items hidden">
                        <button type="button" data-tone="default" data-label="View Details"
                                onclick="openProductDetail({{ $product->id }})">View Details</button>
                        <button type="button" data-tone="blue" data-label="View Item SKUs"
                                onclick="openItemSkusModal({{ $product->id }})">View Item SKUs</button>
                        <button type="button" data-tone="default" data-label="Edit Product"
                                onclick="toggleEditProductModal({{ $product->id }}, true)">Edit Product</button>
                        <button type="button" data-tone="rose" data-label="Delete Product"
                                onclick="toggleDeleteModal({{ $product->id }}, true)">Delete Product</button>
                    </div>
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