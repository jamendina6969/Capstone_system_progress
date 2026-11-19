@extends('layouts.default')

@section('title', 'Stock Logs')

@section('content')

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Stock Logs</h1>

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-[calc(100vh-140px)] mb-4">

        <!-- Header / Category Filter Bar -->
        <div class="shrink-0 px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Movement Batches</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Review historical stock adjustments and transfer logs</p>
            </div>

            <form method="GET" action="{{ route('inventory.logs') }}" class="flex items-center gap-3 w-full sm:w-auto">
                <select name="category_id" onchange="this.form.submit()"
                        class="w-full sm:w-64 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 dark:focus:border-slate-500 cursor-pointer">
                    <option value="">All Categories (Default)</option>

                    @if(isset($categories))
                        @foreach ($categories as $category)
                            <optgroup label="{{ strtoupper($category->name) }}">
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    All {{ $category->name }}
                                </option>
                                @foreach($category->children->sortBy('name') as $child)
                                    <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>
                                        &nbsp;&nbsp;&mdash; {{ $child->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @endif
                </select>
                @if (request('category_id'))
                    <a href="{{ route('inventory.logs') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-900 dark:text-slate-400 dark:hover:text-white whitespace-nowrap">Reset</a>
                @endif
            </form>
        </div>

        <!-- Scrollable Body -->
        <div id="batchScrollContainer" class="flex-1 min-h-0 overflow-y-auto custom-scrollbar relative">
            <div id="batchesContainer" class="p-8 space-y-6">
                @include('inventory.partials.log-batches', ['grouped' => $grouped])
            </div>

            {{-- LOADING --}}
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more batches...
            </div>

            {{-- ERROR --}}
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more batches. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($grouped->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $grouped->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $grouped->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $grouped->total() }}</span>
                    batches
                @else
                    No batches found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Stock log pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($grouped->getUrlRange(max(1, $grouped->currentPage() - 2), min($grouped->lastPage(), $grouped->currentPage() + 2)) as $page => $url)
                        @if ($page == $grouped->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- STOCK LOG DETAIL MODAL -->
    <div id="stockLogModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeStockLogModal()">
        <div class="relative w-full max-w-lg bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-6 sm:p-8 text-left" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-4 mb-4 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="logModalTitle">Stock Movement Details</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5" id="logModalDate"></p>
                </div>
                <button type="button" onclick="closeStockLogModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Product</span>
                    <span id="logModalProduct" class="font-bold text-gray-900 dark:text-white text-right"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Category</span>
                    <span id="logModalCategory" class="font-medium text-gray-800 dark:text-slate-200"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Movement Type</span>
                    <span id="logModalType" class="font-semibold text-blue-600 dark:text-blue-400"></span>
                </div>
                <div class="grid grid-cols-3 gap-2 py-2 text-center bg-gray-50 dark:bg-[#161B26] p-3 rounded-2xl border border-gray-100 dark:border-[#1e2536]">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Previous</p>
                        <p id="logModalPrev" class="text-base font-bold text-gray-700 dark:text-slate-300"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Change</p>
                        <p id="logModalChange" class="text-base font-bold text-emerald-600 dark:text-emerald-400"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">New Qty</p>
                        <p id="logModalNew" class="text-base font-bold text-gray-900 dark:text-white"></p>
                    </div>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Requested By</span>
                    <span id="logModalRequester" class="font-medium text-gray-900 dark:text-white"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Approved By</span>
                    <span id="logModalApprover" class="font-medium text-gray-900 dark:text-white"></span>
                </div>
                <div class="py-2">
                    <span class="text-gray-500 dark:text-slate-400 text-xs block mb-1">Notes / Reason</span>
                    <p id="logModalNote" class="text-xs bg-gray-50 dark:bg-[#161B26] p-3 rounded-xl border border-gray-100 dark:border-[#1e2536] text-gray-700 dark:text-slate-300 italic"></p>
                </div>
            </div>
            <div class="flex justify-end pt-4 border-t-2 border-gray-200 dark:border-[#1e2536] mt-4">
                <button type="button" onclick="closeStockLogModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Close</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openStockLogModal(row) {
        if (!row) return;
        document.getElementById('logModalProduct').textContent = row.dataset.product || '—';
        document.getElementById('logModalCategory').textContent = row.dataset.category || '—';
        document.getElementById('logModalType').textContent = row.dataset.type || '—';
        document.getElementById('logModalDate').textContent = row.dataset.date || '';
        document.getElementById('logModalPrev').textContent = row.dataset.prev || '—';
        document.getElementById('logModalChange').textContent = (row.dataset.qty > 0 ? '+' : '') + row.dataset.qty;
        document.getElementById('logModalNew').textContent = row.dataset.new || '—';
        document.getElementById('logModalRequester').textContent = row.dataset.requester || 'Deleted User';
        document.getElementById('logModalApprover').textContent = row.dataset.approver || '—';
        document.getElementById('logModalNote').textContent = row.dataset.note || 'No notes provided.';

        const modal = document.getElementById('stockLogModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeStockLogModal() {
        const modal = document.getElementById('stockLogModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeStockLogModal();
    });
</script>
@endpush

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
    const tableContainer = document.getElementById('batchScrollContainer');
    const batchesContainer = document.getElementById('batchesContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    let nextPageUrl = @json($grouped->nextPageUrl());
    let currentPage = {{ $grouped->currentPage() }};
    let lastPage = {{ $grouped->lastPage() }};
    let totalRecords = {{ $grouped->total() }};
    let perPage = {{ $grouped->perPage() }};
    let initialPage = {{ $grouped->currentPage() }};
    let initialStart = {{ $grouped->firstItem() ?? 0 }} || 0;

    let isLoading = false;
    let visualPage = initialPage;

    function updateResultsCounter() {
        const rowsLoaded = batchesContainer.querySelectorAll('[data-record-row="true"]').length;
        if (totalRecords === 0 || rowsLoaded === 0) { resultsText.textContent = 'No batches found.'; return; }
        const firstRecord = initialStart;
        const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
        resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> batches`;
    }

    function getVisiblePage() {
        const rows = batchesContainer.querySelectorAll('[data-record-row="true"]');
        if (!rows.length) return initialPage;

        const isAtBottom = (tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight) <= 5;
        if (isAtBottom && lastPage > 0) return lastPage;

        const containerRect = tableContainer.getBoundingClientRect();
        const visibleTop = containerRect.top;
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

    async function loadMoreBatches() {
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
                const temp = document.createElement('div');
                temp.innerHTML = data.html;
                batchesContainer.querySelector('[data-empty-row="true"]')?.remove();
                Array.from(temp.children).forEach(batch => {
                    if (batch.dataset.recordRow === 'true') batchesContainer.appendChild(batch);
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
            console.error('Error loading batches:', error);
            loadError.classList.remove('hidden');
        } finally {
            isLoading = false;
            loadingIndicator.classList.add('hidden');
        }
    }

    tableContainer.addEventListener('scroll', function () {
        updatePaginationNumbers(getVisiblePage());
        const distanceFromBottom = tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight;
        if (distanceFromBottom < 150 && nextPageUrl && !isLoading) loadMoreBatches();
    }, { passive: true });

    retryLoadButton?.addEventListener('click', () => loadMoreBatches());

    // Init counters
    updateResultsCounter();
    updatePaginationNumbers(initialPage);
});
</script>
@endpush