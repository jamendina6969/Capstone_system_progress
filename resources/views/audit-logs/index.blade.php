
@extends('layouts.default')

@section('title', 'Audit Logs')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Logs</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                Track and audit all critical system operations and user activities.
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden mb-8">

        <!-- Filters -->
        <div class="px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
            <form id="filterForm" method="GET" action="{{ route('audit-logs.index') }}"
                class="flex flex-col xl:flex-row xl:items-center gap-4">

                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>

                        <input type="text" id="searchInput" name="search"
                            value="{{ request('search') }}"
                            placeholder="Search logs by user, action, or model..."
                            autocomplete="off"
                            class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select name="event" id="eventFilter"
                            onchange="this.form.submit()"
                            class="w-full sm:w-48 rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">

                            <option value="">All Actions</option>

                            <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                            <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                            <option value="login" {{ request('event') == 'login' ? 'selected' : '' }}>Login</option>
                            <option value="logout" {{ request('event') == 'logout' ? 'selected' : '' }}>Logout</option>

                            @foreach($events as $event)
                                @if(!in_array($event, ['created', 'updated', 'deleted', 'login', 'logout']))
                                    <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                        {{ ucfirst($event) }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    @if(request()->anyFilled(['search', 'event']))
                        <a href="{{ route('audit-logs.index') }}"
                            class="text-xs font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26]">
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer"
            class="overflow-x-auto h-[600px] overflow-y-auto custom-scrollbar relative">

            <table class="w-full text-sm text-left relative">
                <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">User</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Action</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Target Model</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">IP Address</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Timestamp</th>
                    </tr>
                </thead>

                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('audit-logs.partials.rows', ['logs' => $logs])
                </tbody>
            </table>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l2.647-2.647z"></path>
                </svg>

                Loading more records...
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">

            <div class="whitespace-nowrap" id="resultsText">
                @if($logs->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">
                        {{ $logs->firstItem() }}
                    </span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">
                        {{ $logs->lastItem() }}
                    </span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">
                        {{ $logs->total() }}
                    </span>
                    records
                @else
                    No records found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full">
                <div id="paginationNumbersContainer"
                    class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">

                    @foreach($logs->getUrlRange(
                        max(1, $logs->currentPage() - 3),
                        min($logs->lastPage(), $logs->currentPage() + 3)
                    ) as $page => $url)

                        @if($page == $logs->currentPage())
                            <span
                                class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                                class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">
                                {{ $page }}
                            </a>
                        @endif

                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- Audit Log Detail Modal -->
    <div id="auditModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden">

        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden">

            <div class="flex justify-between items-center px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Audit Log Specifications
                    </h3>
                    <p id="modalDescription" class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                        Detailed breakdown of user action and data modifications.
                    </p>
                </div>

                <button type="button" onclick="closeAuditModal()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">
                    &times;
                </button>
            </div>

            <div class="px-8 py-6 space-y-6 max-h-[65vh] overflow-y-auto text-sm">

                <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-[#161B26] p-4 rounded-2xl border border-gray-200 dark:border-[#1e2536]">
                    <div>
                        <span class="text-gray-500 dark:text-slate-400 block text-xs">User</span>
                        <span id="modalUser" class="font-semibold text-gray-900 dark:text-white">-</span>
                    </div>

                    <div>
                        <span class="text-gray-500 dark:text-slate-400 block text-xs">Action</span>
                        <span id="modalEvent" class="font-semibold text-gray-900 dark:text-white uppercase">-</span>
                    </div>

                    <div>
                        <span class="text-gray-500 dark:text-slate-400 block text-xs">Target Model</span>
                        <span id="modalTarget" class="font-semibold text-gray-900 dark:text-white">-</span>
                    </div>

                    <div>
                        <span class="text-gray-500 dark:text-slate-400 block text-xs">IP Address</span>
                        <span id="modalIp" class="font-mono text-gray-900 dark:text-white">-</span>
                    </div>

                    <div class="col-span-2">
                        <span class="text-gray-500 dark:text-slate-400 block text-xs">Origin / Context</span>
                        <span id="modalContext" class="text-gray-900 dark:text-white">-</span>
                    </div>

                    <div class="col-span-2">
                        <span class="text-gray-500 dark:text-slate-400 block text-xs">Timestamp</span>
                        <span id="modalTimestamp" class="text-gray-900 dark:text-white">-</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                        Activity Details & Modifications
                    </h4>

                    <div class="border border-gray-200 dark:border-[#1e2536] rounded-2xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-500 dark:text-slate-400 border-b border-gray-200 dark:border-[#1e2536]">
                                <tr>
                                    <th class="px-4 py-3 font-semibold w-1/3">Field / Property</th>
                                    <th class="px-4 py-3 font-semibold w-1/3">Previous Value</th>
                                    <th class="px-4 py-3 font-semibold w-1/3">New Value</th>
                                </tr>
                            </thead>

                            <tbody id="modalChangesTable"
                                class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                        Loading details...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] border-t-2 border-gray-200 dark:border-[#1e2536] flex justify-end">
                <button type="button" onclick="closeAuditModal()"
                    class="px-5 py-2.5 bg-gray-200 dark:bg-[#1e2536] text-gray-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-gray-300 dark:hover:bg-[#283248] transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #334155;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8;
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // =====================================================
    // AUDIT LOG PAGINATION AND INFINITE SCROLL
    // =====================================================

    const tableContainer = document.getElementById('tableScrollContainer');
    const tableBody = document.getElementById('tableBody');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const paginationContainer = document.getElementById('paginationNumbersContainer');

    let nextPageUrl = @json($logs->nextPageUrl());
    let currentPage = {{ $logs->currentPage() }};
    let lastPage = {{ $logs->lastPage() }};
    let totalRecords = {{ $logs->total() }};
    let perPage = {{ $logs->perPage() }};
    let initialPage = {{ $logs->currentPage() }};
    let isLoading = false;
    let visualPage = initialPage;

    // Get the page number based on the first visible record.
    // This works when scrolling both down and up.
    function getVisiblePage() {
        const rows = Array.from(
            tableBody.querySelectorAll('tr[data-record-row="true"]')
        );

        if (!rows.length) {
            return initialPage;
        }

        const containerRect = tableContainer.getBoundingClientRect();
        const header = tableContainer.querySelector('thead');
        const headerHeight = header ? header.getBoundingClientRect().height : 0;

        const visibleTop = containerRect.top + headerHeight;
        const visibleBottom = containerRect.bottom;

        let firstVisibleIndex = -1;

        rows.forEach((row, index) => {
            const rect = row.getBoundingClientRect();

            if (firstVisibleIndex === -1 &&
                rect.bottom > visibleTop &&
                rect.top < visibleBottom) {
                firstVisibleIndex = index;
            }
        });

        // If no row is visible, use the last loaded record.
        if (firstVisibleIndex === -1) {
            firstVisibleIndex = rows.length - 1;
        }

        const pageOffset = Math.floor(firstVisibleIndex / perPage);

        return Math.min(
            lastPage,
            Math.max(1, initialPage + pageOffset)
        );
    }

    // Keep pagination numbers updated.
    function updatePaginationNumbers(pageToShow) {
        const page = Math.min(lastPage, Math.max(1, pageToShow));

        if (visualPage === page &&
            paginationContainer.children.length > 0) {
            return;
        }

        visualPage = page;

        let html = '';

        const startPage = Math.max(1, page - 3);
        const endPage = Math.min(lastPage, page + 3);

        const currentUrl = new URL(window.location.href);

        for (let i = startPage; i <= endPage; i++) {
            if (i === page) {
                html += `
                    <span class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">
                        ${i}
                    </span>
                `;
            } else {
                const pageUrl = new URL(currentUrl.href);
                pageUrl.searchParams.set('page', i);

                html += `
                    <a href="${pageUrl.pathname + pageUrl.search}"
                        class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">
                        ${i}
                    </a>
                `;
            }
        }

        paginationContainer.innerHTML = html;
    }

    // Update the results counter.
    function updateResultsCounter() {
        const rowsLoaded = tableBody.querySelectorAll(
            'tr[data-record-row="true"]'
        ).length;

        const firstRecord = rowsLoaded > 0
            ? ((initialPage - 1) * perPage) + 1
            : 0;

        const lastRecord = Math.min(
            firstRecord + rowsLoaded - 1,
            totalRecords
        );

        const resultsText = document.getElementById('resultsText');

        if (totalRecords === 0 || rowsLoaded === 0) {
            resultsText.textContent = 'No records found';
            return;
        }

        resultsText.innerHTML = `
            Showing
            <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span>
            to
            <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span>
            of
            <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span>
            records
        `;
    }

    // Load the next page.
    async function loadMoreLogs() {
        if (!nextPageUrl || isLoading) {
            return;
        }

        isLoading = true;
        loadingIndicator.classList.remove('hidden');

        try {
            const response = await fetch(nextPageUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load audit logs.');
            }

            const data = await response.json();

            if (data.html) {
                tableBody.insertAdjacentHTML('beforeend', data.html);
            }

            nextPageUrl = data.next_page_url;
            currentPage = data.current_page;
            lastPage = data.last_page;
            totalRecords = data.total_records;
            perPage = data.per_page;

            updateResultsCounter();

            // Recalculate the active page using the current scroll position.
            updatePaginationNumbers(getVisiblePage());

        } catch (error) {
            console.error('Error loading audit logs:', error);
        } finally {
            isLoading = false;
            loadingIndicator.classList.add('hidden');
        }
    }

    // Scroll listener: updates the active page in both directions.
    tableContainer.addEventListener('scroll', function () {
        updatePaginationNumbers(getVisiblePage());

        const distanceFromBottom =
            tableContainer.scrollHeight -
            tableContainer.scrollTop -
            tableContainer.clientHeight;

        // Load more records when the user is near the bottom.
        if (distanceFromBottom < 100) {
            loadMoreLogs();
        }
    });

    // Keep the pagination correct after loading additional records.
    updatePaginationNumbers(getVisiblePage());
    updateResultsCounter();


    // =====================================================
    // LIVE SEARCH
    // =====================================================

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');

        let debounceTimer;

        if (!searchInput) {
            return;
        }

        const savedCursorPos = sessionStorage.getItem('searchCursorPos');

        if (savedCursorPos !== null) {
            searchInput.focus();

            const cursorPosition = parseInt(savedCursorPos, 10);

            searchInput.setSelectionRange(
                cursorPosition,
                cursorPosition
            );

            sessionStorage.removeItem('searchCursorPos');
        } else if (searchInput.value.length > 0) {
            searchInput.focus();

            searchInput.setSelectionRange(
                searchInput.value.length,
                searchInput.value.length
            );
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);

            sessionStorage.setItem(
                'searchCursorPos',
                searchInput.selectionStart
            );

            debounceTimer = setTimeout(function () {
                filterForm.submit();
            }, 300);
        });
    });


    // =====================================================
    // AUDIT LOG DETAIL MODAL
    // =====================================================

    function openAuditModal(id) {
        const modal = document.getElementById('auditModal');

        modal.classList.remove('hidden');

        document.getElementById('modalDescription').innerText =
            'Fetching specifications...';

        document.getElementById('modalUser').innerText = 'Loading...';
        document.getElementById('modalEvent').innerText = 'Loading...';
        document.getElementById('modalTarget').innerText = 'Loading...';
        document.getElementById('modalIp').innerText = 'Loading...';
        document.getElementById('modalContext').innerText = 'Loading...';
        document.getElementById('modalTimestamp').innerText = 'Loading...';

        document.getElementById('modalChangesTable').innerHTML = `
            <tr>
                <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                    Fetching specifications...
                </td>
            </tr>
        `;

        fetch(`/audit-logs/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Unable to fetch audit log details.');
            }

            return response.json();
        })
        .then(res => {
            if (!res.success) {
                throw new Error('Audit log details were not returned.');
            }

            const data = res.data;

            document.getElementById('modalDescription').innerText =
                data.description ?? '';

            document.getElementById('modalUser').innerText =
                data.user ?? 'System / Guest';

            document.getElementById('modalEvent').innerText =
                data.event ?? '';

            document.getElementById('modalTarget').innerText =
                data.target_model ?? '';

            document.getElementById('modalIp').innerText =
                data.ip_address ?? '—';

            document.getElementById('modalContext').innerText =
                data.direction_context ?? '';

            document.getElementById('modalTimestamp').innerText =
                data.timestamp ?? '';

            const changesTable = document.getElementById('modalChangesTable');

            changesTable.innerHTML = '';

            if (data.changes && data.changes.length > 0) {
                data.changes.forEach(change => {
                    const row = document.createElement('tr');

                    row.className =
                        'hover:bg-gray-50 dark:hover:bg-[#161B26]/30';

                    const fieldCell = document.createElement('td');
                    fieldCell.className =
                        'px-4 py-3 font-medium text-gray-900 dark:text-white';
                    fieldCell.textContent = change.field ?? '';

                    const oldCell = document.createElement('td');
                    oldCell.className =
                        'px-4 py-3 text-rose-500 dark:text-rose-400 line-through font-mono text-xs break-all';
                    oldCell.textContent = change.old ?? 'None';

                    const newCell = document.createElement('td');
                    newCell.className =
                        'px-4 py-3 text-emerald-600 dark:text-emerald-400 font-semibold font-mono text-xs break-all';
                    newCell.textContent = change.new ?? 'None';

                    row.appendChild(fieldCell);
                    row.appendChild(oldCell);
                    row.appendChild(newCell);

                    changesTable.appendChild(row);
                });
            } else {
                changesTable.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                            No specific field changes recorded for this action.
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error(error);

            document.getElementById('modalChangesTable').innerHTML = `
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-red-400">
                        Failed to load specifications.
                    </td>
                </tr>
            `;

            document.getElementById('modalDescription').innerText =
                'Unable to load audit log specifications.';
        });
    }

    function closeAuditModal() {
        document.getElementById('auditModal').classList.add('hidden');
    }

    // Close modal when clicking outside it.
    document.getElementById('auditModal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeAuditModal();
        }
    });

    // Close modal using Escape key.
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAuditModal();
        }
    });
</script>
@endpush
