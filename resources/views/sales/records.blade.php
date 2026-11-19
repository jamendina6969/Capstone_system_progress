@extends('layouts.default')

@section('title', 'Sales Records')

@section('content')

    @php
        $summarySource = request('source');

        // Determine initial labels for custom dropdowns
        $currentCategoryId = request('category_id');
        $categoryLabel = 'All Categories';
        if ($currentCategoryId) {
            foreach ($categories as $category) {
                if ((string) $currentCategoryId === (string) $category->id) {
                    $categoryLabel = 'All ' . $category->name;
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

        $sourceOptions = [
            '' => 'All Sources',
            'pos' => 'POS',
            'imported' => 'Imported',
        ];
        $currentSource = request('source', '');
        $selectedSourceLabel = $sourceOptions[$currentSource] ?? 'All Sources';
    @endphp

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 lg:gap-5 mb-5 sm:mb-6 lg:mb-8">

        <!-- Today -->
        <a href="{{ route('sales.records', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'source' => $summarySource]) }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-4 sm:p-5 lg:p-6 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">Today's Sales</span>
                <div class="p-3 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-gray-900 dark:text-white tracking-tight mb-2 text-lg sm:text-xl md:text-lg lg:text-xl xl:text-2xl break-all">₱{{ number_format($summary['today'], 2) }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold {{ $summary['today_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    <svg class="w-4 h-4 {{ $summary['today_change'] < 0 ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>{{ $summary['today_change'] >= 0 ? '+' : '' }}{{ $summary['today_change'] }}% <span class="text-gray-400 dark:text-slate-500 font-normal">vs yesterday</span></span>
                </div>
            </div>
        </a>

        <!-- This Week -->
        <a href="{{ route('sales.records', ['date_from' => now()->startOfWeek()->toDateString(), 'date_to' => now()->endOfWeek()->toDateString(), 'source' => $summarySource]) }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-4 sm:p-5 lg:p-6 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">This Week's Sales</span>
                <div class="p-3 rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-gray-900 dark:text-white tracking-tight mb-2 text-lg sm:text-xl md:text-lg lg:text-xl xl:text-2xl break-all">₱{{ number_format($summary['week'], 2) }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold {{ $summary['week_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    <svg class="w-4 h-4 {{ $summary['week_change'] < 0 ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>{{ $summary['week_change'] >= 0 ? '+' : '' }}{{ $summary['week_change'] }}% <span class="text-gray-400 dark:text-slate-500 font-normal">vs last week</span></span>
                </div>
            </div>
        </a>

        <!-- This Month -->
        <a href="{{ route('sales.records', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->endOfMonth()->toDateString(), 'source' => $summarySource]) }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-4 sm:p-5 lg:p-6 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">This Month's Sales</span>
                <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-gray-900 dark:text-white tracking-tight mb-2 text-lg sm:text-xl md:text-lg lg:text-xl xl:text-2xl break-all">₱{{ number_format($summary['month'], 2) }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold {{ $summary['month_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    <svg class="w-4 h-4 {{ $summary['month_change'] < 0 ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>{{ $summary['month_change'] >= 0 ? '+' : '' }}{{ $summary['month_change'] }}% <span class="text-gray-400 dark:text-slate-500 font-normal">vs last month</span></span>
                </div>
            </div>
        </a>

        <!-- This Year -->
        <a href="{{ route('sales.records', ['date_from' => now()->startOfYear()->toDateString(), 'date_to' => now()->endOfYear()->toDateString(), 'source' => $summarySource]) }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-4 sm:p-5 lg:p-6 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">This Year's Sales</span>
                <div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-gray-900 dark:text-white tracking-tight mb-2 text-lg sm:text-xl md:text-lg lg:text-xl xl:text-2xl break-all">₱{{ number_format($summary['year'], 2) }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold {{ $summary['year_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    <svg class="w-4 h-4 {{ $summary['year_change'] < 0 ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>{{ $summary['year_change'] >= 0 ? '+' : '' }}{{ $summary['year_change'] }}% <span class="text-gray-400 dark:text-slate-500 font-normal">vs last year</span></span>
                </div>
            </div>
        </a>

        <!-- Overall -->
        <a href="{{ route('sales.records', ['source' => $summarySource]) }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-4 sm:p-5 lg:p-6 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">Overall Sales</span>
                <div class="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-gray-900 dark:text-white tracking-tight mb-2 text-lg sm:text-xl md:text-lg lg:text-xl xl:text-2xl break-all">₱{{ number_format($summary['overall'], 2) }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400"><span>All-time accumulated revenue</span></div>
            </div>
        </a>
    </div>

    <!-- Main Container -->
    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-180px)] min-h-[550px]">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] gap-4 shrink-0">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Sales Records</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">All recorded sales — from POS and imported data</p>
            </div>

            <div class="flex gap-3 items-center">
                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    @if(\App\Models\TransactionItem::whereNull('main_category_name')->orWhereNull('product_sku')->orWhereNull('item_sku')->exists())
                        <button type="button" onclick="toggleSyncModal(true)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">
                            Sync Old Records
                        </button>
                    @endif

                    <button type="button" onclick="toggleImportSalesModal(true)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer self-start sm:self-auto">
                        Import Sales
                    </button>
                @endif
            </div>
        </div>

        <!-- Filters -->
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50 shrink-0">
            <form id="filterForm" method="GET" class="flex flex-col xl:flex-row xl:items-center gap-4">
                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search ID, product, category..." autocomplete="off"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>

                    <!-- Custom Category Dropdown -->
                    <div class="custom-dropdown relative w-full sm:w-56 shrink-0" id="filter-category-dropdown">
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $categoryLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
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

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 whitespace-nowrap">FROM:</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 whitespace-nowrap">TO:</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <!-- Custom Source Dropdown -->
                        <div class="custom-dropdown relative w-full sm:w-44 shrink-0" id="filter-source-dropdown">
                            <input type="hidden" name="source" value="{{ $currentSource }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400 cursor-pointer text-left">
                                <span class="dropdown-label truncate">{{ $selectedSourceLabel }}</span>
                                <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                @foreach ($sourceOptions as $value => $label)
                                    <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>

                        @if (request()->anyFilled(['date_from', 'date_to', 'source', 'search', 'category_id']))
                            <a href="{{ route('sales.records') }}" class="text-xs font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2.5 rounded-xl border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26] whitespace-nowrap">Clear Filters</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 overflow-x-auto overflow-y-auto custom-scrollbar relative">
            <table class="w-full min-w-[850px] text-sm text-left relative">
                <thead id="salesTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Date</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Transaction ID</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Main Category</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Sub-Category / Brand</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Source</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Total</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Recorded By</th>
                        <th class="px-6 py-4 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('sales.partials.rows', ['records' => $records])
                </tbody>
            </table>

            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more records...
            </div>
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">Unable to load more records. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button></div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($records->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $records->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $records->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $records->total() }}</span>
                    records
                @else
                    No records found
                @endif
            </div>
            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Sales record pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($records->getUrlRange(max(1, $records->currentPage() - 3), min($records->lastPage(), $records->currentPage() + 3)) as $page => $url)
                        @if ($page == $records->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- Import Sales Modal -->
    <div id="importSalesModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-6 sm:p-8 text-left">
            <div class="flex items-center justify-between pb-4 mb-2">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Import Customer Sales Records</h3>
                <button type="button" onclick="toggleImportSalesModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-[#10141D]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
                Upload a CSV file of historical sales to backfill demand data.
                <strong class="text-gray-700 dark:text-slate-200">Note: This is for Revenue (Customer Sales) only. Do not upload Supplier Payments here.</strong>
            </p>
            <form action="{{ route('sales.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="bg-gray-50 dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold text-gray-700 dark:text-slate-300">Required CSV columns:</p>
                        <a href="{{ route('sales.template') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Download Template</a>
                    </div>
                    <ul class="space-y-2 text-xs text-gray-600 dark:text-slate-400">
                        <li>• <code>product_name</code></li>
                        <li>• <code>quantity</code></li>
                        <li>• <code>date</code> — YYYY-MM-DD</li>
                        <li>• <code>purchase_cost</code></li>
                        <li>• <code>selling_price</code></li>
                        <li>• <code>main_category</code> — optional</li>
                        <li>• <code>brand_category</code> — optional</li>
                    </ul>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-2">CSV File</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required class="w-full text-xs text-gray-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-100 dark:file:bg-[#10141D] file:text-gray-700 dark:file:text-slate-200 hover:file:bg-gray-200 dark:hover:file:bg-[#1e2536]">
                </div>
                <div class="border border-gray-200 dark:border-[#1e2536] rounded-2xl p-4 bg-white dark:bg-[#10141D]">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_create_products" value="1" id="modalAutoCreate" onchange="toggleModalCategoryField()" class="mt-0.5 rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                        <div>
                            <span class="text-xs font-semibold text-gray-900 dark:text-white block">Auto-create products not found in the system</span>
                            <span class="text-[11px] text-gray-500 dark:text-slate-400 leading-normal block mt-0.5">Unmatched product names will be created automatically and assigned to the Main Category and Brand provided in the CSV.</span>
                        </div>
                    </label>
                    <div id="modalCategoryField" class="hidden mt-3">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-2">Default Category</label>
                        <select name="default_category_id" class="w-full rounded-xl border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26] text-gray-900 dark:text-slate-100 px-3 py-2 text-sm">
                            <option value="">Uncategorized (default)</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleImportSalesModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sync Old Records Modal -->
    <div id="syncModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-6 sm:p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-950/50 mb-5">
                <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Sync Old Records</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-8">This will scan old transactions and fill in missing categories and SKUs based on current product data. Are you sure you want to proceed?</p>
            <div class="flex items-center justify-center gap-3 w-full pt-4 border-t border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="toggleSyncModal(false)" class="w-full inline-flex justify-center bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                <form action="{{ route('sales.syncRecords') }}" method="POST" class="w-full m-0">
                    @csrf
                    <button type="submit" class="w-full inline-flex justify-center bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Yes, Sync Now</button>
                </form>
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
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // REUSABLE CUSTOM DROPDOWN LOGIC
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
                    if (form && dropdown.id.includes('filter')) {
                        form.submit();
                    }
                });
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

    initCustomDropdowns();

    // =====================================================
    // PAGINATION + INFINITE SCROLL
    // =====================================================
    const tableContainer = document.getElementById('tableScrollContainer');
    const tableBody = document.getElementById('tableBody');
    const tableHead = document.getElementById('salesTableHead');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    let nextPageUrl = @json($records->nextPageUrl());
    let currentPage = {{ $records->currentPage() }};
    let lastPage = {{ $records->lastPage() }};
    let totalRecords = {{ $records->total() }};
    let perPage = {{ $records->perPage() }};
    let initialPage = {{ $records->currentPage() }};
    let initialStart = {{ $records->firstItem() ?? 0 }} || 0;

    let isLoading = false;
    let visualPage = initialPage;

    function getRecordRows() {
        return Array.from(tableBody.querySelectorAll('tr[data-record-row="true"]'));
    }

    function updateResultsCounter() {
        const rowsLoaded = getRecordRows().length;

        if (totalRecords === 0 || rowsLoaded === 0) {
            resultsText.textContent = 'No records found.';
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
            records
        `;
    }

    function getVisiblePage() {
        const rows = getRecordRows();
        if (!rows.length) return initialPage;

        // Bottom-scroll fix: Force the last page indicator if scrolled all the way down
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

    async function loadMoreRecords() {
        if (!nextPageUrl || isLoading) return;

        isLoading = true;
        loadError.classList.add('hidden');
        loadingIndicator.classList.remove('hidden');

        try {
            const fetchUrl = new URL(nextPageUrl, window.location.origin);
            fetchUrl.searchParams.set('t', Date.now());

            const response = await fetch(fetchUrl.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            });

            if (!response.ok) throw new Error('Request failed: ' + response.status);
            const data = await response.json();

            const emptyRow = tableBody.querySelector('tr[data-empty-row="true"]');
            if (emptyRow) emptyRow.remove();

            if (data.html) tableBody.insertAdjacentHTML('beforeend', data.html);

            currentPage = Number(data.current_page || currentPage);
            lastPage = Number(data.last_page || lastPage);
            perPage = Number(data.per_page || perPage);
            totalRecords = Number(data.total_records ?? totalRecords);
            nextPageUrl = data.next_page_url || null;

            updateResultsCounter();
            updatePaginationNumbers(getVisiblePage());
        } catch (error) {
            console.error('Error loading sales records:', error);
            loadError.classList.remove('hidden');
        } finally {
            isLoading = false;
            loadingIndicator.classList.add('hidden');
        }
    }

    tableContainer.addEventListener('scroll', function () {
        updatePaginationNumbers(getVisiblePage());
        const distanceFromBottom = tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight;
        if (distanceFromBottom < 100 && nextPageUrl && !isLoading) loadMoreRecords();
    });

    retryLoadButton.addEventListener('click', function () { loadMoreRecords(); });

    updateResultsCounter();
    updatePaginationNumbers(initialPage);

    // =====================================================
    // LIVE SEARCH
    // =====================================================
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    let debounceTimer;

    if (searchInput && filterForm) {
        const savedCursorPos = sessionStorage.getItem('searchCursorPos');
        if (savedCursorPos !== null) {
            searchInput.focus();
            const position = parseInt(savedCursorPos, 10);
            searchInput.setSelectionRange(position, position);
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

    // =====================================================
    // MODAL LOGIC
    // =====================================================
    window.toggleImportSalesModal = function (show) {
        const modal = document.getElementById('importSalesModal');
        if (show) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    window.toggleSyncModal = function (show) {
        const modal = document.getElementById('syncModal');
        if (show) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    window.toggleModalCategoryField = function () {
        const checkbox = document.getElementById('modalAutoCreate');
        const categoryField = document.getElementById('modalCategoryField');
        if (!checkbox || !categoryField) return;
        categoryField.classList.toggle('hidden', !checkbox.checked);
    };

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            toggleImportSalesModal(false);
            toggleSyncModal(false);
        }
    });

    window.addEventListener('click', function (event) {
        const importModal = document.getElementById('importSalesModal');
        const syncModal = document.getElementById('syncModal');
        if (event.target === importModal) toggleImportSalesModal(false);
        if (event.target === syncModal) toggleSyncModal(false);
    });

});
</script>
@endpush