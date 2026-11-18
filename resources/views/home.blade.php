@extends('layouts.default')

@section('title', 'Dashboard')

@section('content')

    <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-900 dark:text-white">Welcome, {{ auth()->user()->firstname }}</h1>

    {{-- Top Summary Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5 lg:gap-6 mb-6 sm:mb-8">
        <!-- Today's Sales Card -->
        <a href="{{ route('sales.records', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-5 sm:p-6 lg:p-8 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">Today's Sales</span>
                <div class="p-2.5 sm:p-3 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-gray-900 dark:text-white tracking-tight mb-2 text-2xl sm:text-3xl lg:text-[2rem] break-words">₱{{ number_format($todaySales, 2) }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400">
                    <span>Daily revenue total</span>
                </div>
            </div>
        </a>

        <!-- Overall Sales Card -->
        <a href="{{ route('sales.records') }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-5 sm:p-6 lg:p-8 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">Overall Sales</span>
                <div class="p-2.5 sm:p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-gray-900 dark:text-white tracking-tight mb-2 text-2xl sm:text-3xl lg:text-[2rem] break-words">₱{{ number_format($overallSales, 2) }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                    <span>All-time accumulated revenue</span>
                </div>
            </div>
        </a>

        <!-- POS Orders Card -->
        <a href="{{ route('transaction.history', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-5 sm:p-6 lg:p-8 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">POS Orders</span>
                <div class="p-2.5 sm:p-3 rounded-2xl bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-gray-900 dark:text-white tracking-tight mb-2 text-2xl sm:text-3xl lg:text-[2rem] break-words">{{ $todayTransactions }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-violet-600 dark:text-violet-400">
                    <span>Completed today</span>
                </div>
            </div>
        </a>

        <!-- Total Products Card -->
        <a href="{{ route('admin.products.index') }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-5 sm:p-6 lg:p-8 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">Products</span>
                <div class="p-2.5 sm:p-3 rounded-2xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-gray-900 dark:text-white tracking-tight mb-2 text-2xl sm:text-3xl lg:text-[2rem] break-words">{{ $totalProducts }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-sky-600 dark:text-sky-400">
                    <span>Active catalog items</span>
                </div>
            </div>
        </a>

        <!-- Restock Needed Card -->
        <a href="{{ route('inventory.index', ['status' => 'restock']) }}"
           class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-5 sm:p-6 lg:p-8 shadow-sm hover:border-slate-400 dark:hover:border-slate-500 transition-all cursor-pointer group flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors">Restock Needed</span>
                <div class="p-2.5 sm:p-3 rounded-2xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-rose-400">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div>
                <p class="font-black text-red-600 dark:text-rose-400 tracking-tight mb-2 text-2xl sm:text-3xl lg:text-[2rem] break-words">{{ $restockNeededCount }}</p>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-rose-400">
                    <span>Items require attention</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Demand Forecast Line Chart Component --}}
    @if (in_array(auth()->user()->role, ['admin', 'manager']))
        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8">
            <form method="GET" action="{{ route('home') }}" id="forecastFilterForm">
                {{-- Preserve Fast/Slow parameters --}}
                @if(request('product_category'))
                    <input type="hidden" name="product_category" value="{{ request('product_category') }}">
                @endif
                @if(request('fast_slow_range'))
                    <input type="hidden" name="fast_slow_range" value="{{ request('fast_slow_range') }}">
                @endif
                @if(request('fast_slow_date_from'))
                    <input type="hidden" name="fast_slow_date_from" value="{{ request('fast_slow_date_from') }}">
                @endif
                @if(request('fast_slow_date_to'))
                    <input type="hidden" name="fast_slow_date_to" value="{{ request('fast_slow_date_to') }}">
                @endif

                <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1">Sales Predictions</h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">
                            Showing <strong class="text-gray-700 dark:text-slate-200">{{ $forecastProductName }}</strong>
                            @if($forecastCategoryName)
                                &middot; <span class="text-gray-600 dark:text-slate-300">{{ $forecastCategoryName }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                        <div class="flex items-center gap-2 flex-wrap w-full xl:w-auto">
                            
                            {{-- Category Dropdown (Custom UI) --}}
                            <div class="custom-dropdown relative w-full sm:w-56 shrink-0" id="filter-category-dropdown">
                                <input type="hidden" name="forecast_category" id="forecastCategoryInput" value="{{ request('forecast_category') }}">
                                <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-2xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-slate-300 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                    <span class="dropdown-label truncate">
                                        @if($selectedForecastCategory)
                                            @php
                                                $selectedCatName = $allCategories->firstWhere('id', $selectedForecastCategory)?->name ?? 'All Categories';
                                            @endphp
                                            {{ $selectedCatName }}
                                        @else
                                            All Categories
                                        @endif
                                    </span>
                                    <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                    <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All Categories</button>
                                    @foreach($mainCategories as $mainCat)
                                        <div class="mt-1">
                                            <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">{{ strtoupper($mainCat->name) }}</div>
                                            <button type="button" data-value="{{ $mainCat->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $mainCat->name }} (Main)</button>
                                            @foreach($allCategories->where('parent_id', $mainCat->id) as $subCat)
                                                <button type="button" data-value="{{ $subCat->id }}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $subCat->name }}</button>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Product Dropdown (Custom UI) --}}
                            <div class="custom-dropdown relative w-full sm:w-64 shrink-0" id="filter-product-dropdown">
                                <input type="hidden" name="forecast_product" id="forecastProductInput" value="{{ request('forecast_product') }}">
                                <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-2xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-slate-300 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                    <span class="dropdown-label truncate">
                                        @if($selectedForecastProduct)
                                            {{ $forecastProducts->firstWhere('id', $selectedForecastProduct)?->product_name ?? 'All Products (Combined)' }}
                                        @else
                                            All Products (Combined)
                                        @endif
                                    </span>
                                    <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                    <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All Products (Combined)</button>
                                    @foreach($forecastProducts as $prod)
                                        <button type="button" data-value="{{ $prod->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                            {{ $prod->product_name }}{{ $prod->product_sku ? ' (' . $prod->product_sku . ')' : '' }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Range Dropdown (Custom UI) --}}
                            <div class="custom-dropdown relative w-full sm:w-40 shrink-0" id="filter-range-dropdown">
                                <input type="hidden" name="forecast_range" id="forecastRangeInput" value="{{ request('forecast_range', '3months') }}">
                                <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-2xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-slate-300 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                    <span class="dropdown-label truncate">
                                        @php
                                            $rangeLabels = [
                                                '7days' => 'Next 7 Days',
                                                '14days' => 'Next 14 Days',
                                                '1month' => 'Next 1 Month',
                                                '3months' => 'Next 3 Months',
                                                '6months' => 'Next 6 Months',
                                                '1yr' => 'Next 1 Year',
                                                'custom' => 'Custom Range'
                                            ];
                                        @endphp
                                        {{ $rangeLabels[request('forecast_range', '3months')] ?? 'Next 3 Months' }}
                                    </span>
                                    <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                    <button type="button" data-value="7days" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Next 7 Days</button>
                                    <button type="button" data-value="14days" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Next 14 Days</button>
                                    <button type="button" data-value="1month" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Next 1 Month</button>
                                    <button type="button" data-value="3months" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Next 3 Months</button>
                                    <button type="button" data-value="6months" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Next 6 Months</button>
                                    <button type="button" data-value="1yr" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Next 1 Year</button>
                                    <button type="button" data-value="custom" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Custom Range</button>
                                </div>
                            </div>

                            {{-- Custom Date Inputs (Hidden unless Custom is selected) --}}
                            <div id="forecastCustomDateContainer" class="{{ $forecastRange == 'custom' ? 'flex' : 'hidden' }} items-center gap-2 flex-wrap w-full sm:w-auto">
                                <div class="flex items-center gap-1.5 bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2 rounded-2xl flex-1 sm:flex-initial">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">FROM:</span>
                                    <input type="date" name="forecast_date_from" value="{{ $forecastDateFrom ?? '' }}" class="bg-transparent text-gray-700 dark:text-slate-300 text-xs font-semibold focus:outline-none cursor-pointer w-full min-w-0">
                                </div>
                                <div class="flex items-center gap-1.5 bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2 rounded-2xl flex-1 sm:flex-initial">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">TO:</span>
                                    <input type="date" name="forecast_date_to" value="{{ $forecastDateTo ?? '' }}" class="bg-transparent text-gray-700 dark:text-slate-300 text-xs font-semibold focus:outline-none cursor-pointer w-full min-w-0">
                                </div>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-2xl transition-colors shadow-xs w-full sm:w-auto">Apply</button>
                            </div>

                            <div class="h-5 w-px bg-gray-200 dark:bg-[#1e2536] hidden sm:block"></div>

                            <a href="{{ route('forecast.settings') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2.5 rounded-2xl transition-all duration-300 ease-in-out shadow-xs w-full sm:w-auto text-center">
                                Settings
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Friendly Legend / Info Bar --}}
                <div class="flex flex-wrap items-center gap-x-4 sm:gap-x-6 gap-y-2 text-xs text-gray-600 dark:text-slate-400 bg-gray-50 dark:bg-[#161B26] px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border border-gray-200 dark:border-[#1e2536] mb-4 sm:mb-6">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-1 bg-blue-600 rounded-full"></span>
                        <span><strong>Past Sales:</strong> What you actually sold.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-1 bg-[#8b5cf6] rounded-full" style="border-top: 1px dashed #8b5cf6;"></span>
                        <span><strong>Prediction:</strong> What we expect you to sell.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-3 bg-purple-100 dark:bg-purple-900/40 rounded-sm"></span>
                        <span><strong>Shaded Area:</strong> The expected range (best & worst case).</span>
                    </div>
                </div>
            </form>

            <div class="relative w-full h-[280px] sm:h-[360px] lg:h-[420px]">
                <canvas id="demandForecastChart"></canvas>
            </div>
            
            {{-- Friendly Footer Tip --}}
            <div class="mt-4 flex items-start gap-2 text-xs text-gray-500 dark:text-slate-400">
                <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p><strong>Why does the prediction line drop after a spike?</strong> The system calculates an average based on your recent history. A single large sale is spread out over time, so the system expects your normal baseline to continue.</p>
            </div>
        </div>
    @endif

    {{-- Fast and Slow Moving Products Widget --}}
    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8">
        <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 mb-4 sm:mb-6">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Fast and Slow Moving Products
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400">Fast items sorted by sales velocity • Slow items sorted by stock quantity</p>
            </div>
            
            <form method="GET" action="{{ route('home') }}" id="fastSlowFilterForm" class="flex flex-wrap items-end gap-3 w-full xl:w-auto">
                {{-- Preserve Forecast parameters --}}
                @if(request('forecast_category'))
                    <input type="hidden" name="forecast_category" value="{{ request('forecast_category') }}">
                @endif
                @if(request('forecast_product'))
                    <input type="hidden" name="forecast_product" value="{{ request('forecast_product') }}">
                @endif
                @if(request('forecast_range'))
                    <input type="hidden" name="forecast_range" value="{{ request('forecast_range') }}">
                @endif
                @if(request('forecast_date_from'))
                    <input type="hidden" name="forecast_date_from" value="{{ request('forecast_date_from') }}">
                @endif
                @if(request('forecast_date_to'))
                    <input type="hidden" name="forecast_date_to" value="{{ request('forecast_date_to') }}">
                @endif

                {{-- Grouped Main & Sub Category Dropdown (Custom UI) --}}
                <div class="flex flex-col w-full sm:w-auto">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Category (Optional)</span>
                    <div class="custom-dropdown relative w-full sm:w-56 shrink-0" id="filter-fs-category-dropdown">
                        <input type="hidden" name="product_category" id="fsCategoryInput" value="{{ request('product_category') }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-2xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-slate-300 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">
                                @if($selectedCategory)
                                    {{ $allCategories->firstWhere('id', $selectedCategory)?->name ?? 'Uncategorized (default)' }}
                                @else
                                    Uncategorized (default)
                                @endif
                            </span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Uncategorized (default)</button>
                            @foreach($mainCategories as $mainCat)
                                <div class="mt-1">
                                    <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">{{ strtoupper($mainCat->name) }}</div>
                                    <button type="button" data-value="{{ $mainCat->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $mainCat->name }} (Main)</button>
                                    @foreach($allCategories->where('parent_id', $mainCat->id) as $subCat)
                                        <button type="button" data-value="{{ $subCat->id }}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $subCat->name }}</button>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Date Range Select & Custom Date Pickers (Custom UI) --}}
                <div class="flex flex-col w-full sm:w-auto">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Date Range</span>
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="custom-dropdown relative w-full sm:w-40 shrink-0" id="filter-fs-range-dropdown">
                            <input type="hidden" name="fast_slow_range" id="fsRangeInput" value="{{ request('fast_slow_range', '3months') }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-2xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-slate-300 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                <span class="dropdown-label truncate">
                                    @php
                                        $fsRangeLabels = [
                                            '7days' => '7 Days',
                                            '14days' => '14 Days',
                                            '1month' => '1 Month',
                                            '3months' => '3 Months',
                                            '6months' => '6 Months',
                                            '1yr' => '1 Year',
                                            'custom' => 'Custom Range'
                                        ];
                                    @endphp
                                    {{ $fsRangeLabels[request('fast_slow_range', '3months')] ?? '3 Months' }}
                                </span>
                                <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                <button type="button" data-value="7days" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">7 Days</button>
                                <button type="button" data-value="14days" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">14 Days</button>
                                <button type="button" data-value="1month" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">1 Month</button>
                                <button type="button" data-value="3months" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">3 Months</button>
                                <button type="button" data-value="6months" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">6 Months</button>
                                <button type="button" data-value="1yr" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">1 Year</button>
                                <button type="button" data-value="custom" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Custom Range</button>
                            </div>
                        </div>

                        <div id="fastSlowCustomDateContainer" class="{{ (isset($fastSlowRange) && $fastSlowRange == 'custom') ? 'flex' : 'hidden' }} items-center gap-2 flex-wrap w-full sm:w-auto">
                            <div class="flex items-center gap-1.5 bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2 rounded-2xl flex-1 sm:flex-initial">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">FROM:</span>
                                <input type="date" name="fast_slow_date_from" value="{{ $fastSlowDateFrom ?? '' }}" class="bg-transparent text-gray-700 dark:text-slate-300 text-xs font-semibold focus:outline-none cursor-pointer w-full min-w-0">
                            </div>
                            <div class="flex items-center gap-1.5 bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2 rounded-2xl flex-1 sm:flex-initial">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">TO:</span>
                                <input type="date" name="fast_slow_date_to" value="{{ $fastSlowDateTo ?? '' }}" class="bg-transparent text-gray-700 dark:text-slate-300 text-xs font-semibold focus:outline-none cursor-pointer w-full min-w-0">
                            </div>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-2xl transition-colors shadow-xs w-full sm:w-auto">Apply</button>
                        </div>
                    </div>
                </div>

                <div class="h-8 w-px bg-gray-200 dark:bg-[#1e2536] hidden xl:block self-end mb-1"></div>

                {{-- Toggle View Buttons --}}
                <div class="inline-flex rounded-2xl border-2 border-gray-200 dark:border-[#1e2536] p-1 bg-gray-50 dark:bg-[#161B26] gap-1 self-end mb-1 w-full sm:w-auto justify-center sm:justify-start">
                    <button type="button" onclick="toggleProductView('both')" id="btn-both" class="flex-1 sm:flex-initial px-2 sm:px-3 py-1.5 text-xs font-semibold rounded-xl transition-colors bg-white dark:bg-[#10141D] text-blue-600 dark:text-blue-400 shadow-xs">Show Both</button>
                    <button type="button" onclick="toggleProductView('fast')" id="btn-fast" class="flex-1 sm:flex-initial px-2 sm:px-3 py-1.5 text-xs font-semibold rounded-xl transition-colors text-emerald-600 dark:text-emerald-400 hover:text-gray-900 dark:hover:text-white">🔥 Fast</button>
                    <button type="button" onclick="toggleProductView('slow')" id="btn-slow" class="flex-1 sm:flex-initial px-2 sm:px-3 py-1.5 text-xs font-semibold rounded-xl transition-colors text-amber-600 dark:text-amber-400 hover:text-gray-900 dark:hover:text-white">📦 Slow</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            {{-- Fast Moving Panel --}}
            <div id="fastMovingCol" class="bg-gray-50 dark:bg-[#161B26]/50 border border-gray-200 dark:border-[#1e2536] rounded-2xl p-4 sm:p-5 lg:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <span class="inline-flex items-center text-xs font-bold px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 uppercase tracking-wider border border-emerald-200 dark:border-emerald-900">
                            🔥 Fast Moving Products
                        </span>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 rounded-lg">High Sales</span>
                    </div>

                    <div class="space-y-3 sm:space-y-4 max-h-[320px] overflow-y-auto pr-1 sm:pr-2 custom-scrollbar">
                        @php $maxSold = optional($fastMoving->first())['sold'] ?: 1; @endphp
                        @forelse($fastMoving as $item)
                        @php 
                            $percentage = min(100, max(15, ($item['sold'] / $maxSold) * 100)); 
                        @endphp
                        <a href="{{ route('inventory.index', ['search' => $item['sku']]) }}"
                           class="relative z-10 block p-3 rounded-xl bg-white dark:bg-[#10141D] border border-gray-100 dark:border-[#1e2536] hover:border-emerald-400 dark:hover:border-emerald-500 hover:shadow-sm transition-all cursor-pointer group">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5 sm:gap-2 text-sm mb-1">
                                <span class="font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors break-words">
                                    #{{ $item['rank'] }} {{ $item['name'] }} 
                                    <span class="text-xs font-normal text-gray-500 dark:text-slate-400 ml-1">({{ $item['sku'] }})</span>
                                </span>
                                <div class="text-xs shrink-0">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $item['sold'] }} sold</span>
                                    <span class="text-gray-400">· {{ number_format($item['stock'], 2) }} in stock</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-[#1e2536] rounded-full h-2 overflow-hidden">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="flex items-center justify-between mt-0.5">
                                <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">Fast Velocity</span>
                                <span class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity">View in inventory →</span>
                            </div>
                        </a>
                        @empty
                        <p class="text-center text-xs text-gray-400 py-4">No fast moving products found.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Slow Moving Panel --}}
            <div id="slowMovingCol" class="bg-gray-50 dark:bg-[#161B26]/50 border border-gray-200 dark:border-[#1e2536] rounded-2xl p-4 sm:p-5 lg:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <span class="inline-flex items-center text-xs font-bold px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 uppercase tracking-wider border border-amber-200 dark:border-amber-900">
                            📦 Slow Moving Products
                        </span>
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 px-2.5 py-1 rounded-lg">High Stock</span>
                    </div>

                    <div class="space-y-3 sm:space-y-4 max-h-[320px] overflow-y-auto pr-1 sm:pr-2 custom-scrollbar">
                        @forelse($slowMoving as $item)
                        <a href="{{ route('inventory.index', ['search' => $item['sku']]) }}"
                           class="relative z-10 block p-3 rounded-xl bg-white dark:bg-[#10141D] border border-gray-100 dark:border-[#1e2536] hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-sm transition-all cursor-pointer group">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5 sm:gap-2 text-sm mb-1">
                                <span class="font-bold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors break-words">
                                    #{{ $item['rank'] }} {{ $item['name'] }} 
                                    <span class="text-xs font-normal text-gray-500 dark:text-slate-400 ml-1">({{ $item['sku'] }})</span>
                                </span>
                                <div class="text-xs shrink-0">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($item['stock'], 2) }} in stock</span>
                                    <span class="text-gray-400">· {{ $item['sold'] }} sold</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-[#1e2536] rounded-full h-2 overflow-hidden">
                                <div class="bg-amber-500 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                            <div class="flex items-center justify-between mt-0.5">
                                <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400">⚠ {{ $item['supply_days'] }}</span>
                                <span class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity">View in inventory →</span>
                            </div>
                        </a>
                        @empty
                        <p class="text-center text-xs text-gray-400 py-4">No slow moving products found.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 sm:mt-6 flex items-start sm:items-center gap-2 text-xs text-gray-500 dark:text-slate-400 bg-gray-50 dark:bg-[#161B26] p-3 sm:p-4 rounded-xl border border-gray-200 dark:border-[#1e2536]">
            <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span><strong>Tip:</strong> Reorder <strong>Fast-Moving</strong> items to prevent stockouts, and pause <strong>Slow-Moving</strong> orders to prevent excess stock.</span>
        </div>
    </div>

    {{-- Admin + Manager: Inventory Alerts Table --}}
    @if (in_array(auth()->user()->role, ['admin', 'manager']))
        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm mb-8 overflow-hidden flex flex-col h-[450px] sm:h-[400px]">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4 px-4 sm:px-6 lg:px-8 py-4 sm:py-5 lg:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] shrink-0">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">Inventory Alerts</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400 mt-0.5 sm:mt-1">Combined store, warehouse, and total stock tracking</p>
                </div>
                <span class="bg-slate-100 text-slate-700 dark:bg-[#161B26] dark:text-slate-300 text-xs font-bold px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl self-start sm:self-auto">
                    {{ $lowStockProducts->total() }} Products
                </span>
            </div>

            {{-- Scrollable Table Container --}}
            <div id="alertScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
                <table class="w-full min-w-[760px] text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                        <tr>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 bg-gray-50 dark:bg-[#161B26]">Product Name</th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 bg-gray-50 dark:bg-[#161B26]">Category</th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 bg-gray-50 dark:bg-[#161B26]">Store Status</th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 bg-gray-50 dark:bg-[#161B26]">Warehouse Status</th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 bg-gray-50 dark:bg-[#161B26]">Total Status</th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 bg-gray-50 dark:bg-[#161B26] text-right">Current Stock / ROP</th>
                        </tr>
                    </thead>
                    <tbody id="alertTableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                        @include('dashboard.partials.alert-rows', ['lowStockProducts' => $lowStockProducts])
                    </tbody>
                </table>

                {{-- Loading Indicator --}}
                <div id="alertLoadingIndicator" class="hidden py-6 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                    <svg class="animate-spin h-5 w-5 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Loading more products...
                </div>

                {{-- Error Retry --}}
                <div id="alertLoadError" class="hidden py-4 text-center text-rose-500 text-sm">
                    Unable to load more products. <button type="button" id="alertRetryButton" class="underline font-semibold ml-1">Try again</button>
                </div>
            </div>

            {{-- Pagination Footer --}}
            <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-3 sm:py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
                <div class="text-xs sm:text-sm whitespace-normal sm:whitespace-nowrap text-center sm:text-left w-full sm:w-auto" id="alertResultsText">
                    @if($lowStockProducts->total() > 0)
                        Showing
                        <span class="font-semibold text-gray-900 dark:text-white" id="alertFirstRecord">{{ $lowStockProducts->firstItem() }}</span>
                        to
                        <span class="font-semibold text-gray-900 dark:text-white" id="alertLastRecord">{{ $lowStockProducts->lastItem() }}</span>
                        of
                        <span class="font-semibold text-gray-900 dark:text-white" id="alertTotalRecord">{{ $lowStockProducts->total() }}</span>
                        results
                    @else
                        No alerts found
                    @endif
                </div>

                <nav class="flex items-center gap-1.5 max-w-full" aria-label="Pagination">
                    <div id="alertPaginationContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                        @foreach ($lowStockProducts->getUrlRange(max(1, $lowStockProducts->currentPage() - 2), min($lowStockProducts->lastPage(), $lowStockProducts->currentPage() + 2)) as $page => $url)
                            @if ($page == $lowStockProducts->currentPage())
                                <span data-page="{{ $page }}" class="shrink-0 px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                            @else
                                <button type="button" data-page="{{ $page }}" class="shrink-0 px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                            @endif
                        @endforeach
                    </div>
                </nav>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    {{-- 1. Load the downloaded local file --}}
    <script src="{{ asset('js/chart.umd.js') }}"></script>
    
    <script>
        // =====================================================
        // CUSTOM DROPDOWNS LOGIC
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
                        const val = this.dataset.value || '';
                        if (hiddenInput) hiddenInput.value = val;
                        if (label) label.textContent = this.textContent.trim();
                        closeMenu();

                        // Custom Submits based on dropdown ID
                        if (dropdown.id === 'filter-category-dropdown') {
                            document.getElementById('forecastProductInput').value = ''; // Clear product
                            document.getElementById('forecastFilterForm').submit();
                        } else if (dropdown.id === 'filter-product-dropdown' || dropdown.id === 'filter-fs-category-dropdown') {
                            dropdown.closest('form').submit();
                        } else if (dropdown.id === 'filter-range-dropdown' || dropdown.id === 'filter-fs-range-dropdown') {
                            const isForecast = dropdown.id === 'filter-range-dropdown';
                            const customContainer = document.getElementById(isForecast ? 'forecastCustomDateContainer' : 'fastSlowCustomDateContainer');
                            
                            if (val === 'custom') {
                                if (customContainer) {
                                    customContainer.classList.remove('hidden');
                                    customContainer.classList.add('flex');
                                }
                            } else {
                                if (customContainer) {
                                    customContainer.classList.remove('flex');
                                    customContainer.classList.add('hidden');
                                }
                                dropdown.closest('form').submit();
                            }
                        }
                    });
                });
                
                document.addEventListener('click', e => { if (!dropdown.contains(e.target)) closeMenu(); });
                document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });
                dropdown.dataset.initialized = 'true';
            });
        }

        window.toggleProductView = function(type) {
            const fastCol = document.getElementById('fastMovingCol');
            const slowCol = document.getElementById('slowMovingCol');
            const btnBoth = document.getElementById('btn-both');
            const btnFast = document.getElementById('btn-fast');
            const btnSlow = document.getElementById('btn-slow');

            [btnBoth, btnFast, btnSlow].forEach(btn => {
                btn.className = 'flex-1 sm:flex-initial px-2 sm:px-3 py-1.5 text-xs font-semibold rounded-xl transition-colors text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white';
            });

            if (type === 'fast') {
                fastCol.style.display = 'block';
                fastCol.className = 'lg:col-span-2 bg-gray-50 dark:bg-[#161B26]/50 border border-gray-200 dark:border-[#1e2536] rounded-2xl p-4 sm:p-5 lg:p-6 flex flex-col justify-between';
                slowCol.style.display = 'none';
                btnFast.className = 'flex-1 sm:flex-initial px-2 sm:px-3 py-1.5 text-xs font-semibold rounded-xl transition-colors bg-white dark:bg-[#10141D] text-emerald-600 dark:text-emerald-400 shadow-xs';
            } else if (type === 'slow') {
                slowCol.style.display = 'block';
                slowCol.className = 'lg:col-span-2 bg-gray-50 dark:bg-[#161B26]/50 border border-gray-200 dark:border-[#1e2536] rounded-2xl p-4 sm:p-5 lg:p-6 flex flex-col justify-between';
                fastCol.style.display = 'none';
                btnSlow.className = 'flex-1 sm:flex-initial px-2 sm:px-3 py-1.5 text-xs font-semibold rounded-xl transition-colors bg-white dark:bg-[#10141D] text-amber-600 dark:text-amber-400 shadow-xs';
            } else {
                fastCol.style.display = 'block';
                fastCol.className = 'bg-gray-50 dark:bg-[#161B26]/50 border border-gray-200 dark:border-[#1e2536] rounded-2xl p-4 sm:p-5 lg:p-6 flex flex-col justify-between';
                slowCol.style.display = 'block';
                slowCol.className = 'bg-gray-50 dark:bg-[#161B26]/50 border border-gray-200 dark:border-[#1e2536] rounded-2xl p-4 sm:p-5 lg:p-6 flex flex-col justify-between';
                btnBoth.className = 'flex-1 sm:flex-initial px-2 sm:px-3 py-1.5 text-xs font-semibold rounded-xl transition-colors bg-white dark:bg-[#10141D] text-blue-600 dark:text-blue-400 shadow-xs';
            }
        };

        // =====================================================
        // INITIALIZATION & CHART RENDERING
        // =====================================================
        let demandChartInstance = null;

        document.addEventListener('DOMContentLoaded', function () {
            initCustomDropdowns();

            // 1. Initialize Chart
            const canvas = document.getElementById('demandForecastChart');
            if (canvas) {
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js failed to load locally. Check the path public/js/chart.umd.js');
                    canvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-sm font-semibold text-rose-500">Local file missing. Make sure chart.umd.js is in the public/js folder.</div>';
                } else {
                    try {
                        renderBackendForecastChart();
                    } catch (error) {
                        console.error('Error rendering chart:', error);
                        canvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-sm font-semibold text-rose-500">An error occurred while rendering the chart.</div>';
                    }
                }
            }

            // 2. Initialize Infinite Scroll
            initInventoryAlerts();
        });

        function renderBackendForecastChart() {
            const canvas = document.getElementById('demandForecastChart');
            const ctx = canvas.getContext('2d');

            if (demandChartInstance) {
                demandChartInstance.destroy();
            }

            const chartData = @json($forecastChart) || {};

            if (!chartData.labels || chartData.labels.length === 0) {
                canvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-sm text-gray-500">No data available for the selected period.</div>';
                return;
            }

            const todayLinePlugin = {
                id: 'todayLine',
                afterDraw(chart) {
                    const idx = chartData.today_index;
                    if (idx === null || idx === undefined) return;

                    const xScale = chart.scales.x;
                    if (!xScale) return;

                    const x = xScale.getPixelForValue(idx);
                    if (x === null || x === undefined || isNaN(x)) return;

                    const { top, bottom } = chart.chartArea;
                    const c = chart.ctx;

                    c.save();
                    c.beginPath();
                    c.moveTo(x, top);
                    c.lineTo(x, bottom);
                    c.lineWidth = 1.5;
                    c.strokeStyle = 'rgba(100, 116, 139, 0.55)';
                    c.setLineDash([6, 4]);
                    c.stroke();

                    c.fillStyle = 'rgba(100, 116, 139, 0.9)';
                    c.font = '600 10px Inter, sans-serif';
                    c.textAlign = 'center';
                    c.fillText('TODAY', x, top - 8);
                    c.restore();
                }
            };

            demandChartInstance = new Chart(ctx, {
                type: 'line',
                plugins: [todayLinePlugin],
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Past Sales (Actual)',
                            data: chartData.historical,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.08)',
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#2563eb',
                            fill: 'origin',
                            tension: 0.2,
                            spanGaps: false,
                        },
                        {
                            label: 'Predicted Future Sales',
                            data: chartData.forecast,
                            borderColor: '#8b5cf6',
                            backgroundColor: '#8b5cf6',
                            borderWidth: 2.5,
                            borderDash: [6, 4],
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#8b5cf6',
                            fill: false,
                            tension: 0.2,
                            spanGaps: false,
                        },
                        {
                            label: 'Low Estimate',
                            data: chartData.lower_bound,
                            borderColor: 'rgba(139, 92, 246, 0.3)',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            borderWidth: 1,
                            borderDash: [3, 3],
                            pointRadius: 0,
                            pointHoverRadius: 0,
                            fill: '+1',
                            tension: 0.2,
                            spanGaps: false,
                        },
                        {
                            label: 'High Estimate',
                            data: chartData.upper_bound,
                            borderColor: 'rgba(139, 92, 246, 0.3)',
                            borderWidth: 1,
                            borderDash: [3, 3],
                            pointRadius: 0,
                            pointHoverRadius: 0,
                            fill: false,
                            tension: 0.2,
                            spanGaps: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            display: false 
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label === 'Low Estimate' || label === 'High Estimate') {
                                        return null; 
                                    }
                                    if (context.parsed.y !== null) {
                                        return label + ': ' + context.parsed.y + ' items';
                                    }
                                    return null;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            title: { display: true, text: 'Number of Items Sold', font: { size: 11, weight: 'bold' }, color: '#64748b' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { autoSkip: true, maxRotation: 0, minRotation: 0, maxTicksLimit: 12 }
                        }
                    }
                }
            });
        }

        // =====================================================
        // INVENTORY ALERTS INFINITE SCROLL
        // =====================================================
        function initInventoryAlerts() {
            const container = document.getElementById('alertScrollContainer');
            const tableBody = document.getElementById('alertTableBody');
            const loadingIndicator = document.getElementById('alertLoadingIndicator');
            const loadError = document.getElementById('alertLoadError');
            const retryBtn = document.getElementById('alertRetryButton');
            const paginationContainer = document.getElementById('alertPaginationContainer');
            const resultsText = document.getElementById('alertResultsText');

            if (!container || !tableBody) return;

            let nextPageUrl = @json($lowStockProducts->nextPageUrl());
            let currentPage = {{ $lowStockProducts->currentPage() }};
            let lastPage = {{ $lowStockProducts->lastPage() }};
            let totalRecords = {{ $lowStockProducts->total() }};
            let perPage = {{ $lowStockProducts->perPage() }};
            let initialPage = {{ $lowStockProducts->currentPage() }};
            let initialStart = {{ $lowStockProducts->firstItem() ?? 0 }} || 0;

            let isLoading = false;
            let visualPage = initialPage;

            function updateResultsCounter() {
                const rowsLoaded = tableBody.querySelectorAll('tr').length;
                if (totalRecords === 0 || rowsLoaded === 0) {
                    resultsText.textContent = 'No alerts found.';
                    return;
                }
                const firstRecord = initialStart;
                const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
                resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> results`;
            }

            function getVisiblePage() {
                const rows = tableBody.querySelectorAll('tr');
                if (!rows.length) return initialPage;

                const isAtBottom = (container.scrollHeight - container.scrollTop - container.clientHeight) <= 5;
                if (isAtBottom && lastPage > 0) return lastPage;

                const containerRect = container.getBoundingClientRect();
                let firstVisibleIndex = -1;
                
                rows.forEach((row, index) => {
                    const rect = row.getBoundingClientRect();
                    if (firstVisibleIndex === -1 && rect.bottom > containerRect.top && rect.top < containerRect.bottom) {
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
                        html += `<span class="shrink-0 px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">${i}</span>`;
                    } else {
                        html += `<button type="button" class="shrink-0 px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">${i}</button>`;
                    }
                }
                paginationContainer.innerHTML = html;
            }

            async function loadMoreAlerts() {
                if (!nextPageUrl || isLoading) return;

                isLoading = true;
                loadError.classList.add('hidden');
                loadingIndicator.classList.remove('hidden');

                try {
                    const fetchUrl = new URL(nextPageUrl, window.location.origin);
                    fetchUrl.searchParams.set('alert_ajax', '1');
                    fetchUrl.searchParams.set('t', Date.now());

                    const response = await fetch(fetchUrl.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
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
                            tableBody.appendChild(row);
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
                    console.error('Error loading alerts:', error);
                    loadError.classList.remove('hidden');
                } finally {
                    isLoading = false;
                    loadingIndicator.classList.add('hidden');
                }
            }

            // --- AUTO LOAD IF CONTENT DOESN'T FILL CONTAINER ---
            setTimeout(() => {
                if (nextPageUrl && container.scrollHeight <= container.clientHeight + 20) {
                    loadMoreAlerts();
                }
            }, 100);

            container.addEventListener('scroll', function () {
                updatePaginationNumbers(getVisiblePage());
                const distanceFromBottom = container.scrollHeight - container.scrollTop - container.clientHeight;
                if (distanceFromBottom < 150 && nextPageUrl && !isLoading) {
                    loadMoreAlerts();
                }
            }, { passive: true });

            retryBtn?.addEventListener('click', loadMoreAlerts);
        }
    </script>
@endpush