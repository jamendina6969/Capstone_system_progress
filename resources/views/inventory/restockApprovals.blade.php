@extends('layouts.default')

@section('title', 'Restock Approvals')

@section('content')

    @php
        $statusOptions = [
            '' => 'All Statuses',
            'pending' => 'Pending Approval',
            'in_transit' => 'In Transit (Approved)',
        ];
        $currentStatus = request('status', '');
        $selectedStatusLabel = $statusOptions[$currentStatus] ?? 'All Statuses';
    @endphp

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Restock Approvals</h1>

    @if (session('error'))
        <div class="mb-6 rounded-2xl bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-rose-300 border-2 border-red-200 dark:border-rose-900 px-6 py-4 text-sm font-medium">{{ session('error') }}</div>
    @endif

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-180px)] min-h-[500px] mb-4">

        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Restock Approvals</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Staff-submitted stock replenishment requests awaiting review</p>
            </div>
        </div>

        <!-- FILTERS -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-3.5 sm:py-4 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/30">
            <form method="GET" action="{{ route('inventory.approvals') }}" id="filterForm" class="flex flex-col xl:flex-row flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px] w-full xl:w-auto">
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search product, requester, note..." class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:border-slate-400">
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <div class="flex items-center gap-3 w-full xl:w-auto">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide whitespace-nowrap">FROM:</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-300 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wide whitespace-nowrap">TO:</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-300 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                    </div>
                </div>

                <div class="custom-dropdown relative w-full xl:w-auto" id="filter-status-dropdown">
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                    <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-300 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                        <span class="dropdown-label truncate">{{ $selectedStatusLabel }}</span>
                        <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                        @foreach ($statusOptions as $value => $label)
                            <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                @if (request()->anyFilled(['status', 'search', 'date_from', 'date_to']))
                    <a href="{{ route('inventory.approvals') }}" class="text-sm font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2 rounded-lg transition-colors whitespace-nowrap">Clear</a>
                @endif
            </form>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full min-w-[850px] text-sm text-left relative">
                <thead id="approvalsTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Product</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Type</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Quantity</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Requested By</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Date</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] min-w-[190px] whitespace-nowrap">Status</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Note</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('inventory.partials.approval-rows', ['pending' => $pending])
                </tbody>
            </table>

            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more approvals...
            </div>

            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more approvals. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($pending->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $pending->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $pending->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $pending->total() }}</span>
                    approvals
                @else
                    No approvals found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Approvals pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($pending->getUrlRange(max(1, $pending->currentPage() - 2), min($pending->lastPage(), $pending->currentPage() + 2)) as $page => $url)
                        @if ($page == $pending->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- ==================== VIEW DETAILS MODAL ==================== -->
    <div id="viewDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs hidden" onclick="closeViewModal()">
        <div class="bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-2xl p-6 w-full max-w-md shadow-xl mx-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-[#1e2536]">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2-2z"></path></svg>
                    Request Details
                </h3>
                <button type="button" onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="space-y-3 pt-4 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Date Requested</span><span id="viewDate" class="font-semibold text-gray-900 dark:text-white"></span></div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Requested By</span><span id="viewRequester" class="font-semibold text-gray-900 dark:text-white"></span></div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Product</span><span id="viewProduct" class="font-semibold text-gray-900 dark:text-white text-right max-w-[200px] truncate"></span></div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Quantity</span><span id="viewQuantity" class="font-semibold text-gray-900 dark:text-white"></span></div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Type</span><span id="viewType" class="font-semibold text-gray-900 dark:text-white"></span></div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Status</span><span id="viewStatus" class="font-semibold text-gray-900 dark:text-white"></span></div>

                <div class="pt-2">
                    <span class="block text-gray-500 dark:text-slate-400 mb-1">Note / Reason</span>
                    <p id="viewNote" class="bg-gray-50 dark:bg-[#161B26] p-3 rounded-xl text-gray-700 dark:text-slate-300 border border-gray-100 dark:border-[#1e2536] italic"></p>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 mt-4 border-t border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="closeViewModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Close</button>
            </div>
        </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div id="approveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs hidden">
        <div class="bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-2xl p-6 w-full max-w-sm shadow-xl mx-4 text-center">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">Approve Restock Request</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-6">Approve this restock? Stock will be updated immediately.</p>

            <form id="approveForm" method="POST" class="flex items-center justify-center gap-3">
                @csrf
                <button type="button" onclick="closeApproveModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">Cancel</button>
                <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">OK</button>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    // =====================================================
    // INFINITE SCROLL
    // =====================================================
    const tableContainer = document.getElementById('tableScrollContainer');
    const tableBody = document.getElementById('tableBody');
    const tableHead = document.getElementById('approvalsTableHead');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    let nextPageUrl = @json($pending->nextPageUrl());
    let currentPage = {{ $pending->currentPage() }};
    let lastPage = {{ $pending->lastPage() }};
    let totalRecords = {{ $pending->total() }};
    let perPage = {{ $pending->perPage() }};
    let initialPage = {{ $pending->currentPage() }};
    let initialStart = {{ $pending->firstItem() ?? 0 }} || 0;

    let isLoading = false;
    let visualPage = initialPage;

    function updateResultsCounter() {
        const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
        if (totalRecords === 0 || rowsLoaded === 0) { resultsText.textContent = 'No approvals found.'; return; }
        const firstRecord = initialStart;
        const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
        resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> approvals`;
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
            if (firstVisibleIndex === -1 && rect.bottom > visibleTop && rect.top < visibleBottom) firstVisibleIndex = index;
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
            if (i === page) html += `<span class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">${i}</span>`;
            else html += `<button type="button" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">${i}</button>`;
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
            const response = await fetch(fetchUrl.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Cache-Control': 'no-cache' } });
            if (!response.ok) throw new Error('Request failed.');
            const data = await response.json();

            const newPage = Number(data.current_page);
            if (data.html) {
                const temp = document.createElement('tbody');
                temp.innerHTML = data.html;
                tableBody.querySelector('[data-empty-row="true"]')?.remove();
                Array.from(temp.children).forEach(row => {
                    if (row.dataset.recordRow === 'true' || row.id.startsWith('reject-row-')) tableBody.appendChild(row);
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
            console.error('Error loading approvals:', error);
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

    retryLoadButton?.addEventListener('click', () => loadMoreRecords());

    // Custom dropdown
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
                    if (dropdown.id.startsWith('filter-')) dropdown.closest('form')?.submit();
                });
            });
            document.addEventListener('click', e => { if (!dropdown.contains(e.target)) closeMenu(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });
            dropdown.dataset.initialized = 'true';
        });
    }
    initCustomDropdowns();

    // Live Search
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    let debounceTimer;
    if (searchInput && filterForm) {
        const savedCursorPos = sessionStorage.getItem('approvalsSearchCursorPos');
        if (savedCursorPos !== null) {
            searchInput.focus();
            searchInput.setSelectionRange(parseInt(savedCursorPos), parseInt(savedCursorPos));
            sessionStorage.removeItem('approvalsSearchCursorPos');
        } else if (searchInput.value.length > 0) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            sessionStorage.setItem('approvalsSearchCursorPos', searchInput.selectionStart);
            debounceTimer = setTimeout(() => filterForm.submit(), 300);
        });
    }

    // Init counters
    updateResultsCounter();
    updatePaginationNumbers(initialPage);
});

// =====================================================
// MODAL FUNCTIONS (Global)
// =====================================================
function openViewModal(requestJson) {
    const req = JSON.parse(requestJson);
    const dateObj = new Date(req.created_at);
    const dateStr = dateObj.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    document.getElementById('viewDate').textContent = dateStr;
    document.getElementById('viewRequester').textContent = req.requester ? `${req.requester.firstname || ''} ${req.requester.lastname || ''}` : 'System';
    document.getElementById('viewProduct').textContent = req.product ? req.product.product_name : 'Deleted Product';
    document.getElementById('viewQuantity').textContent = Number(req.quantity).toLocaleString();
    document.getElementById('viewType').textContent = req.type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    document.getElementById('viewStatus').textContent = req.status === 'approved' ? 'Approved (In Transit)' : 'Pending';
    document.getElementById('viewNote').textContent = req.note || 'No notes provided.';
    document.getElementById('viewDetailsModal').classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('viewDetailsModal').classList.add('hidden');
}

function toggleRejectForm(id) {
    document.querySelectorAll('.reject-form-row').forEach(row => { if (row.id !== 'reject-row-' + id) row.classList.add('hidden'); });
    document.getElementById('reject-row-' + id)?.classList.toggle('hidden');
}

function openApproveModal(url) {
    document.getElementById('approveForm').action = url;
    document.getElementById('approveModal').classList.remove('hidden');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeViewModal();
        closeApproveModal();
    }
});
</script>
@endpush