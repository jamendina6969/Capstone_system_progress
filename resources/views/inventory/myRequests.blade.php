@extends('layouts.default')

@section('title', 'Restock Requests')

@section('content')

    @php
        $statusOptions = [
            '' => 'All Delivery Statuses',
            'pending' => 'Pending',
            'in_transit' => 'In Transit',
            'received' => 'Received',
            'not_approved' => 'Not Approved',
        ];

        $currentStatus = request('status', '');
        $selectedStatusLabel = $statusOptions[$currentStatus] ?? 'All Delivery Statuses';
    @endphp

    @if ($errors->any() && !$errors->has('action_password'))
        <div class="mb-4 rounded-xl bg-red-100 dark:bg-rose-950/85 text-red-700 dark:text-rose-300 border border-red-200 dark:border-rose-900 px-4 py-3 text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-180px)] lg:min-h-[500px] mb-4">

        <!-- HEADER -->
        <div class="shrink-0 px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Restock Requests</h1>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                    {{ in_array(auth()->user()->role, ['admin', 'manager']) ? 'View and manage all restock requests.' : 'Submit and track your stock replenishment requests.' }}
                </p>
            </div>
            <button type="button" onclick="openRequestModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 shadow-xs cursor-pointer flex items-center gap-2 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Request Stocks
            </button>
        </div>

        <!-- FILTERS -->
        <div class="shrink-0 px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-100 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/30">
            <form method="GET" action="{{ route('inventory.myRequests') }}" id="filterForm" class="flex flex-col xl:flex-row flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px] w-full xl:w-auto">
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search date, requester, product, status..." class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:border-slate-400">
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
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-300 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                        <span class="dropdown-label truncate">{{ $selectedStatusLabel }}</span>
                        <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                        @foreach ($statusOptions as $value => $label)
                            <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                @if (request()->anyFilled(['status', 'search', 'date_from', 'date_to']))
                    <a href="{{ route('inventory.myRequests') }}" class="text-sm font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2 rounded-lg transition-colors whitespace-nowrap">Clear</a>
                @endif
            </form>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full min-w-[760px] text-sm text-left relative">
                <thead id="requestsTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Date</th>
                        <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Requested By</th>
                        <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Product</th>
                        <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Quantity</th>
                        <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Request Status</th>
                        <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Delivery Status</th>
                        <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Reviewed By</th>
                        <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('inventory.partials.my-requests-rows', ['requests' => $requests])
                </tbody>
            </table>

            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more requests...
            </div>

            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more requests. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-4 sm:px-6 py-3.5 sm:py-4 border-t border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
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

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Requests pagination">
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
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Location</span><span id="viewLocation" class="font-semibold text-gray-900 dark:text-white"></span></div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Request Status</span><span id="viewStatus" class="font-semibold text-gray-900 dark:text-white"></span></div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Delivery Status</span><span id="viewDelivery" class="font-semibold text-gray-900 dark:text-white"></span></div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]"><span class="text-gray-500 dark:text-slate-400">Reviewed By</span><span id="viewReviewer" class="font-semibold text-gray-900 dark:text-white"></span></div>

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

    <!-- REQUEST STOCKS MODAL -->
    <div id="requestModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs hidden">
        <div class="bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-2xl p-6 w-full max-w-lg shadow-xl mx-4">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-[#1e2536]">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">New Restock Request</h3>
                <button type="button" onclick="closeRequestModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('inventory.storeRequest') }}" method="POST" class="space-y-4 pt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-slate-400 mb-1.5">Product</label>
                    <select name="product_id" required class="w-full rounded-xl bg-gray-50 dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Select a product...</option>
                        @foreach (\App\Models\Product::orderBy('product_name')->get() as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->product_name }} (Store: {{ $p->store_quantity }}, WH: {{ $p->warehouse_quantity }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-slate-400 mb-1.5">Location</label>
                    <select name="location" required class="w-full rounded-xl bg-gray-50 dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        @if (auth()->user()->role === 'staff')
                            <option value="store">Store</option>
                        @elseif (auth()->user()->role === 'warehouse_staff')
                            <option value="warehouse">Warehouse</option>
                        @else
                            <option value="store">Store</option>
                            <option value="warehouse">Warehouse</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-slate-400 mb-1.5">Quantity</label>
                    <input type="number" name="quantity" min="1" value="{{ old('quantity') }}" required class="w-full rounded-xl bg-gray-50 dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Enter quantity">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-slate-400 mb-1.5">Note / Reason (Optional)</label>
                    <textarea name="note" rows="3" class="w-full rounded-xl bg-gray-50 dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Add any details or reason for request...">{{ old('note') }}</textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="closeRequestModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MANAGER VERIFICATION MODAL -->
    <div id="receiveVerificationModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-xs hidden" onclick="closeReceiveModal()">
        <div class="bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-2xl p-6 w-full max-w-md shadow-xl mx-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-[#1e2536]">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manager Verification</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">High-level action requires authorization.</p>
                </div>
                <button type="button" onclick="closeReceiveModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="receiveForm" method="POST" action="#" data-route-template="{{ route('inventory.receiveRequest', ['movement' => '__MOVEMENT_ID__']) }}" onsubmit="return validateReceiveForm(event)">
                @csrf
                <div class="space-y-4 pt-4">
                    @if ($errors->has('action_password'))
                        <div class="p-3 rounded-xl bg-red-50 dark:bg-rose-900/20 border border-red-200 dark:border-rose-800 text-xs text-red-800 dark:text-rose-300">{{ $errors->first('action_password') }}</div>
                    @endif
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300">
                        <span class="font-bold">Note:</span> Enter the Manager/Admin action password to confirm receipt. This is not your personal login password.
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-slate-400 mb-1.5">Action Password</label>
                        <input type="password" name="action_password" required autocomplete="off" placeholder="Enter manager override password" class="w-full rounded-xl bg-gray-50 dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-[#1e2536]">
                        <button type="button" onclick="closeReceiveModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                        <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Confirm Receipt</button>
                    </div>
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
    const tableHead = document.getElementById('requestsTableHead');
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
        if (totalRecords === 0 || rowsLoaded === 0) { resultsText.textContent = 'No requests found.'; return; }
        const firstRecord = initialStart;
        const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
        resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> requests`;
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
                    if (row.dataset.recordRow === 'true') tableBody.appendChild(row);
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

    retryLoadButton?.addEventListener('click', () => loadMoreRecords());

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
        const savedCursorPos = sessionStorage.getItem('restockRequestSearchCursorPos');
        if (savedCursorPos !== null) {
            searchInput.focus();
            searchInput.setSelectionRange(parseInt(savedCursorPos), parseInt(savedCursorPos));
            sessionStorage.removeItem('restockRequestSearchCursorPos');
        } else if (searchInput.value.length > 0) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            sessionStorage.setItem('restockRequestSearchCursorPos', searchInput.selectionStart);
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
    document.getElementById('viewLocation').textContent = req.type === 'restock_warehouse' ? 'Warehouse' : 'Store';
    document.getElementById('viewStatus').textContent = req.status.charAt(0).toUpperCase() + req.status.slice(1);
    document.getElementById('viewDelivery').textContent = req.delivery_status ? req.delivery_status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : '—';
    document.getElementById('viewReviewer').textContent = req.approver ? `${req.approver.firstname || ''} ${req.approver.lastname || ''}` : '—';
    document.getElementById('viewNote').textContent = req.note || 'No notes provided.';
    document.getElementById('viewDetailsModal').classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('viewDetailsModal').classList.add('hidden');
}

function openRequestModal() {
    document.getElementById('requestModal').classList.remove('hidden');
}

function closeRequestModal() {
    document.getElementById('requestModal').classList.add('hidden');
}

function openReceiveModal(requestId) {
    const modal = document.getElementById('receiveVerificationModal');
    const form = document.getElementById('receiveForm');
    const passwordInput = form.querySelector('input[name="action_password"]');
    const routeTemplate = form.dataset.routeTemplate;
    if (!requestId || !routeTemplate) { alert('Unable to prepare the receipt request. Please refresh the page and try again.'); return; }
    const receiveUrl = routeTemplate.replace('__MOVEMENT_ID__', encodeURIComponent(requestId));
    form.setAttribute('action', receiveUrl);
    form.setAttribute('method', 'POST');
    passwordInput.value = '';
    modal.classList.remove('hidden');
    setTimeout(function () { passwordInput.focus(); }, 100);
}

function validateReceiveForm(event) {
    const form = event.target;
    const action = form.getAttribute('action');
    const method = form.getAttribute('method');
    if (!action || action === '#' || method.toUpperCase() !== 'POST') {
        event.preventDefault();
        alert('Invalid receipt request. Please close the modal and click Mark as Received again.');
        return false;
    }
    form.method = 'POST';
    return true;
}

function closeReceiveModal() {
    const modal = document.getElementById('receiveVerificationModal');
    const form = document.getElementById('receiveForm');
    if (!modal || !form) return;
    modal.classList.add('hidden');
    form.querySelector('input[name="action_password"]').value = '';
    form.setAttribute('action', '#');
}

@if ($errors->has('action_password'))
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('receiveVerificationModal').classList.remove('hidden');
    });
@endif

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeViewModal();
        closeRequestModal();
        closeReceiveModal();
    }
});
</script>
@endpush