@extends('layouts.default')

@section('title', 'EOQ Calibration')

@section('content')

    @php
        // Determine initial labels for filter dropdowns
        $currentCategoryId = request('category_id');
        $selectedCategoryLabel = 'All Categories';
        if ($currentCategoryId) {
            foreach ($categories->whereNull('parent_id') as $mainCat) {
                if ((string) $currentCategoryId === (string) $mainCat->id) {
                    $selectedCategoryLabel = $mainCat->name . ' (Main)';
                    break;
                }
                foreach ($categories->where('parent_id', $mainCat->id)->sortBy('name') as $subCat) {
                    if ((string) $currentCategoryId === (string) $subCat->id) {
                        $selectedCategoryLabel = $subCat->name;
                        break 2;
                    }
                }
            }
        }

        $timeframeOptions = [
            'annual' => 'Annual Demand',
            'quarterly' => 'Quarterly Demand',
            'monthly' => 'Monthly Demand',
            'weekly' => 'Weekly Demand',
            'daily' => 'Daily Demand',
        ];
        $selectedTimeframeLabel = $timeframeOptions[$timeframe] ?? 'Annual Demand';
    @endphp

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-140px)] min-h-[500px] mb-4">

        @if(session('success'))
            <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:opacity-75 font-bold cursor-pointer">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 dark:text-red-400 hover:opacity-75 font-bold cursor-pointer">&times;</button>
            </div>
        @endif

        <!-- Header & Unified Toolbar -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
            <div class="flex items-center justify-between mb-5 gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">EOQ Calibration</h1>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Select products and apply ordering/holding cost overrides in batch.</p>
                </div>
                <button type="button" onclick="openEoqSettingsModal()" class="whitespace-nowrap bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Global EOQ Settings &rarr;
                </button>
            </div>

            <div class="flex flex-col xl:flex-row gap-4 items-start xl:items-center w-full">
                
                <!-- LEFT: Filters (GET) -->
                <form id="filterForm" method="GET" class="flex flex-1 flex-col sm:flex-row gap-3 w-full">
                    <!-- Live Search -->
                    <div class="relative flex-1 min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search products..."
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] pl-11 pr-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:border-slate-400 transition-colors">
                    </div>

                    <!-- Category Filter -->
                    <div class="custom-dropdown relative w-full sm:w-56 shrink-0" id="filter-category-dropdown">
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left transition-colors">
                            <span class="dropdown-label truncate">{{ $selectedCategoryLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All Categories</button>
                            @foreach($categories->whereNull('parent_id') as $mainCat)
                                <div class="mt-1">
                                    <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">{{ $mainCat->name }}</div>
                                    <button type="button" data-value="{{ $mainCat->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $mainCat->name }} (Main)</button>
                                    @foreach($categories->where('parent_id', $mainCat->id)->sortBy('name') as $subCat)
                                        <button type="button" data-value="{{ $subCat->id }}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $subCat->name }}</button>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Timeframe Filter -->
                    <div class="custom-dropdown relative w-full sm:w-48 shrink-0" id="filter-timeframe-dropdown">
                        <input type="hidden" name="timeframe" value="{{ $timeframe }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left transition-colors">
                            <span class="dropdown-label truncate">{{ $selectedTimeframeLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            @foreach ($timeframeOptions as $value => $label)
                                <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                </form>

                <!-- RIGHT: Batch Form (POST) -->
                <form id="batchForm" method="POST" action="{{ route('eoq.calibration.apply') }}" class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto shrink-0 xl:border-l-2 xl:border-gray-200 xl:dark:border-[#1e2536] xl:pl-4">
                    @csrf
                    <div class="w-full sm:w-32">
                        <input type="number" step="0.01" min="0" name="ordering_cost" placeholder="Ordering Cost (₱)"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:border-slate-400 transition-colors">
                    </div>
                    <div class="w-full sm:w-44">
                        <input type="number" step="0.01" min="0" name="holding_cost_override" placeholder="Holding Cost (₱/unit)"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:border-slate-400 transition-colors">
                    </div>
                    <button type="button" onclick="checkSelectionAndApply()" class="w-full sm:w-auto whitespace-nowrap bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                        Apply
                    </button>
                    
                    <!-- Selected Count Badge -->
                    <div class="hidden xl:flex items-center justify-center px-2">
                        <span id="selectedCount" class="text-sm font-semibold text-gray-500 dark:text-slate-400 whitespace-nowrap">0 selected</span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full min-w-[960px] text-sm text-left table-fixed">
                <thead id="eoqTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-3 bg-gray-50 dark:bg-[#161B26] w-12">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#10141D] text-blue-600 focus:ring-slate-400 w-4 h-4 cursor-pointer">
                        </th>
                        <th class="px-8 py-3 bg-gray-50 dark:bg-[#161B26] w-64">Product</th>
                        <th class="px-8 py-3 bg-gray-50 dark:bg-[#161B26] w-48">Main Category</th>
                        <th class="px-8 py-3 bg-gray-50 dark:bg-[#161B26] w-48">Brand / Subcategory</th>
                        <th class="px-8 py-3 bg-gray-50 dark:bg-[#161B26] text-gray-900 dark:text-white">{{ ucfirst($timeframe) }} Demand</th>
                        <th class="px-8 py-3 bg-gray-50 dark:bg-[#161B26]">Ordering Cost</th>
                        <th class="px-8 py-3 bg-gray-50 dark:bg-[#161B26]">Holding Cost</th>
                        <th class="px-8 py-3 bg-gray-50 dark:bg-[#161B26] text-right">Target EOQ</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('forecast.partials.eoqTable', ['products' => $products, 'timeframe' => $timeframe])
                </tbody>
            </table>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more products...
            </div>

            <!-- Error Retry -->
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more products. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-3.5 sm:py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($products->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $products->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $products->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $products->total() }}</span>
                    products
                @else
                    No products found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                        @if ($page == $products->currentPage())
                            <span data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- Warning Modal (No Products Selected) -->
    <div id="noSelectionModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-center" onclick="event.stopPropagation()">
            
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 dark:bg-amber-950/50 mb-5">
                <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>

            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Products Selected</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-8">
                Please select at least one product from the list before applying calibration.
            </p>

            <div class="flex items-center justify-center w-full pt-4 border-t border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="closeNoSelectionModal()" class="w-full inline-flex justify-center bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Okay, got it
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal (Apply Calibration) -->
    <div id="applyConfirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-center" onclick="event.stopPropagation()">
            
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-950/50 mb-5">
                <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2-4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Apply Calibration</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-8">
                Are you sure you want to apply these ordering and holding costs to all selected products?
            </p>

            <div class="flex items-center justify-center gap-3 w-full pt-4 border-t border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="closeApplyConfirmModal()" class="w-full inline-flex justify-center bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Cancel
                </button>
                <button type="button" onclick="submitBatchForm()" class="w-full inline-flex justify-center bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Yes, Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Global EOQ Settings Modal -->
    <div id="eoqSettingsModal" onclick="closeEoqSettingsModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div onclick="event.stopPropagation()" class="relative w-full max-w-xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left">
            
            <div class="flex items-center justify-between pb-6 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">EOQ Settings</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Default values used when a product hasn't been individually calibrated.</p>
                </div>
                <button type="button" onclick="closeEoqSettingsModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors cursor-pointer text-lg font-bold">
                    &times;
                </button>
            </div>

            <form method="POST" action="{{ route('eoq.settings.update') }}" class="space-y-6 text-sm">
                @csrf
                @if ($errors->any())
                    <div class="p-3.5 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="space-y-4">
                    <h4 class="font-bold text-gray-900 dark:text-white text-xs uppercase tracking-wider">Ordering Cost</h4>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Flat Default (₱ per order)</label>
                        <input type="number" step="0.01" name="default_ordering_cost" placeholder="Leave blank to use percentage instead" value="{{ $settings->default_ordering_cost ?? '' }}" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Fallback: % of Purchase Cost</label>
                        <input type="number" step="0.01" name="ordering_cost_percentage" value="{{ $settings->ordering_cost_percentage ?? 10 }}" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>
                <div class="space-y-4 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <h4 class="font-bold text-gray-900 dark:text-white text-xs uppercase tracking-wider">Holding Cost</h4>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">% of Purchase Cost / Year</label>
                        <input type="number" step="0.01" name="holding_cost_percentage" value="{{ $settings->holding_cost_percentage ?? 20 }}" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">₱ per Cubic Meter / Year</label>
                        <input type="number" step="0.01" name="holding_cost_per_cbm" value="{{ $settings->holding_cost_per_cbm ?? 0 }}" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="closeEoqSettingsModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Save Settings</button>
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
</style>
@endpush

@push('scripts')
<script>
    // =====================================================
    // CUSTOM DROPDOWNS & FILTERING
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
                    
                    // Submit the filter form immediately when an option is selected
                    if (dropdown.closest('form')?.id === 'filterForm') {
                        dropdown.closest('form').submit();
                    }
                });
            });
            document.addEventListener('click', e => { if (!dropdown.contains(e.target)) closeMenu(); });
            dropdown.dataset.initialized = 'true';
        });
    }
    document.addEventListener('DOMContentLoaded', initCustomDropdowns);

    // =====================================================
    // INFINITE SCROLL SETUP
    // =====================================================
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('tableScrollContainer');
        const tableBody = document.getElementById('tableBody');
        const tableHead = document.getElementById('eoqTableHead');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const loadError = document.getElementById('loadError');
        const retryLoadButton = document.getElementById('retryLoadButton');
        const paginationContainer = document.getElementById('paginationNumbersContainer');
        const resultsText = document.getElementById('resultsText');

        let nextPageUrl = @json($products->nextPageUrl());
        let currentPage = {{ $products->currentPage() }};
        let lastPage = {{ $products->lastPage() }};
        let totalRecords = {{ $products->total() }};
        let perPage = {{ $products->perPage() }};
        let initialPage = {{ $products->currentPage() }};
        let initialStart = {{ $products->firstItem() ?? 0 }} || 0;

        let isLoading = false;
        let visualPage = initialPage;

        // Fix: checkboxes need to be re-bound after appending new rows
        function bindCheckboxes() {
            const selectAll = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const selectedCount = document.getElementById('selectedCount');

            if (selectAll) {
                const newSelectAll = selectAll.cloneNode(true);
                selectAll.parentNode.replaceChild(newSelectAll, selectAll);
                newSelectAll.addEventListener('change', function () {
                    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = newSelectAll.checked);
                    updateCount();
                });
            }

            rowCheckboxes.forEach(cb => {
                const newCb = cb.cloneNode(true);
                cb.parentNode.replaceChild(newCb, cb);
                newCb.addEventListener('change', updateCount);
            });

            function updateCount() {
                const checked = document.querySelectorAll('.row-checkbox:checked').length;
                selectedCount.textContent = checked + ' selected';
            }
        }

        function updateResultsCounter() {
            const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
            if (totalRecords === 0 || rowsLoaded === 0) {
                resultsText.textContent = 'No products found.';
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
                products
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

                bindCheckboxes();
                updateResultsCounter();
                updatePaginationNumbers(getVisiblePage());

            } catch (error) {
                console.error('Error loading products:', error);
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

        // Initial bind and counters
        bindCheckboxes();
        updateResultsCounter();
        updatePaginationNumbers(initialPage);

        // Live search
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        let debounceTimer;
        if (searchInput && filterForm) {
            const savedCursorPos = sessionStorage.getItem('eoqSearchCursorPos');
            if (savedCursorPos !== null) {
                searchInput.focus();
                searchInput.setSelectionRange(parseInt(savedCursorPos), parseInt(savedCursorPos));
                sessionStorage.removeItem('eoqSearchCursorPos');
            } else if (searchInput.value.length > 0) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }

            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                sessionStorage.setItem('eoqSearchCursorPos', searchInput.selectionStart);
                debounceTimer = setTimeout(() => filterForm.submit(), 400);
            });
        }
    });

    // =====================================================
    // MODAL HELPERS
    // =====================================================
    function checkSelectionAndApply() {
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        if (checked === 0) {
            document.getElementById('noSelectionModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            document.getElementById('applyConfirmModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeNoSelectionModal() {
        document.getElementById('noSelectionModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function closeApplyConfirmModal() {
        document.getElementById('applyConfirmModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function submitBatchForm() {
        const form = document.getElementById('batchForm');
        
        // Remove any previously appended dynamic product inputs
        form.querySelectorAll('input.dynamic-product-id').forEach(el => el.remove());

        // Gather all checked checkboxes (including those loaded via infinite scroll)
        document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_ids[]';
            input.value = cb.value;
            input.className = 'dynamic-product-id';
            form.appendChild(input);
        });

        form.submit();
    }

    function openEoqSettingsModal() {
        document.getElementById('eoqSettingsModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeEoqSettingsModal() {
        document.getElementById('eoqSettingsModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('eoqSettingsModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeEoqSettingsModal();
            }
        }
    });

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openEoqSettingsModal();
        });
    @endif
</script>
@endpush