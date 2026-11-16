
@extends('layouts.default')

@section('title', 'Supplier Ledger')

@section('content')

    @php
        $transactionOptions = [
            '' => 'All Transactions',
            'purchase' => 'Purchases',
            'payment' => 'Payments',
            'shortage' => 'Shortage Adjustments',
        ];

        $currentType = request('transaction_type', '');
        $selectedTypeLabel = $transactionOptions[$currentType] ?? 'All Transactions';

        $ledgerPaginator = $ledgers->appends(request()->query());

        $initialNextPageUrl = $ledgerPaginator->nextPageUrl();
        $initialCurrentPage = $ledgerPaginator->currentPage();
        $initialLastPage = $ledgerPaginator->lastPage();
        $initialPerPage = $ledgerPaginator->perPage();
        $initialTotal = $ledgerPaginator->total();

        // Preserve the actual balance sign to distinguish debt from credit.
        $displayBalance = (float) $supplier->balance;
        $absoluteBalance = abs($displayBalance);
    @endphp

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Supplier Ledger
            </h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                Review and monitor supplier transactions, purchases, and payment history.
            </p>
        </div>

        <a href="{{ route('suppliers.index') }}"
           class="inline-flex items-center justify-center gap-2 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 shadow-xs cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Suppliers
        </a>
    </div>

    <!-- Supplier Information -->
    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden mb-8">

        <div class="px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50 dark:bg-[#161B26]/50">

            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 rounded-2xl bg-slate-100 dark:bg-[#1e2536] text-slate-700 dark:text-slate-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Supplier Information
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400">
                        Registered supplier account details
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                        Supplier Name
                    </p>
                    <p class="text-sm font-bold text-gray-900 dark:text-white mt-2">
                        {{ $supplier->name }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                        Contact Information
                    </p>
                    <p class="text-sm text-gray-700 dark:text-slate-300 mt-2 break-all">
                        {{ $supplier->email ?? 'No contact information available' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                        Outstanding Balance
                    </p>

                    <p class="text-xl font-black tracking-tight mt-1
                        @if($displayBalance > 0)
                            text-rose-600 dark:text-rose-400
                        @elseif($displayBalance < 0)
                            text-emerald-600 dark:text-emerald-400
                        @else
                            text-gray-900 dark:text-white
                        @endif">

                        ₱{{ number_format($absoluteBalance, 2) }}
                    </p>

                    <p class="text-xs font-semibold mt-1
                        @if($displayBalance > 0)
                            text-rose-500 dark:text-rose-400
                        @elseif($displayBalance < 0)
                            text-emerald-600 dark:text-emerald-400
                        @else
                            text-gray-400 dark:text-slate-500
                        @endif">

                        @if($displayBalance > 0)
                            Amount to be paid
                        @elseif($displayBalance < 0)
                            Supplier Credit / Overpayment
                        @else
                            Settled — No Outstanding Balance
                        @endif

                    </p>
                </div>

            </div>
        </div>

        <!-- Ledger Section Header -->
        <div class="px-8 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Transaction History
                </h2>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                    Complete record of purchases, payments, and shortage adjustments associated with this supplier.
                </p>
            </div>

            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Financial Transaction Records
            </div>
        </div>

        <!-- Filters -->
        <div class="px-8 py-6 border-y-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50 shrink-0">

            <form id="filterForm" method="GET" action="{{ url()->current() }}"
                  class="flex flex-col xl:flex-row xl:items-center gap-4">

                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">

                    <!-- Live Search -->
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>

                        <input
                            type="text"
                            id="searchInput"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search description, transaction..."
                            autocomplete="off"
                            class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>

                    <!-- Transaction Type Dropdown -->
                    <div class="custom-dropdown relative w-full sm:w-56 shrink-0" id="filter-type-dropdown">

                        <input type="hidden" name="transaction_type" value="{{ $currentType }}">

                        <button type="button"
                                class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400 cursor-pointer text-left">

                            <span class="dropdown-label truncate">
                                {{ $selectedTypeLabel }}
                            </span>

                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] rounded-xl shadow-lg py-1">

                            @foreach ($transactionOptions as $value => $label)
                                <button type="button"
                                        data-value="{{ $value }}"
                                        class="dropdown-option w-full text-left px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">
                                    {{ $label }}
                                </button>
                            @endforeach

                        </div>
                    </div>

                </div>

                <!-- Date Filters and Clear -->
                <div class="flex flex-col sm:flex-row items-center gap-3">

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 whitespace-nowrap">
                            FROM:
                        </span>

                        <input
                            type="date"
                            id="dateFrom"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            max="{{ request('date_to') }}"
                            class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 whitespace-nowrap">
                            TO:
                        </span>

                        <input
                            type="date"
                            id="dateTo"
                            name="date_to"
                            value="{{ request('date_to') }}"
                            min="{{ request('date_from') }}"
                            class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>

                    @if(request()->anyFilled(['date_from', 'date_to', 'transaction_type', 'search']))
                        <a href="{{ url()->current() }}"
                           class="text-xs font-semibold text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2.5 rounded-xl border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26] whitespace-nowrap transition-colors">
                            Clear Filters
                        </a>
                    @endif

                </div>

            </form>
        </div>

        <!-- Scrollable Ledger Table -->
        <div id="tableScrollContainer"
             data-next-page-url="{{ $initialNextPageUrl }}"
             data-current-page="{{ $initialCurrentPage }}"
             data-last-page="{{ $initialLastPage }}"
             data-per-page="{{ $initialPerPage }}"
             data-total="{{ $initialTotal }}"
             class="h-[600px] overflow-x-auto overflow-y-auto custom-scrollbar relative">

            <table class="w-full text-sm text-left">

                <thead id="ledgerTableHead"
                       class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">

                    <tr>
                        <th class="px-8 py-4 whitespace-nowrap bg-gray-50 dark:bg-[#161B26]">
                            Transaction Date
                        </th>

                        <th class="px-8 py-4 whitespace-nowrap bg-gray-50 dark:bg-[#161B26]">
                            Transaction Type
                        </th>

                        <th class="px-8 py-4 min-w-[250px] bg-gray-50 dark:bg-[#161B26]">
                            Description
                        </th>

                        <th class="px-8 py-4 text-center whitespace-nowrap bg-gray-50 dark:bg-[#161B26]">
                            Receipt
                        </th>

                        <th class="px-8 py-4 text-right whitespace-nowrap bg-gray-50 dark:bg-[#161B26]">
                            Transaction Amount
                        </th>
                    </tr>

                </thead>

                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">

                    @forelse ($ledgers as $ledger)

                        <tr data-record-row="true"
                            data-record-id="{{ $ledger->id }}"
                            class="ledger-row hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">

                            <!-- Date -->
                            <td class="px-8 py-5 text-gray-600 dark:text-slate-400 whitespace-nowrap">
                                <div class="font-medium text-gray-900 dark:text-slate-200">
                                    {{ $ledger->created_at->format('M d, Y') }}
                                </div>

                                <div class="text-xs text-gray-400 dark:text-slate-500 mt-1">
                                    {{ $ledger->created_at->format('h:i A') }}
                                </div>
                            </td>

                            <!-- Transaction Type -->
                            <td class="px-8 py-5 whitespace-nowrap">

                                @if($ledger->transaction_type === 'purchase')

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                                        </svg>
                                        Purchase
                                    </span>

                                @elseif($ledger->transaction_type === 'shortage')

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.7 3.86a2 2 0 00-3.4 0z"/>
                                        </svg>
                                        Shortage
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7"/>
                                        </svg>
                                        Payment
                                    </span>

                                @endif

                            </td>

                            <!-- Description -->
                            <td class="px-8 py-5 text-gray-600 dark:text-slate-400">
                                <div class="font-medium text-gray-800 dark:text-slate-200">
                                    {{ $ledger->description ?? '—' }}
                                </div>

                                @if($ledger->transaction_type === 'payment' && $ledger->change_amount > 0)
                                    <div class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-bold text-orange-700 dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-900/50 px-2 py-0.5 rounded-md">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        Change: ₱{{ number_format($ledger->change_amount, 2) }}
                                    </div>
                                @endif

                                @if($ledger->transaction_type === 'shortage')
                                    <div class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 dark:text-amber-400">
                                        Informational adjustment — not deducted again
                                    </div>
                                @endif
                            </td>

                            <!-- Receipt / Voucher / Purchase Order -->
                            <td class="px-8 py-5 text-center">

                                <div class="flex flex-col items-center gap-2">

                                    @if($ledger->transaction_type === 'payment' && $ledger->receipt_path)

                                        <a
                                            href="{{ asset('storage/' . $ledger->receipt_path) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full border border-blue-600 dark:border-blue-500 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 dark:hover:text-white transition whitespace-nowrap">

                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z"/>
                                            </svg>

                                            View Receipt
                                        </a>

                                    @endif

                                    @if($ledger->transaction_type === 'payment')
                                        <a href="{{ route('transaction.payment.receipt', $ledger->id) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:underline whitespace-nowrap">
                                            View Voucher
                                        </a>
                                    @endif

                                    @if($ledger->purchaseOrder)
                                        <a href="{{ route('purchase-orders.pdf', $ledger->purchaseOrder) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap">
                                            View PO
                                        </a>
                                    @endif

                                    @if(
                                        !($ledger->transaction_type === 'payment' && $ledger->receipt_path)
                                        && $ledger->transaction_type !== 'payment'
                                        && !$ledger->purchaseOrder
                                    )
                                        <span class="text-xs text-gray-400 dark:text-slate-500">
                                            —
                                        </span>
                                    @endif

                                </div>

                            </td>

                            <!-- Amount -->
                            <td class="px-8 py-5 text-right whitespace-nowrap">

                                <span class="text-sm font-bold {{ $ledger->transaction_type === 'shortage' ? 'text-amber-700 dark:text-amber-400' : '' }}">
                                    ₱{{ number_format($ledger->amount, 2) }}
                                </span>

                                @if($ledger->transaction_type === 'payment')
                                    <p class="text-[10px] uppercase tracking-wider font-semibold text-orange-600 dark:text-orange-400 mt-1">
                                        Net Paid: ₱{{ number_format($ledger->amount - $ledger->change_amount, 2) }}
                                    </p>
                                @elseif($ledger->transaction_type === 'shortage')
                                    <p class="text-[10px] uppercase tracking-wider font-semibold text-amber-600 dark:text-amber-400 mt-1">
                                        Not included in balance
                                    </p>
                                @endif

                                <p class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 dark:text-slate-500 mt-1">
                                    @if($ledger->transaction_type === 'purchase')
                                        Purchase Recorded
                                    @elseif($ledger->transaction_type === 'shortage')
                                        Shortage Recorded
                                    @else
                                        Payment Recorded
                                    @endif
                                </p>

                            </td>

                        </tr>

                    @empty

                        <tr data-empty-row="true">
                            <td colspan="5" class="px-8 py-16 text-center">

                                <div class="flex flex-col items-center justify-center">

                                    <div class="p-4 rounded-2xl bg-gray-100 dark:bg-[#161B26] mb-4">
                                        <svg class="w-8 h-8 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a2 2 0 011.414 0L19 8v11a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>

                                    <p class="text-sm font-bold text-gray-700 dark:text-slate-300">
                                        No Transactions Found
                                    </p>

                                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">
                                        There are currently no ledger records available for this supplier.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l2.647-2.647z"/>
                </svg>
                Loading more transactions...
            </div>

            <!-- Load Error -->
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more transactions.
                <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">
                    Try again
                </button>
            </div>

        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">

            <div class="whitespace-nowrap" id="resultsText">
                Showing
                <span class="font-semibold text-gray-900 dark:text-white" id="loadedRecordsCount">
                    {{ $ledgers->count() }}
                </span>
                of
                <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordsCount">
                    {{ $ledgers->total() }}
                </span>
                records loaded
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Supplier ledger pagination">

                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">

                    @foreach ($ledgers->getUrlRange(max(1, $ledgers->currentPage() - 2), min($ledgers->lastPage(), $ledgers->currentPage() + 2)) as $page => $url)

                        @if ($page == $ledgers->currentPage())

                            <span data-page="{{ $page }}"
                                  aria-current="page"
                                  class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">
                                {{ $page }}
                            </span>

                        @else

                            <button type="button"
                                    data-page="{{ $page }}"
                                    class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">
                                {{ $page }}
                            </button>

                        @endif

                    @endforeach

                </div>

            </nav>

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

    input[type="date"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: 0.7;
    }

    .dark input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');

    const tableContainer = document.getElementById('tableScrollContainer');
    const tableBody = document.getElementById('tableBody');
    const tableHead = document.getElementById('ledgerTableHead');

    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');

    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const loadedRecordsCount = document.getElementById('loadedRecordsCount');
    const totalRecordsCount = document.getElementById('totalRecordsCount');

    if (!filterForm || !tableContainer || !tableBody || !tableHead || !paginationContainer) {
        return;
    }

    let debounceTimer;

    function submitFilters() {
        const pageInput = filterForm.querySelector('input[name="page"]');

        if (pageInput) {
            pageInput.remove();
        }

        filterForm.requestSubmit();
    }

    document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menu = dropdown.querySelector('.dropdown-menu');
        const chevron = dropdown.querySelector('.dropdown-chevron');
        const label = dropdown.querySelector('.dropdown-label');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        const options = dropdown.querySelectorAll('.dropdown-option');

        if (!trigger || !menu || !hiddenInput) return;

        function closeMenu() {
            menu.classList.add('hidden');

            if (chevron) {
                chevron.classList.remove('rotate-180');
            }
        }

        function openMenu() {
            document.querySelectorAll('.custom-dropdown').forEach(other => {
                if (other !== dropdown) {
                    other.querySelector('.dropdown-menu')?.classList.add('hidden');
                    other.querySelector('.dropdown-chevron')?.classList.remove('rotate-180');
                }
            });

            menu.classList.remove('hidden');

            if (chevron) {
                chevron.classList.add('rotate-180');
            }
        }

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();

            if (menu.classList.contains('hidden')) {
                openMenu();
            } else {
                closeMenu();
            }
        });

        options.forEach(option => {
            option.addEventListener('click', function (event) {
                event.stopPropagation();

                hiddenInput.value = this.dataset.value || '';

                if (label) {
                    label.textContent = this.textContent.trim();
                }

                closeMenu();
                submitFilters();
            });
        });

        document.addEventListener('click', function (event) {
            if (!dropdown.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(function () {
                submitFilters();
            }, 400);
        });
    }

    function submitDateFilters(changedInput) {
        if (!dateFrom || !dateTo) return;

        if (dateFrom.value && dateTo.value && dateFrom.value > dateTo.value) {
            alert('The start date cannot be later than the end date.');
            changedInput.value = '';
            return;
        }

        dateFrom.max = dateTo.value || '';
        dateTo.min = dateFrom.value || '';

        submitFilters();
    }

    if (dateFrom) {
        dateFrom.addEventListener('change', () => submitDateFilters(dateFrom));
    }

    if (dateTo) {
        dateTo.addEventListener('change', () => submitDateFilters(dateTo));
    }

    let nextPageUrl = tableContainer.dataset.nextPageUrl || null;
    let basePage = parseInt(tableContainer.dataset.currentPage || 1, 10);
    let lastPage = parseInt(tableContainer.dataset.lastPage || 1, 10);
    let perPage = parseInt(tableContainer.dataset.perPage || 13, 10);
    let totalRecords = parseInt(tableContainer.dataset.total || 0, 10);
    let loadedPages = 1;
    let isLoading = false;
    let visualPage = basePage;

    function getRecordRows() {
        return Array.from(
            tableBody.querySelectorAll('tr[data-record-row="true"]')
        );
    }

    function updateRecordCounter() {
        const count = getRecordRows().length;

        if (loadedRecordsCount) {
            loadedRecordsCount.textContent = count;
        }

        if (totalRecordsCount) {
            totalRecordsCount.textContent = totalRecords;
        }
    }

    function calculateVisiblePage() {
        const rows = getRecordRows();

        if (rows.length === 0) {
            return basePage;
        }

        const containerRect = tableContainer.getBoundingClientRect();
        const headerHeight = tableHead.getBoundingClientRect().height;
        const anchorPosition = containerRect.top + headerHeight + 2;

        let visibleIndex = 0;

        for (let i = 0; i < rows.length; i++) {
            const rowRect = rows[i].getBoundingClientRect();

            if (rowRect.bottom > anchorPosition) {
                visibleIndex = i;
                break;
            }

            visibleIndex = i;
        }

        return Math.min(
            lastPage,
            basePage + Math.floor(visibleIndex / perPage)
        );
    }

    function updatePaginationNumbers(pageToShow) {
        const loadedLastPage = Math.min(
            lastPage,
            basePage + loadedPages - 1
        );

        const startPage = Math.max(basePage, pageToShow - 2);
        const endPage = Math.min(loadedLastPage, pageToShow + 2);

        let html = '';

        for (let page = startPage; page <= endPage; page++) {
            if (page === pageToShow) {
                html += `<span data-page="${page}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">${page}</span>`;
            } else {
                html += `<button type="button" data-page="${page}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">${page}</button>`;
            }
        }

        paginationContainer.innerHTML = html;
    }

    function updateVisiblePagination() {
        const page = calculateVisiblePage();

        if (page !== visualPage) {
            visualPage = page;
            updatePaginationNumbers(visualPage);
        }
    }

    function goToPage(page) {
        const rows = getRecordRows();
        const rowIndex = (page - basePage) * perPage;

        if (rowIndex < 0 || rowIndex >= rows.length) {
            return;
        }

        const targetRow = rows[rowIndex];
        const containerRect = tableContainer.getBoundingClientRect();
        const rowRect = targetRow.getBoundingClientRect();
        const headerHeight = tableHead.getBoundingClientRect().height;

        const targetScrollTop =
            tableContainer.scrollTop
            + rowRect.top
            - containerRect.top
            - headerHeight;

        tableContainer.scrollTo({
            top: Math.max(0, targetScrollTop),
            behavior: 'smooth'
        });

        visualPage = page;
        updatePaginationNumbers(page);
    }

    async function goToPageWhenLoaded(page) {
        if (page < basePage || page > lastPage) {
            return;
        }

        while (page > basePage + loadedPages - 1 && nextPageUrl) {
            const previousLoadedPages = loadedPages;

            await loadMoreRecords();

            if (loadedPages === previousLoadedPages || !nextPageUrl) {
                break;
            }
        }

        goToPage(page);
    }

    paginationContainer.addEventListener('click', function (event) {
        const button = event.target.closest('button[data-page]');

        if (!button) return;

        const page = parseInt(button.dataset.page, 10);

        if (!Number.isNaN(page)) {
            goToPageWhenLoaded(page);
        }
    });

    async function loadMoreRecords() {
        if (!nextPageUrl || isLoading) {
            return;
        }

        isLoading = true;

        if (loadError) {
            loadError.classList.add('hidden');
        }

        if (loadingIndicator) {
            loadingIndicator.classList.remove('hidden');
        }

        try {
            const response = await fetch(nextPageUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) {
                throw new Error('Request failed: ' + response.status);
            }

            const html = await response.text();
            const parser = new DOMParser();
            const parsedDocument = parser.parseFromString(html, 'text/html');

            const incomingTableBody = parsedDocument.getElementById('tableBody');
            const incomingContainer = parsedDocument.getElementById('tableScrollContainer');

            if (!incomingTableBody || !incomingContainer) {
                throw new Error('Unable to read the next ledger page.');
            }

            const incomingRows = incomingTableBody.querySelectorAll(
                'tr[data-record-row="true"]'
            );

            const emptyRow = tableBody.querySelector('tr[data-empty-row="true"]');

            if (emptyRow) {
                emptyRow.remove();
            }

            const existingIds = new Set(
                getRecordRows().map(row => row.dataset.recordId)
            );

            incomingRows.forEach(row => {
                const recordId = row.dataset.recordId;

                if (!existingIds.has(recordId)) {
                    tableBody.insertAdjacentHTML('beforeend', row.outerHTML);
                }
            });

            const incomingPage = parseInt(
                incomingContainer.dataset.currentPage || 1,
                10
            );

            lastPage = parseInt(
                incomingContainer.dataset.lastPage || lastPage,
                10
            );

            perPage = parseInt(
                incomingContainer.dataset.perPage || perPage,
                10
            );

            totalRecords = parseInt(
                incomingContainer.dataset.total || totalRecords,
                10
            );

            loadedPages = Math.max(
                loadedPages,
                incomingPage - basePage + 1
            );

            nextPageUrl = incomingContainer.dataset.nextPageUrl || null;

            updateRecordCounter();
            updateVisiblePagination();

        } catch (error) {
            console.error('Error loading supplier ledger:', error);

            if (loadError) {
                loadError.classList.remove('hidden');
            }

        } finally {
            isLoading = false;

            if (loadingIndicator) {
                loadingIndicator.classList.add('hidden');
            }
        }
    }

    tableContainer.addEventListener('scroll', function () {
        updateVisiblePagination();

        const distanceFromBottom =
            tableContainer.scrollHeight
            - tableContainer.scrollTop
            - tableContainer.clientHeight;

        if (distanceFromBottom < 100 && nextPageUrl && !isLoading) {
            loadMoreRecords();
        }
    });

    if (retryLoadButton) {
        retryLoadButton.addEventListener('click', function () {
            loadMoreRecords();
        });
    }

    updateRecordCounter();
    updatePaginationNumbers(visualPage);

});
</script>
@endpush
