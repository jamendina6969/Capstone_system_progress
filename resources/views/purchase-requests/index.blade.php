@extends('layouts.default')

@section('title', 'Purchase Requests')

@section('content')

@php
    // Determine initial labels for the dropdowns
    $currentStatus = request('status', '');
    $statusOptions = [
        '' => 'All Statuses',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'converted' => 'Converted',
    ];
    $selectedStatusLabel = $statusOptions[$currentStatus] ?? 'All Statuses';

    $suppliers = \App\Models\Supplier::where('status', 'active')->orderBy('name')->get();
    $selectedSupplierLabel = 'Select a Supplier';
@endphp

@if(request()->filled('id'))
    <div class="mb-6 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 flex items-center justify-between">
        <p class="text-sm text-blue-800 dark:text-blue-300 font-medium">
            Showing details for a specific request.
        </p>
        <a href="{{ route('purchase-requests.index') }}" class="text-xs font-bold px-4 py-2 bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] rounded-full shadow-xs hover:bg-gray-50 dark:hover:bg-[#1e2536]/50">
            View All Requests
        </a>
    </div>
@endif

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Purchase Requests</h1>
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
            Submit and track stock replenishment requests for approval
        </p>
    </div>

    <button type="button" onclick="togglePRModal(true)"
        class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">
        + New Purchase Request
    </button>
</div>

@if(auth()->user()->role === 'manager' || auth()->user()->role === 'admin')
    <div class="mb-6 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-900 px-6 py-4 text-sm text-blue-800 dark:text-blue-300 flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <strong>Workflow reminder:</strong>
            Approve purchase requests here, then convert approved requests into Purchase Orders.
            <a href="{{ route('purchase-orders.index') }}" class="underline font-semibold">
                Go to Purchase Orders &rarr;
            </a>
        </div>
    </div>
@endif

<div id="ajaxAlert" class="hidden mb-6 rounded-xl px-4 py-3 text-sm font-medium"></div>

<!-- Main Container -->
<div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-[calc(100vh-220px)] mb-4">
    
    <!-- Filter Section -->
    <div class="shrink-0 px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
        <form id="filterForm" method="GET" action="{{ route('purchase-requests.index') }}" class="flex flex-col xl:flex-row xl:items-center gap-4">
            
            <!-- Search & Group -->
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search PR #, requester, status..." autocomplete="off"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
            </div>

            <!-- Date & Status Group -->
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 whitespace-nowrap">FROM:</span>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()"
                           class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 whitespace-nowrap">TO:</span>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()"
                           class="w-full sm:w-auto rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="custom-dropdown relative w-full sm:w-40" id="filter-status-dropdown">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedStatusLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            @foreach ($statusOptions as $value => $label)
                                <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if (request()->anyFilled(['date_from', 'date_to', 'status', 'search']))
                    <a href="{{ route('purchase-requests.index') }}" class="text-xs font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#161B26]">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Scrollable Table -->
    <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
        <table class="w-full text-sm text-left relative">
            <thead id="prTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                <tr>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">PR Number</th>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Requested By</th>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Date</th>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Reason</th>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Status</th>
                    <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right">Actions</th>
                </tr>
            </thead>

            <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                @include('purchase-requests.partials.rows', ['requests' => $requests])
            </tbody>
        </table>

        {{-- LOADING --}}
        <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
            <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            Loading more requests...
        </div>

        {{-- ERROR --}}
        <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
            Unable to load more requests. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
        </div>
    </div>

    <!-- Pagination Footer -->
    <div class="shrink-0 px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
        <div class="whitespace-nowrap" id="resultsText">
            @if($requests->total() > 0)
                Showing
                <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $requests->firstItem() }}</span>
                to
                <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $requests->lastItem() }}</span>
                of
                <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $requests->total() }}</span>
                requests
            @else
                No requests found
            @endif
        </div>

        <nav class="flex items-center gap-1.5 max-w-full" aria-label="Purchase request pagination">
            <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                @foreach ($requests->getUrlRange(max(1, $requests->currentPage() - 2), min($requests->lastPage(), $requests->currentPage() + 2)) as $page => $url)
                    @if ($page == $requests->currentPage())
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
<div id="actionConfirmModal" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <h3 id="actionModalTitle" class="text-lg font-bold text-gray-900 dark:text-white"></h3>
            <button type="button" onclick="toggleActionModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>

        <p id="actionModalMessage" class="text-sm text-gray-600 dark:text-slate-400 mb-6"></p>

        <form id="actionForm" method="POST" action="">
            @csrf
            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Manager Notes (Optional)</label>
                <textarea name="manager_notes" rows="2" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm" placeholder="Add a reason or note..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="toggleActionModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white transition-all shadow-xs">
                    Cancel
                </button>
                <button type="submit" id="actionSubmitBtn" class="text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs border-2">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

{{-- VIEW PURCHASE REQUEST MODAL --}}
<div id="viewPRModal" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-4xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                Purchase Request Details
            </h3>
            <button type="button" onclick="toggleViewPRModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">PR Number</p>
                    <p id="view_pr_number" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Requested By</p>
                    <p id="view_pr_user" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Status</p>
                    <p id="view_pr_status" class="text-sm font-semibold"></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-500">Date</p>
                    <p id="view_pr_date" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                </div>
            </div>

            <div>
                <p class="text-[10px] uppercase font-bold text-gray-500">Reason</p>
                <p id="view_pr_reason" class="text-sm text-gray-800 dark:text-slate-300 mt-1 p-3 bg-gray-50 dark:bg-[#161B26] rounded-xl border border-gray-200 dark:border-[#1e2536]"></p>
            </div>

            <div>
                <p class="text-[10px] uppercase font-bold text-gray-500">Preferred Supplier</p>
                <p id="view_pr_supplier" class="text-sm font-semibold text-gray-900 dark:text-white mt-1"></p>
            </div>

            <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                <p class="text-[10px] uppercase font-bold text-gray-500 mb-2">Requested Items</p>
                <div class="border border-gray-200 dark:border-[#1e2536] rounded-xl overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3 text-center">Quantity</th>
                                <th class="px-4 py-3">Notes</th>
                                <th class="px-4 py-3">Item Status</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="view_pr_items_body" class="divide-y divide-gray-100 dark:divide-[#1e2536]"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-6 mt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
            <button type="button" onclick="toggleViewPRModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white transition-all shadow-xs">
                Close
            </button>
        </div>
    </div>
</div>

{{-- REJECT ITEM MODAL --}}
<div id="rejectItemModal" class="fixed inset-0 z-[80] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Reject Item</h3>
            <button type="button" onclick="toggleRejectItemModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>

        <p class="text-sm text-gray-600 dark:text-slate-400 mb-4">Why are you removing this item from the request?</p>

        <form id="rejectItemForm" method="POST" action="">
            @csrf
            <div class="mb-6">
                <textarea name="rejection_reason" rows="3" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm" placeholder="E.g. Out of budget, wrong product..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="toggleRejectItemModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white transition-all shadow-xs">
                    Cancel
                </button>
                <button type="submit" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">
                    Reject Item
                </button>
            </div>
        </form>
    </div>
</div>

{{-- CREATE PURCHASE REQUEST MODAL --}}
<div id="prModal" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-3xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                Create Purchase Request
            </h3>
            <button type="button" onclick="togglePRModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>

        <form id="prForm" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reason</label>
                <textarea name="reason" rows="2" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm" placeholder="Why is this request needed?"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Preferred Supplier (Optional)</label>
                <div class="custom-dropdown relative w-full" id="modal-supplier-dropdown">
                    <input type="hidden" name="supplier_id" value="">
                    <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                        <span class="dropdown-label truncate">Select a Supplier</span>
                        <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                        <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Select a Supplier</button>
                        @foreach($suppliers as $supplier)
                            <button type="button" data-value="{{ $supplier->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $supplier->name }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                <h4 class="font-bold text-gray-900 dark:text-white mb-3">Requested Items</h4>
                <div id="itemsContainer" class="space-y-3"></div>
                <button type="button" onclick="addItemRow()" class="mt-2 text-xs font-semibold text-emerald-600 hover:text-emerald-700">+ Add Another Item</button>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="togglePRModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white transition-all shadow-xs">Cancel</button>
                <button type="submit" id="submitPRBtn" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">Submit Request</button>
            </div>
        </form>
    </div>
</div>

{{-- CONVERT TO PO CONFIRMATION MODAL --}}
@if(in_array(auth()->user()->role, ['admin', 'manager']))
<div id="convertPOModal" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Convert to Purchase Order</h3>
            <button type="button" onclick="toggleConvertModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>

        <p class="text-sm text-gray-600 dark:text-slate-400 mb-6">
            Are you sure you want to convert <strong id="convert_pr_number" class="text-gray-900 dark:text-white"></strong> into a Purchase Order? You will be redirected to the PO creation form.
        </p>

        <form id="convertForm" method="GET" action="">
            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="toggleConvertModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2 rounded-full hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white transition-all shadow-xs">Cancel</button>
                <button type="submit" id="convertBtn" class="bg-white border-2 border-blue-600 text-blue-700 hover:bg-blue-600 hover:text-white dark:bg-[#10141D] dark:border-blue-600/80 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">Yes, Convert</button>
            </div>
        </form>
    </div>
</div>
@endif

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
    // =====================================================
    // CUSTOM DROPDOWNS
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
                    if (dropdown.id === 'filter-status-dropdown') dropdown.closest('form')?.submit();
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
        const tableHead = document.getElementById('prTableHead');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const loadError = document.getElementById('loadError');
        const retryLoadButton = document.getElementById('retryLoadButton');
        const paginationContainer = document.getElementById('paginationNumbersContainer');
        const resultsText = document.getElementById('resultsText');

        let nextPageUrl = @json($requests->nextPageUrl());
        let currentPage = {{ $requests->currentPage() }};
        let lastPage = {{ $requests->lastPage() }};
        let totalRecords = {{ $requests->total() }};
        let perPage = {{ $requests->perPage() }};
        let initialPage = {{ $requests->currentPage() }};
        let initialStart = {{ $requests->firstItem() ?? 0 }} || 0;

        let isLoading = false;
        let visualPage = initialPage;

        function updateResultsCounter() {
            const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
            if (totalRecords === 0 || rowsLoaded === 0) {
                resultsText.textContent = 'No requests found.';
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
                requests
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
                console.error('Error loading requests:', error);
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
    });

    // =====================================================
    // REST OF EXISTING LOGIC (Modals, Create Form, etc.)
    // =====================================================
    const allProducts = @json(\App\Models\Product::orderBy('product_name')->select('id', 'product_name')->get());
    let itemIndex = 0;
    const currentUserRole = "{{ auth()->user()->role }}";

    function toggleActionModal(show) {
        const modal = document.getElementById('actionConfirmModal');
        modal.classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
        if (!show) document.getElementById('actionForm').reset();
    }

    function openActionModal(action, id, prNumber) {
        const form = document.getElementById('actionForm');
        const title = document.getElementById('actionModalTitle');
        const message = document.getElementById('actionModalMessage');
        const submitBtn = document.getElementById('actionSubmitBtn');

        if (action === 'approve') {
            title.textContent = 'Approve Purchase Request';
            message.innerHTML = `Are you sure you want to approve <strong>${prNumber}</strong>? This will allow it to be converted into a Purchase Order.`;
            form.action = `/purchase-requests/${id}/approve`;
            submitBtn.className = 'bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs';
            submitBtn.textContent = 'Yes, Approve';
        } else {
            title.textContent = 'Reject Purchase Request';
            message.innerHTML = `Are you sure you want to reject <strong>${prNumber}</strong>? This action cannot be undone.`;
            form.action = `/purchase-requests/${id}/reject`;
            submitBtn.className = 'bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs';
            submitBtn.textContent = 'Yes, Reject';
        }
        toggleActionModal(true);
    }

    function toggleRejectItemModal(show) {
        const modal = document.getElementById('rejectItemModal');
        modal.classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
        if (!show) document.getElementById('rejectItemForm').reset();
    }

    function openRejectItemModal(prId, itemId) {
        document.getElementById('rejectItemForm').action = `/purchase-requests/${prId}/items/${itemId}/reject`;
        toggleRejectItemModal(true);
    }

    function togglePRModal(show) {
        const modal = document.getElementById('prModal');
        modal.classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
        if (show && document.getElementById('itemsContainer').children.length === 0) addItemRow();
        if (!show) {
            document.getElementById('prForm').reset();
            document.getElementById('itemsContainer').innerHTML = '';
            itemIndex = 0;
        }
    }

    function addItemRow() {
        const container = document.getElementById('itemsContainer');
        const row = document.createElement('div');
        row.className = 'flex flex-wrap gap-2 items-end item-row mb-3';
        row.innerHTML = `
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Product</label>
                <select name="items[${itemIndex}][product_id]" required class="w-full h-[36px] rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-2 py-1 text-xs">
                    <option value="">Select Product</option>
                    ${allProducts.map(p => `<option value="${p.id}">${p.product_name}</option>`).join('')}
                </select>
            </div>
            <div class="w-24">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Quantity</label>
                <input type="number" name="items[${itemIndex}][quantity]" required min="1" class="w-full h-[36px] rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-2 py-1 text-xs text-center">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Notes (Optional)</label>
                <input type="text" name="items[${itemIndex}][notes]" class="w-full h-[36px] rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-2 py-1 text-xs">
            </div>
            <div class="pb-0.5">
                <button type="button" onclick="this.closest('.item-row').remove()" class="bg-rose-100 text-rose-600 hover:bg-rose-200 h-[36px] w-[36px] flex items-center justify-center rounded-xl">&times;</button>
            </div>
        `;
        container.appendChild(row);
        itemIndex++;
    }

    document.getElementById('prForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitPRBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Submitting...';
        btn.disabled = true;

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        data.items = [];
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row) => {
            const productId = row.querySelector('select').value;
            const qty = row.querySelector('input[type="number"]').value;
            const notes = row.querySelector('input[type="text"]').value;
            if (productId && qty) data.items.push({ product_id: productId, quantity: qty, notes: notes });
        });

        fetch("{{ route('purchase-requests.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json().then(res => ({ status: response.status, body: res })))
        .then(res => {
            if (res.status === 200 || res.status === 201) {
                togglePRModal(false);
                showAlert('success', res.body.message || 'Purchase Request submitted successfully.');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                let errorMsg = res.body.message || 'Validation failed.';
                if (res.body.errors) errorMsg = Object.values(res.body.errors).flat().join('<br>');
                showAlert('error', errorMsg);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            showAlert('error', 'An unexpected error occurred. Please try again.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });

    function toggleViewPRModal(show) {
        const modal = document.getElementById('viewPRModal');
        modal.classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
    }

    function openViewPRModal(prData) {
        document.getElementById('view_pr_number').textContent = prData.pr_number;
        document.getElementById('view_pr_user').textContent = prData.user ? `${prData.user.firstname || ''} ${prData.user.lastname || ''}`.trim() : 'N/A';
        document.getElementById('view_pr_date').textContent = new Date(prData.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        document.getElementById('view_pr_reason').textContent = prData.reason || '—';
        document.getElementById('view_pr_supplier').textContent = prData.supplier?.name || 'None specified';

        const statusEl = document.getElementById('view_pr_status');
        statusEl.textContent = (prData.status || '').toUpperCase();
        if (prData.status === 'pending') statusEl.className = 'text-sm font-semibold text-amber-600';
        else if (prData.status === 'approved') statusEl.className = 'text-sm font-semibold text-emerald-600';
        else if (prData.status === 'rejected') statusEl.className = 'text-sm font-semibold text-rose-600';
        else statusEl.className = 'text-sm font-semibold text-blue-600';

        const tbody = document.getElementById('view_pr_items_body');
        tbody.innerHTML = '';
        if (prData.items && prData.items.length > 0) {
            prData.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 dark:hover:bg-[#1e2536]/30';
                let itemStatusHtml = '';
                if (item.status === 'pending') itemStatusHtml = `<span class="px-2 py-1 rounded text-[10px] font-bold bg-amber-100 text-amber-700">PENDING</span>`;
                else if (item.status === 'approved') itemStatusHtml = `<span class="px-2 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">APPROVED</span>`;
                else if (item.status === 'rejected') itemStatusHtml = `<span class="px-2 py-1 rounded text-[10px] font-bold bg-rose-100 text-rose-700">REJECTED</span>`;
                else itemStatusHtml = `<span class="px-2 py-1 rounded text-[10px] font-bold bg-gray-100 text-gray-700">N/A</span>`;

                let actionHtml = '—';
                if (item.status === 'pending' && ['admin', 'manager'].includes(currentUserRole)) {
                    actionHtml = `<button type="button" onclick="openRejectItemModal(${prData.id}, ${item.id})" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-[10px] font-semibold px-2 py-1 rounded-md transition-all shadow-xs">Reject Item</button>`;
                }

                tr.innerHTML = `
                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                        ${item.product?.product_name || 'Deleted Product'}
                        ${item.rejection_reason ? `<br><span class="text-[10px] text-rose-500 font-normal">Reason: ${item.rejection_reason}</span>` : ''}
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">${item.quantity}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">${item.notes || '—'}</td>
                    <td class="px-4 py-3">${itemStatusHtml}</td>
                    <td class="px-4 py-3 text-right">${actionHtml}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No items found.</td></tr>`;
        }
        toggleViewPRModal(true);
    }

    function toggleConvertModal(show) {
        const modal = document.getElementById('convertPOModal');
        modal.classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
    }

    function openConvertModal(id, prNumber) {
        document.getElementById('convert_pr_number').textContent = prNumber;
        document.getElementById('convertForm').action = `/purchase-requests/${id}/convert`;
        const btn = document.getElementById('convertBtn');
        btn.innerHTML = 'Yes, Convert';
        btn.disabled = false;
        toggleConvertModal(true);
    }

    document.getElementById('convertForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('convertBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Redirecting...';
        btn.disabled = true;

        fetch(this.action, { method: 'GET', headers: { 'Accept': 'application/json' } })
        .then(response => {
            if (response.ok && response.url) window.location.href = response.url;
            else throw new Error('Failed to convert.');
        })
        .catch(err => {
            console.error(err);
            showAlert('error', 'An error occurred during conversion. Please try again.');
            toggleConvertModal(false);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });

    function showAlert(type, message) {
        const alertBox = document.getElementById('ajaxAlert');
        alertBox.innerHTML = message;
        alertBox.className = `mb-6 rounded-xl px-4 py-3 text-sm font-medium border-2 ${
            type === 'success' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200'
        }`;
        alertBox.classList.remove('hidden');
        setTimeout(() => alertBox.classList.add('hidden'), 5000);
    }
</script>
@endpush