@extends('layouts.default')

@section('title', 'Inventory')

@section('content')

    @php
        $currentCategoryId = request('category_id');
        $selectedCategoryLabel = 'All Categories';
        if ($currentCategoryId) {
            foreach ($categories as $category) {
                if ((string) $currentCategoryId === (string) $category->id) {
                    $selectedCategoryLabel = $category->name;
                    break;
                }
            }
        }

        // Updated Status Options
        $statusOptions = [
            '' => 'All Status',
            'store_low' => 'Store: Low Stock',
            'store_out' => 'Store: Out of Stock',
            'wh_low' => 'Warehouse: Low Stock',
            'wh_out' => 'Warehouse: Out of Stock',
            'total_low' => 'Total: Low Stock',
            'total_out' => 'Total: Out of Stock',
        ];
        $currentStatus = request('status', '');
        $selectedStatusLabel = $statusOptions[$currentStatus] ?? 'All Status';

        $sortOptions = [
            'product_name' => 'Sort: Name', 'product_sku' => 'Sort: SKU / Code',
            'store_quantity' => 'Sort: Store Qty', 'warehouse_quantity' => 'Sort: Warehouse Qty',
            'selling_price' => 'Sort: Price', 'created_at' => 'Sort: Newest',
        ];
        $currentSort = request('sort', 'product_name');
        $selectedSortLabel = $sortOptions[$currentSort] ?? 'Sort: Name';

        $directionOptions = ['asc' => 'Ascending', 'desc' => 'Descending'];
        $currentDirection = request('direction', 'asc');
        $selectedDirectionLabel = $directionOptions[$currentDirection] ?? 'Ascending';
    @endphp

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Inventory Management</h1>

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-180px)] min-h-[550px] mb-4">

        <!-- Filters & Header (shrink-0) -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Product Inventory</h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage store and warehouse stock levels, items, and transfers</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    {{-- Export & Print Report Buttons --}}
                    <div class="flex items-center gap-2">
                        <a href="{{ route('inventory.export', request()->query()) }}" target="_blank"
                           class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-4 sm:px-5 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer inline-flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Export CSV
                        </a>
                        <button type="button" onclick="openPrintModal()"
                           class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-4 sm:px-5 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer inline-flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print Report
                        </button>
                    </div>

                    @include('partials.viewToggle')
                    <a href="{{ route('inventory.restockBatch') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer inline-flex items-center whitespace-nowrap">+ Restock Mode</a>
                </div>
            </div>

            <form id="filterForm" method="GET" class="flex flex-col xl:flex-row xl:items-center gap-4">
                
                {{-- LEFT SIDE FILTERS (Search + Category) --}}
                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search products or SKU..." class="w-full sm:w-72 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:border-slate-400">

                    <div class="custom-dropdown relative w-full sm:w-56 shrink-0" id="filter-category-dropdown">
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedCategoryLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">All Categories</button>
                            @foreach ($categories as $category)
                                <button type="button" data-value="{{ $category->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $category->name }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- RIGHT SIDE FILTERS (Status + Sort + Direction + Clear) --}}
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    
                    {{-- STATUS DROPDOWN --}}
                    <div class="custom-dropdown relative w-full sm:w-48 shrink-0" id="filter-status-dropdown">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedStatusLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            @foreach ($statusOptions as $value => $label)
                                <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- SORT DROPDOWN --}}
                    <div class="custom-dropdown relative w-full sm:w-48 shrink-0" id="filter-sort-dropdown">
                        <input type="hidden" name="sort" value="{{ $currentSort }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedSortLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            @foreach ($sortOptions as $value => $label)
                                <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- DIRECTION DROPDOWN --}}
                    <div class="custom-dropdown relative w-full sm:w-40 shrink-0" id="filter-direction-dropdown">
                        <input type="hidden" name="direction" value="{{ $currentDirection }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedDirectionLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            @foreach ($directionOptions as $value => $label)
                                <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    @if (request()->anyFilled(['search', 'category_id', 'status']) || request('sort', 'product_name') !== 'product_name')
                        <a href="{{ request()->url() }}" class="text-sm font-semibold text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536] px-4 py-2.5 rounded-xl transition-colors flex items-center whitespace-nowrap">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Scrollable Body -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <div id="productResultsContainer" data-view="grid">
                <div class="view-list">
                    <table class="w-full min-w-[1100px] text-sm text-left relative">
                        <thead id="productTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Product</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Product SKU</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Category</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Cost</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Price</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Store Qty</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Warehouse Qty</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Store Status</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Warehouse Status</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Total Status</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536] align-top">
                            @include('inventory.partials.rows', ['products' => $products])
                        </tbody>
                    </table>
                </div>

                <div id="productGrid" class="view-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4 sm:gap-5 p-4 sm:p-6">
                    @include('inventory.partials.cards', ['products' => $products])
                </div>
            </div>

            {{-- LOADING --}}
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more products...
            </div>

            {{-- ERROR --}}
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more products. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
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

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Inventory pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                        @if ($page == $products->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- PRINT REPORT MODAL -->
    <div id="printReportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs hidden" onclick="closePrintModal()">
        <div class="bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-2xl p-6 w-full max-w-5xl shadow-2xl mx-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-[#1e2536] mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Report Preview
                </h3>
                <button type="button" onclick="closePrintModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white cursor-pointer p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div id="printModalBody">
                <div class="py-12 text-center text-gray-400">Loading report preview...</div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-[#1e2536] mt-4">
                <button type="button" onclick="closePrintModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Close</button>
            </div>
        </div>
    </div>

    @include('partials.productDetailModal')

@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #334155; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }

    #productResultsContainer .view-list, #productResultsContainer .view-grid { display: none !important; }
    #productResultsContainer[data-view="list"] .view-list { display: block !important; }
    #productResultsContainer[data-view="grid"] .view-grid { display: grid !important; }

    /* List view — matches the products module's table (horizontal scroll on narrow screens) */
    #productResultsContainer[data-view="list"] table {
        width: 100%;
        min-width: 1250px;
        border-collapse: collapse;
    }

    #paginationNumbersContainer { scrollbar-width: none; -ms-overflow-style: none; }
    #paginationNumbersContainer::-webkit-scrollbar { display: none; }

    @media (max-width: 639px) {
        #productResultsContainer[data-view="list"] .view-list { overflow-x: auto; }
    }

    /* =====================================================
       PRINT STYLES: HIDE EVERYTHING EXCEPT THE PRINT MODAL
       ===================================================== */
    @media print {
        body * {
            visibility: hidden !important;
        }
        #printReportModal, #printReportModal * {
            visibility: visible !important;
        }
        #printReportModal {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: auto !important;
            background: white !important;
            backdrop-filter: none !important;
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
        }
        #printReportModal > div {
            border: none !important;
            box-shadow: none !important;
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            background: white !important;
        }
        #printReportModal button {
            display: none !important;
        }
        #printModalBody .max-h-\[60vh\] {
            max-height: none !important;
            overflow: visible !important;
            border: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function openPrintModal() {
    const modal = document.getElementById('printReportModal');
    const modalBody = document.getElementById('printModalBody');
    modalBody.innerHTML = '<div class="py-12 text-center text-gray-400">Loading report preview...</div>';
    modal.classList.remove('hidden');

    const currentParams = new URLSearchParams(window.location.search);
    currentParams.set('modal', '1');

    fetch(`{{ route('inventory.export') }}?` + currentParams.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.text())
    .then(html => {
        modalBody.innerHTML = html;
    })
    .catch(err => {
        modalBody.innerHTML = '<div class="py-8 text-center text-rose-500 text-sm">Failed to load print preview. Please try again.</div>';
    });
}

function closePrintModal() {
    document.getElementById('printReportModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    const productResultsContainer = document.getElementById('productResultsContainer');
    const tableContainer = document.getElementById('tableScrollContainer');
    const tableBody = document.getElementById('tableBody');
    const productGrid = document.getElementById('productGrid');
    const tableHead = document.getElementById('productTableHead');
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

    function getActiveItems() {
        if (productResultsContainer.dataset.view === 'grid') return Array.from(productGrid.querySelectorAll('[data-record-row="true"]'));
        return Array.from(tableBody.querySelectorAll('tr[data-record-row="true"]'));
    }

    function updateResultsCounter() {
        const rowsLoaded = getActiveItems().length;
        if (totalRecords === 0 || rowsLoaded === 0) { resultsText.textContent = 'No products found.'; return; }
        const firstRecord = initialStart;
        const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
        resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> products`;
    }

    function getVisiblePage() {
        const rows = getActiveItems();
        if (!rows.length) return initialPage;

        const isAtBottom = (tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight) <= 5;
        if (isAtBottom && lastPage > 0) return lastPage;

        const containerRect = tableContainer.getBoundingClientRect();
        const headerHeight = (productResultsContainer.dataset.view === 'list' && tableHead) ? tableHead.getBoundingClientRect().height : 0;
        const visibleTop = containerRect.top + headerHeight;
        const visibleBottom = containerRect.bottom;

        let firstVisibleIndex = -1;
        rows.forEach((row, index) => {
            const rect = row.getBoundingClientRect();
            if (firstVisibleIndex === -1 && rect.bottom > visibleTop && rect.top < visibleBottom) firstVisibleIndex = index;
        });

        if (firstVisibleIndex === -1) firstVisibleIndex = rows.length - 1;

        let rowsPerVisualLine = 1;
        if (productResultsContainer.dataset.view === 'grid') {
            const computedStyle = window.getComputedStyle(productGrid);
            rowsPerVisualLine = computedStyle.gridTemplateColumns.split(' ').length;
        }

        const linesScrolled = Math.floor(firstVisibleIndex / rowsPerVisualLine);
        const pageOffset = Math.floor((linesScrolled * rowsPerVisualLine) / perPage);
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
                    if (row.tagName === 'TR') tableBody.appendChild(row);
                });
            }

            if (data.cards_html) {
                const temp = document.createElement('div');
                temp.innerHTML = data.cards_html;
                productGrid.querySelector('[data-empty-row="true"]')?.remove();
                Array.from(temp.children).forEach(card => {
                    if (card.dataset.recordRow === 'true') productGrid.appendChild(card);
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

    retryLoadButton?.addEventListener('click', () => loadMoreRecords());

    // =====================================================
    // VIEW TOGGLE
    // =====================================================
    function updateViewButtonStyles(view) {
        document.querySelectorAll('[data-set-view]').forEach(button => {
            const active = button.dataset.setView === view;
            
            button.classList.remove('bg-white', 'dark:bg-[#1E293B]', 'text-slate-900', 'dark:text-white', 'shadow-sm');
            
            if (active) {
                button.classList.add('bg-white', 'dark:bg-[#1E293B]', 'text-slate-900', 'dark:text-white', 'shadow-sm');
            }
            
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function setProductView(view) {
        if (!['list', 'grid'].includes(view)) view = 'grid';
        productResultsContainer.dataset.view = view;
        try { localStorage.setItem('inventory_view', view); } catch (e) {}
        updateViewButtonStyles(view);
        updateResultsCounter();
        updatePaginationNumbers(getVisiblePage());
    }

    let savedView = 'grid';
    try { savedView = localStorage.getItem('inventory_view') || 'grid'; } catch (e) {}
    productResultsContainer.dataset.view = ['list', 'grid'].includes(savedView) ? savedView : 'grid';
    updateViewButtonStyles(productResultsContainer.dataset.view);

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-set-view]');
        if (!button) return;
        event.preventDefault();
        setProductView(button.dataset.setView);
    });

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

    // =====================================================
    // LIVE SEARCH
    // =====================================================
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    let debounceTimer;
    if (searchInput && filterForm) {
        const savedCursorPos = sessionStorage.getItem('inventorySearchCursorPos');
        if (savedCursorPos !== null) {
            searchInput.focus();
            searchInput.setSelectionRange(parseInt(savedCursorPos), parseInt(savedCursorPos));
            sessionStorage.removeItem('inventorySearchCursorPos');
        } else if (searchInput.value.length > 0) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            sessionStorage.setItem('inventorySearchCursorPos', searchInput.selectionStart);
            debounceTimer = setTimeout(() => filterForm.submit(), 300);
        });
    }

    updateResultsCounter();
    updatePaginationNumbers(initialPage);

    function toggleInventoryAction(productId, actionType) {
        const isCard = actionType.startsWith('card-');
        const restockId = isCard ? `card-restock-${productId}` : `restock-${productId}`;
        const transferId = isCard ? `card-transfer-${productId}` : `transfer-${productId}`;

        const targetEl = actionType.includes('restock') ? document.getElementById(restockId) : document.getElementById(transferId);
        const isTargetHidden = targetEl && targetEl.classList.contains('hidden');

        document.querySelectorAll('[id^="restock-"], [id^="transfer-"], [id^="card-restock-"], [id^="card-transfer-"]').forEach(el => { el.classList.add('hidden'); });
        if (targetEl && isTargetHidden) targetEl.classList.remove('hidden');
    }
    window.toggleInventoryAction = toggleInventoryAction;
});
</script>
@endpush
