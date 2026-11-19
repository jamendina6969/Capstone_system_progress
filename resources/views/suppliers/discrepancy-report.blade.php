@extends('layouts.default')

@section('title', 'Discrepancy Report')

@section('content')

    @php
        $currentCategoryId = request('category_id');
        $selectedCategoryLabel = 'All Categories';
        if ($currentCategoryId) {
            foreach ($categories as $category) {
                if ((string) $currentCategoryId === (string) $category->id) {
                    $selectedCategoryLabel = 'All ' . $category->name;
                    break;
                }
                foreach ($category->children as $child) {
                    if ((string) $currentCategoryId === (string) $child->id) {
                        $selectedCategoryLabel = $child->name;
                        break 2;
                    }
                }
            }
        }
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Discrepancy Report</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Short deliveries, missing items, and financial impact from suppliers</p>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
            &larr; Back to Purchase Orders
        </a>
    </div>

    {{-- Top Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase text-gray-400">Total Orders Received</p>
                <div class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-4">{{ $totalOrders }}</p>
            <p class="text-xs text-gray-500 mt-1">Successfully processed POs</p>
        </div>

        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase text-gray-400">Orders with Discrepancies</p>
                <div class="p-2 bg-amber-50 dark:bg-amber-900/20 text-amber-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-amber-600 mt-4">{{ $totalDiscrepancies }}</p>
            <p class="text-xs text-gray-500 mt-1">POs with short deliveries</p>
        </div>

        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase text-gray-400">Total Lost Value</p>
                <div class="p-2 bg-rose-50 dark:bg-rose-900/20 text-rose-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-rose-600 mt-4">₱{{ number_format($totalLost, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Unfulfilled stock value</p>
        </div>
    </div>

    {{-- Main Table Container --}}
    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-[calc(100vh-360px)] mb-4">
        
        <!-- Filter Section -->
        <div class="shrink-0 px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
            <form id="filterForm" method="GET" action="{{ route('suppliers.discrepancy') }}" class="flex flex-col xl:flex-row gap-4 items-center justify-between">
                <div class="flex flex-col sm:flex-row flex-wrap gap-3 items-center w-full flex-1">
                    <!-- Search -->
                    <div class="flex items-center gap-2 flex-1 min-w-[260px] w-full sm:w-auto">
                        <label class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase whitespace-nowrap">Search:</label>
                        <div class="relative w-full">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search PO # or Supplier..." autocomplete="off"
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] pl-9 pr-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:border-slate-400">
                        </div>
                    </div>

                    <!-- Category Dropdown -->
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="custom-dropdown relative w-full sm:w-56" id="filter-category-dropdown">
                            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                <span class="dropdown-label truncate">{{ $selectedCategoryLabel }}</span>
                                <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All Categories</button>
                                @foreach($categories as $category)
                                    <div class="mt-1">
                                        <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">{{ $category->name }}</div>
                                        <button type="button" data-value="{{ $category->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All {{ $category->name }}</button>
                                        @foreach($category->children->sortBy('name') as $child)
                                            <button type="button" data-value="{{ $child->id }}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $child->name }}</button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Date Filters -->
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label for="date_from" class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase whitespace-nowrap cursor-pointer">From:</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label for="date_to" class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase whitespace-nowrap cursor-pointer">To:</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                    </div>
                </div>

                @if (request('search') || request('date_from') || request('date_to') || request('category_id'))
                    <div class="w-full xl:w-auto flex justify-end">
                        <a href="{{ route('suppliers.discrepancy') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-xs whitespace-nowrap flex items-center justify-center">
                            Clear Filters
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full text-sm text-left relative">
                <thead id="discrepancyTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">PO Number</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Supplier</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Date Received</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right">Ordered Total</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right">Discrepancy Amount</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('suppliers.partials.discrepancy-rows', ['ordersWithDiscrepancies' => $ordersWithDiscrepancies])
                </tbody>
            </table>

            {{-- LOADING --}}
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more discrepancies...
            </div>

            {{-- ERROR --}}
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more discrepancies. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($ordersWithDiscrepancies->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $ordersWithDiscrepancies->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $ordersWithDiscrepancies->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $ordersWithDiscrepancies->total() }}</span>
                    discrepancies
                @else
                    No discrepancies found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Discrepancy pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($ordersWithDiscrepancies->getUrlRange(max(1, $ordersWithDiscrepancies->currentPage() - 2), min($ordersWithDiscrepancies->lastPage(), $ordersWithDiscrepancies->currentPage() + 2)) as $page => $url)
                        @if ($page == $ordersWithDiscrepancies->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    {{-- DETAILS MODAL --}}
    <div id="discrepancyModal" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" onclick="if(event.target===this) toggleDiscrepancyModal(false)">
        <div class="relative w-full max-w-4xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            
            <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Discrepancy Breakdown</h3>
                    <p id="modal_po_number" class="text-xs text-gray-500 mt-1"></p>
                </div>
                <button type="button" onclick="toggleDiscrepancyModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500">Supplier</p>
                        <p id="modal_supplier" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500">Date Received</p>
                        <p id="modal_date" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500">Ordered Total</p>
                        <p id="modal_total" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500">Discrepancy Total</p>
                        <p id="modal_discrepancy" class="text-sm font-bold text-rose-600"></p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500 mb-2">Item Breakdown</p>
                    <div class="border border-gray-200 dark:border-[#1e2536] rounded-xl overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-4 py-3">Category</th>
                                    <th class="px-4 py-3 text-center">Ordered</th>
                                    <th class="px-4 py-3 text-center">Received</th>
                                    <th class="px-4 py-3 text-center">Short</th>
                                    <th class="px-4 py-3 text-right">Lost Value</th>
                                </tr>
                            </thead>
                            <tbody id="modal_items_body" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                                <!-- Injected via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="modal_notes_container" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 hidden">
                    <p class="text-[10px] uppercase font-bold text-amber-700 dark:text-amber-500 mb-1">GRN Notes</p>
                    <p id="modal_notes" class="text-sm text-amber-800 dark:text-amber-300"></p>
                </div>
            </div>

            <div class="flex justify-end pt-6 mt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="toggleDiscrepancyModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Close</button>
            </div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    // =====================================================
    // INFINITE SCROLL SETUP
    // =====================================================
    const tableContainer = document.getElementById('tableScrollContainer');
    const tableBody = document.getElementById('tableBody');
    const tableHead = document.getElementById('discrepancyTableHead');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    let nextPageUrl = @json($ordersWithDiscrepancies->nextPageUrl());
    let currentPage = {{ $ordersWithDiscrepancies->currentPage() }};
    let lastPage = {{ $ordersWithDiscrepancies->lastPage() }};
    let totalRecords = {{ $ordersWithDiscrepancies->total() }};
    let perPage = {{ $ordersWithDiscrepancies->perPage() }};
    let initialPage = {{ $ordersWithDiscrepancies->currentPage() }};
    let initialStart = {{ $ordersWithDiscrepancies->firstItem() ?? 0 }} || 0;

    let isLoading = false;
    let visualPage = initialPage;

    function updateResultsCounter() {
        const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
        if (totalRecords === 0 || rowsLoaded === 0) {
            resultsText.textContent = 'No discrepancies found.';
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
            discrepancies
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
            console.error('Error loading discrepancies:', error);
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

    // =====================================================
    // CUSTOM DROPDOWNS
    // =====================================================
    function initCustomDropdowns() {
        document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
            if (dropdown.dataset.initialized) return;
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const menu = dropdown.querySelector('.dropdown-menu');
            const chevron = dropdown.querySelector('.dropdown-chevron');
            const label = dropdown.querySelector('.dropdown-label');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            if (!trigger || !menu) return;

            function closeMenu() { menu.classList.add('hidden'); chevron?.classList.remove('rotate-180'); }
            function openMenu() {
                document.querySelectorAll('.custom-dropdown .dropdown-menu').forEach(m => { if (m !== menu) m.classList.add('hidden'); });
                document.querySelectorAll('.custom-dropdown .dropdown-chevron').forEach(c => { if (c !== chevron) c.classList.remove('rotate-180'); });
                menu.classList.remove('hidden'); chevron?.classList.add('rotate-180');
            }

            trigger.addEventListener('click', e => { e.stopPropagation(); menu.classList.contains('hidden') ? openMenu() : closeMenu(); });
            menu.querySelectorAll('.dropdown-option').forEach(opt => {
                opt.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (hiddenInput) hiddenInput.value = this.dataset.value || '';
                    if (label) label.textContent = this.textContent.trim();
                    closeMenu();
                    if (dropdown.id === 'filter-category-dropdown') dropdown.closest('form')?.submit();
                });
            });
            document.addEventListener('click', e => { if (!dropdown.contains(e.target)) closeMenu(); });
            dropdown.dataset.initialized = 'true';
        });
    }
    initCustomDropdowns();

    // =====================================================
    // LIVE SEARCH & DATE FILTERS
    // =====================================================
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
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

    if (dateFrom && filterForm) dateFrom.addEventListener('change', () => filterForm.submit());
    if (dateTo && filterForm) dateTo.addEventListener('change', () => filterForm.submit());

    // Init counters
    updateResultsCounter();
    updatePaginationNumbers(initialPage);

    // =====================================================
    // MODAL LOGIC
    // =====================================================
    function toggleDiscrepancyModal(show) {
        const modal = document.getElementById('discrepancyModal');
        if (show) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function openDiscrepancyModal(poData) {
        document.getElementById('modal_po_number').textContent = `PO ${poData.po_number}`;
        document.getElementById('modal_supplier').textContent = poData.supplier ? poData.supplier.name : 'N/A';
        document.getElementById('modal_date').textContent = poData.received_at ? new Date(poData.received_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
        document.getElementById('modal_total').textContent = '₱' + parseFloat(poData.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
        document.getElementById('modal_discrepancy').textContent = '- ₱' + parseFloat(poData.discrepancy_amount).toLocaleString('en-US', { minimumFractionDigits: 2 });

        const tbody = document.getElementById('modal_items_body');
        tbody.innerHTML = '';

        poData.items.forEach(item => {
            const ordered = parseInt(item.quantity);
            const received = item.received_quantity !== null ? parseInt(item.received_quantity) : ordered;
            const short = ordered - received;

            if (short > 0) {
                const unitCost = parseFloat(item.unit_cost);
                const lostValue = short * unitCost;

                let mainCategoryName = '—';
                let subCategoryName = '—';

                if (item.product?.category) {
                    const cat = item.product.category;
                    if (cat.parent) { mainCategoryName = cat.parent.name; subCategoryName = cat.name; }
                    else { mainCategoryName = cat.name; }
                }
                
                const categoryDisplay = mainCategoryName + (subCategoryName !== '—' ? ' › ' + subCategoryName : '');

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-[#1e2536]/30';
                tr.innerHTML = `
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">${item.product ? item.product.product_name : 'Deleted Product'}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-slate-400">${categoryDisplay}</td>
                    <td class="px-4 py-3 text-center text-gray-600 dark:text-slate-400">${ordered}</td>
                    <td class="px-4 py-3 text-center text-emerald-600 font-bold">${received}</td>
                    <td class="px-4 py-3 text-center text-rose-600 font-bold">${short}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">₱${lostValue.toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                `;
                tbody.appendChild(tr);
            }
        });

        const notesContainer = document.getElementById('modal_notes_container');
        const notesEl = document.getElementById('modal_notes');
        if (poData.grn_notes) {
            notesEl.textContent = poData.grn_notes;
            notesContainer.classList.remove('hidden');
        } else {
            notesContainer.classList.add('hidden');
        }

        toggleDiscrepancyModal(true);
    }

    // Expose functions globally
    window.openDiscrepancyModal = openDiscrepancyModal;
    window.toggleDiscrepancyModal = toggleDiscrepancyModal;

    // Close modal when clicking outside
    document.getElementById('discrepancyModal').addEventListener('click', function(e) {
        if (e.target === this) toggleDiscrepancyModal(false);
    });
});
</script>
@endpush