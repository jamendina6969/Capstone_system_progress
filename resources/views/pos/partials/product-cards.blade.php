@php 
    $viewType = $viewType ?? 'both'; 
    $isProjectMode = isset($hasProjectInCart) && $hasProjectInCart;
    
    // Calculate how much stock is currently locked up in the active cart (regular items + projects)
    $cartReserved = ['store' => [], 'warehouse' => []];
    if (isset($cart) && is_array($cart)) {
        foreach ($cart as $id => $cartItem) {
            if (($cartItem['type'] ?? 'product') === 'project') {
                foreach ($cartItem['project_breakdown']['materials'] as $mat) {
                    $cartReserved[$mat['source']][$mat['product_id']] = 
                        ($cartReserved[$mat['source']][$mat['product_id']] ?? 0) + (int)$mat['qty'];
                }
            } else {
                $cartReserved['store'][$id] = 
                    ($cartReserved['store'][$id] ?? 0) + (int)$cartItem['quantity'];
            }
        }
    }
@endphp

@forelse ($items as $item)
    @php
        $cartQty = isset($cart[$item->id]) ? $cart[$item->id]['quantity'] : 0;
        
        // Calculate available quantities by subtracting what's already in the cart/projects
        $reservedStoreQty = $cartReserved['store'][$item->id] ?? 0;
        $reservedWhQty = $cartReserved['warehouse'][$item->id] ?? 0;
        
        // Sanitize to 0 minimum
        $storeQty = max(0, $item->store_quantity - $reservedStoreQty);
        $whQty = max(0, $item->warehouse_quantity - $reservedWhQty);
        $totalQty = $storeQty + $whQty;
        
        // Retail Mode sells strictly from Store shelf stock; Project Mode can draw from Store or Warehouse
        $isOutOfStock = $isProjectMode ? ($totalQty <= 0) : ($storeQty <= 0);
        
        $remainingStock = $storeQty;
        $reorderPoint = $item->reorder_point ?? 10;

        // Status Logic
        if ($isOutOfStock) {
            $statusClass = 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-900';
            $statusText = 'OUT OF STOCK';
        } elseif ($remainingStock <= $reorderPoint) {
            $statusClass = 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-900';
            $statusText = 'LOW STOCK';
        } else {
            $statusClass = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900';
            $statusText = 'IN STOCK';
        }

        // Calculate project quantities for drop logic (what is already in the project)
        $projStoreQty = 0;
        $projWhQty = 0;
        if ($isProjectMode && isset($cart) && is_array($cart)) {
            foreach ($cart as $cartItem) {
                if (($cartItem['type'] ?? 'product') === 'project') {
                    foreach ($cartItem['project_breakdown']['materials'] as $mat) {
                        if ($mat['product_id'] == $item->id) {
                            if ($mat['source'] === 'store') $projStoreQty += $mat['qty'];
                            if ($mat['source'] === 'warehouse') $projWhQty += $mat['qty'];
                        }
                    }
                }
            }
        }

        // Determine the click action (prevent click if completely out of stock)
        if ($isOutOfStock) {
            $cardClickAction = '';
            $cardCursorClass = 'cursor-not-allowed opacity-60';
        } else {
            $cardClickAction = $isProjectMode 
                ? "openQuickAdd({$item->id}, '" . addslashes($item->product_name) . "', {$storeQty}, {$whQty})" 
                : "updateQty('{$item->id}', 'increase', null, '{$viewType}')";
            $cardCursorClass = 'cursor-pointer hover:shadow-md hover:border-slate-300 dark:hover:border-slate-600';
        }
    @endphp

    @if($viewType === 'both' || $viewType === 'grid')
        <!-- GRID VIEW ITEM -->
        <div data-record-row="true" 
             onclick="{{ $cardClickAction }}"
             class="grid-view-item bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-3.5 sm:p-4 lg:p-5 flex flex-col justify-between shadow-sm transition-all duration-200 {{ $cardCursorClass }}">
            
            <!-- Top Section: Image & Stock Badge -->
            <div class="flex items-start gap-4 mb-4 pointer-events-none">
                <!-- Image Placeholder -->
                <div class="w-16 h-16 sm:w-20 sm:h-20 lg:w-20 lg:h-20 xl:w-24 xl:h-24 rounded-2xl bg-[#e0f2fe] dark:bg-[#161B26] border border-transparent dark:border-[#1e2536] flex items-center justify-center overflow-hidden shrink-0">
                    @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}"
                             onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                             class="w-full h-full object-cover">
                        <svg class="hidden w-10 h-10 text-sky-200 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    @else
                        <svg class="w-10 h-10 text-sky-200 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    @endif
                </div>
                
                <!-- Stock Info (Right side) -->
                <div class="flex flex-col items-end flex-1 min-w-0 pt-1">
                    @if($isProjectMode)
                        <!-- Project Mode: Show St and WH explicitly -->
                        <span class="text-[11px] font-bold px-3 py-1.5 rounded-full border border-gray-200 dark:border-[#1e2536] bg-gray-50 dark:bg-[#161B26] text-gray-500 dark:text-slate-400 w-full text-center tracking-wide">
                            St: {{ $storeQty }} | WH: {{ $whQty }}
                        </span>
                    @else
                        <!-- Retail Mode: Show Big Number UI -->
                        <span data-pos-status-id="{{ $item->id }}" class="text-[10px] font-bold px-2.5 py-1 rounded-full border {{ $statusClass }} tracking-wide">{{ $statusText }}</span>
                        
                        <div class="flex items-baseline gap-1 mt-1">
                            <div data-pos-stock-id="{{ $item->id }}" class="text-2xl sm:text-3xl lg:text-3xl xl:text-4xl font-black {{ $isOutOfStock ? 'text-gray-400' : 'text-gray-900 dark:text-white' }} leading-none tracking-tight">{{ $storeQty }}</div>
                        </div>
                        
                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-1.5 uppercase tracking-wide">UNIT : {{ $item->unit_of_measure ?? 'PIECE' }}</div>
                        @if($storeQty <= 0 && $whQty > 0)
                            <div class="text-[9px] text-amber-600 dark:text-amber-400 font-semibold mt-0.5 uppercase tracking-wide">WH: {{ $whQty }} (Restock needed)</div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Middle Section: Product Name & Category -->
            <div class="mb-3 flex-1 pointer-events-none">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm line-clamp-1">{{ $item->product_name }}</h3>
                <p class="text-[11px] text-gray-400 dark:text-slate-500 truncate mt-0.5 uppercase tracking-wide">
                    {{ $item->category->parent->name ?? $item->category->name ?? 'UNCATEGORIZED' }} 
                    @if(isset($item->category->parent)) <span class="mx-0.5">›</span> {{ $item->category->name }} @endif
                </p>
            </div>

            <!-- Bottom Section: Price & Actions -->
            <div class="pt-4 border-t border-gray-100 dark:border-[#1e2536] flex items-end justify-between gap-3 mt-auto">
                <!-- Price -->
                <div class="flex flex-col shrink-0 mb-0.5 pointer-events-none">
                    <span class="text-[11px] font-black text-gray-900 dark:text-white uppercase tracking-wider">PRICE:</span>
                    <span class="text-base sm:text-lg lg:text-base xl:text-lg font-black text-gray-900 dark:text-white leading-tight">₱{{ number_format($item->selling_price, 2) }}</span>
                </div>
                
                <!-- Actions -->
                <div class="flex flex-col flex-1 items-end">
                    <span class="text-[10px] font-black text-gray-900 dark:text-white uppercase tracking-wider mb-1 pointer-events-none">ENTER QTY:</span>
                    
                    @if($isOutOfStock)
                        <div class="flex items-center w-full max-w-[130px] sm:max-w-[145px] border border-gray-200 dark:border-[#1e2536] rounded-full overflow-hidden bg-gray-100 dark:bg-[#161B26] shadow-2xs h-[38px] justify-center">
                            <span class="text-[11px] font-bold text-gray-400 uppercase">Unavailable</span>
                        </div>
                    @elseif($isProjectMode)
                        <div class="flex items-center w-full max-w-[130px] sm:max-w-[145px] border border-gray-200 dark:border-[#1e2536] rounded-full overflow-hidden bg-gray-50 dark:bg-[#161B26] shadow-2xs h-[38px]" onclick="event.stopPropagation()">
                            <button type="button" onclick="event.stopPropagation(); openQuickDrop({{ $item->id }}, '{{ addslashes($item->product_name) }}', {{ $projStoreQty }}, {{ $projWhQty }})" class="flex-1 h-full text-[11px] font-bold text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-[#1e2536] transition-colors uppercase {{ ($projStoreQty + $projWhQty) <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}">DROP</button>
                            <div class="w-px h-5 bg-gray-300 dark:bg-gray-600"></div>
                            <button type="button" onclick="event.stopPropagation(); openQuickAdd({{ $item->id }}, '{{ addslashes($item->product_name) }}', {{ $storeQty }}, {{ $whQty }})" class="flex-1 h-full text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors uppercase">ADD</button>
                        </div>
                    @else
                        <div class="flex items-center w-full max-w-[130px] sm:max-w-[145px] border border-gray-200 dark:border-[#1e2536] rounded-full overflow-hidden bg-gray-50 dark:bg-[#161B26] shadow-2xs h-[38px]" onclick="event.stopPropagation()">
                            <button type="button" onclick="event.stopPropagation(); updateQty('{{ $item->id }}', 'decrease', null, 'grid')" class="px-3 h-full text-[11px] font-bold text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-[#1e2536] transition-colors uppercase">DROP</button>
                            <input type="number" value="1" min="1" step="1" id="qty-{{ $item->id }}-grid" onkeydown="if(event.key === 'Enter'){ event.preventDefault(); updateQty('{{ $item->id }}', 'exact', this.value, 'grid'); }" onclick="event.stopPropagation()" class="w-full text-center text-sm font-black text-gray-900 dark:text-slate-100 bg-transparent border-none focus:outline-none focus:ring-0 p-0" title="Enter quantity and press Enter">
                            <button type="button" onclick="event.stopPropagation(); updateQty('{{ $item->id }}', 'increase', null, 'grid')" class="px-3 h-full text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors uppercase">ADD</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($viewType === 'both' || $viewType === 'list')
        <!-- LIST VIEW ITEM -->
        <div data-record-row="true" 
             onclick="{{ $cardClickAction }}"
             class="list-view-item bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl px-4 py-3 flex items-center justify-between gap-4 shadow-xs transition-all {{ $cardCursorClass }}">
            
            <!-- Left Section: Image & Info -->
            <div class="flex items-center gap-3 flex-1 min-w-0 pointer-events-none">
                <div class="w-11 h-11 rounded-lg bg-gray-100 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] flex items-center justify-center overflow-hidden shrink-0">
                    @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}"
                             onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                             class="w-full h-full object-cover">
                        <svg class="hidden w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    @else
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-gray-900 dark:text-white text-sm truncate">{{ $item->product_name }}</h3>
                    <p class="text-[11px] text-gray-400 dark:text-slate-500 truncate mt-0.5">
                        {{ $item->category->parent->name ?? $item->category->name ?? 'Uncategorized' }} 
                        @if(isset($item->category->parent)) › {{ $item->category->name }} @endif
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold ml-1">({{ $item->unit_of_measure ?? 'Piece' }})</span>
                    </p>
                </div>
            </div>

            <!-- Right Section: Status, Price & Controls -->
            <div class="flex items-center gap-6 shrink-0">
                
                <!-- Status Badge -->
                <div class="w-40 flex justify-center pointer-events-none">
                    @if($isProjectMode)
                        <span class="text-xs font-bold px-3 py-1.5 rounded-full border border-gray-200 dark:border-[#1e2536] bg-gray-50 dark:bg-[#161B26] text-gray-500 dark:text-slate-400 w-full text-center">
                            St: {{ $storeQty }} | WH: {{ $whQty }}
                        </span>
                    @else
                        <span data-pos-list-status-id="{{ $item->id }}" class="text-[10px] font-bold px-2 py-1 rounded-full border {{ $statusClass }} tracking-wide">
                            {{ $statusText }} ({{ $storeQty }})
                        </span>
                    @endif
                </div>

                <!-- Price -->
                <div class="w-24 text-right pointer-events-none">
                    <span class="text-sm font-extrabold text-gray-900 dark:text-slate-100">₱{{ number_format($item->selling_price, 2) }}</span>
                </div>

                <!-- Qty Controls (Stop Propagation) -->
                @if($isOutOfStock)
                    <div class="flex items-center w-36 h-[38px] border border-gray-200 dark:border-[#1e2536] rounded-full overflow-hidden bg-gray-100 dark:bg-[#161B26] justify-center">
                        <span class="text-[11px] font-bold text-gray-400 uppercase">Unavailable</span>
                    </div>
                @elseif($isProjectMode)
                    <div class="flex items-center border border-gray-200 dark:border-[#1e2536] rounded-full overflow-hidden bg-gray-50 dark:bg-[#161B26] shadow-2xs w-36 h-[38px]" onclick="event.stopPropagation()">
                        <button type="button" onclick="event.stopPropagation(); openQuickDrop({{ $item->id }}, '{{ addslashes($item->product_name) }}', {{ $projStoreQty }}, {{ $projWhQty }})" class="flex-1 h-full text-[11px] font-bold text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-[#1e2536] transition-colors uppercase {{ ($projStoreQty + $projWhQty) <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}">Drop</button>
                        <div class="w-px h-5 bg-gray-300 dark:bg-gray-600"></div>
                        <button type="button" onclick="event.stopPropagation(); openQuickAdd({{ $item->id }}, '{{ addslashes($item->product_name) }}', {{ $storeQty }}, {{ $whQty }})" class="flex-1 h-full text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors uppercase">Add</button>
                    </div>
                @else
                    <div class="flex items-center border border-gray-200 dark:border-[#1e2536] rounded-full overflow-hidden bg-gray-50 dark:bg-[#161B26] shadow-2xs h-[38px]" onclick="event.stopPropagation()">
                        <button type="button" onclick="event.stopPropagation(); updateQty('{{ $item->id }}', 'decrease', null, 'list')" class="px-4 h-full text-[11px] font-bold text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-[#1e2536] transition-colors uppercase">Drop</button>
                        <input type="number" value="1" min="1" step="1" id="qty-{{ $item->id }}-list" onkeydown="if(event.key === 'Enter'){ event.preventDefault(); updateQty('{{ $item->id }}', 'exact', this.value, 'list'); }" onclick="event.stopPropagation()" class="w-14 text-center bg-transparent text-sm font-bold text-gray-900 dark:text-slate-100 focus:outline-none p-0" title="Enter quantity for Add/Drop. Press Enter to set the exact cart quantity">
                        <button type="button" onclick="event.stopPropagation(); updateQty('{{ $item->id }}', 'increase', null, 'list')" class="px-4 h-full text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors uppercase">Add</button>
                    </div>
                @endif
            </div>
        </div>
    @endif

@empty
    <div class="col-span-full py-16 text-center text-gray-400 dark:text-slate-500 font-medium text-sm">
        No products found matching your criteria.
    </div>
@endforelse