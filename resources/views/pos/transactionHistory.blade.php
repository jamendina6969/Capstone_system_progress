@extends('layouts.default')

@section('title', 'Transaction History')

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

    $sourceOptions = [
        '' => 'All Sources',
        'pos' => 'POS',
        'imported' => 'Imported',
        'refund' => 'Refund',
        'payment' => 'Payment',
    ];
    $currentSource = request('source', '');
    $selectedSourceLabel = $sourceOptions[$currentSource] ?? 'All Sources';
@endphp

<div class="flex flex-col">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6 shrink-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Transaction History
            </h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                All system transactions — POS Sales, Imported Sales, Refunds, and Supplier Payments
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col flex-1 min-h-0">

        <!-- Filters -->
        <div class="px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50 shrink-0">
            <form id="filterForm" method="GET" action="{{ route('transaction.history') }}" class="flex flex-col xl:flex-row xl:items-center gap-4">

                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search ID, product, category..." autocomplete="off"
                            class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>

                    <div class="custom-dropdown relative w-full sm:w-56 shrink-0" id="filter-category-dropdown">
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedCategoryLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                All Categories
                            </button>
                            @foreach($categories as $category)
                                <div class="mt-1">
                                    <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                                        {{ $category->name }}
                                    </div>
                                    <button type="button" data-value="{{ $category->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                        All {{ $category->name }}
                                    </button>
                                    @foreach($category->children->sortBy('name') as $child)
                                        <button type="button" data-value="{{ $child->id }}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                            {{ $child->name }}
                                        </button>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
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
                        <div class="custom-dropdown relative w-full sm:w-44 shrink-0" id="filter-source-dropdown">
                            <input type="hidden" name="source" value="{{ $currentSource }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400 cursor-pointer text-left">
                                <span class="dropdown-label truncate">{{ $selectedSourceLabel }}</span>
                                <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                @foreach ($sourceOptions as $value => $label)
                                    <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @if(request()->anyFilled(['date_from', 'date_to', 'source', 'search', 'category_id']))
                            <a href="{{ route('transaction.history') }}"
                                class="text-xs font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26]">
                                Clear Filters
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Transaction Table -->
        <div id="tableScrollContainer" class="overflow-x-auto h-[600px] overflow-y-auto custom-scrollbar relative">
            <table class="w-full text-sm text-left relative">
                <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26]">Date</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26]">Reference ID</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26]">Entity / Description</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26]">Source / Type</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26]">Total Amount</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26]">Recorded By</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('pos.partials.transaction-rows', ['transactions' => $transactions])
                </tbody>
            </table>

            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading more records...
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($transactions->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $transactions->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $transactions->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $transactions->total() }}</span>
                    records
                @else
                    No records found.
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @if($transactions->total() > 0)
                        @foreach($transactions->getUrlRange(max(1, $transactions->currentPage() - 3), min($transactions->lastPage(), $transactions->currentPage() + 3)) as $page => $url)
                            @if($page == $transactions->currentPage())
                                <span class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                </div>
            </nav>
        </div>

    </div>
</div>

<!-- ============================================================= -->
<!-- RETURN MODAL — included once per page                          -->
<!-- ============================================================= -->
<div id="returnModal" class="fixed inset-0 z-[100] hidden bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="relative w-full max-w-lg bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-6">
    <div class="flex items-center justify-between pb-4 mb-4 border-b-2 border-gray-200 dark:border-[#1e2536]">
      <h3 class="text-lg font-bold text-gray-900 dark:text-white">Process Refund / Return</h3>
      <button type="button" onclick="closeReturnModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
    </div>

    <form id="returnForm" method="POST">
      @csrf
      <input type="hidden" name="client_request_id" id="clientRequestId">

      <div class="mb-4">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Receipt No.</label>
        <input type="text" id="returnReceiptNo" readonly
               class="w-full rounded-xl bg-gray-100 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-500">
      </div>

      <!-- SKU VERIFICATION (LOCKED WORKFLOW) -->
      <div class="mb-4 relative">
        <label class="block text-xs font-bold text-rose-500 uppercase mb-1 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
            Step 1: Verify Physical Item
        </label>
        <input type="text" id="skuScanner" placeholder="Scan or type SKU from the item's tag..." autocomplete="off"
               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-rose-300 dark:border-rose-900/50 px-4 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-rose-500 transition-colors uppercase">
        <p class="text-[10px] text-gray-400 mt-1">The item below will unlock only when its exact SKU is entered here.</p>
      </div>

      <div id="returnItemsContainer" class="space-y-3 mb-4 max-h-60 overflow-y-auto custom-scrollbar"></div>

      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Refund Method <span class="text-rose-500">*</span></label>
          <select name="refund_method" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
            <option value="cash">Cash</option>
            <option value="original">Original payment</option>
            <option value="store_credit">Store Credit (For Exchange)</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reason <span class="text-rose-500">*</span></label>
          <select name="reason" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
            <option value="Defective">Defective</option>
            <option value="Wrong Item">Wrong Item</option>
            <option value="Changed Mind">Changed Mind</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notes</label>
        <input type="text" name="notes" maxlength="1000"
               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm"
               placeholder="Optional details...">
      </div>

      <div class="flex items-center justify-between pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
        <div class="text-sm text-gray-600 dark:text-gray-300">
          Refund total: <span id="refundTotal" class="font-bold text-rose-600">₱0.00</span>
        </div>
        <div class="flex gap-3">
          <button type="button" onclick="closeReturnModal()"
                  class="bg-white border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full hover:bg-slate-700 hover:text-white transition-colors">
            Cancel
          </button>
          <button type="submit"
                  class="bg-rose-600 text-white text-xs font-bold px-6 py-2 rounded-full hover:bg-rose-700 transition-colors shadow-md">
            Process Refund
          </button>
        </div>
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
    
    /* Visual styling for locked inputs */
    .locked-input { background-color: #f3f4f6 !important; color: #9ca3af !important; cursor: not-allowed !important; }
    .dark .locked-input { background-color: #1e2536 !important; color: #64748b !important; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // CUSTOM DROPDOWNS
    // =====================================================
    function initCustomDropdowns() {
        const dropdowns = document.querySelectorAll('.custom-dropdown');
        dropdowns.forEach(dropdown => {
            if (dropdown.dataset.initialized) return;

            const trigger = dropdown.querySelector('.dropdown-trigger');
            const menu = dropdown.querySelector('.dropdown-menu');
            const chevron = dropdown.querySelector('.dropdown-chevron');
            const label = dropdown.querySelector('.dropdown-label');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            const options = dropdown.querySelectorAll('.dropdown-option');

            if (!trigger || !menu) return;

            function openMenu() {
                document.querySelectorAll('.custom-dropdown .dropdown-menu').forEach(m => {
                    if (m !== menu) m.classList.add('hidden');
                });
                document.querySelectorAll('.custom-dropdown .dropdown-chevron').forEach(c => {
                    if (c !== chevron) c.classList.remove('rotate-180');
                });
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

            options.forEach(option => {
                option.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const value = this.dataset.value || '';
                    const text = this.textContent.trim();
                    if (hiddenInput) hiddenInput.value = value;
                    if (label) label.textContent = text;
                    closeMenu();
                    const form = dropdown.closest('form');
                    if (form && dropdown.id.includes('filter')) form.submit();
                });
            });

            document.addEventListener('click', function (event) {
                if (!dropdown.contains(event.target)) closeMenu();
            });

            dropdown.dataset.initialized = 'true';
        });
    }
    initCustomDropdowns();

    // =====================================================
    // PAGINATION / INFINITE SCROLL
    // =====================================================
    const tableContainer = document.getElementById('tableScrollContainer');
    const tableBody = document.getElementById('tableBody');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    let nextPageUrl = @json($transactions->nextPageUrl());
    let currentPage = {{ $transactions->currentPage() }};
    let lastPage = {{ $transactions->lastPage() }};
    let totalRecords = {{ $transactions->total() }};
    let perPage = {{ $transactions->perPage() }};
    let initialPage = {{ $transactions->currentPage() }};
    let initialStart = {{ $transactions->firstItem() ?? 0 }} || 0;
    let isLoading = false;
    let visualPage = initialPage;

    function getVisiblePage() {
        const rows = Array.from(tableBody.querySelectorAll('tr[data-record-row="true"]'));
        if (!rows.length) return initialPage;
        const isAtBottom = (tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight) <= 5;
        if (isAtBottom && lastPage > 0) return lastPage;
        const containerRect = tableContainer.getBoundingClientRect();
        const header = tableContainer.querySelector('thead');
        const headerHeight = header ? header.getBoundingClientRect().height : 0;
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
        const startPage = Math.max(1, page - 3);
        const endPage = Math.min(lastPage, page + 3);
        const currentUrl = new URL(window.location.href);
        for (let i = startPage; i <= endPage; i++) {
            if (i === page) {
                html += `<span class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">${i}</span>`;
            } else {
                const pageUrl = new URL(currentUrl.href);
                pageUrl.searchParams.set('page', i);
                html += `<a href="${pageUrl.pathname + pageUrl.search}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">${i}</a>`;
            }
        }
        paginationContainer.innerHTML = html;
    }

    function updateResultsCounter() {
        const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
        if (totalRecords === 0 || rowsLoaded === 0) {
            resultsText.textContent = 'No records found.';
            return;
        }
        const firstRecord = initialStart;
        const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
        resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> records`;
    }

    async function loadMoreTransactions() {
        if (!nextPageUrl || isLoading) return;
        isLoading = true;
        loadingIndicator.classList.remove('hidden');
        try {
            const fetchUrl = new URL(nextPageUrl, window.location.origin);
            fetchUrl.searchParams.set('t', Date.now());
            const response = await fetch(fetchUrl.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Cache-Control': 'no-cache' }
            });
            if (!response.ok) throw new Error('Failed to load transactions.');
            const data = await response.json();
            if (data.html && data.html.trim() !== '') {
                tableBody.insertAdjacentHTML('beforeend', data.html);
            }
            nextPageUrl = data.next_page_url;
            currentPage = data.current_page;
            lastPage = data.last_page;
            totalRecords = data.total_records;
            perPage = data.per_page ?? perPage;
            updateResultsCounter();
            updatePaginationNumbers(getVisiblePage());
        } catch (error) {
            console.error('Error loading transactions:', error);
        } finally {
            isLoading = false;
            loadingIndicator.classList.add('hidden');
        }
    }

    tableContainer.addEventListener('scroll', function () {
        updatePaginationNumbers(getVisiblePage());
        const distanceFromBottom = tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight;
        if (distanceFromBottom < 100) loadMoreTransactions();
    }, { passive: true });

    updatePaginationNumbers(initialPage);
    updateResultsCounter();

    // =====================================================
    // LIVE SEARCH
    // =====================================================
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    let debounceTimer;

    if (searchInput) {
        const savedCursorPos = sessionStorage.getItem('searchCursorPos');
        if (savedCursorPos !== null) {
            searchInput.focus();
            const cursorPosition = parseInt(savedCursorPos, 10);
            searchInput.setSelectionRange(cursorPosition, cursorPosition);
            sessionStorage.removeItem('searchCursorPos');
        } else if (searchInput.value.length > 0) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            sessionStorage.setItem('searchCursorPos', searchInput.selectionStart);
            debounceTimer = setTimeout(function () { filterForm.submit(); }, 300);
        });
    }

});

// =====================================================
// RETURN MODAL LOGIC & LOCKED SKU SCANNER
// =====================================================
let returnContext = null;
let loadedReceiptItems = []; 

function openReturnModal(btn) {
  returnContext = {
    action:   btn.dataset.action,
    itemsUrl: btn.dataset.itemsUrl,
    receipt:  btn.dataset.receipt,
  };

  document.getElementById('returnReceiptNo').value = returnContext.receipt;
  document.getElementById('returnForm').action = returnContext.action;
  document.getElementById('skuScanner').value = '';
  document.getElementById('skuScanner').focus();

  const uuid = (window.crypto && crypto.randomUUID)
    ? crypto.randomUUID()
    : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
        const r = Math.random() * 16 | 0;
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
      });
  document.getElementById('clientRequestId').value = uuid;

  document.getElementById('refundTotal').textContent = '₱0.00';
  document.getElementById('returnItemsContainer').innerHTML =
    '<p class="text-xs text-center text-gray-500 py-4">Loading items…</p>';

  document.getElementById('returnModal').classList.remove('hidden');
  document.body.classList.add('overflow-hidden');

  fetch(returnContext.itemsUrl, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(items => {
      loadedReceiptItems = items; 

      if (!items.length) {
        document.getElementById('returnItemsContainer').innerHTML =
          '<p class="text-xs text-center text-rose-500">No items on this receipt.</p>';
        return;
      }
      
      document.getElementById('returnItemsContainer').innerHTML = items.map(it => {
        
        // --- RENDERING A PROJECT/SERVICE ---
        if (it.item_type === 'project') {
          
          if (it.whole_project_returned) {
            return `
              <div class="flex flex-col bg-rose-50 dark:bg-rose-950/30 p-3 rounded-xl border border-rose-200 dark:border-rose-900">
                <p class="text-sm font-bold text-rose-700 dark:text-rose-400">${it.name}</p>
                <p class="text-[11px] text-rose-500 mt-1">The entire project has already been returned.</p>
              </div>
            `;
          }

            let materialsHtml = it.materials.map((m, index) => `
            <div class="flex items-center justify-between gap-4 mt-2 p-2 bg-white dark:bg-[#10141D] rounded border border-gray-200 dark:border-[#1e2536]">
              <div class="flex-1 text-xs">
                <p class="font-bold text-gray-900 dark:text-white">${m.name}</p>
                
                <!-- SHOWING SKU FOR MATERIALS -->
                <div class="flex gap-2 mt-1.5 mb-1.5">
                  <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 bg-slate-200 dark:bg-slate-800 px-2 py-1 rounded-md border border-slate-300 dark:border-slate-600">
                    SKU: ${m.sku || 'N/A'}
                  </span>
                </div>

                <p class="text-gray-500 mt-1">Max: ${m.max} · ₱${m.price.toFixed(2)}/ea</p>
              </div>
              <div class="w-20">
                <input type="number" 
                       id="mat-input-${it.id}-${index}"
                       name="items[${it.id}][materials][${index}]" 
                       min="0" max="${m.max}" value="0" 
                       data-unit="${m.price}"
                       oninput="recalcRefund()"
                       disabled
                       placeholder="Locked"
                       class="w-full text-center text-sm rounded-lg border-gray-300 dark:border-[#1e2536] material-input locked-input">
              </div>
            </div>
          `).join('');

          return `
            <div class="flex flex-col bg-gray-50 dark:bg-[#161B26] p-3 rounded-xl border border-gray-200 dark:border-[#1e2536] return-item-row" data-project-total="${it.unit}" id="row-item-${it.id}">
              <input type="hidden" class="whole-project-input" name="items[${it.id}][whole_project]" value="1">
              <div class="flex items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${it.name}</p>
                  <p class="text-[11px] text-gray-500">Project Bundle · Total: ₱${it.unit.toFixed(2)}</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                  <label class="text-[11px] font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1 cursor-pointer bg-white dark:bg-[#1e2536] px-2 py-1 rounded border border-gray-300 dark:border-gray-600">
                    <input type="checkbox" class="whole-project-toggle" onchange="toggleWholeProject(this)" checked>
                    Entire Project
                  </label>
                  <label class="text-[10px] text-gray-500 flex items-center gap-1">
                    <input type="checkbox" name="items[${it.id}][restock]" value="1" checked>
                    Restock Items
                  </label>
                </div>
              </div>
              <div class="mt-3 pl-3 border-l-2 border-gray-300 dark:border-gray-600 space-y-1 material-container" style="display: none;">
                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Select Specific Materials to Return:</p>
                ${materialsHtml}
              </div>
            </div>
          `;
        }

        // --- RENDERING A STANDARD PRODUCT ---
        return `
          <div class="flex items-center justify-between gap-4 bg-gray-50 dark:bg-[#161B26] p-3 rounded-xl border border-gray-200 dark:border-[#1e2536] return-item-row" id="row-item-${it.id}">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${it.name}</p>
              
              <!-- SHOWING BOTH SKUs FOR STANDARD PRODUCTS -->
              <div class="flex flex-wrap gap-2 mt-2 mb-2">
                <!-- Primary SKU (Large and prominent) -->
                <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 bg-slate-200 dark:bg-slate-800 px-2 py-1 rounded-md border border-slate-300 dark:border-slate-600">
                  SKU: ${it.product_sku || it.item_sku || 'N/A'}
                </span>
                
                <!-- Secondary Item SKU (Only show if it exists AND is different from the primary SKU) -->
                ${it.item_sku && it.item_sku !== it.product_sku ? `
                  <span class="text-xs font-mono font-bold text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/50 px-2 py-1 rounded-md border border-blue-200 dark:border-blue-800">
                    Item SKU: ${it.item_sku}
                  </span>
                ` : ''}
              </div>

              <p class="text-[11px] text-gray-500">
                Bought: ${it.qty} · Returned: ${it.returned} · Unit: ₱${it.unit.toFixed(2)}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <input type="number"
                     id="qty-input-${it.id}"
                     name="items[${it.id}][qty]"
                     min="0" max="${it.max}" value="0"
                     data-unit="${it.unit}"
                     oninput="recalcRefund()"
                     disabled
                     placeholder="Locked"
                     class="w-20 text-center text-sm rounded-lg border-gray-300 dark:border-[#1e2536] locked-input">
              <label class="text-[10px] text-gray-500 flex items-center gap-1">
                <input type="checkbox" name="items[${it.id}][restock]" value="1" checked>
                Restock
              </label>
            </div>
          </div>
        `;
      }).join('');

    })
    .catch(() => {
      document.getElementById('returnItemsContainer').innerHTML =
        '<p class="text-xs text-center text-rose-500">Failed to load items.</p>';
    });
}

// =====================================================
// SKU SCANNER & UNLOCK LOGIC
// =====================================================
document.getElementById('skuScanner').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault(); 
        const scannedCode = this.value.trim().toUpperCase(); 
        if (!scannedCode) return;

        let found = false;

        loadedReceiptItems.forEach(item => {
            // 1. Search Standard Products
            if (item.item_type === 'product') {
                const itemSku = (item.item_sku || '').toUpperCase();
                const prodSku = (item.product_sku || '').toUpperCase();
                
                if (itemSku === scannedCode || prodSku === scannedCode) {
                    const input = document.getElementById(`qty-input-${item.id}`);
                    if (input) {
                        // UNLOCK the input
                        input.disabled = false;
                        input.classList.remove('locked-input');
                        input.placeholder = "0";
                        
                        // Increment quantity
                        const currentQty = parseInt(input.value) || 0;
                        if (currentQty < parseInt(input.max)) {
                            input.value = currentQty + 1;
                            found = true;
                            highlightRow(`row-item-${item.id}`);
                        }
                    }
                }
            }
            
            // 2. Search inside Projects
            if (item.item_type === 'project' && item.project_breakdown && item.project_breakdown.materials) {
                item.project_breakdown.materials.forEach((mat, index) => {
                    const matSku = (mat.sku || '').toUpperCase();
                    
                    if (matSku === scannedCode) {
                        const row = document.getElementById(`row-item-${item.id}`);
                        const toggle = row.querySelector('.whole-project-toggle');
                        
                        if (toggle && toggle.checked) {
                            alert("This item is part of a Project. Uncheck 'Entire Project' to return specific materials.");
                            found = true;
                        } else {
                            const input = document.getElementById(`mat-input-${item.id}-${index}`);
                            if (input) {
                                // UNLOCK the input
                                input.disabled = false;
                                input.classList.remove('locked-input');
                                input.placeholder = "0";

                                const currentQty = parseInt(input.value) || 0;
                                if (currentQty < parseInt(input.max)) {
                                    input.value = currentQty + 1;
                                    found = true;
                                    highlightRow(`row-item-${item.id}`);
                                }
                            }
                        }
                    }
                });
            }
        });

        if (found) {
            // Flash green
            this.classList.add('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/20');
            setTimeout(() => this.classList.remove('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/20'), 500);
            this.value = ''; 
            recalcRefund();
        } else {
            // Flash red
            this.classList.add('border-red-500', 'bg-red-50', 'dark:bg-red-900/20');
            setTimeout(() => this.classList.remove('border-red-500', 'bg-red-50', 'dark:bg-red-900/20'), 500);
            alert("FRAUD ALERT: This SKU does not exist on this receipt. Item is not from our store.");
            this.value = ''; 
        }
    }
});

function highlightRow(id) {
    const row = document.getElementById(id);
    if (row) {
        row.classList.add('ring-2', 'ring-emerald-500');
        setTimeout(() => row.classList.remove('ring-2', 'ring-emerald-500'), 1000);
    }
}

function toggleWholeProject(checkbox) {
  const row = checkbox.closest('.return-item-row');
  const container = row.querySelector('.material-container');
  const hiddenInput = row.querySelector('.whole-project-input');

  if (checkbox.checked) {
    container.style.display = 'none';
    if (hiddenInput) hiddenInput.value = 1;
  } else {
    container.style.display = 'block';
    if (hiddenInput) hiddenInput.value = 0;
  }
  recalcRefund();
}

function recalcRefund() {
  let total = 0;
  document.querySelectorAll('.return-item-row').forEach(row => {
    const toggle = row.querySelector('.whole-project-toggle');
    
    if (toggle && toggle.checked) {
      total += parseFloat(row.dataset.projectTotal || 0);
    } else {
      row.querySelectorAll('input[type="number"]').forEach(inp => {
        // Only calculate if the input is NOT disabled
        if (!inp.disabled) {
            const qty = parseFloat(inp.value) || 0;
            total += qty * parseFloat(inp.dataset.unit || 0);
        }
      });
    }
  });
  document.getElementById('refundTotal').textContent = '₱' + total.toFixed(2);
}

function closeReturnModal() {
  document.getElementById('returnModal').classList.add('hidden');
  document.body.classList.remove('overflow-hidden');
  document.getElementById('returnForm').reset();
}
</script>
@endpush