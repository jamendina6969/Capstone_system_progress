@forelse ($products as $product)
    @php
        $role = auth()->user()->role;
        $canTransfer = in_array($role, ['admin', 'manager'], true);
        $hideCost = $role === 'warehouse_staff';

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

    <div data-record-row="true"
         data-product-id="{{ $product->id }}"
         onclick="openProductDetail({{ $product->id }})"
         class="w-full min-w-0 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-4 sm:p-5 flex flex-col justify-between shadow-sm hover:shadow-md hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 cursor-pointer">

        {{-- Top Section: 64x64 Thumbnail & Stock Summary --}}
        <div class="flex items-start gap-4 mb-4">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#e0f2fe] dark:bg-[#161B26] border border-transparent dark:border-[#1e2536] flex items-center justify-center overflow-hidden shrink-0">
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->product_name }}"
                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                         class="w-full h-full object-cover" loading="lazy">
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
                @unless($hideCost)
                    <span class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 mt-0.5 truncate">
                        Cost: ₱{{ $product->purchase_cost !== null ? number_format($product->purchase_cost, 2) : '0.00' }}
                    </span>
                @endunless
            </div>

            <div class="inline-flex items-center justify-end shrink-0" onclick="event.stopPropagation()" data-row-actions>
                <button type="button" onclick="openRowActionMenu(event, this)"
                        class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 transition-all shadow-xs cursor-pointer"
                        aria-label="Inventory Actions" title="Actions">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </button>
                <div class="row-action-items hidden">
                    <button type="button" data-tone="default" data-label="View Details"
                            onclick="openProductDetail({{ $product->id }})">View Details</button>
                    <button type="button" data-tone="blue" data-label="View Item SKUs"
                            onclick="openItemSkusModal({{ $product->id }})">View Item SKUs</button>
                    <button type="button" data-tone="emerald" data-label="Restock Product"
                            onclick="toggleInventoryAction({{ $product->id }}, 'card-restock')">Restock Product</button>
                    @if ($canTransfer)
                        <button type="button" data-tone="default" data-label="Transfer Stock"
                                onclick="toggleInventoryAction({{ $product->id }}, 'card-transfer')">Transfer Stock</button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Inline Expandable Restock / Transfer Forms (stopPropagation so typing/submitting doesn't open detail modal) --}}
        <div id="card-restock-{{ $product->id }}" class="hidden mt-3 pt-3 border-t-2 border-gray-100 dark:border-[#1e2536]" onclick="event.stopPropagation()">
            @include('partials.restockForm', ['product' => $product])
        </div>
        @if ($canTransfer)
            <div id="card-transfer-{{ $product->id }}" class="hidden mt-3 pt-3 border-t-2 border-gray-100 dark:border-[#1e2536]" onclick="event.stopPropagation()">
                @include('partials.transferForm', ['product' => $product])
            </div>
        @endif
    </div>
@empty
    <p class="col-span-full text-center text-gray-400 dark:text-slate-500 py-12 font-medium">No products found.</p>
@endforelse
