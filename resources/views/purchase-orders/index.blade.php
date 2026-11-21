@extends('layouts.default')

@section('title', 'Purchase Orders')

@section('content')

@php
    // Determine initial labels for filter dropdowns
    $currentStatus = request('status', '');
    $statusOptions = [
        '' => 'All Statuses',
        'pending' => 'Pending',
        'received' => 'Received',
    ];
    $selectedStatusLabel = $statusOptions[$currentStatus] ?? 'All Statuses';

    // Prepare category data for the JS to use in the custom dropdowns
    $categoriesData = \App\Models\Category::whereNull('parent_id')->with('children')->where('type', 'product')->orderBy('name')->get();
    $productsData = \App\Models\Product::select('id', 'product_name', 'category_id', 'approx_volume')->get();
@endphp

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Purchase Orders</h1>
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
            Create and manage orders sent to suppliers
        </p>
    </div>

    <div class="flex items-center gap-3 flex-wrap">
        {{-- EXPORT (respects current filters) --}}
        <a href="{{ route('purchase-orders.export', request()->only(['search', 'status', 'date_from', 'date_to'])) }}"
           class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
            </svg>
            Export
        </a>

        {{-- IMPORT --}}
        <button type="button"
                onclick="toggleImportModal(true)"
                class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12"/>
            </svg>
            Import
        </button>

        @if(auth()->user()->role === 'admin')
            <button type="button"
                onclick="togglePOModal(true)"
                class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">
                + Create Purchase Order (Admin)
            </button>
        @endif
    </div>
</div>

@if(auth()->user()->role === 'manager')
    <div class="mb-6 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-900 px-6 py-4 text-sm text-blue-800 dark:text-blue-300 flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <strong>Workflow reminder:</strong>
            Purchase Orders are created by converting approved Purchase Requests.
            <a href="{{ route('purchase-requests.index') }}" class="underline font-semibold">
                Go to Purchase Requests &rarr;
            </a>
        </div>
    </div>
@endif

<!-- Main Container -->
<div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-220px)] lg:min-h-[500px] mb-4">
    
    <!-- Filter Section -->
    <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
        <form id="filterForm" method="GET" action="{{ route('purchase-orders.index') }}" class="flex flex-col lg:flex-row lg:items-center flex-wrap gap-3 sm:gap-4">
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search PO #, supplier, status..." autocomplete="off"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 whitespace-nowrap">FROM:</span>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()"
                           class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 whitespace-nowrap">TO:</span>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()"
                           class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="custom-dropdown relative w-full sm:w-40" id="filter-status-dropdown">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedStatusLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            @foreach ($statusOptions as $value => $label)
                                <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if (request()->anyFilled(['date_from', 'date_to', 'status', 'search']))
                    <a href="{{ route('purchase-orders.index') }}" class="text-xs font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26]">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Scrollable Table -->
    <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
        <table class="w-full min-w-[850px] text-sm text-left relative">
            <thead id="poTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                <tr>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">PO Number</th>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Supplier</th>
                    {{-- UPDATED HEADER --}}
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Expected Delivery</th>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Total</th>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Status</th>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Actions</th>
                </tr>
            </thead>

            <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                @include('purchase-orders.partials.rows', ['purchaseOrders' => $purchaseOrders])
            </tbody>
        </table>

        {{-- LOADING --}}
        <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
            <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            Loading more orders...
        </div>

        {{-- ERROR --}}
        <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
            Unable to load more orders. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
        </div>
    </div>

    <!-- Pagination Footer -->
    <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
        <div class="whitespace-nowrap" id="resultsText">
            @if($purchaseOrders->total() > 0)
                Showing
                <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $purchaseOrders->firstItem() }}</span>
                to
                <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $purchaseOrders->lastItem() }}</span>
                of
                <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $purchaseOrders->total() }}</span>
                orders
            @else
                No orders found
            @endif
        </div>

        <nav class="flex items-center gap-1.5 max-w-full" aria-label="Purchase order pagination">
            <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                @foreach ($purchaseOrders->getUrlRange(max(1, $purchaseOrders->currentPage() - 2), min($purchaseOrders->lastPage(), $purchaseOrders->currentPage() + 2)) as $page => $url)
                    @if ($page == $purchaseOrders->currentPage())
                        <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                    @else
                        <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                    @endif
                @endforeach
            </div>
        </nav>
    </div>
</div>

{{-- CREATE PO MODAL --}}
<div id="poModal" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-3xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="poModalTitle">Create Purchase Order</h3>
            <button type="button" onclick="togglePOModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-5" id="poForm">
            @csrf
            <input type="hidden" name="from_pr" id="from_pr_input" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Supplier</label>
                    <div class="custom-dropdown relative w-full" id="po-supplier-dropdown">
                        <input type="hidden" name="supplier_id" id="po_supplier_select" value="">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">Select a Supplier</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Select a Supplier</button>
                            @foreach($suppliers as $supplier)
                                <button type="button" data-value="{{ $supplier->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $supplier->name }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Expected Delivery Date</label>
                    <input type="date" name="expected_delivery_date" class="w-full h-[42px] rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm">
                </div>
            </div>

            <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                <h4 class="font-bold text-gray-900 dark:text-white mb-3">Order Items</h4>
                <template id="itemRowTemplate">
                    <div class="flex flex-wrap gap-2 items-end item-row mb-3">
                        <div class="w-48">
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Category</label>
                            <div class="custom-dropdown relative w-full category-dropdown-container">
                                <input type="hidden" class="category-hidden-input" value="">
                                <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-2 py-1 text-xs focus:outline-none focus:border-slate-400 cursor-pointer text-left h-[36px]">
                                    <span class="dropdown-label truncate">All Categories</span>
                                    <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                    <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All Categories</button>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Product</label>
                            <div class="custom-dropdown relative w-full product-dropdown-container">
                                <input type="hidden" class="product-hidden-input" value="">
                                <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-2 py-1 text-xs focus:outline-none focus:border-slate-400 cursor-pointer text-left h-[36px]">
                                    <span class="dropdown-label truncate">Select Product</span>
                                    <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                    <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Select Product</button>
                                </div>
                            </div>
                        </div>

                        <div class="w-16">
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Qty</label>
                            <input type="number" required min="1" class="qty-input w-full h-[36px] rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-2 py-1 text-xs text-center">
                        </div>

                        <div class="w-20">
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Unit Cost</label>
                            <input type="number" step="0.01" required min="0" class="cost-input w-full h-[36px] rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-2 py-1 text-xs text-center">
                        </div>

                        <div class="w-20">
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Total</label>
                            <div class="row-total w-full h-[36px] flex items-center justify-center rounded-xl bg-gray-50 dark:bg-[#161B26]/50 border-2 border-gray-200 dark:border-[#1e2536] px-2 py-1 text-xs font-bold">₱0.00</div>
                        </div>

                        <div class="pb-0.5">
                            <button type="button" onclick="this.closest('.item-row').remove()" class="bg-rose-100 text-rose-600 hover:bg-rose-200 h-[36px] w-[36px] flex items-center justify-center rounded-xl">&times;</button>
                        </div>
                    </div>
                </template>

                <div id="itemsContainer" class="space-y-3"></div>
                <button type="button" id="addItemBtn" class="mt-2 text-xs font-semibold text-emerald-600 hover:text-emerald-700">+ Add Another Item</button>
            </div>

            {{-- Tax and Freight Inputs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tax Amount (Optional)</label>
                    <input type="number" step="0.01" min="0" name="tax_amount" value="0" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Shipping / Freight (Optional)</label>
                    <input type="number" step="0.01" min="0" name="freight_amount" value="0" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm" placeholder="0.00">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notes / Instructions</label>
                <textarea name="notes" id="po_notes" rows="2" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm" placeholder="Delivery instructions..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="togglePOModal(false)" class="border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full">Cancel</button>
                <button type="submit" class="border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white text-xs font-semibold px-4 py-2 rounded-full">Create Order</button>
            </div>
        </form>
    </div>
</div>

{{-- IMPORT PO MODAL --}}
<div id="importPOModal" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto"
         onclick="event.stopPropagation()">

        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Import Purchase Orders</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Upload a CSV or XLSX file to bulk-create purchase orders.</p>
            </div>
            <button type="button" onclick="toggleImportModal(false)"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>

        <div class="mb-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-900 px-4 py-3 text-xs text-blue-800 dark:text-blue-300">
            <strong>Before importing:</strong> Make sure supplier names and product names already exist in the system.
            Use the template below as a guide.
            <a href="{{ route('purchase-orders.import.template') }}"
               class="underline font-semibold inline-block mt-1">
                Download Template (.csv)
            </a>
        </div>

        <form method="POST" action="{{ route('purchase-orders.import') }}"
              enctype="multipart/form-data" id="importForm" class="space-y-4">
            @csrf

            <div id="importDropZone"
                 class="border-2 border-dashed border-gray-300 dark:border-[#1e2536] rounded-2xl px-6 py-10 text-center cursor-pointer transition-colors hover:border-slate-400 dark:hover:border-[#2a3347]">
                <svg class="mx-auto w-10 h-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 16a4 4 0 01-.88-7.9A5 5 0 1115.9 6H16a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-sm font-semibold text-gray-700 dark:text-slate-300">
                    Click to browse or drag &amp; drop your file here
                </p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">CSV or XLSX &middot; Max 5&nbsp;MB</p>
                <p id="importFileName" class="text-xs font-mono text-emerald-600 dark:text-emerald-400 mt-3 truncate">
                    No file selected
                </p>
                <input type="file" name="file" id="importFileInput"
                       accept=".csv,.xlsx,.xls" required class="hidden">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                    On Duplicate PO Numbers
                </label>
                <select name="duplicate_action"
                        class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2 text-sm cursor-pointer">
                    <option value="skip">Skip duplicates (recommended)</option>
                    <option value="update">Update existing orders</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="toggleImportModal(false)"
                        class="border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full">
                    Cancel
                </button>
                <button type="submit"
                        class="border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-colors">
                    Import Orders
                </button>
            </div>
        </form>
    </div>
</div>

{{-- VIEW PO MODAL --}}
<div id="viewPOModal" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-3xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Purchase Order Details</h3>
            <button type="button" onclick="toggleViewPOModal(false)" class="text-gray-400 text-xl font-bold">&times;</button>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">PO Number</p>
                    <p id="view_po_number" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Supplier</p>
                    <p id="view_po_supplier" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Status</p>
                    <p id="view_po_status" class="text-sm font-semibold"></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Date Created</p>
                    <p id="view_po_date" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Expected Delivery</p>
                    <p id="view_po_expected_date" class="text-sm font-semibold text-blue-600 dark:text-blue-400"></p>
                </div>
            </div>

            <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                <p class="text-[10px] uppercase font-bold text-gray-500 mb-2">Ordered Items</p>
                <div class="border border-gray-200 dark:border-[#1e2536] rounded-xl overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 whitespace-nowrap">Product</th>
                                <th class="px-4 py-3 whitespace-nowrap">Category</th>
                                <th class="px-4 py-3 text-center whitespace-nowrap">Ordered</th>
                                <th class="px-4 py-3 text-center whitespace-nowrap">Received</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Unit Cost</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="view_po_items_body" class="divide-y divide-gray-100 dark:divide-[#1e2536]"></tbody>
                    </table>
                </div>
            </div>

            <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4 flex justify-end">
                <div class="text-right">
                    <p class="text-xs text-gray-500">Total Amount</p>
                    <p id="view_po_total" class="text-2xl font-bold text-gray-900 dark:text-white">₱0.00</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-6 mt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
            <button type="button" onclick="toggleViewPOModal(false)" class="border-2 border-slate-300 text-slate-700 text-xs font-semibold px-5 py-2.5 rounded-full">Close</button>
        </div>
    </div>
</div>

{{-- GRN RECEIVE MODAL --}}
<div id="receiveModal" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-3xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Goods Received Note (GRN)</h3>
                <p id="receive_po_label" class="text-xs text-gray-500 mt-1"></p>
            </div>
            <button type="button" onclick="toggleReceiveModal(false)" class="text-gray-400 text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="" class="space-y-5" id="receiveForm">
            @csrf
            @method('PATCH')

            <div class="rounded-xl bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-900 px-4 py-3 text-sm text-blue-800 dark:text-blue-300">
                Enter the actual quantity received for each item and choose where the stock should be stored. Any shortage will be recorded in the supplier ledger.
            </div>

            <div id="receiveItemsContainer" class="space-y-3"></div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">GRN Notes</label>
                <textarea name="grn_notes" rows="2" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm" placeholder="E.g. 2 items damaged, 1 item missing..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="toggleReceiveModal(false)" class="border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full">Cancel</button>
                <button type="submit" class="border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white text-xs font-semibold px-4 py-2 rounded-full">Confirm Receipt</button>
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
</style>
@endpush

@push('scripts')
@php
    $prNumber     = $purchaseRequest->pr_number ?? null;
    $prId         = $purchaseRequest->id ?? null;
    $prSupplierId = $purchaseRequest->supplier_id ?? null;
    $prReason     = $purchaseRequest->reason ?? null;
    $prItems      = $purchaseRequest->items ?? collect();
@endphp

<script>
    const categoriesData = @json($categoriesData);
    const productsData   = @json($productsData);

    // =====================================================
    // REUSABLE CUSTOM DROPDOWN LOGIC
    // =====================================================
    function initCustomDropdowns(container = document) {
        const dropdowns = container.querySelectorAll('.custom-dropdown');
        dropdowns.forEach(dropdown => {
            if (dropdown.dataset.initialized) return;

            const trigger = dropdown.querySelector('.dropdown-trigger');
            const menu = dropdown.querySelector('.dropdown-menu');
            const chevron = dropdown.querySelector('.dropdown-chevron');
            const label = dropdown.querySelector('.dropdown-label');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            
            if (!trigger || !menu) return;

            function openMenu() {
                document.querySelectorAll('.custom-dropdown .dropdown-menu').forEach(m => { if (m !== menu) m.classList.add('hidden'); });
                document.querySelectorAll('.custom-dropdown .dropdown-chevron').forEach(c => { if (c !== chevron) c.classList.remove('rotate-180'); });
                menu.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
            }

            function closeMenu() {
                menu.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }

            trigger.addEventListener('click', function (event) {
                event.stopPropagation();
                if (menu.classList.contains('hidden')) openMenu();
                else closeMenu();
            });

            menu.addEventListener('click', function (event) {
                const option = event.target.closest('.dropdown-option');
                if (!option) return;

                event.stopPropagation();
                const value = option.dataset.value || '';
                const text = option.textContent.trim();

                if (hiddenInput) hiddenInput.value = value;
                if (label) label.textContent = text;

                closeMenu();

                if (hiddenInput) hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                if (dropdown.id === 'filter-status-dropdown') dropdown.closest('form')?.submit();
            });

            document.addEventListener('click', function (event) {
                if (!dropdown.contains(event.target)) closeMenu();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeMenu();
            });

            dropdown.dataset.initialized = 'true';
        });
    }

    document.addEventListener('DOMContentLoaded', () => initCustomDropdowns());

    // =====================================================
    // INFINITE SCROLL SETUP
    // =====================================================
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('tableScrollContainer');
        const tableBody = document.getElementById('tableBody');
        const tableHead = document.getElementById('poTableHead');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const loadError = document.getElementById('loadError');
        const retryLoadButton = document.getElementById('retryLoadButton');
        const paginationContainer = document.getElementById('paginationNumbersContainer');
        const resultsText = document.getElementById('resultsText');

        let nextPageUrl = @json($purchaseOrders->nextPageUrl());
        let currentPage = {{ $purchaseOrders->currentPage() }};
        let lastPage = {{ $purchaseOrders->lastPage() }};
        let totalRecords = {{ $purchaseOrders->total() }};
        let perPage = {{ $purchaseOrders->perPage() }};
        let initialPage = {{ $purchaseOrders->currentPage() }};
        let initialStart = {{ $purchaseOrders->firstItem() ?? 0 }} || 0;

        let isLoading = false;
        let visualPage = initialPage;

        function updateResultsCounter() {
            const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
            if (totalRecords === 0 || rowsLoaded === 0) {
                resultsText.textContent = 'No orders found.';
                return;
            }
            const firstRecord = initialStart;
            const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
            resultsText.innerHTML = `
                Showing
                <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span>
                to
                <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span>
                of
                <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span>
                orders
            `;
        }

        function getVisiblePage() {
            const rows = tableBody.querySelectorAll('tr[data-record-row="true"]');
            if (!rows.length) return initialPage;

            const isAtBottom = (tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight) <= 5;
            if (isAtBottom && lastPage > 0) return lastPage;

            const containerRect = tableContainer.getBoundingClientRect();
            const headerHeight = tableHead ? tableHead.getBoundingClientRect().height : 0;
            const visibleTop = containerRect.top + headerHeight;
            const visibleBottom = containerRect.bottom;

            let firstVisibleIndex = -1;
            rows.forEach((row, index) => {
                const rect = row.getBoundingClientRect();
                if (firstVisibleIndex === -1 && rect.bottom > visibleTop && rect.top < visibleBottom) {
                    firstVisibleIndex = index;
                }
            });

            if (firstVisibleIndex === -1) firstVisibleIndex = rows.length - 1;
            const pageOffset = Math.floor(firstVisibleIndex / perPage);
            return Math.min(lastPage, Math.max(1, initialPage + pageOffset));
        }

        function updatePaginationNumbers(pageToShow) {
            const page = Math.min(lastPage, Math.max(1, pageToShow));
            if (visualPage === page && paginationContainer.children.length > 0) return;

            visualPage = page;
            let html = '';
            const startPage = Math.max(1, page - 2);
            const endPage = Math.min(lastPage, page + 2);

            for (let i = startPage; i <= endPage; i++) {
                if (i === page) {
                    html += `<span class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">${i}</span>`;
                } else {
                    html += `<button type="button" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">${i}</button>`;
                }
            }
            paginationContainer.innerHTML = html;
        }

        async function loadMoreRecords() {
            if (!nextPageUrl || isLoading) return;

            isLoading = true;
            loadError.classList.add('hidden');
            loadingIndicator.classList.remove('hidden');

            try {
                const fetchUrl = new URL(nextPageUrl, window.location.origin);
                fetchUrl.searchParams.set('t', Date.now());

                const response = await fetch(fetchUrl.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache'
                    }
                });

                if (!response.ok) throw new Error('Request failed.');
                const data = await response.json();

                const newPage = Number(data.current_page);
                if (data.html) {
                    const temp = document.createElement('tbody');
                    temp.innerHTML = data.html;
                    tableBody.querySelector('[data-empty-row="true"]')?.remove();
                    Array.from(temp.children).forEach(row => {
                        if (row.dataset.recordRow === 'true') {
                            row.dataset.productPage = newPage;
                            tableBody.appendChild(row);
                        }
                    });
                }

                nextPageUrl = data.next_page_url;
                currentPage = newPage;
                lastPage = data.last_page;
                totalRecords = data.total_records;
                perPage = data.per_page;

                updateResultsCounter();
                updatePaginationNumbers(getVisiblePage());

            } catch (error) {
                console.error('Error loading orders:', error);
                loadError.classList.remove('hidden');
            } finally {
                isLoading = false;
                loadingIndicator.classList.add('hidden');
            }
        }

        tableContainer.addEventListener('scroll', function () {
            updatePaginationNumbers(getVisiblePage());
            const distanceFromBottom = tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight;
            if (distanceFromBottom < 150 && nextPageUrl && !isLoading) loadMoreRecords();
        }, { passive: true });

        retryLoadButton?.addEventListener('click', function () { loadMoreRecords(); });

        // Live search
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        let debounceTimer;
        if (searchInput && filterForm) {
            const savedCursorPos = sessionStorage.getItem('searchCursorPos');
            if (savedCursorPos !== null) {
                searchInput.focus();
                searchInput.setSelectionRange(parseInt(savedCursorPos), parseInt(savedCursorPos));
                sessionStorage.removeItem('searchCursorPos');
            } else if (searchInput.value.length > 0) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }

            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                sessionStorage.setItem('searchCursorPos', searchInput.selectionStart);
                debounceTimer = setTimeout(() => filterForm.submit(), 300);
            });
        }

        // Init counters
        updateResultsCounter();
        updatePaginationNumbers(initialPage);
    });

    // =====================================================
    // REST OF EXISTING LOGIC (Modals, Create Form, etc.)
    // =====================================================
    function togglePOModal(show) {
        document.getElementById('poModal').classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
        
        if (!show) {
            document.getElementById('poForm').reset();
            document.getElementById('itemsContainer').innerHTML = '';
            document.getElementById('from_pr_input').value = '';
            document.getElementById('poModalTitle').textContent = 'Create Purchase Order';
            const supInput = document.getElementById('po_supplier_select');
            if(supInput) {
                supInput.value = '';
                const label = supInput.closest('.custom-dropdown').querySelector('.dropdown-label');
                if(label) label.textContent = 'Select a Supplier';
            }
            window.addRow(); 
        }
    }

    function toggleViewPOModal(show) {
        document.getElementById('viewPOModal').classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
    }

    function toggleReceiveModal(show) {
        document.getElementById('receiveModal').classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
    }

    function toggleImportModal(show) {
        document.getElementById('importPOModal').classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
        if (!show) {
            document.getElementById('importForm').reset();
            document.getElementById('importFileName').textContent = 'No file selected';
            document.getElementById('importDropZone').classList.remove('border-emerald-500', 'bg-emerald-50');
        }
    }

    function openReceiveModal(poData, receiveUrl) {
        const form = document.getElementById('receiveForm');
        form.action = receiveUrl;
        form.reset();
        document.getElementById('receive_po_label').textContent = `PO ${poData.po_number} — ${poData.supplier?.name || 'N/A'}`;
        const container = document.getElementById('receiveItemsContainer');
        container.innerHTML = '';
        if (!poData.items || poData.items.length === 0) {
            container.textContent = 'No items found in this Purchase Order.';
            return;
        }

        poData.items.forEach(item => {
            const productName = item.product?.product_name || 'Deleted Product';
            const orderedQuantity = parseInt(item.quantity) || 0;
            const unitCost = parseFloat(item.unit_cost) || 0;

            const row = document.createElement('div');
            row.className = 'border-2 border-gray-200 dark:border-[#1e2536] rounded-xl p-4 grid grid-cols-1 sm:grid-cols-4 gap-4 items-center';

            const productInfo = document.createElement('div');
            productInfo.className = 'sm:col-span-2';
            productInfo.innerHTML = `
                <p class="font-bold text-gray-900 dark:text-white">${productName}</p>
                <p class="text-xs text-gray-500 mt-0.5">Ordered: ${orderedQuantity} × ₱${unitCost.toLocaleString('en-US', { minimumFractionDigits: 2 })}</p>
            `;

            const quantityWrapper = document.createElement('div');
            quantityWrapper.innerHTML = `
                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Received Qty</label>
                <input type="number" name="received_quantities[${item.id}]" value="${orderedQuantity}" min="0" max="${orderedQuantity}" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-center font-bold text-gray-900 dark:text-white">
            `;

            const destinationWrapper = document.createElement('div');
            destinationWrapper.innerHTML = `
                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Destination</label>
                <select name="destinations[${item.id}]" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm cursor-pointer font-semibold">
                    <option value="store">Store</option>
                    <option value="warehouse">Warehouse</option>
                </select>
            `;

            row.appendChild(productInfo);
            row.appendChild(quantityWrapper);
            row.appendChild(destinationWrapper);
            container.appendChild(row);
        });

        toggleReceiveModal(true);
    }

    function openViewPOModal(poData) {
        document.getElementById('view_po_number').textContent = poData.po_number;
        document.getElementById('view_po_supplier').textContent = poData.supplier?.name || 'N/A';
        document.getElementById('view_po_date').textContent = new Date(poData.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        // NEW: Populate Expected Delivery Date
        document.getElementById('view_po_expected_date').textContent = poData.expected_delivery_date 
            ? new Date(poData.expected_delivery_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
            : 'Not set';

        document.getElementById('view_po_total').textContent = '₱' + parseFloat(poData.total_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

        const statusEl = document.getElementById('view_po_status');
        statusEl.textContent = (poData.status || '').toUpperCase();
        statusEl.className = 'text-sm font-semibold ' + (poData.status === 'pending' ? 'text-amber-600' : poData.status === 'received' ? 'text-emerald-600' : 'text-gray-600');

        const tbody = document.getElementById('view_po_items_body');
        tbody.innerHTML = '';

        poData.items.forEach(item => {
            let mainCategoryName = '—';
            let subCategoryName = '—';

            if (item.product?.category) {
                const cat = item.product.category;
                if (cat.parent) { mainCategoryName = cat.parent.name; subCategoryName = cat.name; }
                else { mainCategoryName = cat.name; }
            }

            let receivedHtml = '<span class="text-gray-400 text-xs">Pending</span>';
            if (poData.status === 'received') {
                const received = item.received_quantity !== null ? item.received_quantity : item.quantity;
                const isShort = received < item.quantity;
                receivedHtml = `<span class="font-bold ${isShort ? 'text-rose-600' : 'text-emerald-600'}">${received}</span>${isShort ? '<span class="block text-[9px] text-rose-500 font-bold leading-tight">Short: ' + (item.quantity - received) + '</span>' : ''}`;
            }

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 dark:hover:bg-[#1e2536]/30';
            tr.innerHTML = `
                <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">${item.product?.product_name || 'Item'}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-slate-400">${mainCategoryName + (subCategoryName !== '—' ? ' › ' + subCategoryName : '')}</td>
                <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">${item.quantity}</td>
                <td class="px-4 py-3 text-center">${receivedHtml}</td>
                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">₱${parseFloat(item.unit_cost || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">₱${parseFloat(item.subtotal || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
            `;
            tbody.appendChild(tr);
        });

        toggleViewPOModal(true);
    }

    function populateProducts(categoryId, productHiddenInput, productDropdownContainer, selectedProductId = null) {
        const productMenu = productDropdownContainer.querySelector('.dropdown-menu');
        const productLabel = productDropdownContainer.querySelector('.dropdown-label');
        productMenu.innerHTML = '<button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Select Product</button>';
        productHiddenInput.value = '';
        productLabel.textContent = 'Select Product';

        const filtered = productsData.filter(p => {
            if (!categoryId) return true;
            const parentCat = categoriesData.find(c => c.id == categoryId);
            if (parentCat) {
                const childIds = parentCat.children.map(c => c.id);
                return p.category_id == categoryId || childIds.includes(p.category_id);
            } else {
                return p.category_id == categoryId;
            }
        });

        filtered.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.value = p.id;
            btn.className = 'dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]';
            let displayName = p.product_name;
            if (p.approx_volume) displayName += ` (${p.approx_volume})`;
            btn.textContent = displayName;
            productMenu.appendChild(btn);

            if (selectedProductId && p.id == selectedProductId) {
                productHiddenInput.value = p.id;
                productLabel.textContent = displayName;
            }
        });
    }

    function autoFillCategory(row) {
        const prodHidden = row.querySelector('.product-hidden-input');
        const catHidden = row.querySelector('.category-hidden-input');
        const catLabel = row.querySelector('.category-dropdown-container .dropdown-label');
        const catMenu = row.querySelector('.category-dropdown-container .dropdown-menu');
        const selectedProductId = prodHidden.value;
        if (!selectedProductId) return;
        const product = productsData.find(p => p.id == selectedProductId);
        if (!product) return;
        catHidden.value = product.category_id;
        const optBtn = catMenu.querySelector(`button[data-value="${product.category_id}"]`);
        if (optBtn) catLabel.textContent = optBtn.textContent.trim();
    }

    document.addEventListener('DOMContentLoaded', function() {
        let itemIndex = 0;
        const itemsContainer = document.getElementById('itemsContainer');
        const addItemBtn = document.getElementById('addItemBtn');
        const template = document.getElementById('itemRowTemplate');

        window.setupRow = function(row, productId = null, quantity = null) {
            const catHidden = row.querySelector('.category-hidden-input');
            const prodHidden = row.querySelector('.product-hidden-input');
            const qtyInput = row.querySelector('.qty-input');
            const costInput = row.querySelector('.cost-input');
            const totalDisplay = row.querySelector('.row-total');
            const catContainer = row.querySelector('.category-dropdown-container');
            const prodContainer = row.querySelector('.product-dropdown-container');
            const catMenu = catContainer.querySelector('.dropdown-menu');

            prodHidden.name = `items[${itemIndex}][product_id]`;
            qtyInput.name = `items[${itemIndex}][quantity]`;
            costInput.name = `items[${itemIndex}][unit_cost]`;
            
            catMenu.innerHTML = '<button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All Categories</button>';
            categoriesData.forEach(mainCat => {
                const groupDiv = document.createElement('div');
                groupDiv.className = 'mt-1';
                groupDiv.innerHTML = `<div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">${mainCat.name}</div>`;
                const mainBtn = document.createElement('button');
                mainBtn.type = 'button';
                mainBtn.dataset.value = mainCat.id;
                mainBtn.className = 'dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]';
                mainBtn.textContent = mainCat.name;
                groupDiv.appendChild(mainBtn);
                mainCat.children.forEach(child => {
                    const childBtn = document.createElement('button');
                    childBtn.type = 'button';
                    childBtn.dataset.value = child.id;
                    childBtn.className = 'dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]';
                    childBtn.textContent = child.name;
                    groupDiv.appendChild(childBtn);
                });
                catMenu.appendChild(groupDiv);
            });

            initCustomDropdowns(row);
            populateProducts('', prodHidden, prodContainer, productId);
            if (productId) autoFillCategory(row);
            if (quantity) qtyInput.value = quantity;

            catHidden.addEventListener('change', function() { populateProducts(this.value, prodHidden, prodContainer); });
            prodHidden.addEventListener('change', function() { autoFillCategory(row); });

            function updateRowTotal() {
                const qty = parseFloat(qtyInput.value) || 0;
                const cost = parseFloat(costInput.value) || 0;
                totalDisplay.textContent = '₱' + (qty * cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            qtyInput.addEventListener('input', updateRowTotal);
            costInput.addEventListener('input', updateRowTotal);
            updateRowTotal();
        }

        window.addRow = function(productId = null, quantity = null) {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.item-row');
            itemsContainer.appendChild(row);
            setupRow(row, productId, quantity);
            itemIndex++;
        };

        addRow();
        addItemBtn.addEventListener('click', () => addRow());

        @if($autoOpenModal && $purchaseRequest)
            document.getElementById('poModalTitle').textContent = 'Create Purchase Order from ' + @json($prNumber);
            document.getElementById('from_pr_input').value = @json($prId);
            @if($prSupplierId)
                const supInput = document.getElementById('po_supplier_select');
                const supLabel = supInput.closest('.custom-dropdown').querySelector('.dropdown-label');
                const supMenu = supInput.closest('.custom-dropdown').querySelector('.dropdown-menu');
                const selectedSupBtn = supMenu.querySelector(`button[data-value="${@json($prSupplierId)}"]`);
                supInput.value = @json($prSupplierId);
                if (selectedSupBtn) supLabel.textContent = selectedSupBtn.textContent.trim();
            @endif
            document.getElementById('po_notes').value = @json($prReason);
            itemsContainer.innerHTML = '';
            itemIndex = 0;
            @foreach($prItems as $item)
                addRow(@json($item->product_id), @json($item->quantity));
            @endforeach
            togglePOModal(true);
            window.history.replaceState({}, document.title, window.location.pathname);
        @elseif(session('open_po_modal'))
            togglePOModal(true);
        @endif
    });

    // ====== IMPORT DROP ZONE ======
    document.addEventListener('DOMContentLoaded', function () {
        const dropZone  = document.getElementById('importDropZone');
        const fileInput = document.getElementById('importFileInput');
        const fileName  = document.getElementById('importFileName');
        if (!dropZone || !fileInput) return;

        dropZone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                fileName.textContent = fileInput.files[0].name;
                dropZone.classList.add('border-emerald-500', 'bg-emerald-50');
            } else {
                fileName.textContent = 'No file selected';
                dropZone.classList.remove('border-emerald-500', 'bg-emerald-50');
            }
        });

        ['dragenter', 'dragover'].forEach(evt => {
            dropZone.addEventListener(evt, e => {
                e.preventDefault(); e.stopPropagation();
                dropZone.classList.add('border-emerald-500', 'bg-emerald-50');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, e => {
                e.preventDefault(); e.stopPropagation();
                if (evt === 'dragleave' && dropZone.contains(e.relatedTarget)) return;
                if (evt === 'dragleave') {
                    dropZone.classList.remove('border-emerald-500', 'bg-emerald-50');
                }
                if (evt === 'drop' && e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        });
    });

    // ====== BACKDROP CLOSE ======
    document.getElementById('poModal').addEventListener('click', function(e) { if (e.target === this) togglePOModal(false); });
    document.getElementById('viewPOModal').addEventListener('click', function(e) { if (e.target === this) toggleViewPOModal(false); });
    document.getElementById('receiveModal').addEventListener('click', function(e) { if (e.target === this) toggleReceiveModal(false); });
    document.getElementById('importPOModal').addEventListener('click', function(e) { if (e.target === this) toggleImportModal(false); });
</script>
@endpush