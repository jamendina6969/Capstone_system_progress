@extends('layouts.default')

@section('title', 'Customer Returns')

@section('content')

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customer Returns</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Track processed customer refunds and returned items</p>
        </div>
        <button type="button" onclick="toggleCreateReturnModal(true)"
                class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
            + Create Return
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
    @endif

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-180px)] min-h-[500px] mb-4">

        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
            <form id="filterForm" method="GET" action="{{ route('customer.returns.index') }}" class="flex flex-col xl:flex-row xl:items-center gap-4">
                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search Return #, Transaction, or Customer..." autocomplete="off"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="w-full sm:w-40">
                        <select name="status" onchange="this.form.submit()" class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                            <option value="">All Statuses</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-44">
                        <select name="refund_method" onchange="this.form.submit()" class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('refund_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="original" {{ request('refund_method') == 'original' ? 'selected' : '' }}>Original Payment</option>
                            <option value="store_credit" {{ request('refund_method') == 'store_credit' ? 'selected' : '' }}>Store Credit</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">From</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()"
                               class="rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">To</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()"
                               class="rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>

                    @if (request()->anyFilled(['search', 'status', 'refund_method', 'date_from', 'date_to']))
                        <a href="{{ route('customer.returns.index') }}" class="text-xs font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26]">Clear Filters</a>
                    @endif
                </div>
            </form>
        </div>

        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full min-w-[760px] text-sm text-left relative">
                <thead id="returnsTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Return #</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Transaction</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Reason</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Refund Amount</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Status</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('customer-returns.partials.rows', ['returns' => $returns])
                </tbody>
            </table>

            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more returns...
            </div>

            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more returns. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($returns->total() > 0)
                    Showing <span class="font-semibold text-gray-900 dark:text-white">{{ $returns->firstItem() }}</span>
                    to <span class="font-semibold text-gray-900 dark:text-white">{{ $returns->lastItem() }}</span>
                    of <span class="font-semibold text-gray-900 dark:text-white">{{ $returns->total() }}</span> returns
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

    {{-- VIEW RETURN MODAL --}}
    <div id="viewReturnModal" class="fixed inset-0 z-[70] hidden bg-slate-900/70 backdrop-blur-xs p-4 sm:p-6 flex items-center justify-center">
        <div class="relative w-full max-w-6xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl flex flex-col min-h-[65vh] max-h-[92vh]" onclick="event.stopPropagation()">

            {{-- Header (Fixed) --}}
            <div class="flex items-center justify-between px-8 py-5 border-b-2 border-gray-200 dark:border-[#1e2536] flex-shrink-0">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Return Details</h3>
                    <p id="view_return_label" class="text-xs text-gray-500 dark:text-slate-400 mt-0.5"></p>
                </div>
                <button type="button" onclick="toggleViewReturnModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-2xl font-bold cursor-pointer leading-none">×</button>
            </div>

            {{-- Body (Scrollable) --}}
            <div class="p-8 overflow-y-auto custom-scrollbar flex-1 space-y-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <div><p class="text-[10px] uppercase font-bold text-gray-500">Customer</p><p id="view_return_customer" class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5"></p></div>
                    <div><p class="text-[10px] uppercase font-bold text-gray-500">Processed By</p><p id="view_return_user" class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5"></p></div>
                    <div><p class="text-[10px] uppercase font-bold text-gray-500">Status</p><p id="view_return_status" class="text-sm font-semibold mt-0.5"></p></div>
                    <div><p class="text-[10px] uppercase font-bold text-gray-500">Refund Method</p><p id="view_return_method" class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5"></p></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div><p class="text-[10px] uppercase font-bold text-gray-500">Reason</p><p id="view_return_reason" class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5"></p></div>
                    <div><p class="text-[10px] uppercase font-bold text-gray-500">Date Processed</p><p id="view_return_date" class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5"></p></div>
                    <div><p class="text-[10px] uppercase font-bold text-gray-500">Refund Amount</p><p id="view_return_total" class="text-base font-bold text-emerald-600 mt-0.5"></p></div>
                </div>

                <div id="view_return_notes_container" class="hidden">
                    <p class="text-[10px] uppercase font-bold text-gray-500 mb-1">Notes</p>
                    <p id="view_return_notes" class="text-sm text-gray-700 dark:text-slate-300 whitespace-pre-line"></p>
                </div>

                <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-5">
                    <p class="text-[10px] uppercase font-bold text-gray-500 mb-3">Returned Items</p>
                    <div class="border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 whitespace-nowrap">Product</th>
                                    <th class="px-4 py-3 whitespace-nowrap">SKUs</th>
                                    <th class="px-4 py-3 text-right whitespace-nowrap">Qty</th>
                                    <th class="px-4 py-3 text-right whitespace-nowrap">Unit Refund</th>
                                    <th class="px-4 py-3 text-right whitespace-nowrap">Subtotal</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Restocked</th>
                                </tr>
                            </thead>
                            <tbody id="view_return_items_body" class="divide-y divide-gray-100 dark:divide-[#1e2536]"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Footer (Fixed) --}}
            <div class="flex justify-end px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex-shrink-0 bg-gray-50/50 dark:bg-[#161B26]/50 rounded-b-3xl">
                <button type="button" onclick="toggleViewReturnModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Close</button>
            </div>
        </div>
    </div>

    {{-- VOID RETURN MODAL --}}
    <div id="voidReturnModal" class="fixed inset-0 z-[80] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6" onclick="event.stopPropagation()">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4 bg-rose-100 text-rose-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Void Return</h3>
                <p id="voidReturnLabel" class="text-sm text-gray-500 dark:text-slate-400 mb-1"></p>
                <p class="text-xs text-amber-600 dark:text-amber-400 mb-4">Voiding will reverse the refund and remove any restocked items from inventory.</p>

                <form id="voidForm" method="POST" class="text-left mb-6">
                    @csrf
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Void Reason</label>
                    <textarea name="void_reason" required rows="3" maxlength="255"
                              class="w-full rounded-xl border-2 border-gray-200 dark:border-[#1e2536] bg-white dark:bg-[#161B26] text-gray-900 dark:text-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-rose-400"
                              placeholder="Explain why this return is being voided..."></textarea>
                </form>

                <div class="flex justify-center gap-3">
                    <button type="button" onclick="toggleVoidModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Go Back</button>
                    <button type="button" onclick="document.getElementById('voidForm').submit()" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Yes, Void</button>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE RETURN MODAL --}}
    <div id="createReturnModal" class="fixed inset-0 z-[90] hidden bg-slate-900/70 backdrop-blur-xs p-4 sm:p-6 flex items-center justify-center">
        <div class="relative w-full max-w-6xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl flex flex-col min-h-[65vh] max-h-[92vh]" onclick="event.stopPropagation()">

            {{-- Header (Fixed) --}}
            <div class="flex items-center justify-between px-8 py-5 border-b-2 border-gray-200 dark:border-[#1e2536] flex-shrink-0">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Create Customer Return</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Search by transaction ID, customer name, or item/product SKU to verify authenticity</p>
                </div>
                <button type="button" onclick="toggleCreateReturnModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-2xl font-bold cursor-pointer leading-none">×</button>
            </div>

            {{-- Body (Scrollable) --}}
            <div class="p-8 overflow-y-auto custom-scrollbar flex-1 min-h-[380px]">
                {{-- Step 1: Select Transaction --}}
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Step 1 · Select Transaction</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="transactionSearchInput" autocomplete="off"
                               placeholder="Search transaction ID, customer name, or scan/enter product_sku / item_sku..."
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] pl-11 pr-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <div id="transactionSearchResults" class="hidden absolute left-0 right-0 top-full mt-1.5 z-40 max-h-80 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl shadow-xl"></div>
                    </div>

                    <div id="selectedTransactionInfo" class="hidden mt-4 p-4 bg-gray-50 dark:bg-[#161B26] rounded-2xl border-2 border-gray-200 dark:border-[#1e2536]"></div>
                </div>

                {{-- Step 2: Items & SKU Crosscheck --}}
                <div id="returnItemsSection" class="hidden mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase">Step 2 · Verify SKU & Select Items to Return</label>

                        {{-- SKU Crosscheck / Anti-Theft Scanner Input --}}
                        <div class="flex items-center gap-2 w-full sm:w-96">
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </span>
                                <input type="text" id="skuCrosscheckInput" autocomplete="off"
                                       placeholder="Scan or type product_sku / item_sku to verify..."
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] pl-9 pr-3 py-2 text-xs font-mono text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                            </div>
                            <button type="button" onclick="crosscheckSku()"
                                    class="shrink-0 bg-slate-800 hover:bg-slate-900 dark:bg-[#1E293B] dark:hover:bg-slate-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition-colors cursor-pointer">
                                Verify SKU
                            </button>
                        </div>
                    </div>

                    {{-- SKU Verification Status Banner --}}
                    <div id="skuCrosscheckResult" class="hidden mb-3 px-4 py-3 rounded-2xl border-2 text-xs font-medium flex items-center justify-between gap-3"></div>

                    <div class="border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">Item & SKU</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Purchased</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Returned</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Max</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Return Qty</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Refund</th>
                                </tr>
                            </thead>
                            <tbody id="returnItemsBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Step 3: Refund Details --}}
                <div id="refundDetailsSection" class="hidden mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Step 3 · Refund Details</label>
                    <form id="createReturnForm" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Refund Method</label>
                                <select name="refund_method" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm">
                                    <option value="cash">Cash</option>
                                    <option value="original">Original Payment</option>
                                    <option value="store_credit">Store Credit</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Reason</label>
                                <input type="text" name="reason" required maxlength="255" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm" placeholder="e.g., Defective item, wrong size">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Notes (Optional)</label>
                            <textarea name="notes" rows="2" maxlength="1000" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm" placeholder="Additional details..."></textarea>
                        </div>

                        <div class="mt-5 p-4 bg-emerald-50 dark:bg-emerald-950/20 border-2 border-emerald-200 dark:border-emerald-900 rounded-2xl">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Estimated Refund:</span>
                                <span id="estimatedRefund" class="text-xl font-bold text-emerald-700 dark:text-emerald-400">₱0.00</span>
                            </div>
                            <p class="text-xs text-emerald-600/80 dark:text-emerald-400/80 mt-1">Final amount may differ slightly if the original transaction had a discount.</p>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Footer (Fixed) --}}
            <div class="flex justify-end gap-3 px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex-shrink-0 bg-gray-50/50 dark:bg-[#161B26]/50 rounded-b-3xl">
                <button type="button" onclick="toggleCreateReturnModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">Cancel</button>
                <button type="button" id="submitReturnBtn"
                        class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
                    Submit Return
                </button>
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
    // ============================================================
    // INFINITE SCROLL
    // ============================================================
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
        resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> returns`;
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
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Cache-Control': 'no-cache' }
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

    updateResultsCounter();
    updatePaginationNumbers(initialPage);

    // ============================================================
    // VIEW / VOID MODALS
    // ============================================================
    function toggleViewReturnModal(show) {
        const modal = document.getElementById('viewReturnModal');
        if (show) { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
        else { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
    }

    function toggleVoidModal(show) {
        const modal = document.getElementById('voidReturnModal');
        if (show) { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
        else { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); document.getElementById('voidForm').reset(); }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function openViewReturnModal(returnDataString) {
        const returnData = JSON.parse(returnDataString);

        document.getElementById('view_return_label').textContent = `Return #${returnData.return_number}`;
        document.getElementById('view_return_customer').textContent = returnData.transaction ? (returnData.transaction.customer_name || 'Walk-in') : 'N/A';
        document.getElementById('view_return_user').textContent = returnData.user ? (returnData.user.full_name || returnData.user.name) : 'Deleted User';
        document.getElementById('view_return_reason').textContent = returnData.reason || '—';
        document.getElementById('view_return_method').textContent = returnData.refund_method.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        document.getElementById('view_return_date').textContent = returnData.created_at ? new Date(returnData.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
        document.getElementById('view_return_total').textContent = '₱' + parseFloat(returnData.refund_amount).toLocaleString('en-US', { minimumFractionDigits: 2 });

        const statusEl = document.getElementById('view_return_status');
        statusEl.textContent = returnData.status.toUpperCase();
        statusEl.className = 'text-sm font-semibold ' + (
            returnData.status === 'completed' ? 'text-emerald-600' :
            returnData.status === 'voided' ? 'text-rose-600' : 'text-amber-600'
        );

        const notesContainer = document.getElementById('view_return_notes_container');
        if (returnData.notes && returnData.notes.trim() !== '') {
            document.getElementById('view_return_notes').textContent = returnData.notes;
            notesContainer.classList.remove('hidden');
        } else {
            notesContainer.classList.add('hidden');
        }

        const tbody = document.getElementById('view_return_items_body');
        tbody.innerHTML = '';
        if (!returnData.items || returnData.items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No items recorded.</td></tr>`;
        } else {
            returnData.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-[#1e2536]/30';

                const productSku = (item.product && item.product.product_sku) ? item.product.product_sku : '';
                const itemSku = item.item_sku || '';
                let skuHtml = '<span class="text-gray-400 text-xs">—</span>';
                if (productSku || itemSku) {
                    skuHtml = '';
                    if (productSku) {
                        skuHtml += `<div class="font-mono text-[11px] leading-tight"><span class="text-gray-400">product_sku:</span> <span class="text-gray-700 dark:text-slate-300">${escapeHtml(productSku)}</span></div>`;
                    }
                    if (itemSku) {
                        skuHtml += `<div class="font-mono text-[11px] leading-tight mt-0.5"><span class="text-gray-400">item_sku:</span> <span class="text-gray-700 dark:text-slate-300">${escapeHtml(itemSku)}</span></div>`;
                    }
                }

                tr.innerHTML = `
                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">${item.product ? item.product.product_name : (item.product_name || 'Whole Project')}</td>
                    <td class="px-4 py-3">${skuHtml}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">${item.quantity}</td>
                    <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">₱${parseFloat(item.unit_refund_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">₱${parseFloat(item.refund_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                    <td class="px-4 py-3 text-center">${item.restock ? '<span class="text-emerald-600 font-semibold">Yes</span>' : '<span class="text-gray-400">No</span>'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        toggleViewReturnModal(true);
    }

    function openVoidReturnModal(returnId, returnNumber) {
        document.getElementById('voidReturnLabel').textContent = `Return: ${returnNumber}`;
        document.getElementById('voidForm').action = `/returns/${returnId}/void`;
        toggleVoidModal(true);
    }

    // ============================================================
    // CREATE RETURN MODAL & SKU CROSSCHECK
    // ============================================================
    let currentTransactionId = null;
    let loadedReturnItems = [];
    let createSearchTimeout;

    function toggleCreateReturnModal(show) {
        const modal = document.getElementById('createReturnModal');
        if (show) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            resetCreateReturnModal();
            searchTransactionsForReturn('');
            setTimeout(() => document.getElementById('transactionSearchInput').focus(), 50);
        } else {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            resetCreateReturnModal();
        }
    }

    function resetCreateReturnModal() {
        currentTransactionId = null;
        loadedReturnItems = [];
        document.getElementById('transactionSearchInput').value = '';
        document.getElementById('transactionSearchResults').innerHTML = '';
        document.getElementById('transactionSearchResults').classList.add('hidden');
        document.getElementById('selectedTransactionInfo').classList.add('hidden');
        document.getElementById('selectedTransactionInfo').innerHTML = '';
        document.getElementById('returnItemsSection').classList.add('hidden');
        document.getElementById('refundDetailsSection').classList.add('hidden');
        document.getElementById('returnItemsBody').innerHTML = '';
        document.getElementById('skuCrosscheckInput').value = '';
        document.getElementById('skuCrosscheckResult').classList.add('hidden');
        document.getElementById('submitReturnBtn').disabled = true;
        document.getElementById('createReturnForm').reset();
        document.getElementById('estimatedRefund').textContent = '₱0.00';
        document.querySelectorAll('#createReturnForm input[type="hidden"][name^="items["]').forEach(el => el.remove());
    }

    document.getElementById('transactionSearchInput').addEventListener('input', function (e) {
        clearTimeout(createSearchTimeout);
        const q = e.target.value.trim();
        createSearchTimeout = setTimeout(() => searchTransactionsForReturn(q), 300);
    });

    document.getElementById('transactionSearchInput').addEventListener('focus', function () {
        if (this.value.trim() === '') searchTransactionsForReturn('');
    });

    document.getElementById('skuCrosscheckInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            crosscheckSku();
        }
    });

    async function searchTransactionsForReturn(q) {
        try {
            const response = await fetch(`{{ route('customer.returns.searchTransactions') }}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Server error occurred');
            }
            
            renderTransactionResults(data, q);
        } catch (e) {
            console.error(e);
            let errorMsg = e.message;
            if (errorMsg.includes('Unexpected token') || errorMsg.includes('JSON')) {
                errorMsg = 'Backend returned HTML instead of JSON (500 Error). Check Laravel logs.';
            }
            document.getElementById('transactionSearchResults').innerHTML = `<div class="px-4 py-3 text-sm text-rose-500 text-center font-semibold">Error: ${escapeHtml(errorMsg)}</div>`;
            document.getElementById('transactionSearchResults').classList.remove('hidden');
        }
    }

    function renderTransactionResults(data, query = '') {
        const container = document.getElementById('transactionSearchResults');
        container.innerHTML = '';

        const transactions = Array.isArray(data) ? data : (data.data || []);

        if (!transactions.length) {
            if (query.trim() === '') {
                container.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500 text-center">Type to search for transactions...</div>';
            } else {
                container.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500 text-center">No matching transactions or SKUs found</div>';
            }
            container.classList.remove('hidden');
            return;
        }

        const qLower = query.toLowerCase();

        transactions.forEach(tx => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full text-left px-4 py-3 hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors border-b border-gray-100 dark:border-[#1e2536] last:border-0';

            const skus = Array.isArray(tx.skus) ? tx.skus : [];
            const matchedSku = qLower ? skus.find(s => String(s).toLowerCase().includes(qLower)) : null;
            const skuBadgeHtml = matchedSku
                ? `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 ml-2">SKU Match: ${escapeHtml(matchedSku)}</span>`
                : (skus.length ? `<div class="text-[11px] font-mono text-gray-400 dark:text-slate-500 mt-0.5 truncate">SKUs: ${escapeHtml(skus.slice(0, 4).join(', '))}${skus.length > 4 ? '...' : ''}</div>` : '');

            btn.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 dark:text-white text-sm truncate flex items-center">
                            <span>${escapeHtml(tx.transaction_id)}</span>
                            ${matchedSku ? skuBadgeHtml : ''}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5 truncate">${escapeHtml(tx.customer_name)} · ${escapeHtml(tx.date_short)}</div>
                        ${!matchedSku ? skuBadgeHtml : ''}
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="font-bold text-gray-900 dark:text-white text-sm">₱${parseFloat(tx.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                        <div class="text-[10px] uppercase font-bold ${tx.transaction_type === 1 ? 'text-purple-600' : 'text-blue-600'}">${tx.transaction_type === 1 ? 'Imported' : 'POS'}</div>
                    </div>
                </div>
            `;
            btn.onclick = () => selectTransactionForReturn(tx, matchedSku || '');
            container.appendChild(btn);
        });

        container.classList.remove('hidden');
    }

    async function selectTransactionForReturn(tx, prefillSku = '') {
        currentTransactionId = tx.id;
        document.getElementById('transactionSearchResults').classList.add('hidden');
        document.getElementById('transactionSearchInput').value = '';

        const info = document.getElementById('selectedTransactionInfo');
        info.innerHTML = `
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-[10px] text-gray-500 dark:text-slate-400 uppercase font-bold mb-1">Selected Transaction</div>
                    <div class="font-semibold text-gray-900 dark:text-white truncate">${escapeHtml(tx.transaction_id)}</div>
                    <div class="text-xs text-gray-500 dark:text-slate-400 truncate">${escapeHtml(tx.customer_name)} · ${escapeHtml(tx.created_at)}</div>
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="font-bold text-gray-900 dark:text-white">₱${parseFloat(tx.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                    <button type="button" onclick="resetTransactionSelection()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-1">Change</button>
                </div>
            </div>
        `;
        info.classList.remove('hidden');

        await loadReturnItems(tx.id);

        if (prefillSku) {
            document.getElementById('skuCrosscheckInput').value = prefillSku;
            crosscheckSku();
        }
    }

    function resetTransactionSelection() {
        currentTransactionId = null;
        loadedReturnItems = [];
        document.getElementById('selectedTransactionInfo').classList.add('hidden');
        document.getElementById('returnItemsSection').classList.add('hidden');
        document.getElementById('refundDetailsSection').classList.add('hidden');
        document.getElementById('skuCrosscheckInput').value = '';
        document.getElementById('skuCrosscheckResult').classList.add('hidden');
        document.getElementById('submitReturnBtn').disabled = true;
        document.getElementById('transactionSearchInput').focus();
        searchTransactionsForReturn('');
    }

    async function loadReturnItems(transactionId) {
        try {
            const response = await fetch(`/transactions/${transactionId}/return-items`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const items = await response.json();
            loadedReturnItems = items;
            renderReturnItems(items);
            document.getElementById('returnItemsSection').classList.remove('hidden');
            document.getElementById('refundDetailsSection').classList.remove('hidden');
        } catch (e) {
            console.error(e);
            alert('Failed to load transaction items.');
        }
    }

    function renderReturnItems(items) {
        const tbody = document.getElementById('returnItemsBody');
        tbody.innerHTML = '';

        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400 text-sm">No returnable items found.</td></tr>';
            return;
        }

        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'return-item transition-colors';
            tr.dataset.itemId = item.id;
            tr.dataset.itemType = item.item_type;

            const primarySku = item.product_sku || item.item_sku || '';
            const secondarySku = (item.item_sku && item.product_sku && item.item_sku !== item.product_sku) ? item.item_sku : '';
            tr.dataset.skus = [primarySku, secondarySku].filter(Boolean).join('|').toLowerCase();

            if (item.item_type === 'project') {
                const alreadyWhole = item.whole_project_returned;

                const groupedMaterials = {};
                item.materials.forEach((mat, idx) => {
                    const key = mat.product_sku || mat.sku || mat.name;
                    if (!groupedMaterials[key]) {
                        groupedMaterials[key] = {
                            name: mat.name,
                            product_sku: mat.product_sku || mat.sku || '',
                            price: mat.price,
                            totalMax: 0,
                            units: []
                        };
                    }
                    groupedMaterials[key].totalMax += mat.max;
                    groupedMaterials[key].units.push({ ...mat, originalIndex: idx });
                });

                tr.innerHTML = `
                    <td class="px-4 py-3 align-top">
                        <div class="font-medium text-gray-900 dark:text-white">${escapeHtml(item.name)}</div>
                        <div class="flex flex-wrap items-center gap-1.5 mt-1">
                            <span class="text-[11px] text-gray-500 dark:text-slate-400">Project · ${Object.keys(groupedMaterials).length} material type(s)</span>
                            ${primarySku ? `<span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#1e2536] text-slate-700 dark:text-slate-300 font-mono text-[10px] font-semibold">SKU: ${escapeHtml(primarySku)}</span>` : ''}
                        </div>
                        <div class="mt-2 space-y-1">
                            <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-slate-400">
                                <input type="checkbox" class="whole-project-check" ${alreadyWhole ? 'disabled' : ''} onchange="toggleProjectWhole(this)">
                                <span>${alreadyWhole ? 'Already fully returned' : 'Return entire project'}</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-slate-400">
                                <input type="checkbox" class="project-restock" checked>
                                <span>Restock items</span>
                            </label>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600 dark:text-slate-400 align-top">${item.qty}</td>
                    <td class="px-4 py-3 text-center text-gray-600 dark:text-slate-400 align-top">${item.returned}</td>
                    <td class="px-4 py-3 text-center text-gray-600 dark:text-slate-400 align-top">${item.max}</td>
                    <td class="px-4 py-3 align-top" colspan="2">
                        <div class="materials-container ${alreadyWhole ? 'opacity-40 pointer-events-none' : ''} space-y-2">
                            ${Object.values(groupedMaterials).map((group, gIdx) => `
                                <div class="material-group border border-gray-200 dark:border-[#1e2536] rounded-xl bg-gray-50/50 dark:bg-[#161B26]/50 overflow-hidden">
                                    <div class="flex items-center justify-between px-3.5 py-2.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors select-none" onclick="toggleMaterialAccordion(this)">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <svg class="w-4 h-4 text-gray-400 transition-transform transform duration-200 chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-gray-900 dark:text-white truncate">${escapeHtml(group.name)}</div>
                                                <div class="text-[10px] text-gray-500 font-mono">SKU: ${escapeHtml(group.product_sku || 'N/A')} · ₱${parseFloat(group.price).toFixed(2)}/unit</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5 flex-shrink-0">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-[#1e2536] text-slate-700 dark:text-slate-300">${group.units.length} unit(s)</span>
                                        </div>
                                    </div>

                                    <div class="accordion-content hidden border-t border-gray-200 dark:border-[#1e2536] p-2 space-y-1.5 bg-white dark:bg-[#161B26]">
                                        ${group.units.map(mat => {
                                            const prodSku = mat.product_sku || mat.sku || '';
                                            const itemSku = mat.item_sku || '';
                                            const combinedSku = [prodSku, itemSku].filter(Boolean).join(' ').toLowerCase();
                                            return `
                                                <div class="material-row flex items-center justify-between gap-3 rounded-lg p-2 bg-gray-50 dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] transition-colors"
                                                     data-material-index="${mat.originalIndex}"
                                                     data-max="${mat.max}"
                                                     data-price="${mat.price}"
                                                     data-sku="${escapeHtml(combinedSku)}">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex flex-wrap items-center gap-1.5">
                                                            ${itemSku ? `<span class="px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-mono text-[10px] border border-emerald-200 dark:border-emerald-900">item_sku: ${escapeHtml(itemSku)}</span>` : ''}
                                                            ${prodSku ? `<span class="px-1.5 py-0.5 rounded bg-slate-200/60 dark:bg-[#1e2536] text-slate-700 dark:text-slate-300 font-mono text-[10px]">prod_sku: ${escapeHtml(prodSku)}</span>` : ''}
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2 flex-shrink-0">
                                                        <span class="text-[10px] text-gray-400 font-medium">max ${mat.max}</span>
                                                        <input type="number" class="material-qty w-14 rounded-md bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-1.5 py-0.5 text-xs text-center font-bold text-gray-900 dark:text-white" min="0" max="${mat.max}" value="0" onchange="updateEstimatedRefund()">
                                                    </div>
                                                </div>
                                            `;
                                        }).join('')}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </td>
                `;
            } else {
                tr.dataset.unit = item.unit;
                tr.dataset.max = item.max;
                tr.innerHTML = `
                    <td class="px-4 py-3 align-top">
                        <div class="font-medium text-gray-900 dark:text-white">${escapeHtml(item.name)}</div>
                        <div class="flex flex-wrap items-center gap-1.5 mt-1">
                            ${primarySku ? `<span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#1e2536] text-slate-700 dark:text-slate-300 font-mono text-[10px] font-semibold">product_sku: ${escapeHtml(primarySku)}</span>` : '<span class="text-[11px] text-gray-400">No SKU</span>'}
                            ${secondarySku ? `<span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#1e2536] text-slate-700 dark:text-slate-300 font-mono text-[10px] font-semibold">item_sku: ${escapeHtml(secondarySku)}</span>` : ''}
                            <span class="text-[11px] text-gray-500 dark:text-slate-400">· ₱${parseFloat(item.unit).toFixed(2)}/unit</span>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-slate-400 mt-1.5">
                            <input type="checkbox" class="return-restock" checked>
                            <span>Restock</span>
                        </label>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600 dark:text-slate-400 align-top">${item.qty}</td>
                    <td class="px-4 py-3 text-center text-gray-600 dark:text-slate-400 align-top">${item.returned}</td>
                    <td class="px-4 py-3 text-center text-gray-600 dark:text-slate-400 align-top">${item.max}</td>
                    <td class="px-4 py-3 text-center align-top">
                        <input type="number" class="return-qty w-20 rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-2 py-1 text-sm text-center" min="0" max="${item.max}" value="0" ${item.max === 0 ? 'disabled' : ''} onchange="updateEstimatedRefund()">
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500 align-top refund-preview" data-item-id="${item.id}">—</td>
                `;
            }
            tbody.appendChild(tr);
        });

        updateEstimatedRefund();
    }

    // ============================================================
    // ACCORDION TOGGLE FOR PROJECT MATERIALS
    // ============================================================
    function toggleMaterialAccordion(headerEl) {
        const groupEl = headerEl.closest('.material-group');
        const contentEl = groupEl.querySelector('.accordion-content');
        const chevron = headerEl.querySelector('.chevron-icon');

        contentEl.classList.toggle('hidden');
        if (contentEl.classList.contains('hidden')) {
            chevron.style.transform = 'rotate(0deg)';
        } else {
            chevron.style.transform = 'rotate(90deg)';
        }
    }

    // ============================================================
    // SKU ANTI-THEFT / RECEIPT CROSSCHECK
    // ============================================================
    async function crosscheckSku() {
        const input = document.getElementById('skuCrosscheckInput');
        const banner = document.getElementById('skuCrosscheckResult');
        const rawSku = input.value.trim();

        document.querySelectorAll('.return-item').forEach(tr => {
            tr.classList.remove('bg-emerald-50/70', 'dark:bg-emerald-950/30', 'ring-2', 'ring-emerald-500');
        });
        document.querySelectorAll('.material-row').forEach(mr => {
            mr.classList.remove('bg-emerald-100/80', 'dark:bg-emerald-900/40', 'ring-1', 'ring-emerald-500');
        });

        if (!rawSku) {
            banner.classList.add('hidden');
            return;
        }

        const query = rawSku.toLowerCase();
        let matchedRow = null;
        let matchedMaterial = null;
        let matchedName = '';

        document.querySelectorAll('.return-item').forEach(tr => {
            const rowSkus = (tr.dataset.skus || '').split('|').filter(Boolean);
            if (rowSkus.some(s => s === query || s.includes(query))) {
                matchedRow = tr;
                matchedName = tr.querySelector('.font-medium')?.textContent || rawSku;
            }

            tr.querySelectorAll('.material-row').forEach(mr => {
                const matSku = mr.dataset.sku || '';
                if (matSku && (matSku === query || matSku.includes(query))) {
                    matchedRow = tr;
                    matchedMaterial = mr;
                    matchedName = mr.closest('.material-group')?.querySelector('.text-xs.font-bold')?.textContent || rawSku;
                }
            });
        });

        if (matchedRow) {
            matchedRow.classList.add('bg-emerald-50/70', 'dark:bg-emerald-950/30', 'ring-2', 'ring-emerald-500');
            if (matchedMaterial) {
                const materialGroup = matchedMaterial.closest('.material-group');
                const contentEl = materialGroup.querySelector('.accordion-content');
                const chevron = materialGroup.querySelector('.chevron-icon');
                if (contentEl.classList.contains('hidden')) {
                    contentEl.classList.remove('hidden');
                    if (chevron) chevron.style.transform = 'rotate(90deg)';
                }

                matchedMaterial.classList.add('bg-emerald-100/80', 'dark:bg-emerald-900/40', 'ring-1', 'ring-emerald-500');
            }
            matchedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });

            banner.className = 'mb-3 px-4 py-3 rounded-2xl border-2 text-xs font-medium flex items-center justify-between gap-3 bg-emerald-50 dark:bg-emerald-950/30 border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300';
            banner.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-bold uppercase">Verified On Receipt</span>
                    <span>SKU <strong class="font-mono">${escapeHtml(rawSku)}</strong> matches <strong>${escapeHtml(matchedName)}</strong> in this transaction.</span>
                </div>
                <button type="button" onclick="clearSkuCrosscheck()" class="underline font-semibold shrink-0 cursor-pointer">Clear</button>
            `;
            banner.classList.remove('hidden');
            return;
        }

        try {
            const response = await fetch(`/customer-returns/verify-sku?sku=${encodeURIComponent(rawSku)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (response.ok) {
                const data = await response.json();
                if (data.found) {
                    banner.className = 'mb-3 px-4 py-3 rounded-2xl border-2 text-xs font-medium flex items-center justify-between gap-3 bg-amber-50 dark:bg-amber-950/30 border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300';
                    banner.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-bold uppercase">Store Item · Wrong Receipt</span>
                            <span><strong>${escapeHtml(data.product_name)}</strong> (<span class="font-mono">${escapeHtml(data.product_sku)}</span>) belongs to this store, but is <strong>NOT in this transaction</strong>.</span>
                        </div>
                        <button type="button" onclick="clearSkuCrosscheck()" class="underline font-semibold shrink-0 cursor-pointer">Clear</button>
                    `;
                    banner.classList.remove('hidden');
                    return;
                }
            }
        } catch (e) {}

        banner.className = 'mb-3 px-4 py-3 rounded-2xl border-2 text-xs font-medium flex items-center justify-between gap-3 bg-rose-50 dark:bg-rose-950/30 border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300';
        banner.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-bold uppercase">Unrecognized SKU</span>
                <span>SKU <strong class="font-mono">${escapeHtml(rawSku)}</strong> was <strong>NOT found</strong> on this receipt or in the store catalog. Do not accept return without supervisor check.</span>
            </div>
            <button type="button" onclick="clearSkuCrosscheck()" class="underline font-semibold shrink-0 cursor-pointer">Clear</button>
        `;
        banner.classList.remove('hidden');
    }

    function clearSkuCrosscheck() {
        document.getElementById('skuCrosscheckInput').value = '';
        document.getElementById('skuCrosscheckResult').classList.add('hidden');
        document.querySelectorAll('.return-item').forEach(tr => {
            tr.classList.remove('bg-emerald-50/70', 'dark:bg-emerald-950/30', 'ring-2', 'ring-emerald-500');
        });
        document.querySelectorAll('.material-row').forEach(mr => {
            mr.classList.remove('bg-emerald-100/80', 'dark:bg-emerald-900/40', 'ring-1', 'ring-emerald-500');
        });
        document.getElementById('skuCrosscheckInput').focus();
    }

    function toggleProjectWhole(checkbox) {
        const container = checkbox.closest('tr').querySelector('.materials-container');
        if (checkbox.checked) {
            container.classList.add('opacity-40', 'pointer-events-none');
            container.querySelectorAll('.material-qty').forEach(el => el.value = 0);
        } else {
            container.classList.remove('opacity-40', 'pointer-events-none');
        }
        updateEstimatedRefund();
    }

    function updateEstimatedRefund() {
        let total = 0;
        let hasItems = false;

        document.querySelectorAll('.return-item').forEach(row => {
            if (row.dataset.itemType === 'project') {
                const isWhole = row.querySelector('.whole-project-check')?.checked;
                if (isWhole) {
                    row.querySelectorAll('.material-row').forEach(matRow => {
                        const max = parseInt(matRow.dataset.max || 0);
                        const price = parseFloat(matRow.dataset.price || 0);
                        total += max * price;
                    });
                    hasItems = true;
                } else {
                    row.querySelectorAll('.material-row').forEach(matRow => {
                        const qty = parseInt(matRow.querySelector('.material-qty').value || 0);
                        if (qty > 0) {
                            total += qty * parseFloat(matRow.dataset.price || 0);
                            hasItems = true;
                        }
                    });
                }
            } else {
                const qty = parseInt(row.querySelector('.return-qty')?.value || 0);
                const unit = parseFloat(row.dataset.unit || 0);
                const rowTotal = qty * unit;
                if (qty > 0) {
                    total += rowTotal;
                    hasItems = true;
                }
                const preview = row.querySelector('.refund-preview');
                if (preview) {
                    if (qty > 0) {
                        preview.textContent = `₱${rowTotal.toFixed(2)}`;
                        preview.className = 'px-4 py-3 text-center text-xs align-top refund-preview text-emerald-600 dark:text-emerald-400 font-semibold';
                    } else {
                        preview.textContent = '—';
                        preview.className = 'px-4 py-3 text-center text-xs align-top refund-preview text-gray-500';
                    }
                }
            }
        });

        document.getElementById('estimatedRefund').textContent = `₱${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        
        const submitBtn = document.getElementById('submitReturnBtn');
        const isValid = hasItems && currentTransactionId;
        submitBtn.toggleAttribute('disabled', !isValid);
        submitBtn.style.opacity = isValid ? '1' : '0.4';
        submitBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
    }

    function addHiddenInput(form, name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }

    function submitReturnForm() {
        const form = document.getElementById('createReturnForm');
        
        if (!currentTransactionId) {
            alert('Please select a transaction first.'); 
            return; 
        }

        form.querySelectorAll('input[type="hidden"][name^="items["]').forEach(el => el.remove());

        let hasItems = false;

        document.querySelectorAll('.return-item').forEach((row) => {
            const itemId = row.dataset.itemId;
            const itemType = row.dataset.itemType;

            if (itemType === 'project') {
                const isWhole = row.querySelector('.whole-project-check')?.checked;
                const restock = row.querySelector('.project-restock')?.checked ? 1 : 0;

                if (isWhole) {
                    addHiddenInput(form, `items[${itemId}][whole_project]`, '1');
                    addHiddenInput(form, `items[${itemId}][restock]`, restock);
                    hasItems = true;
                } else {
                    let hasMaterial = false;
                    row.querySelectorAll('.material-row').forEach(matRow => {
                        const matIndex = matRow.dataset.materialIndex;
                        const qty = parseInt(matRow.querySelector('.material-qty')?.value || 0);
                        if (qty > 0) {
                            addHiddenInput(form, `items[${itemId}][materials][${matIndex}]`, qty);
                            hasMaterial = true;
                        }
                    });
                    if (hasMaterial) {
                        addHiddenInput(form, `items[${itemId}][restock]`, restock);
                        hasItems = true;
                    }
                }
            } else {
                const qty = parseInt(row.querySelector('.return-qty')?.value || 0);
                if (qty > 0) {
                    addHiddenInput(form, `items[${itemId}][qty]`, qty);
                    addHiddenInput(form, `items[${itemId}][restock]`, row.querySelector('.return-restock')?.checked ? 1 : 0);
                    hasItems = true;
                }
            }
        });

        if (!hasItems) {
            alert('Please select at least one item to return.'); 
            return; 
        }

        const reason = form.querySelector('[name="reason"]');
        if (!reason || !reason.value.trim()) {
            alert('Please enter a reason for the return.'); 
            if (reason) reason.focus(); 
            return; 
        }

        form.action = `/transactions/${currentTransactionId}/return`;
        form.submit();
    }

    document.addEventListener('click', function (e) {
        const container = document.getElementById('transactionSearchResults');
        const searchBox = document.getElementById('transactionSearchInput');
        if (container && !container.contains(e.target) && e.target !== searchBox) {
            container.classList.add('hidden');
        }
    });

    document.getElementById('viewReturnModal').addEventListener('click', function (e) { if (e.target === this) toggleViewReturnModal(false); });
    document.getElementById('voidReturnModal').addEventListener('click', function (e) { if (e.target === this) toggleVoidModal(false); });
    document.getElementById('createReturnModal').addEventListener('click', function (e) { if (e.target === this) toggleCreateReturnModal(false); });

    // Directly bind click listener to submit button
    const submitBtn = document.getElementById('submitReturnBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            submitReturnForm();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            toggleViewReturnModal(false);
            toggleVoidModal(false);
            toggleCreateReturnModal(false);
        }
    });

    window.openViewReturnModal = openViewReturnModal;
    window.openVoidReturnModal = openVoidReturnModal;
    window.toggleViewReturnModal = toggleViewReturnModal;
    window.toggleVoidModal = toggleVoidModal;
    window.toggleCreateReturnModal = toggleCreateReturnModal;
    window.resetTransactionSelection = resetTransactionSelection;
    window.toggleProjectWhole = toggleProjectWhole;
    window.updateEstimatedRefund = updateEstimatedRefund;
    window.submitReturnForm = submitReturnForm;
    window.crosscheckSku = crosscheckSku;
    window.clearSkuCrosscheck = clearSkuCrosscheck;
    window.toggleMaterialAccordion = toggleMaterialAccordion;
});
</script>
@endpush