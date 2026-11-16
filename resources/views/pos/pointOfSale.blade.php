@extends('layouts.default')

@section('title', 'Point of Sale')

@section('content')

    @php
        $currentCategoryId = request('category_id');
        $categoryLabel = 'All Categories';
        if ($currentCategoryId) {
            foreach ($categories as $category) {
                if ((string) $currentCategoryId === (string) $category->id) {
                    $categoryLabel = $category->name;
                    break;
                }
                foreach ($category->children as $child) {
                    if ((string) $currentCategoryId === (string) $child->id) {
                        $categoryLabel = $child->name;
                        break 2;
                    }
                }
            }
        }
    @endphp

    <!-- Top Navigation Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 mb-8">
        <div class="flex items-center gap-6 flex-wrap">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Point of Sale</h1>

            <!-- DUAL-MODE TOGGLE (Bigger) -->
            <div class="flex items-center bg-gray-100 dark:bg-[#161B26] p-1.5 rounded-full border-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="setPosMode('retail')" id="btn-mode-retail" class="px-6 py-2 rounded-full text-sm font-bold transition-all bg-white dark:bg-[#10141D] shadow-xs text-emerald-600 dark:text-emerald-400">Retail Sale</button>
                <button type="button" onclick="setPosMode('project')" id="btn-mode-project" class="px-6 py-2 rounded-full text-sm font-bold transition-all text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">Project / Service</button>
            </div>

            <div class="flex items-center gap-3">
                <!-- Counter POS badge pill (Bigger) -->
                <div class="inline-flex items-center gap-2.5 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 rounded-full shadow-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">Counter POS</span>
                </div>

                <!-- Parked Orders Button (Bigger) -->
                @php
                    $parkedOrders = session()->get('parked_orders', []);
                    $parkedCount = count($parkedOrders);
                @endphp
                <button type="button" onclick="openParkedModal()"
                   class="inline-flex items-center gap-2 bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-200 dark:border-amber-900/60 px-4 py-2 rounded-full text-sm font-bold text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 transition-all cursor-pointer shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Parked Orders ({{ $parkedCount }})
                </button>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        <!-- PRODUCTS SECTION -->
        <div class="flex-[2] flex flex-col gap-6 h-[calc(100vh-180px)] min-h-[600px]">
            
            <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-5 flex flex-col md:flex-row items-center justify-between gap-4 shrink-0">
                <form id="filterForm" method="GET" action="{{ route('pos') }}" class="relative w-full md:w-80">
                    @if(request('category_id'))
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                    @endif
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search items or categories..." autocomplete="off"
                           class="w-full rounded-xl bg-gray-50 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:border-slate-400">
                </form>

                <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end flex-wrap">
                    
                    <button type="button" id="btn-create-project" onclick="toggleProjectModal(true)" 
                            class="hidden bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all shadow-xs flex items-center gap-1.5 shrink-0 {{ $hasProjectInCart ? 'opacity-50 cursor-not-allowed' : '' }}" 
                            {{ $hasProjectInCart ? 'disabled' : '' }}
                            title="{{ $hasProjectInCart ? 'Clear the current project to create a new one.' : 'Create a new project.' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Custom Project
                    </button>

                    <div class="relative w-full sm:w-48" id="categoryDropdown">
                        <input type="hidden" id="categoryHiddenInput" value="{{ request('category_id') }}">
                        <button type="button" id="categoryTrigger"
                                class="w-full flex items-center justify-between gap-2 rounded-xl bg-gray-50 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span id="categoryLabel" class="truncate">{{ $categoryLabel }}</span>
                            <svg class="w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" id="categoryChevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="categoryMenu" class="hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            <button type="button" data-value="" class="category-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All Categories</button>
                            @foreach ($categories as $category)
                                <div class="mt-1">
                                    <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">{{ $category->name }}</div>
                                    <button type="button" data-value="{{ $category->id }}" class="category-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All {{ $category->name }}</button>
                                    @foreach ($category->children->sortBy('name') as $child)
                                        <button type="button" data-value="{{ $child->id }}" class="category-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $child->name }}</button>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center bg-gray-100 dark:bg-[#161B26] p-1 rounded-xl border-2 border-gray-200 dark:border-[#1e2536] shrink-0">
                        <button type="button" onclick="switchView('grid')" id="gridViewBtn" class="p-2 rounded-lg text-gray-600 dark:text-slate-300 transition-all cursor-pointer" title="Grid View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </button>
                        <button type="button" onclick="switchView('list')" id="listViewBtn" class="p-2 rounded-lg text-gray-600 dark:text-slate-300 transition-all cursor-pointer" title="List View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div id="productScrollContainer" class="flex-1 overflow-y-auto custom-scrollbar pr-1 relative">
                <div id="productGridView" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 pb-4">
                    @include('pos.partials.product-cards', ['items' => $items, 'cart' => $cart, 'viewType' => 'grid', 'hasProjectInCart' => $hasProjectInCart])
                </div>
                <div id="productListView" class="hidden flex flex-col gap-2.5 pb-4">
                    @include('pos.partials.product-cards', ['items' => $items, 'cart' => $cart, 'viewType' => 'list', 'hasProjectInCart' => $hasProjectInCart])
                </div>
                
                <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 font-semibold text-sm">
                    <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading more products...
                </div>
            </div>

            <div class="shrink-0 pt-3 pb-2 flex items-center justify-center">
                <div class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-full shadow-xs text-xs font-medium text-gray-500 dark:text-slate-400" id="resultsText"></div>
            </div>
        </div>

        <!-- ORDER SUMMARY SIDEBAR -->
        <div class="flex-1 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-6 flex flex-col justify-between h-fit min-h-[520px]">
            <div>
                <div class="flex items-center justify-between mb-5 pb-4 border-b-2 border-gray-100 dark:border-[#1e2536]">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">Order Summary</h2>
                    <div class="flex items-center gap-2">
                        @if (count($cart) > 0)
                            <form action="{{ route('pos.hold') }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-2 border-amber-200 dark:border-amber-900 px-4 py-2 rounded-full text-sm font-bold transition-all cursor-pointer flex items-center gap-1.5 shadow-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Hold
                                </button>
                            </form>
                        @endif
                        <span class="text-sm font-bold text-gray-500 dark:text-slate-400 bg-gray-100 dark:bg-[#161B26] px-4 py-2 rounded-full border border-gray-200 dark:border-[#1e2536]">
                            {{ array_sum(array_column($cart, 'quantity')) }} {{ array_sum(array_column($cart, 'quantity')) === 1 ? 'item' : 'items' }}
                        </span>
                    </div>
                </div>

                @if (count($cart) > 0)
                    <div class="divide-y divide-gray-100 dark:divide-[#1e2536] max-h-[320px] overflow-y-auto pr-1 custom-scrollbar">
                        @foreach ($cart as $id => $item)
                            <div class="py-3 flex flex-col gap-2.5 group border-b border-gray-100 dark:border-[#1e2536]">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $item['product_name'] }}
                                                @if(($item['type'] ?? 'product') === 'product')
                                                    <span class="text-[10px] font-normal text-gray-500">(From Store)</span>
                                                @endif
                                            </h3>
                                            <span class="text-sm font-bold text-gray-900 dark:text-slate-100">₱{{ number_format($item['subtotal'], 2) }}</span>
                                        </div>
                                        
                                        {{-- CLEAN READ-ONLY LIST FOR PROJECTS --}}
                                        @if(($item['type'] ?? 'product') === 'project')
                                            <div class="flex items-center justify-between mt-1">
                                                <p class="text-[11px] text-gray-400 dark:text-slate-400">Custom bundle</p>
                                                <button type="button" onclick="editProject('{{ $id }}')" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors cursor-pointer">Edit Details</button>
                                            </div>
                                            
                                            <div class="mt-2 pl-2 border-l-2 border-gray-300 dark:border-gray-600 space-y-1">
                                                @foreach($item['project_breakdown']['materials'] as $mat)
                                                    <div class="flex justify-between items-center text-[11px] text-gray-500">
                                                        <span>{{ $mat['qty'] }}x {{ $mat['name'] }}</span>
                                                        <span class="capitalize font-semibold">({{ $mat['source'] }})</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-[11px] text-gray-400 dark:text-slate-400 mt-0.5">Qty: {{ $item['quantity'] }} × ₱{{ number_format($item['selling_price'], 2) }}</p>
                                            
                                            @if(($item['item_discount_type'] ?? 'none') !== 'none' && ($item['item_discount_amount'] ?? 0) > 0)
                                                <p class="text-[11px] font-medium text-rose-600 dark:text-rose-400 mt-0.5 flex items-center gap-1">
                                                    <span>Item Disc:</span>
                                                    <span>-₱{{ number_format($item['item_discount_amount'], 2) }}</span>
                                                    <span class="text-[9px] text-gray-400">({{ ($item['item_discount_scope'] ?? 'whole') === 'per_item' ? 'Per Piece' : 'Whole Row' }})</span>
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                    <form action="{{ route('pos.remove', $id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors p-1 cursor-pointer" title="Remove item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>

                                @if(($item['type'] ?? 'product') !== 'project')
                                    <!-- Per-Item Discount Form -->
                                    <form action="{{ route('pos.item-discount', $id) }}" method="POST" class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px] bg-gray-50 dark:bg-[#161B26] p-2 rounded-xl border border-gray-200 dark:border-[#1e2536]">
                                        @csrf
                                        <select name="item_discount_type" class="bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-md px-1.5 py-0.5 text-gray-600 dark:text-slate-300 text-[10px] cursor-pointer">
                                            <option value="none" {{ ($item['item_discount_type'] ?? '') == 'none' ? 'selected' : '' }}>No Disc</option>
                                            <option value="amount" {{ ($item['item_discount_type'] ?? '') == 'amount' ? 'selected' : '' }}>Disc (₱)</option>
                                            <option value="percentage" {{ ($item['item_discount_type'] ?? '') == 'percentage' ? 'selected' : '' }}>Disc (%)</option>
                                        </select>
                                        <select name="item_discount_scope" class="bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-md px-1.5 py-0.5 text-gray-600 dark:text-slate-300 text-[10px] cursor-pointer">
                                            <option value="whole" {{ ($item['item_discount_scope'] ?? 'whole') == 'whole' ? 'selected' : '' }}>Whole Row</option>
                                            <option value="per_item" {{ ($item['item_discount_scope'] ?? '') == 'per_item' ? 'selected' : '' }}>Per Piece</option>
                                        </select>
                                        <input type="number" step="0.01" min="0" name="item_discount_value" value="{{ $item['item_discount_value'] ?? '' }}" placeholder="Value" class="w-16 bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-md px-2 py-0.5 text-gray-900 dark:text-slate-100 text-[10px] focus:outline-none">
                                        <button type="submit" class="bg-white border border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white font-semibold px-3 py-1 rounded-full transition-all text-[10px] ml-auto cursor-pointer shadow-xs">Set</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-16 text-center text-gray-400 dark:text-slate-500 font-medium text-sm">
                        Your cart is empty. Use Drop/Add buttons or type a quantity on product cards to add items.
                    </div>
                @endif
            </div>

            <!-- Cart Footer Totals & Discounts Integration -->
            <div class="pt-5 border-t-2 border-gray-200 dark:border-[#1e2536] mt-4">
                @php
                    $discountedItemsList = [];
                    foreach ($cart as $item) {
                        if (($item['item_discount_type'] ?? 'none') !== 'none' && ($item['item_discount_amount'] ?? 0) > 0) {
                            $discountedItemsList[] = $item;
                        }
                    }
                    $totalAllDiscounts = $totalItemDiscounts + ($discountAmount ?? 0);

                    // CHANGED: Calculate VAT Exclusive (Add Tax on top)
                    $vatRate = 0.12;
                    $vatAmount = $total * $vatRate;
                    $grandTotal = $total + $vatAmount;
                @endphp

                <div class="space-y-2 mb-5 text-sm">
                    
                    <!-- Subtotal -->
                    <div class="flex justify-between text-gray-600 dark:text-slate-400 font-medium">
                        <span>Subtotal</span>
                        <span>₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    
                    <!-- Discounts -->
                    <div class="flex justify-between items-center text-gray-600 dark:text-slate-400 relative">
                        <button type="button" onclick="openDiscountModal()" class="text-emerald-600 dark:text-emerald-400 hover:underline font-bold text-sm flex items-center gap-1 cursor-pointer z-10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            Discounts ({{ isset($cartDiscount) && $cartDiscount['type'] !== 'none' ? ucfirst(str_replace('_', ' ', $cartDiscount['type'])) : 'Configure' }})
                        </button>
                        <span class="text-rose-600 dark:text-rose-400 font-bold">-₱{{ number_format($totalAllDiscounts, 2) }}</span>
                    </div>

                    @if(count($discountedItemsList) > 0 || (isset($cartDiscount) && $cartDiscount['type'] !== 'none'))
                        <div class="bg-gray-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-xl p-3 text-xs space-y-2 my-2">
                            <div class="flex justify-between text-gray-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] pb-1.5 border-b border-gray-200 dark:border-[#1e2536]">
                                <span>Discount Breakdown</span>
                                <span class="text-emerald-600 dark:text-emerald-400">Saved: ₱{{ number_format($totalAllDiscounts, 2) }}</span>
                            </div>

                            @if(count($discountedItemsList) > 0)
                                <div class="space-y-1">
                                    @foreach($discountedItemsList as $discItem)
                                        <div class="flex justify-between items-center text-gray-600 dark:text-slate-400 pl-1">
                                            <span class="truncate max-w-[150px]">• {{ $discItem['product_name'] }}</span>
                                            <span class="text-rose-600 dark:text-rose-400 font-semibold">-₱{{ number_format($discItem['item_discount_amount'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(isset($cartDiscount) && $cartDiscount['type'] !== 'none')
                                <div class="pt-1.5 border-t border-gray-200 dark:border-[#1e2536] flex justify-between font-bold text-gray-700 dark:text-slate-300">
                                    <span>Cart Discount ({{ ucfirst(str_replace('_', ' ', $cartDiscount['type'])) }})</span>
                                    <span class="text-rose-600 dark:text-rose-400">-₱{{ number_format($discountAmount ?? 0, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Tax (12% VAT) -->
                    <div class="flex justify-between text-gray-600 dark:text-slate-400 font-medium pt-1">
                        <span>Tax (12% VAT)</span>
                        <span>₱{{ number_format($vatAmount, 2) }}</span>
                    </div>

                    <!-- Final Total Amount (Uses Grand Total) -->
                    <div class="flex justify-between items-center text-lg font-black text-gray-900 dark:text-white pt-3 border-t-2 border-gray-100 dark:border-[#1e2536] mt-1">
                        <span>Total Amount</span>
                        <span class="text-xl">₱{{ number_format($grandTotal, 2) }}</span>
                    </div>
                </div>

                @if(count($cart) > 0)
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="openClearModal()" class="w-1/3 text-center bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white font-bold py-3 px-2 rounded-full transition-all text-sm cursor-pointer shadow-xs">
                            Clear
                        </button>
                        <a href="{{ route('checkout') }}" class="w-2/3 text-center bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white font-bold py-3 px-3 rounded-full transition-all text-sm shadow-xs cursor-pointer">
                            Proceed to Checkout
                        </a>
                    </div>
                @else
                    <button disabled class="w-full text-center bg-gray-100 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] text-gray-400 dark:text-slate-600 font-bold py-3 rounded-full cursor-not-allowed text-sm">
                        Proceed to Checkout
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- MODALS -->
    
    {{-- PROJECT MODE WARNING MODAL (Replaces Browser Alert) --}}
    <div id="projectWarningModal" class="fixed inset-0 z-[90] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 text-center" onclick="event.stopPropagation()">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-amber-100 dark:bg-amber-950/50 mb-6">
                <svg class="h-10 w-10 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Action Not Allowed</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-8 leading-relaxed">Cannot add standard products in Project Mode. Please edit the project or clear the cart.</p>
            <button type="button" onclick="closeProjectWarningModal()" class="w-full bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-bold py-3 rounded-full shadow-md transition-colors">
                Understood
            </button>
        </div>
    </div>

    {{-- QUICK ADD MODAL (Project Mode) --}}
    <div id="quickAddModal" class="fixed inset-0 z-[80] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4 border-b-2 border-gray-200 dark:border-[#1e2536] pb-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="quickAddTitle">Add Material</h3>
                <button type="button" onclick="closeQuickAddModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            <form id="quickAddForm" method="POST" action="{{ route('pos.quickAddMaterial') }}">
                @csrf
                <input type="hidden" name="product_id" id="quickAddProductId">
                <div class="mb-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Source</label>
                    <select name="source" id="quickAddSource" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#161B26] text-sm py-2 px-3 focus:outline-none" onchange="updateQuickAddMax()">
                        <option value="store">Store</option>
                        <option value="warehouse">Warehouse</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantity</label>
                    <input type="number" name="qty" id="quickAddQty" value="1" min="1" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#161B26] text-sm py-2 px-3 focus:outline-none">
                    <p class="text-[10px] mt-1 text-gray-500 font-semibold" id="quickAddAvailable"></p>
                </div>
                <div class="flex gap-2 justify-end mt-6 border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                    <button type="button" onclick="closeQuickAddModal()" class="border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full hover:bg-slate-700 hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">Add to Project</button>
                </div>
            </form>
        </div>
    </div>

    {{-- QUICK DROP MODAL (Project Mode) --}}
    <div id="quickDropModal" class="fixed inset-0 z-[80] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4 border-b-2 border-gray-200 dark:border-[#1e2536] pb-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="quickDropTitle">Drop Material</h3>
                <button type="button" onclick="closeQuickDropModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            <form id="quickDropForm" method="POST" action="{{ route('pos.quickDropMaterial') }}">
                @csrf
                <input type="hidden" name="product_id" id="quickDropProductId">
                <div class="mb-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Source</label>
                    <select name="source" id="quickDropSource" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#161B26] text-sm py-2 px-3 focus:outline-none" onchange="updateQuickDropMax()">
                        <option value="store">Store</option>
                        <option value="warehouse">Warehouse</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantity</label>
                    <input type="number" name="qty" id="quickDropQty" value="1" min="1" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#161B26] text-sm py-2 px-3 focus:outline-none">
                    <p class="text-[10px] mt-1 text-gray-500 font-semibold" id="quickDropAvailable"></p>
                </div>
                <div class="flex gap-2 justify-end mt-6 border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                    <button type="button" onclick="closeQuickDropModal()" class="border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full hover:bg-slate-700 hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">Drop from Project</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CUSTOM PROJECT MODAL --}}
    <div id="projectModal" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-4xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto custom-scrollbar" onclick="event.stopPropagation()">
            
            <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="projectModalTitle">Create Custom Project / Service</h3>
                    <p class="text-xs text-gray-500 mt-1">Combine materials, labor, and fees into a single billable item.</p>
                </div>
                <button type="button" onclick="toggleProjectModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('pos.addProject') }}" enctype="multipart/form-data" id="projectForm" onsubmit="return validateProjectForm()">
                @csrf
                <input type="hidden" name="edit_project_id" id="edit_project_id" value="">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="md:col-span-2 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Project / Customer Name</label>
                            <input type="text" name="project_name" id="project_name" required placeholder="e.g., Water Filtration Installation - Company ABC" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Project Features / Description</label>
                            <textarea name="project_description" id="project_description" rows="3" placeholder="Details of the work, scope, etc." class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400"></textarea>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Upload Image (Customer)</label>
                        <div class="border-2 border-dashed border-gray-300 dark:border-[#1e2536] rounded-xl h-[120px] flex items-center justify-center text-center p-2 relative cursor-pointer hover:bg-gray-50 dark:hover:bg-[#161B26] transition-colors">
                            <input type="file" name="project_image" id="project_image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0]?.name || 'No file selected'">
                            <div>
                                <svg class="w-6 h-6 mx-auto text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span id="fileNameDisplay" class="text-xs text-gray-500">Click to upload</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Materials Section --}}
                <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4 mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-gray-900 dark:text-white">Materials and Costing</h4>
                        <button type="button" onclick="addProjectMaterialRow()" class="text-xs font-bold text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Material
                        </button>
                    </div>
                    
                    <div class="space-y-3 mb-4" id="projectMaterialsContainer">
                        <!-- Rows will be injected here via JS -->
                    </div>
                </div>

                {{-- Fees & Markup --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                    <div class="space-y-2.5">
                        <div class="flex justify-between items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-slate-300">Labor Cost</label>
                            <input type="number" name="labor_cost" id="labor_cost" step="0.01" placeholder="0.00" value="0.00" class="w-32 rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#161B26] text-right px-2 py-1 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400" oninput="calculateProjectTotal()">
                        </div>
                        <div class="flex justify-between items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-slate-300">Installation Cost</label>
                            <input type="number" name="installation_cost" id="installation_cost" step="0.01" placeholder="0.00" value="0.00" class="w-32 rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#161B26] text-right px-2 py-1 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400" oninput="calculateProjectTotal()">
                        </div>
                        <div class="flex justify-between items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-slate-300">Additional Costs</label>
                            <input type="number" name="additional_cost" id="additional_cost" step="0.01" placeholder="0.00" value="0.00" class="w-32 rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#161B26] text-right px-2 py-1 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400" oninput="calculateProjectTotal()">
                        </div>
                        <div class="flex justify-between items-center">
                            <label class="text-sm font-semibold text-gray-700 dark:text-slate-300">Shipping / Freight</label>
                            <input type="number" name="project_freight" id="project_freight" step="0.01" placeholder="0.00" value="0.00" class="w-32 rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#161B26] text-right px-2 py-1 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400" oninput="calculateProjectTotal()">
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-[#161B26] p-4 rounded-xl border border-gray-200 dark:border-[#1e2536]">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-gray-600 dark:text-slate-400">Subtotal</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white" id="projectSubtotalDisplay">₱0.00</span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-bold text-gray-600 dark:text-slate-400">Mark Up</label>
                                <select name="markup_type" id="markupType" class="text-xs rounded border-gray-300 dark:border-[#1e2536] dark:bg-[#10141D] py-1 text-gray-900 dark:text-white focus:outline-none focus:border-slate-400" onchange="calculateProjectTotal()">
                                    <option value="percentage">%</option>
                                    <option value="amount">₱</option>
                                </select>
                            </div>
                            <input type="number" name="markup_value" id="markupValue" step="0.01" placeholder="0.00" value="0.00" class="w-24 rounded border-gray-300 dark:border-[#1e2536] dark:bg-[#10141D] text-right px-2 py-1 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400" oninput="calculateProjectTotal()">
                        </div>
                        <div class="flex justify-between items-center border-t-2 border-gray-200 dark:border-[#1e2536] pt-2">
                            <span class="text-lg font-black text-gray-900 dark:text-white">Total</span>
                            <span class="text-xl font-black text-gray-900 dark:text-white" id="projectFinalTotalDisplay">₱0.00</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 mt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleProjectModal(false)" class="border-2 border-slate-300 text-slate-700 text-xs font-semibold px-6 py-2.5 rounded-full hover:bg-slate-700 hover:text-white dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white transition-colors">Cancel</button>
                    <button type="submit" id="projectSubmitBtn" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-6 py-2.5 rounded-full transition-all shadow-xs">Add Project to Cart</button>
                </div>
            </form>
        </div>
    </div>

    <!-- PARKED ORDERS MODAL -->
    @php 
        $parkedList = session()->get('parked_orders', []); 
    @endphp
    <div id="parkedModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-3xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
             <div id="parkedSummaryScreen">
                <div class="flex items-center justify-between pb-4 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Parked Summary</h3>
                        <p class="text-xs text-gray-400 mt-1">Select an order to view its products.</p>
                    </div>
                    <button type="button" onclick="closeParkedModal()" aria-label="Close parked summary" class="text-gray-400 hover:text-rose-500 p-2 text-2xl font-bold">&times;</button>
                </div>

                @if(count($parkedList) > 0)
                    <div class="space-y-3">
                        @foreach($parkedList as $index => $parked)
                            @php
                                $isProj = collect($parked['cart'] ?? [])->contains(fn($i) => ($i['type'] ?? '') === 'project');
                            @endphp
                            <div class="border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl p-5 bg-gray-50/50 dark:bg-[#161B26] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $parked['id'] ?? 'Parked Order' }}</span>
                                        <span class="text-[10px] font-semibold {{ $isProj ? 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-900' : 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-900' }} px-2 py-0.5 rounded-full border">
                                            {{ $isProj ? 'Project' : 'Retail' }} · {{ $parked['item_count'] ?? array_sum(array_column($parked['cart'] ?? [], 'quantity')) }} {{ (($parked['item_count'] ?? array_sum(array_column($parked['cart'] ?? [], 'quantity'))) == 1) ? 'item' : 'items' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400 dark:text-slate-400">Parked at: {{ $parked['time'] ?? 'N/A' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="showParkedDetails({{ $index }})" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all cursor-pointer">View Order</button>
                                    <button type="button" onclick="openDeleteConfirmModal({{ $index }})" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all cursor-pointer">Delete</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center text-gray-400 dark:text-slate-500 font-medium text-sm">No parked orders found.</div>
                @endif
            </div>

            @foreach($parkedList as $index => $parked)
                <div id="parkedDetails{{ $index }}" class="hidden">
                    <div class="flex items-center justify-between pb-4 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Order Details</h3>
                            <p class="text-xs text-gray-400 mt-1">{{ $parked['id'] ?? 'Parked Order' }} · {{ $parked['time'] ?? 'N/A' }}</p>
                        </div>
                        <button type="button" onclick="backToParkedSummary()" aria-label="Close order details" class="text-gray-400 hover:text-rose-500 p-2 text-2xl font-bold">&times;</button>
                    </div>

                    <div class="space-y-3">
                        @php $parkedSubtotal = 0; @endphp
                        @forelse(($parked['cart'] ?? []) as $productId => $item)
                            @php
                                $lineBase = (float) ($item['selling_price'] ?? 0) * (int) ($item['quantity'] ?? 0);
                                $lineDiscount = 0;
                                $lineType = $item['item_discount_type'] ?? 'none';
                                $lineValue = (float) ($item['item_discount_value'] ?? 0);
                                $lineScope = $item['item_discount_scope'] ?? 'whole';
                                if ($lineType === 'percentage') $lineDiscount = $lineBase * ($lineValue / 100);
                                elseif ($lineType === 'amount') $lineDiscount = min($lineBase, $lineScope === 'per_item' ? $lineValue * (int) $item['quantity'] : $lineValue);
                                $lineSubtotal = max(0, $lineBase - $lineDiscount);
                                $parkedSubtotal += $lineSubtotal;
                            @endphp
                            <div class="flex items-center justify-between gap-4 border border-gray-200 dark:border-[#1e2536] rounded-xl p-4">
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $item['product_name'] ?? 'Product' }}
                                        @if(($item['type'] ?? 'product') === 'product')
                                            <span class="text-xs font-normal text-gray-500">(From Store)</span>
                                        @endif
                                    </h4>
                                    @if(($item['type'] ?? 'product') === 'project')
                                        <p class="text-xs text-gray-400 mt-1">Custom bundle</p>
                                    @else
                                        <p class="text-xs text-gray-400 mt-1">Qty: {{ $item['quantity'] }} × ₱{{ number_format((float) $item['selling_price'], 2) }}</p>
                                    @endif
                                    @if($lineDiscount > 0)<p class="text-[11px] text-rose-500">Item discount: -₱{{ number_format($lineDiscount, 2) }}</p>@endif
                                </div>
                                <div class="text-sm font-bold text-emerald-600">₱{{ number_format($lineSubtotal, 2) }}</div>
                            </div>
                        @empty
                            <p class="py-8 text-center text-gray-400">This parked order has no products.</p>
                        @endforelse
                    </div>

                    @php
                        $parkedDiscount = $parked['cart_discount'] ?? ['type' => 'none', 'value' => 0, 'mode' => 'percentage'];
                        $parkedCartDiscountAmount = 0;
                        if (in_array($parkedDiscount['type'] ?? 'none', ['senior', 'pwd'])) $parkedCartDiscountAmount = $parkedSubtotal * 0.20;
                        elseif (in_array($parkedDiscount['type'] ?? 'none', ['percentage', 'employee', 'voucher'])) {
                            $mode = $parkedDiscount['mode'] ?? 'percentage';
                            $parkedCartDiscountAmount = $mode === 'percentage' ? $parkedSubtotal * ((float) ($parkedDiscount['value'] ?? 0) / 100) : min($parkedSubtotal, (float) ($parkedDiscount['value'] ?? 0));
                        } elseif (($parkedDiscount['type'] ?? 'none') === 'amount') $parkedCartDiscountAmount = min($parkedSubtotal, (float) ($parkedDiscount['value'] ?? 0));
                        
                        // For consistency, adding VAT to parked order display as well
                        $parkedTotalBeforeTax = max(0, $parkedSubtotal - $parkedCartDiscountAmount);
                        $parkedVatAmount = $parkedTotalBeforeTax * 0.12;
                        $parkedGrandTotal = $parkedTotalBeforeTax + $parkedVatAmount;
                    @endphp
                    <div class="mt-5 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536] space-y-2">
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Subtotal after item discounts</span><span class="font-semibold">₱{{ number_format($parkedSubtotal, 2) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Cart discount</span><span class="font-semibold text-rose-500">-₱{{ number_format($parkedCartDiscountAmount, 2) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Tax (12% VAT)</span><span class="font-semibold">₱{{ number_format($parkedVatAmount, 2) }}</span></div>
                        <div class="flex justify-between text-lg font-bold"><span class="text-gray-900 dark:text-white">Total</span><span class="text-emerald-600">₱{{ number_format($parkedGrandTotal, 2) }}</span></div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between gap-3 mt-6">
                        <button type="button" onclick="backToParkedSummary()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all cursor-pointer">Back to Summary</button>
                        <div class="flex gap-2">
                            <form action="{{ route('pos.parked.add', $index) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-white border-2 border-blue-600 text-blue-700 hover:bg-blue-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-blue-600/80 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all cursor-pointer">Add</button>
                            </form>
                            <button type="button" onclick="openRestoreConfirmModal({{ $index }})" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all cursor-pointer">Restore</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- CLEAR CART MODAL -->
    <div id="clearModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 text-center" onclick="event.stopPropagation()">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-rose-100 dark:bg-rose-950/50 mb-5">
                <svg class="h-8 w-8 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Clear Cart?</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">This will remove all items and release the reserved stock.</p>
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeClearModal()" class="w-full border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-full hover:bg-slate-700 hover:text-white transition-colors">Cancel</button>
                <form action="{{ route('pos.clear') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-md transition-colors">Yes, Clear</button>
                </form>
            </div>
        </div>
    </div>

    <!-- DELETE PARKED ORDER MODAL -->
    <div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 text-center" onclick="event.stopPropagation()">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-rose-100 dark:bg-rose-950/50 mb-5">
                <svg class="h-8 w-8 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Delete Parked Order?</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">This will permanently delete this order and release its stock.</p>
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeDeleteConfirmModal()" class="w-full border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-full hover:bg-slate-700 hover:text-white transition-colors">Cancel</button>
                <form id="deleteConfirmForm" method="POST" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-md transition-colors">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- RESTORE PARKED ORDER MODAL -->
    <div id="restoreConfirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 text-center" onclick="event.stopPropagation()">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100 dark:bg-emerald-950/50 mb-5">
                <svg class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Restore Order?</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">This will move the items into your active cart and re-deduct the stock.</p>
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeRestoreConfirmModal()" class="w-full border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-full hover:bg-slate-700 hover:text-white transition-colors">Cancel</button>
                <form id="restoreConfirmForm" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-md transition-colors">Yes, Restore</button>
                </form>
            </div>
        </div>
    </div>

    <!-- DISCOUNT MODAL -->
    <div id="discountModal" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto custom-scrollbar" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Configure Discount</h3>
                <button type="button" onclick="closeDiscountModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('pos.discount') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="discount_type" id="selectedDiscountType" value="{{ $cartDiscount['type'] ?? 'none' }}">
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                    <button type="button" onclick="selectDiscountCard('none')" class="discount-card border-2 {{ ($cartDiscount['type'] ?? 'none') == 'none' ? 'border-emerald-500 text-emerald-600' : 'border-gray-200 text-gray-600' }} rounded-xl p-3 text-center transition-all">No Discount</button>
                    <button type="button" onclick="selectDiscountCard('senior')" id="card-senior" class="discount-card border-2 {{ ($cartDiscount['type'] ?? 'none') == 'senior' ? 'border-emerald-500 text-emerald-600' : 'border-gray-200 text-gray-600' }} rounded-xl p-3 text-center transition-all">Senior</button>
                    <button type="button" onclick="selectDiscountCard('pwd')" id="card-pwd" class="discount-card border-2 {{ ($cartDiscount['type'] ?? 'none') == 'pwd' ? 'border-emerald-500 text-emerald-600' : 'border-gray-200 text-gray-600' }} rounded-xl p-3 text-center transition-all">PWD</button>
                    <button type="button" onclick="selectDiscountCard('employee')" id="card-employee" class="discount-card border-2 {{ ($cartDiscount['type'] ?? 'none') == 'employee' ? 'border-emerald-500 text-emerald-600' : 'border-gray-200 text-gray-600' }} rounded-xl p-3 text-center transition-all">Employee</button>
                    <button type="button" onclick="selectDiscountCard('voucher')" id="card-voucher" class="discount-card border-2 {{ ($cartDiscount['type'] ?? 'none') == 'voucher' ? 'border-emerald-500 text-emerald-600' : 'border-gray-200 text-gray-600' }} rounded-xl p-3 text-center transition-all">Voucher</button>
                    <button type="button" onclick="selectDiscountCard('percentage')" id="card-percentage" class="discount-card border-2 {{ ($cartDiscount['type'] ?? 'none') == 'percentage' ? 'border-emerald-500 text-emerald-600' : 'border-gray-200 text-gray-600' }} rounded-xl p-3 text-center transition-all">Custom %</button>
                    <button type="button" onclick="selectDiscountCard('amount')" id="card-amount" class="discount-card border-2 {{ ($cartDiscount['type'] ?? 'none') == 'amount' ? 'border-emerald-500 text-emerald-600' : 'border-gray-200 text-gray-600' }} rounded-xl p-3 text-center transition-all">Custom ₱</button>
                </div>

                <div id="dynamicInputContainer" class="{{ ($cartDiscount['type'] ?? 'none') == 'none' ? 'hidden' : '' }} space-y-4 bg-gray-50 dark:bg-[#161B26] p-4 rounded-xl border border-gray-200 dark:border-[#1e2536]">
                    
                    <div id="fields-senior-pwd" class="discount-field-group hidden space-y-3">
                        <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-xl border border-blue-200 dark:border-blue-800 text-xs text-blue-700 dark:text-blue-300 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Standard 20% discount will be automatically applied.
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Customer Name</label>
                            <input type="text" name="customer_name" value="{{ $cartDiscount['customer_name'] ?? '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID Number</label>
                                <input type="text" name="id_number" value="{{ $cartDiscount['id_number'] ?? '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">TIN</label>
                                <input type="text" name="tin_number" value="{{ $cartDiscount['tin_number'] ?? '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                            </div>
                        </div>
                    </div>

                    <div id="fields-employee" class="discount-field-group hidden space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Employee Name</label>
                                <input type="text" name="employee_name" value="{{ $cartDiscount['employee_name'] ?? '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Employee ID</label>
                                <input type="text" name="employee_id" value="{{ $cartDiscount['employee_id'] ?? '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Mode</label>
                            <select name="employee_mode" id="employeeModeSelect" onchange="toggleEmployeeMode()" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                                <option value="percentage" {{ ($cartDiscount['mode'] ?? '') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="amount" {{ ($cartDiscount['mode'] ?? '') == 'amount' ? 'selected' : '' }}>Amount (₱)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1" id="employeeValueLabel">Value</label>
                            <input type="number" step="0.01" min="0" name="employee_percentage" id="employeeValueInput" value="{{ $cartDiscount['value'] ?? '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                        </div>
                    </div>

                    <div id="fields-voucher" class="discount-field-group hidden space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Voucher Code</label>
                            <input type="text" name="voucher_code" value="{{ $cartDiscount['voucher_code'] ?? '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Mode</label>
                            <select name="voucher_mode" id="voucherModeSelect" onchange="toggleVoucherMode()" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                                <option value="percentage" {{ ($cartDiscount['mode'] ?? '') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="amount" {{ ($cartDiscount['mode'] ?? '') == 'amount' ? 'selected' : '' }}>Amount (₱)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1" id="voucherValueLabel">Value</label>
                            <input type="number" step="0.01" min="0" name="voucher_percentage" id="voucherValueInput" value="{{ $cartDiscount['value'] ?? '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                        </div>
                    </div>

                    <div id="fields-percentage" class="discount-field-group hidden space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Percentage (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="percentage_value" value="{{ ($cartDiscount['type'] ?? '') == 'percentage' ? ($cartDiscount['value'] ?? '') : '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                        </div>
                    </div>

                    <div id="fields-amount" class="discount-field-group hidden space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Amount (₱)</label>
                            <input type="number" step="0.01" min="0" name="amount_value" value="{{ ($cartDiscount['type'] ?? '') == 'amount' ? ($cartDiscount['value'] ?? '') : '' }}" class="w-full rounded-xl border-gray-200 dark:border-[#1e2536] dark:bg-[#10141D] dark:text-white text-sm py-2 px-3 focus:outline-none focus:border-slate-400">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="closeDiscountModal()" class="border-2 border-slate-300 text-slate-700 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-slate-700 hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-6 py-2.5 rounded-full transition-all shadow-xs">Apply Discount</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #334155; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('scripts')
<script>
    // =====================================================
    // DUAL-MODE POS LOGIC & LOCKING
    // =====================================================
    let currentPosMode = 'retail';

    window.addEventListener('DOMContentLoaded', () => {
        const hasProject = {{ $hasProjectInCart ? 'true' : 'false' }};
        const cartCount = {{ count($cart) }};

        if (hasProject) {
            setPosMode('project', true);
            const btnRetail = document.getElementById('btn-mode-retail');
            btnRetail.disabled = true;
            btnRetail.classList.add('opacity-50', 'cursor-not-allowed');
            btnRetail.title = "Clear the project from the cart to switch back to Retail Mode.";
        } else if (cartCount > 0) {
            setPosMode('retail', true);
            const btnProject = document.getElementById('btn-mode-project');
            btnProject.disabled = true;
            btnProject.classList.add('opacity-50', 'cursor-not-allowed');
            btnProject.title = "Clear the cart to switch to Project Mode.";
        } else {
            setPosMode('retail', true);
        }
    });

    window.setPosMode = function(mode, force = false) {
        const btnRetail = document.getElementById('btn-mode-retail');
        const btnProject = document.getElementById('btn-mode-project');
        const btnCreateProject = document.getElementById('btn-create-project');
        
        const cartItemsCount = {{ count($cart) }};
        if (!force && cartItemsCount > 0 && currentPosMode !== mode) {
            alert('Please clear your current cart before switching modes.');
            return;
        }

        currentPosMode = mode;

        if (mode === 'retail') {
            btnRetail.className = 'px-6 py-2 rounded-full text-sm font-bold transition-all bg-white dark:bg-[#10141D] shadow-xs text-emerald-600 dark:text-emerald-400';
            btnProject.className = 'px-6 py-2 rounded-full text-sm font-bold transition-all text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200';
            if (btnCreateProject) btnCreateProject.classList.add('hidden');
        } else {
            btnProject.className = 'px-6 py-2 rounded-full text-sm font-bold transition-all bg-white dark:bg-[#10141D] shadow-xs text-emerald-600 dark:text-emerald-400';
            btnRetail.className = 'px-6 py-2 rounded-full text-sm font-bold transition-all text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200';
            if (btnCreateProject) btnCreateProject.classList.remove('hidden');
        }
    };

    // =====================================================
    // WARNING MODAL LOGIC
    // =====================================================
    window.openProjectWarningModal = function() {
        document.getElementById('projectWarningModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeProjectWarningModal = function() {
        document.getElementById('projectWarningModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // =====================================================
    // QUICK ADD MODAL (Project Mode)
    // =====================================================
    let qaStoreQty = 0;
    let qaWhQty = 0;

    window.openQuickAdd = function(id, name, storeQty, whQty) {
        qaStoreQty = Math.max(0, storeQty);
        qaWhQty = Math.max(0, whQty);
        
        if (qaStoreQty <= 0 && qaWhQty <= 0) {
            alert('This product is completely out of stock.');
            return;
        }

        document.getElementById('quickAddProductId').value = id;
        document.getElementById('quickAddTitle').textContent = 'Add: ' + name;
        
        const sourceSelect = document.getElementById('quickAddSource');
        const storeOpt = sourceSelect.querySelector('option[value="store"]');
        const whOpt = sourceSelect.querySelector('option[value="warehouse"]');

        storeOpt.disabled = (qaStoreQty <= 0);
        storeOpt.textContent = qaStoreQty <= 0 ? 'Store (Out of Stock)' : 'Store';
        
        whOpt.disabled = (qaWhQty <= 0);
        whOpt.textContent = qaWhQty <= 0 ? 'Warehouse (Out of Stock)' : 'Warehouse';

        if (qaStoreQty > 0) {
            sourceSelect.value = 'store';
        } else if (qaWhQty > 0) {
            sourceSelect.value = 'warehouse';
        }
        
        updateQuickAddMax();
        document.getElementById('quickAddModal').classList.remove('hidden');
    };

    window.closeQuickAddModal = function() {
        document.getElementById('quickAddModal').classList.add('hidden');
    };

    window.updateQuickAddMax = function() {
        const source = document.getElementById('quickAddSource').value;
        const avail = source === 'store' ? qaStoreQty : qaWhQty;
        const input = document.getElementById('quickAddQty');
        
        input.max = avail;
        if (avail <= 0) {
            input.value = 0;
            input.disabled = true;
            document.getElementById('quickAddAvailable').innerHTML = '<span class="text-rose-500">Out of stock!</span>';
        } else {
            input.disabled = false;
            if (parseInt(input.value) > avail) input.value = avail;
            if (parseInt(input.value) <= 0) input.value = 1;
            document.getElementById('quickAddAvailable').innerHTML = `Available: <span class="text-emerald-500">${avail}</span>`;
        }
    };

    // =====================================================
    // QUICK DROP MODAL (Project Mode)
    // =====================================================
    let qdProjStoreQty = 0;
    let qdProjWhQty = 0;

    window.openQuickDrop = function(id, name, projStoreQty, projWhQty) {
        if ((projStoreQty + projWhQty) <= 0) {
            alert('This product is not currently in the project.');
            return;
        }

        qdProjStoreQty = Math.max(0, projStoreQty);
        qdProjWhQty = Math.max(0, projWhQty);
        
        document.getElementById('quickDropProductId').value = id;
        document.getElementById('quickDropTitle').textContent = 'Drop: ' + name;
        
        const sourceSelect = document.getElementById('quickDropSource');
        const storeOpt = sourceSelect.querySelector('option[value="store"]');
        const whOpt = sourceSelect.querySelector('option[value="warehouse"]');

        storeOpt.disabled = (qdProjStoreQty <= 0);
        storeOpt.textContent = qdProjStoreQty <= 0 ? 'Store (None in Project)' : 'Store';
        
        whOpt.disabled = (qdProjWhQty <= 0);
        whOpt.textContent = qdProjWhQty <= 0 ? 'Warehouse (None in Project)' : 'Warehouse';

        if (qdProjStoreQty > 0) {
            sourceSelect.value = 'store';
        } else if (qdProjWhQty > 0) {
            sourceSelect.value = 'warehouse';
        }
        
        updateQuickDropMax();
        document.getElementById('quickDropModal').classList.remove('hidden');
    };

    window.closeQuickDropModal = function() {
        document.getElementById('quickDropModal').classList.add('hidden');
    };

    window.updateQuickDropMax = function() {
        const source = document.getElementById('quickDropSource').value;
        const avail = source === 'store' ? qdProjStoreQty : qdProjWhQty;
        const input = document.getElementById('quickDropQty');
        
        input.max = avail;
        if (avail <= 0) {
            input.value = 0;
            input.disabled = true;
            document.getElementById('quickDropAvailable').innerHTML = '<span class="text-rose-500">None in project for this source!</span>';
        } else {
            input.disabled = false;
            if (parseInt(input.value) > avail) input.value = avail;
            if (parseInt(input.value) <= 0) input.value = 1;
            document.getElementById('quickDropAvailable').innerHTML = `In Project: <span class="text-amber-500">${avail}</span>`;
        }
    };

    // =====================================================
    // CUSTOM CATEGORY DROPDOWN
    // =====================================================
    const categoryDropdown = document.getElementById('categoryDropdown');
    const categoryTrigger = document.getElementById('categoryTrigger');
    const categoryMenu = document.getElementById('categoryMenu');
    const categoryChevron = document.getElementById('categoryChevron');
    const categoryHiddenInput = document.getElementById('categoryHiddenInput');

    function openCategoryMenu() {
        if (!categoryMenu) return;
        categoryMenu.classList.remove('hidden');
        if (categoryChevron) categoryChevron.classList.add('rotate-180');
    }

    function closeCategoryMenu() {
        if (!categoryMenu) return;
        categoryMenu.classList.add('hidden');
        if (categoryChevron) categoryChevron.classList.remove('rotate-180');
    }

    if (categoryDropdown && categoryTrigger && categoryMenu) {
        categoryTrigger.addEventListener('click', function (event) {
            event.stopPropagation();
            if (categoryMenu.classList.contains('hidden')) {
                openCategoryMenu();
            } else {
                closeCategoryMenu();
            }
        });

        categoryMenu.addEventListener('click', function (event) {
            const option = event.target.closest('.category-option');
            if (!option) return;

            event.stopPropagation();
            const value = option.dataset.value || '';
            
            const labelEl = document.getElementById('categoryLabel');
            if (labelEl) {
                labelEl.textContent = option.textContent.trim();
            }
            closeCategoryMenu();

            const url = new URL(window.location.href);
            const searchParam = new URLSearchParams(window.location.search).get('search');
            if (searchParam) {
                url.searchParams.set('search', searchParam);
            } else {
                url.searchParams.delete('search');
            }

            if (value) {
                url.searchParams.set('category_id', value);
            } else {
                url.searchParams.delete('category_id');
            }

            window.location.href = url.toString();
        });

        document.addEventListener('click', function (event) {
            if (!categoryDropdown.contains(event.target)) {
                closeCategoryMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeCategoryMenu();
            }
        });
    }

    // =====================================================
    // EXISTING POS LOGIC
    // =====================================================

    function updateQty(productId, actionType, exactVal = null, view = 'grid') {
        if (currentPosMode === 'project') {
            openProjectWarningModal();
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/pos/add/${productId}`;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        if (actionType === 'exact') {
            const quantity = Number.parseInt(exactVal, 10);
            const exactInput = document.createElement('input');
            exactInput.type = 'hidden';
            exactInput.name = 'exact_quantity';
            exactInput.value = Number.isInteger(quantity) && quantity >= 0 ? quantity : 0;
            form.appendChild(exactInput);
        } else {
            const quantityInput = document.getElementById(`qty-${productId}-${view}`);
            const quantity = quantityInput ? Number.parseInt(quantityInput.value, 10) : 1;

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = actionType;
            form.appendChild(actionInput);

            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = 'quantity';
            qtyInput.value = Number.isInteger(quantity) && quantity >= 1 ? quantity : 1;
            form.appendChild(qtyInput);
        }

        document.body.appendChild(form);
        form.submit();
    }

    function openParkedModal() {
        const modal = document.getElementById('parkedModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        backToParkedSummary();
        document.body.classList.add('overflow-hidden');
    }

    function closeParkedModal() {
        const modal = document.getElementById('parkedModal');
        if (modal) modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function showParkedDetails(index) {
        const summary = document.getElementById('parkedSummaryScreen');
        if (summary) summary.classList.add('hidden');
        document.querySelectorAll('[id^="parkedDetails"]').forEach(el => el.classList.add('hidden'));
        const details = document.getElementById('parkedDetails' + index);
        if (details) details.classList.remove('hidden');
    }

    function backToParkedSummary() {
        document.querySelectorAll('[id^="parkedDetails"]').forEach(el => el.classList.add('hidden'));
        const summary = document.getElementById('parkedSummaryScreen');
        if (summary) summary.classList.remove('hidden');
    }

    function openDeleteConfirmModal(index) {
        const modal = document.getElementById('deleteConfirmModal');
        const form = document.getElementById('deleteConfirmForm');
        form.action = `/pos/parked/${index}`;
        modal.classList.remove('hidden');
    }

    function closeDeleteConfirmModal() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    }

    function openRestoreConfirmModal(index) {
        const modal = document.getElementById('restoreConfirmModal');
        const form = document.getElementById('restoreConfirmForm');
        form.action = `/pos/parked/${index}/restore`;
        modal.classList.remove('hidden');
    }

    function closeRestoreConfirmModal() {
        document.getElementById('restoreConfirmModal').classList.add('hidden');
    }

    function openClearModal() {
        document.getElementById('clearModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeClearModal() {
        document.getElementById('clearModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function openDiscountModal() {
        document.getElementById('discountModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        const currentType = document.getElementById('selectedDiscountType').value || 'none';
        selectDiscountCard(currentType);
    }

    function closeDiscountModal() {
        document.getElementById('discountModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function selectDiscountCard(type) {
        document.getElementById('selectedDiscountType').value = type;

        document.querySelectorAll('.discount-card').forEach(card => {
            card.classList.remove('border-emerald-500', 'text-emerald-600', 'bg-emerald-50/50', 'dark:bg-emerald-950/30');
            card.classList.add('border-gray-200', 'text-gray-600');
        });

        if (type !== 'none') {
            const activeCard = document.getElementById('card-' + type);
            if (activeCard) {
                activeCard.classList.remove('border-gray-200', 'text-gray-600');
                activeCard.classList.add('border-emerald-500', 'text-emerald-600', 'bg-emerald-50/50', 'dark:bg-emerald-950/30');
            }
        }

        const dynamicContainer = document.getElementById('dynamicInputContainer');
        document.querySelectorAll('.discount-field-group').forEach(group => group.classList.add('hidden'));

        if (type === 'senior' || type === 'pwd') {
            dynamicContainer.classList.remove('hidden');
            document.getElementById('fields-senior-pwd').classList.remove('hidden');
        } else if (type === 'employee') {
            dynamicContainer.classList.remove('hidden');
            document.getElementById('fields-employee').classList.remove('hidden');
            toggleEmployeeMode();
        } else if (type === 'voucher') {
            dynamicContainer.classList.remove('hidden');
            document.getElementById('fields-voucher').classList.remove('hidden');
            toggleVoucherMode();
        } else if (type === 'percentage') {
            dynamicContainer.classList.remove('hidden');
            document.getElementById('fields-percentage').classList.remove('hidden');
        } else if (type === 'amount') {
            dynamicContainer.classList.remove('hidden');
            document.getElementById('fields-amount').classList.remove('hidden');
        } else {
            dynamicContainer.classList.add('hidden');
        }
    }

    function toggleEmployeeMode() {
        const mode = document.getElementById('employeeModeSelect').value;
        const label = document.getElementById('employeeValueLabel');
        const input = document.getElementById('employeeValueInput');
        if (mode === 'percentage') {
            label.innerText = 'Percentage (%)';
            input.placeholder = 'e.g., 10';
            input.max = '100';
        } else {
            label.innerText = 'Amount (₱)';
            input.placeholder = 'e.g., 100.00';
            input.removeAttribute('max');
        }
    }

    function toggleVoucherMode() {
        const mode = document.getElementById('voucherModeSelect').value;
        const label = document.getElementById('voucherValueLabel');
        const input = document.getElementById('voucherValueInput');
        if (mode === 'percentage') {
            label.innerText = 'Percentage (%)';
            input.placeholder = 'e.g., 15';
            input.max = '100';
        } else {
            label.innerText = 'Amount (₱)';
            input.placeholder = 'e.g., 50.00';
            input.removeAttribute('max');
        }
    }

    let currentView = 'grid';

    function switchView(viewType) {
        currentView = viewType;
        const gridView = document.getElementById('productGridView');
        const listView = document.getElementById('productListView');
        const gridBtn = document.getElementById('gridViewBtn');
        const listBtn = document.getElementById('listViewBtn');

        if (viewType === 'list') {
            gridView.classList.add('hidden');
            listView.classList.remove('hidden');
            listBtn.classList.add('bg-white', 'dark:bg-[#10141D]', 'shadow-xs', 'text-emerald-600');
            gridBtn.classList.remove('bg-white', 'dark:bg-[#10141D]', 'shadow-xs', 'text-emerald-600');
            localStorage.setItem('pos_view_mode', 'list');
        } else {
            listView.classList.add('hidden');
            gridView.classList.remove('hidden');
            gridBtn.classList.add('bg-white', 'dark:bg-[#10141D]', 'shadow-xs', 'text-emerald-600');
            listBtn.classList.remove('bg-white', 'dark:bg-[#10141D]', 'shadow-xs', 'text-emerald-600');
            localStorage.setItem('pos_view_mode', 'grid');
        }
        
        if (typeof updateResultsCounter === 'function') updateResultsCounter();
    }

    // =====================================================
    // CUSTOM PROJECT LOGIC (MODAL & CALCULATIONS)
    // =====================================================
    @php
        $allProductsData = \App\Models\Product::select('id', 'product_name', 'selling_price', 'category_id', 'store_quantity', 'warehouse_quantity')->get();
    @endphp
    
    // Sanitize all products to have 0 as minimum stock
    const rawProducts = @json($allProductsData);
    const allProducts = rawProducts.map(p => {
        p.store_quantity = Math.max(0, p.store_quantity);
        p.warehouse_quantity = Math.max(0, p.warehouse_quantity);
        return p;
    });

    const allCategories = @json($categories);
    let materialRowCounter = 0;
    
    // FIX: Track reserved quantities so the Edit Modal doesn't clamp them down
    let cartReserved = {};

    function toggleProjectModal(show, isEdit = false) {
        if (show && {{ $hasProjectInCart ? 'true' : 'false' }} && !isEdit) {
            alert('You already have an active project in the cart. Please clear it first.');
            return;
        }

        document.getElementById('projectModal').classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
        
        if (!show) {
            document.getElementById('projectForm').reset();
            document.getElementById('projectMaterialsContainer').innerHTML = '';
            document.getElementById('edit_project_id').value = '';
            document.getElementById('projectForm').action = "{{ route('pos.addProject') }}";
            document.getElementById('projectSubmitBtn').textContent = 'Add Project to Cart';
            document.getElementById('fileNameDisplay').textContent = 'Click to upload';
            document.getElementById('projectModalTitle').textContent = 'Create Custom Project / Service';
            materialRowCounter = 0;
            cartReserved = {}; // Clear reserved on close
        } else if (!isEdit && document.getElementById('projectMaterialsContainer').children.length === 0) {
            addProjectMaterialRow();
        }
    }

    async function editProject(projectId) {
        try {
            const response = await fetch(`/pos/project/${projectId}/details`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Failed to load project details.');
            
            const data = await response.json();
            
            toggleProjectModal(true, true);
            
            document.getElementById('projectForm').action = `/pos/update-project/${projectId}`;
            document.getElementById('edit_project_id').value = projectId;
            document.getElementById('projectSubmitBtn').textContent = 'Update Project';
            document.getElementById('projectModalTitle').textContent = 'Edit Custom Project / Service';
            
            document.getElementById('project_name').value = data.name;
            document.getElementById('project_description').value = data.description || '';
            
            document.getElementById('labor_cost').value = data.breakdown.labor_cost;
            document.getElementById('installation_cost').value = data.breakdown.installation_cost;
            document.getElementById('additional_cost').value = data.breakdown.additional_cost;
            document.getElementById('project_freight').value = data.breakdown.freight;
            document.getElementById('markupType').value = data.breakdown.markup_type;
            document.getElementById('markupValue').value = data.breakdown.markup_value;
            
            document.getElementById('projectMaterialsContainer').innerHTML = '';
            
            // Build the reserved list BEFORE adding rows
            cartReserved = {};
            data.breakdown.materials.forEach(mat => {
                let key = mat.product_id + '_' + mat.source;
                cartReserved[key] = (cartReserved[key] || 0) + mat.qty;
            });
            
            data.breakdown.materials.forEach(mat => {
                addProjectMaterialRow(mat);
            });
            
            calculateProjectTotal();
            
        } catch (error) {
            console.error(error);
            alert('Error loading project details. Please try again.');
        }
    }

    function addProjectMaterialRow(prefilledData = null) {
        const container = document.getElementById('projectMaterialsContainer');
        const rowId = 'mat-row-' + materialRowCounter;
        
        let catOptionsHtml = `<button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All Categories</button>`;
        allCategories.forEach(mainCat => {
            catOptionsHtml += `
                <div class="mt-1">
                    <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">${mainCat.name}</div>
                    <button type="button" data-value="${mainCat.id}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">${mainCat.name}</button>
                    ${mainCat.children.map(child => `
                        <button type="button" data-value="${child.id}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">${child.name}</button>
                    `).join('')}
                </div>
            `;
        });

        const html = `
            <div class="flex flex-wrap gap-2 items-start bg-gray-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-2 rounded-xl" id="${rowId}">
                
                <div class="custom-dropdown relative w-32 shrink-0 row-cat-dp">
                    <input type="hidden" class="cat-val" value="">
                    <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-1.5 text-xs text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left h-[36px]">
                        <span class="dropdown-label truncate">All Categories</span>
                        <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                        ${catOptionsHtml}
                    </div>
                </div>

                <div class="flex flex-col flex-1 min-w-[180px]">
                    <div class="custom-dropdown relative w-full row-prod-dp">
                        <input type="hidden" name="materials[${materialRowCounter}][product_id]" class="prod-val" value="" required>
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-1.5 text-xs text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left h-[36px]">
                            <span class="dropdown-label truncate">Select Product...</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Select Product...</button>
                        </div>
                    </div>
                    <div class="text-[10px] font-semibold text-gray-500 mt-1.5 ml-1 row-stock-info">Select product to view stock.</div>
                </div>

                <div class="w-[110px] shrink-0">
                    <select name="materials[${materialRowCounter}][source]" class="w-full h-[36px] text-xs rounded-lg border-gray-300 dark:border-[#1e2536] dark:bg-[#10141D] text-gray-900 dark:text-white focus:outline-none focus:border-slate-400 cursor-pointer" onchange="updateRowInfo(this.closest('div[id^=\\'mat-row-\\']'))">
                        <option value="store">Store</option>
                        <option value="warehouse">Warehouse</option>
                    </select>
                </div>
                
                <div class="w-[70px] shrink-0">
                    <input type="number" name="materials[${materialRowCounter}][quantity]" value="1" min="1" class="w-full text-sm rounded-lg border-gray-300 dark:border-[#1e2536] dark:bg-[#10141D] text-center text-gray-900 dark:text-white h-[36px] focus:outline-none focus:border-slate-400" oninput="updateRowInfo(this.closest('div[id^=\\'mat-row-\\']'))">
                </div>
                
                <div class="w-[80px] shrink-0 text-right text-sm font-bold text-gray-800 dark:text-slate-200 pt-2">
                    ₱<span class="row-subtotal">0.00</span>
                </div>

                <div class="shrink-0 pt-0.5">
                    <button type="button" onclick="document.getElementById('${rowId}').remove(); calculateProjectTotal();" class="text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 p-1.5 rounded transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        
        const newRow = document.getElementById(rowId);
        initRowDropdowns(newRow);
        
        if (prefilledData) {
            const catInput = newRow.querySelector('.cat-val');
            const prodInput = newRow.querySelector('.prod-val');
            const prodLabel = newRow.querySelector('.row-prod-dp .dropdown-label');
            const qtyInput = newRow.querySelector('input[type="number"]');
            const sourceSelect = newRow.querySelector('select[name*="[source]"]');
            
            const product = allProducts.find(p => p.id == prefilledData.product_id);
            if (product) {
                prodInput.value = product.id;
                prodLabel.textContent = product.product_name;
                
                catInput.value = product.category_id;
                const catBtn = newRow.querySelector(`.row-cat-dp button[data-value="${product.category_id}"]`);
                if (catBtn) newRow.querySelector('.row-cat-dp .dropdown-label').textContent = catBtn.textContent.trim();
            }
            
            qtyInput.value = prefilledData.qty;
            sourceSelect.value = prefilledData.source;
            
            updateRowInfo(newRow);
        }
        
        materialRowCounter++;
    }

    function initRowDropdowns(row) {
        row.querySelectorAll('.custom-dropdown').forEach(dropdown => {
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const menu = dropdown.querySelector('.dropdown-menu');
            const chevron = dropdown.querySelector('.dropdown-chevron');
            const label = dropdown.querySelector('.dropdown-label');
            
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                document.querySelectorAll('.custom-dropdown .dropdown-menu').forEach(m => { if (m !== menu) m.classList.add('hidden'); });
                document.querySelectorAll('.custom-dropdown .dropdown-chevron').forEach(c => { if (c !== chevron) c.classList.remove('rotate-180'); });
                
                menu.classList.toggle('hidden');
                chevron.classList.toggle('rotate-180');
            });

            menu.addEventListener('click', (e) => {
                const option = e.target.closest('.dropdown-option');
                if (!option) return;
                e.stopPropagation();
                
                const value = option.dataset.value || '';
                label.textContent = option.textContent.trim();
                menu.classList.add('hidden');
                chevron.classList.remove('rotate-180');

                if (dropdown.classList.contains('row-cat-dp')) {
                    dropdown.querySelector('.cat-val').value = value;
                    filterRowProducts(row, value);
                } else if (dropdown.classList.contains('row-prod-dp')) {
                    dropdown.querySelector('.prod-val').value = value;
                    updateRowInfo(row);
                }
            });
        });
    }

    function filterRowProducts(row, catId) {
        const prodDropdown = row.querySelector('.row-prod-dp');
        const prodMenu = prodDropdown.querySelector('.dropdown-menu');
        const prodLabel = prodDropdown.querySelector('.dropdown-label');
        const prodInput = prodDropdown.querySelector('.prod-val');
        
        prodMenu.innerHTML = '<button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Select Product...</button>';
        prodLabel.textContent = 'Select Product...';
        prodInput.value = '';

        let validCatIds = [];
        if (catId) {
            validCatIds.push(parseInt(catId));
            const mainCat = allCategories.find(c => c.id == catId);
            if (mainCat && mainCat.children) {
                mainCat.children.forEach(child => validCatIds.push(child.id));
            }
        }

        allProducts.forEach(p => {
            if (catId === '' || validCatIds.includes(p.category_id)) {
                prodMenu.innerHTML += `<button type="button" data-value="${p.id}" data-price="${p.selling_price}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">${p.product_name}</button>`;
            }
        });

        updateRowInfo(row);
    }

    function updateRowInfo(row) {
        const prodInput = row.querySelector('.prod-val');
        const qtyInput = row.querySelector('input[type="number"]');
        const sourceSelect = row.querySelector('select[name*="[source]"]');
        const stockDisplay = row.querySelector('.row-stock-info');
        const subtotalDisplay = row.querySelector('.row-subtotal');
        
        let price = 0;
        let storeQty = 0;
        let whQty = 0;

        if (prodInput.value) {
            const selectedProduct = allProducts.find(p => p.id == prodInput.value);
            if (selectedProduct) {
                price = parseFloat(selectedProduct.selling_price);
                storeQty = parseInt(selectedProduct.store_quantity) || 0;
                whQty = parseInt(selectedProduct.warehouse_quantity) || 0;
            }
        }

        // Add reserved quantities
        let reservedStore = cartReserved[prodInput.value + '_store'] || 0;
        let reservedWh = cartReserved[prodInput.value + '_warehouse'] || 0;
        let totalStore = Math.max(0, storeQty) + reservedStore;
        let totalWh = Math.max(0, whQty) + reservedWh;

        let available = sourceSelect.value === 'store' ? totalStore : totalWh;
        available = Math.max(0, available);

        qtyInput.max = available;
        let qty = parseFloat(qtyInput.value) || 0;

        if (prodInput.value) {
            // Render the UI badge
            let stHtml = totalStore > 0 ? `<span class="bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded border border-emerald-200">ST: ${totalStore}</span>` : `<span class="bg-rose-50 text-rose-700 px-1.5 py-0.5 rounded border border-rose-200">ST: 0</span>`;
            let whHtml = totalWh > 0 ? `<span class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded border border-blue-200">WH: ${totalWh}</span>` : `<span class="bg-rose-50 text-rose-700 px-1.5 py-0.5 rounded border border-rose-200">WH: 0</span>`;
            
            stockDisplay.innerHTML = `<div class="flex items-center gap-1 mt-1">${stHtml} ${whHtml}</div>`;

            // Validate qty
            if (available === 0) {
                qtyInput.value = 0;
                qtyInput.disabled = true;
                qtyInput.classList.add('bg-gray-100', 'dark:bg-[#161B26]', 'cursor-not-allowed', 'border-rose-300');
                qty = 0;
            } else {
                qtyInput.disabled = false;
                qtyInput.classList.remove('bg-gray-100', 'dark:bg-[#161B26]', 'cursor-not-allowed', 'border-rose-300');

                if (qty <= 0) {
                    qty = 1;
                    qtyInput.value = 1;
                }

                if (qty > available) {
                    qty = available;
                    qtyInput.value = available;
                    qtyInput.classList.add('border-rose-500', 'bg-rose-50');
                    setTimeout(() => qtyInput.classList.remove('border-rose-500', 'bg-rose-50'), 500);
                }
            }
        } else {
            qtyInput.disabled = false;
            qtyInput.classList.remove('bg-gray-100', 'dark:bg-[#161B26]', 'cursor-not-allowed', 'border-rose-300');
            stockDisplay.innerHTML = '<span class="text-gray-400">Select product to view stock.</span>';
        }

        const subtotal = price * Math.max(0, qty);
        subtotalDisplay.textContent = subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        calculateProjectTotal();
    }

    function calculateProjectTotal() {
        let materialsSubtotal = 0;
        document.querySelectorAll('#projectMaterialsContainer .row-subtotal').forEach(el => {
            materialsSubtotal += parseFloat(el.textContent.replace(/,/g, '')) || 0;
        });

        const labor = parseFloat(document.querySelector('input[name="labor_cost"]').value) || 0;
        const install = parseFloat(document.querySelector('input[name="installation_cost"]').value) || 0;
        const additional = parseFloat(document.querySelector('input[name="additional_cost"]').value) || 0;
        const freight = parseFloat(document.querySelector('input[name="project_freight"]').value) || 0;
        
        const projectSubtotal = materialsSubtotal + labor + install + additional + freight;
        document.getElementById('projectSubtotalDisplay').textContent = '₱' + projectSubtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const markupType = document.getElementById('markupType').value;
        const markupValue = parseFloat(document.getElementById('markupValue').value) || 0;
        
        let markupAmount = 0;
        if (markupType === 'percentage') {
            markupAmount = projectSubtotal * (markupValue / 100);
        } else {
            markupAmount = markupValue;
        }

        const finalTotal = projectSubtotal + markupAmount;
        document.getElementById('projectFinalTotalDisplay').textContent = '₱' + finalTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function validateProjectForm() {
        let hasValidProduct = false;
        let stockError = false;
        let errorMsg = '';

        document.querySelectorAll('#projectMaterialsContainer > div').forEach(row => {
            const prodInput = row.querySelector('.prod-val');
            if (!prodInput.value) return;

            hasValidProduct = true;
            const qtyInput = row.querySelector('input[type="number"]');
            const sourceSelect = row.querySelector('select[name*="[source]"]');
            const qty = parseFloat(qtyInput.value) || 0;
            const source = sourceSelect.value;

            const selectedProduct = allProducts.find(p => p.id == prodInput.value);
            if (selectedProduct) {
                let dbAvailable = source === 'store' ? Math.max(0, parseInt(selectedProduct.store_quantity)) : Math.max(0, parseInt(selectedProduct.warehouse_quantity));
                let reserved = cartReserved[prodInput.value + '_' + source] || 0;
                let available = dbAvailable + reserved;
                
                if (available <= 0) {
                    errorMsg = `Cannot add ${selectedProduct.product_name}. It is out of stock in the ${source}.`;
                    stockError = true;
                } else if (qty > available) {
                    errorMsg = `Not enough stock for ${selectedProduct.product_name} in ${source}. Available: ${available}`;
                    stockError = true;
                } else if (qty < 1) {
                    errorMsg = `Quantity for ${selectedProduct.product_name} must be at least 1.`;
                    stockError = true;
                }
            }
        });

        if (!hasValidProduct) {
            alert('Please select at least one product/material for this project.');
            return false;
        }

        if (stockError) {
            alert(errorMsg);
            return false;
        }

        return true;
    }

    // =====================================================
    // INFINITE SCROLL LOGIC & INITIALIZATION
    // =====================================================
    document.addEventListener('DOMContentLoaded', () => {
        const savedMode = localStorage.getItem('pos_view_mode') || 'grid';
        switchView(savedMode);

        const params = new URLSearchParams(window.location.search);
        if (params.get('show_parked') === '1') {
            openParkedModal();
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        const tableContainer = document.getElementById('productScrollContainer');
        const tableBody = document.getElementById('productGridView'); 
        const tableBodyList = document.getElementById('productListView'); 
        const loadingIndicator = document.getElementById('loadingIndicator');
        const resultsText = document.getElementById('resultsText');

        let nextPageUrl = @json($items->nextPageUrl());
        let currentPage = {{ $items->currentPage() }};
        let lastPage = {{ $items->lastPage() }};
        let totalRecords = {{ $items->total() }};
        let perPage = {{ $items->perPage() }};
        let initialPage = {{ $items->currentPage() }};
        let initialStart = {{ $items->firstItem() ?? 0 }} || 0;

        let isLoading = false;

        function getVisiblePage() {
            let rows = [];
            if (currentView === 'grid') {
                rows = Array.from(tableBody.querySelectorAll('[data-record-row="true"]'));
            } else {
                rows = Array.from(tableBodyList.querySelectorAll('[data-record-row="true"]'));
            }
            
            if (!rows.length) return initialPage;

            const isAtBottom = (tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight) <= 5;
            if (isAtBottom && lastPage > 0) return lastPage;

            const containerRect = tableContainer.getBoundingClientRect();
            const visibleTop = containerRect.top;
            const visibleBottom = containerRect.bottom;

            let firstVisibleIndex = -1;

            rows.forEach((row, index) => {
                const rect = row.getBoundingClientRect();
                if (firstVisibleIndex === -1 && rect.bottom > visibleTop && rect.top < visibleBottom) {
                    firstVisibleIndex = index;
                }
            });

            if (firstVisibleIndex === -1) firstVisibleIndex = rows.length - 1;

            let rowsPerVisualLine = 1;
            if (currentView === 'grid') {
                const computedStyle = window.getComputedStyle(tableBody);
                const columns = computedStyle.gridTemplateColumns.split(' ').length;
                rowsPerVisualLine = columns;
            }

            const linesScrolled = Math.floor(firstVisibleIndex / rowsPerVisualLine);
            const totalLines = Math.ceil(rows.length / rowsPerVisualLine);
            
            if (totalLines <= 1) return initialPage;

            const pageOffset = Math.floor((linesScrolled / totalLines) * (lastPage - initialPage + 1));
            return Math.min(lastPage, initialPage + pageOffset);
        }

        function updateResultsCounter() {
            if (!resultsText) return;
            let loadedCount = 0;
            if (currentView === 'grid') {
                loadedCount = tableBody.querySelectorAll('[data-record-row="true"]').length;
            } else {
                loadedCount = tableBodyList.querySelectorAll('[data-record-row="true"]').length;
            }
            
            const currentVisiblePage = getVisiblePage();
            
            if (loadedCount === 0) {
                resultsText.textContent = 'No products found';
            } else {
                resultsText.textContent = `Showing ${loadedCount} of ${totalRecords} products (Page ${currentVisiblePage} of ${lastPage})`;
            }
        }

        async function loadMoreProducts() {
            if (isLoading || !nextPageUrl) return;
            isLoading = true;
            loadingIndicator.classList.remove('hidden');

            try {
                const response = await fetch(nextPageUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) throw new Error('Network response was not ok');
                const html = await response.text();

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;

                const newGridRows = tempDiv.querySelectorAll('#productGridView > *');
                const newListRows = tempDiv.querySelectorAll('#productListView > *');

                newGridRows.forEach(row => tableBody.appendChild(row));
                newListRows.forEach(row => tableBodyList.appendChild(row));

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const nextLink = doc.querySelector('#next-page-data');
                if (nextLink) {
                    nextPageUrl = nextLink.getAttribute('data-next-url');
                    currentPage = parseInt(nextLink.getAttribute('data-current-page'));
                    lastPage = parseInt(nextLink.getAttribute('data-last-page'));
                    totalRecords = parseInt(nextLink.getAttribute('data-total'));
                } else {
                    nextPageUrl = null;
                }

                updateResultsCounter();
            } catch (error) {
                console.error('Error loading more products:', error);
            } finally {
                isLoading = false;
                loadingIndicator.classList.add('hidden');
            }
        }

        if (tableContainer) {
            tableContainer.addEventListener('scroll', () => {
                updateResultsCounter();
                if ((tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight) < 200) {
                    loadMoreProducts();
                }
            });
        }

        updateResultsCounter();
    });
</script>
@endpush