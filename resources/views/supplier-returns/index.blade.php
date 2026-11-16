@extends('layouts.default')

@section('title', 'Supplier Returns')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Supplier Returns</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Track defective goods, wrong items, and refunds</p>
        </div>
        <a href="{{ route('supplier-returns.create') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
            + Create Return
        </a>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-[calc(100vh-180px)] mb-4">
        
        <!-- Filter Section -->
        <div class="shrink-0 px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
            <form id="filterForm" method="GET" action="{{ route('supplier-returns.index') }}" class="flex flex-col xl:flex-row xl:items-center gap-4">
                
                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search Return # or Supplier..." autocomplete="off"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <!-- Status Filter -->
                    <div class="w-full sm:w-40">
                        <select name="status" id="statusFilter" onchange="this.form.submit()" class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Reason Filter -->
                    <div class="w-full sm:w-40">
                        <select name="reason" id="reasonFilter" onchange="this.form.submit()" class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                            <option value="">All Reasons</option>
                            <option value="defective" {{ request('reason') == 'defective' ? 'selected' : '' }}>Defective</option>
                            <option value="wrong_item" {{ request('reason') == 'wrong_item' ? 'selected' : '' }}>Wrong Item</option>
                            <option value="overstock" {{ request('reason') == 'overstock' ? 'selected' : '' }}>Overstock</option>
                            <option value="other" {{ request('reason') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    @if (request()->anyFilled(['search', 'status', 'reason']))
                        <a href="{{ route('supplier-returns.index') }}" class="text-xs font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26]">
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full text-sm text-left relative">
                <thead id="returnsTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Return #</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Supplier</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Reason</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Total Amount</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Status</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('supplier-returns.partials.rows', ['returns' => $returns])
                </tbody>
            </table>

            {{-- LOADING --}}
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more returns...
            </div>

            {{-- ERROR --}}
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more returns. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($returns->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $returns->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $returns->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $returns->total() }}</span>
                    returns
                @else
                    No returns found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Returns pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($returns->getUrlRange(max(1, $returns->currentPage() - 2), min($returns->lastPage(), $returns->currentPage() + 2)) as $page => $url)
                        @if ($page == $returns->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    {{-- ACTION CONFIRMATION MODAL --}}
    <div id="actionConfirmModal" class="fixed inset-0 z-[80] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6" onclick="event.stopPropagation()">
            <div class="text-center">
                <div id="actionIconContainer" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4">
                    <!-- Icon injected by JS -->
                </div>
                <h3 id="actionTitle" class="text-lg font-bold text-gray-900 dark:text-white mb-2"></h3>
                <p id="actionMessage" class="text-sm text-gray-500 dark:text-slate-400 mb-6"></p>
                
                <div class="flex justify-center gap-3">
                    <button type="button" onclick="closeActionModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Go Back</button>
                    <button type="button" id="confirmActionBtn" class="text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs"></button>
                </div>
            </div>
        </div>
    </div>

    {{-- VIEW RETURN MODAL --}}
    <div id="viewReturnModal" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-4xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            
            <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Return Details</h3>
                    <p id="view_return_label" class="text-xs text-gray-500 mt-1"></p>
                </div>
                <button type="button" onclick="toggleViewReturnModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500">Supplier</p>
                        <p id="view_return_supplier" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500">Status</p>
                        <p id="view_return_status" class="text-sm font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500">Reason</p>
                        <p id="view_return_reason" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500">Total Amount</p>
                        <p id="view_return_total" class="text-sm font-bold text-emerald-600"></p>
                    </div>
                </div>

                <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                    <p class="text-[10px] uppercase font-bold text-gray-500 mb-2">Returned Items (with SKUs)</p>
                    <div class="border border-gray-200 dark:border-[#1e2536] rounded-xl overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-4 py-3">Category</th>
                                    <th class="px-4 py-3">SKU</th>
                                    <th class="px-4 py-3 text-right">Qty</th>
                                    <th class="px-4 py-3 text-right">Unit Cost</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="view_return_items_body" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                                <!-- Items injected by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 mt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="toggleViewReturnModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all duration-300 shadow-xs">Close</button>
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
    const tableHead = document.getElementById('returnsTableHead');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    let nextPageUrl = @json($returns->nextPageUrl());
    let currentPage = {{ $returns->currentPage() }};
    let lastPage = {{ $returns->lastPage() }};
    let totalRecords = {{ $returns->total() }};
    let perPage = {{ $returns->perPage() }};
    let initialPage = {{ $returns->currentPage() }};
    let initialStart = {{ $returns->firstItem() ?? 0 }} || 0;

    let isLoading = false;
    let visualPage = initialPage;

    function updateResultsCounter() {
        const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
        if (totalRecords === 0 || rowsLoaded === 0) {
            resultsText.textContent = 'No returns found.';
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
            returns
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
            console.error('Error loading returns:', error);
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

    // =====================================================
    // EXISTING MODAL LOGIC
    // =====================================================
    function toggleViewReturnModal(show) {
        const modal = document.getElementById('viewReturnModal');
        if (show) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    let currentFormToSubmit = null;

    function openActionModal(type, formId) {
        currentFormToSubmit = document.getElementById(formId);
        
        const modal = document.getElementById('actionConfirmModal');
        const title = document.getElementById('actionTitle');
        const message = document.getElementById('actionMessage');
        const confirmBtn = document.getElementById('confirmActionBtn');
        const iconContainer = document.getElementById('actionIconContainer');

        if (type === 'process') {
            title.textContent = 'Process Return';
            message.textContent = 'Are you sure you want to process this return? Stock and balance will be adjusted.';
            confirmBtn.className = 'bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs';
            confirmBtn.textContent = 'Yes, Process';
            iconContainer.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4 bg-emerald-100 text-emerald-600';
            iconContainer.innerHTML = '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
        } else {
            title.textContent = 'Cancel Return';
            message.textContent = 'Are you sure you want to cancel this return? This action cannot be undone.';
            confirmBtn.className = 'bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs';
            confirmBtn.textContent = 'Yes, Cancel';
            iconContainer.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4 bg-rose-100 text-rose-600';
            iconContainer.innerHTML = '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeActionModal() {
        document.getElementById('actionConfirmModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        currentFormToSubmit = null;
    }

    document.getElementById('confirmActionBtn').addEventListener('click', function() {
        if (currentFormToSubmit) currentFormToSubmit.submit();
    });

    function openViewReturnModal(returnDataString) {
        const returnData = JSON.parse(returnDataString);

        document.getElementById('view_return_label').textContent = `Return #${returnData.return_number}`;
        document.getElementById('view_return_supplier').textContent = returnData.supplier ? returnData.supplier.name : 'N/A';
        document.getElementById('view_return_reason').textContent = returnData.reason.replace(/_/g, ' ').toUpperCase();
        document.getElementById('view_return_total').textContent = '₱' + parseFloat(returnData.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
        
        const statusEl = document.getElementById('view_return_status');
        statusEl.textContent = returnData.status.toUpperCase();
        statusEl.className = 'text-sm font-semibold ' + (
            returnData.status === 'pending' ? 'text-amber-600' :
            returnData.status === 'completed' ? 'text-emerald-600' : 'text-rose-600'
        );

        const tbody = document.getElementById('view_return_items_body');
        tbody.innerHTML = '';
        
        returnData.items.forEach(item => {
            let categoryName = '—';
            if (item.product && item.product.category) {
                const cat = item.product.category;
                categoryName = cat.parent ? `${cat.parent.name} › ${cat.name}` : cat.name;
            }

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 dark:hover:bg-[#1e2536]/30';
            tr.innerHTML = `
                <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">${item.product ? item.product.product_name : 'Deleted Product'}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-slate-400">${categoryName}</td>
                <td class="px-4 py-3 text-gray-500 font-mono text-[11px]">${item.product ? item.product.product_sku : '—'}</td>
                <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">${item.quantity}</td>
                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">₱${parseFloat(item.unit_cost).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">₱${parseFloat(item.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
            `;
            tbody.appendChild(tr);
        });

        toggleViewReturnModal(true);
    }

    // Close modals when clicking outside
    document.getElementById('viewReturnModal').addEventListener('click', function(e) { if (e.target === this) toggleViewReturnModal(false); });
    document.getElementById('actionConfirmModal').addEventListener('click', function(e) { if (e.target === this) closeActionModal(); });

    // Expose functions globally for inline onclick handlers
    window.openViewReturnModal = openViewReturnModal;
    window.openActionModal = openActionModal;
    window.closeActionModal = closeActionModal;
    window.toggleViewReturnModal = toggleViewReturnModal;
});
</script>
@endpush