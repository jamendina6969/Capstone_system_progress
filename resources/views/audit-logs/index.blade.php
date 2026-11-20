@extends('layouts.default')

@section('title', 'Audit Logs')

@section('content')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Logs</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                Track and audit all critical system operations, security events, and user activities.
            </p>
        </div>
        <a href="{{ route('audit-logs.export', request()->query()) }}"
           class="inline-flex items-center gap-2 self-start sm:self-auto bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] text-xs font-semibold px-4 py-2.5 rounded-full transition-all shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </div>

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden mb-8">

        <!-- Filters -->
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
            <form id="filterForm" method="GET" action="{{ route('audit-logs.index') }}"
                class="flex flex-col gap-3">

                <div class="flex flex-col xl:flex-row xl:items-center gap-3">
                    <div class="relative flex-1 min-w-[220px]">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>

                        <input type="text" id="searchInput" name="search"
                            value="{{ request('search') }}"
                            placeholder="Search user, action, target, or IP..."
                            autocomplete="off"
                            class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <select name="module" onchange="this.form.submit()"
                            class="rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3 py-2 text-xs sm:text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                            <option value="">All Modules</option>
                            <option value="security" {{ request('module') === 'security' ? 'selected' : '' }}>Security &amp; Auth</option>
                            <option value="users" {{ request('module') === 'users' ? 'selected' : '' }}>User Management</option>
                            <option value="catalog" {{ request('module') === 'catalog' ? 'selected' : '' }}>Catalog &amp; Products</option>
                            <option value="inventory" {{ request('module') === 'inventory' ? 'selected' : '' }}>Inventory &amp; Forecasting</option>
                            <option value="pos" {{ request('module') === 'pos' ? 'selected' : '' }}>POS &amp; Customer Returns</option>
                            <option value="procurement" {{ request('module') === 'procurement' ? 'selected' : '' }}>Suppliers &amp; POs</option>
                            <option value="system" {{ request('module') === 'system' ? 'selected' : '' }}>System &amp; Backups</option>
                        </select>

                        <select name="role" onchange="this.form.submit()"
                            class="rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3 py-2 text-xs sm:text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="warehouse_staff" {{ request('role') === 'warehouse_staff' ? 'selected' : '' }}>Warehouse</option>
                            <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Cashier</option>
                        </select>

                        <select name="event" id="eventFilter" onchange="this.form.submit()"
                            class="rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3 py-2 text-xs sm:text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                            <option value="">All Actions</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                    {{ \App\Http\Controllers\AuditLogController::formatEventLabel($event) }}
                                </option>
                            @endforeach
                        </select>

                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" title="From date"
                            class="rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3 py-2 text-xs text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">

                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" title="To date"
                            class="rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3 py-2 text-xs text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">

                        @if(request()->anyFilled(['search', 'event', 'role', 'module', 'date_from', 'date_to']))
                            <a href="{{ route('audit-logs.index') }}"
                                class="text-xs font-semibold text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-3.5 py-2 rounded-xl transition-colors whitespace-nowrap border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26]">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Scrollable Table (FIXED HEIGHT & FORCED SCROLLBAR) -->
        <div id="tableScrollContainer"
            class="overflow-x-auto custom-scrollbar relative"
            style="height: 600px; max-height: 600px; overflow-y: scroll;">

            <table class="w-full min-w-[920px] text-sm text-left relative">
                <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">User &amp; Role</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Action</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Target &amp; Summary</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">IP &amp; Device</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Timestamp</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Actions</th>
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
        <div class="px-4 sm:px-6 lg:px-8 py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">

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
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-xs hidden p-4"
        onclick="if(event.target===this) closeAuditModal()">

        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]"
            onclick="event.stopPropagation()">

            <!-- Modal Header -->
            <div class="flex justify-between items-center px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] shrink-0">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Audit Log Specifications
                        </h3>
                        <p id="modalDescription" class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            Detailed breakdown of user action and data modifications.
                        </p>
                    </div>
                </div>

                <button type="button" onclick="closeAuditModal()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-8 py-6 space-y-6 overflow-y-auto custom-scrollbar text-sm flex-1">

                <!-- Meta Information Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- User & Action -->
                    <div class="bg-gray-50 dark:bg-[#161B26] p-4 rounded-2xl border-2 border-gray-200 dark:border-[#1e2536] flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 block mb-1">User</span>
                            <span id="modalUser" class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">-</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 block mb-1">Action</span>
                            <div id="modalEventBadge">
                                <span id="modalEvent" class="font-semibold text-gray-900 dark:text-white uppercase">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Target Model & IP -->
                    <div class="bg-gray-50 dark:bg-[#161B26] p-4 rounded-2xl border-2 border-gray-200 dark:border-[#1e2536] flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 block mb-1">Target Model</span>
                            <span id="modalTarget" class="text-sm font-semibold text-gray-900 dark:text-white">-</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 block mb-1">IP Address</span>
                            <span id="modalIp" class="text-xs font-mono font-semibold text-gray-700 dark:text-slate-300 bg-gray-200/70 dark:bg-[#10141D] px-2.5 py-1 rounded-lg border border-gray-300 dark:border-[#1e2536]">-</span>
                        </div>
                    </div>

                    <!-- Origin / Context -->
                    <div class="bg-gray-50 dark:bg-[#161B26] p-4 rounded-2xl border-2 border-gray-200 dark:border-[#1e2536]">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 block mb-1">Origin / Context</span>
                        <span id="modalContext" class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-1.5">-</span>
                    </div>

                    <!-- Timestamp -->
                    <div class="bg-gray-50 dark:bg-[#161B26] p-4 rounded-2xl border-2 border-gray-200 dark:border-[#1e2536]">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 block mb-1">Timestamp</span>
                        <span id="modalTimestamp" class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-1.5">-</span>
                    </div>
                </div>

                <!-- Activity Details Table -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-gray-600 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Activity Details & Modifications
                        </h4>
                    </div>

                    <div class="border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl overflow-hidden shadow-xs">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 border-b-2 border-gray-200 dark:border-[#1e2536] uppercase text-[10px] tracking-wider font-bold">
                                <tr>
                                    <th class="px-5 py-3.5 w-1/3 whitespace-nowrap">Field / Property</th>
                                    <th class="px-5 py-3.5 w-1/3 whitespace-nowrap">Previous Value</th>
                                    <th class="px-5 py-3.5 w-1/3 whitespace-nowrap">New Value</th>
                                </tr>
                            </thead>

                            <tbody id="modalChangesTable"
                                class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-gray-400 dark:text-slate-500 font-medium">
                                        Loading details...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with Official Rounded-Full Button -->
            <div class="px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex justify-end shrink-0">
                <button type="button" onclick="closeAuditModal()"
                    class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    /* Force the scrollbar to be visible and styled */
    .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .dark .custom-scrollbar {
        scrollbar-color: #334155 transparent;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
        border: 2px solid transparent;
        background-clip: padding-box;
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
    document.addEventListener('DOMContentLoaded', function () {
        // =====================================================
        // AUDIT LOG PAGINATION AND INFINITE SCROLL
        // =====================================================

        const tableContainer = document.getElementById('tableScrollContainer');
        const tableBody = document.getElementById('tableBody');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const paginationContainer = document.getElementById('paginationNumbersContainer');

        if (!tableContainer || !tableBody) return;

        let nextPageUrl = @json($logs->nextPageUrl());
        let currentPage = {{ $logs->currentPage() }};
        let lastPage = {{ $logs->lastPage() }};
        let totalRecords = {{ $logs->total() }};
        let perPage = {{ $logs->perPage() }};
        let initialPage = {{ $logs->currentPage() }};
        let isLoading = false;
        let visualPage = initialPage;

        function getVisiblePage() {
            const rows = Array.from(tableBody.querySelectorAll('tr[data-record-row="true"]'));
            if (!rows.length) return initialPage;

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
            const firstRecord = rowsLoaded > 0 ? ((initialPage - 1) * perPage) + 1 : 0;
            const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
            const resultsText = document.getElementById('resultsText');

            if (totalRecords === 0 || rowsLoaded === 0) {
                resultsText.textContent = 'No records found';
                return;
            }

            resultsText.innerHTML = `
                Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span>
                to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span>
                of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> records
            `;
        }

        async function loadMoreLogs() {
            if (!nextPageUrl || isLoading) return;

            isLoading = true;
            loadingIndicator.classList.remove('hidden');

            try {
                const response = await fetch(nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Failed to load audit logs.');

                const data = await response.json();

                if (data.html) tableBody.insertAdjacentHTML('beforeend', data.html);

                nextPageUrl = data.next_page_url;
                currentPage = data.current_page;
                lastPage = data.last_page;
                totalRecords = data.total_records;
                perPage = data.per_page;

                updateResultsCounter();
                updatePaginationNumbers(getVisiblePage());

            } catch (error) {
                console.error('Error loading audit logs:', error);
            } finally {
                isLoading = false;
                loadingIndicator.classList.add('hidden');
            }
        }

        // SCROLL LOGIC
        function handleScroll() {
            updatePaginationNumbers(getVisiblePage());

            // Calculate distance from bottom of the table container
            const distanceFromBottom = tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight;

            if (distanceFromBottom < 100) {
                loadMoreLogs();
            }
        }

        tableContainer.addEventListener('scroll', handleScroll);

        updatePaginationNumbers(getVisiblePage());
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
                debounceTimer = setTimeout(function () {
                    filterForm.submit();
                }, 300);
            });
        }
    });

    // =====================================================
    // AUDIT LOG DETAIL MODAL
    // =====================================================
    function openAuditModal(id) {
        const modal = document.getElementById('auditModal');
        modal.classList.remove('hidden');
        document.getElementById('modalDescription').innerText = 'Fetching specifications...';
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
            if (!response.ok) throw new Error('Unable to fetch audit log details.');
            return response.json();
        })
        .then(res => {
            if (!res.success) throw new Error('Audit log details were not returned.');
            const data = res.data;

            document.getElementById('modalDescription').innerText = data.description ?? '';
            document.getElementById('modalUser').innerText = (data.user ?? 'System / Guest') + (data.role ? ` (${data.role})` : '');

            const escapeVal = (s) => {
                const d = document.createElement('div');
                d.textContent = s == null ? '—' : String(s);
                return d.innerHTML;
            };

            const badgeCls = data.event_badge_class || 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-400 dark:border-purple-800/60';
            document.getElementById('modalEventBadge').innerHTML = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase border ${badgeCls}">${escapeVal(data.event || '')}</span>`;

            document.getElementById('modalTarget').innerText = data.target_model ?? '—';
            document.getElementById('modalIp').innerText = data.ip_address ?? '—';
            document.getElementById('modalContext').innerText = (data.direction_context ?? '—') + (data.device ? ` · ${data.device}` : '');
            document.getElementById('modalTimestamp').innerText = data.timestamp ?? '—';

            const changesTable = document.getElementById('modalChangesTable');
            changesTable.innerHTML = '';

            if (data.changes && data.changes.length > 0) {
                data.changes.forEach(change => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors';

                    const fieldCell = document.createElement('td');
                    fieldCell.className = 'px-5 py-3.5 font-bold text-gray-900 dark:text-white align-middle';
                    fieldCell.textContent = change.field ?? '';

                    const oldCell = document.createElement('td');
                    oldCell.className = 'px-5 py-3.5 align-middle';
                    const oldRaw = change.old ?? '—';
                    oldCell.innerHTML = `<span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-mono ${oldRaw === '—' || oldRaw === 'None' ? 'text-gray-400 dark:text-slate-500 bg-gray-100 dark:bg-[#10141D]' : 'bg-rose-50 text-rose-700 border border-rose-200 line-through dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900/60'} break-all">${escapeVal(oldRaw)}</span>`;

                    const newCell = document.createElement('td');
                    newCell.className = 'px-5 py-3.5 align-middle';
                    const newRaw = change.new ?? '—';
                    newCell.innerHTML = `<span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-mono font-semibold ${newRaw === '—' || newRaw === 'None' ? 'text-gray-400 dark:text-slate-500 bg-gray-100 dark:bg-[#10141D]' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/60'} break-all">${escapeVal(newRaw)}</span>`;

                    row.appendChild(fieldCell);
                    row.appendChild(oldCell);
                    row.appendChild(newCell);
                    changesTable.appendChild(row);
                });
            } else {
                changesTable.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-5 py-8 text-center text-gray-400 dark:text-slate-500 font-medium">
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
            document.getElementById('modalDescription').innerText = 'Unable to load audit log specifications.';
        });
    }

    function closeAuditModal() {
        document.getElementById('auditModal').classList.add('hidden');
    }

    document.getElementById('auditModal').addEventListener('click', function (event) {
        if (event.target === this) closeAuditModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeAuditModal();
    });
</script>
@endpush