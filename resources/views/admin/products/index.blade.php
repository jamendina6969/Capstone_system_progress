@extends('layouts.default')

@section('title', 'Product Management')

@section('content')

    @php
        $sortOptions = [
            'product_name' => 'Sort: Name',
            'product_sku' => 'Sort: Product SKU',
            'store_quantity' => 'Sort: Store Qty',
            'warehouse_quantity' => 'Sort: Warehouse Qty',
            'selling_price' => 'Sort: Price',
            'purchase_cost' => 'Sort: Cost',
            'created_at' => 'Sort: Newest',
        ];

        $directionOptions = [
            'asc' => 'Ascending',
            'desc' => 'Descending',
        ];

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

        $currentSort = request('sort', 'product_name');
        $currentDirection = request('direction', 'asc');

        $selectedSortLabel = $sortOptions[$currentSort] ?? 'Sort: Name';
        $selectedDirectionLabel = $directionOptions[$currentDirection] ?? 'Ascending';

        $currentCategoryId = request('category_id');
        $selectedCategoryLabel = 'All Categories';

        foreach ($categories as $category) {
            if ((string) $currentCategoryId === (string) $category->id) {
                $selectedCategoryLabel = 'All ' . $category->name;
                break;
            }

            foreach ($category->children as $child) {
                if ((string) $currentCategoryId === (string) $child->id) {
                    $selectedCategoryLabel = $child->name;
                    break 2;
                }
            }
        }
    @endphp

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Product Management</h1>

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-180px)] min-h-[550px] mb-4">

        <!-- Filters & Header (shrink-0) -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Product Inventory Catalog</h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage products and services offered</p>
                </div>
                <div class="flex items-center gap-3">
                    @include('partials.viewToggle')
                    <button type="button" onclick="toggleAddProductModal(true)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 shadow-xs cursor-pointer inline-flex items-center whitespace-nowrap">
                        + Add Product
                    </button>
                </div>
            </div>

            <form id="filterForm" method="GET" action="{{ route('admin.products.index') }}" class="flex flex-col xl:flex-row xl:items-center gap-4">
                
                {{-- LEFT SIDE FILTERS (Search + Category) --}}
                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    <input 
                        type="text" 
                        id="searchInput" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search products or SKU..." 
                        autocomplete="off"
                        class="w-full sm:w-72 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:border-slate-400"
                    >

                    <div class="custom-dropdown relative w-full sm:w-56 shrink-0" id="filter-category-dropdown">
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedCategoryLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                All Categories
                            </button>

                            @foreach ($categories as $category)
                                <div class="mt-1">
                                    <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                                        {{ $category->name }}
                                    </div>
                                    <button type="button" data-value="{{ $category->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                        All {{ $category->name }}
                                    </button>
                                    @foreach ($category->children as $child)
                                        <button type="button" data-value="{{ $child->id }}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                            {{ $child->name }}
                                        </button>
                                    @endforeach
                                </div>
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
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            @foreach ($statusOptions as $value => $label)
                                <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- SORT DROPDOWN --}}
                    <div class="custom-dropdown relative w-full sm:w-48 shrink-0" id="filter-sort-dropdown">
                        <input type="hidden" name="sort" value="{{ $currentSort }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedSortLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            @foreach ($sortOptions as $value => $label)
                                <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- DIRECTION DROPDOWN --}}
                    <div class="custom-dropdown relative w-full sm:w-40 shrink-0" id="filter-direction-dropdown">
                        <input type="hidden" name="direction" value="{{ $currentDirection }}">
                        <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                            <span class="dropdown-label truncate">{{ $selectedDirectionLabel }}</span>
                            <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                            @foreach ($directionOptions as $value => $label)
                                <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                    {{ $label }}
                                </button>
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
        <div id="productScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <div id="productResultsContainer" data-view="grid">
                
                <!-- LIST VIEW -->
                <div class="view-list">
                    <table class="w-full min-w-[960px] text-sm text-left relative">
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

                        <tbody id="productTableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536] align-top">
                            @include('admin.products.partials.items', [
                                'products' => $products,
                                'categories' => $categories,
                                'render' => 'rows'
                            ])
                        </tbody>
                    </table>
                </div>

                <!-- GRID VIEW -->
                <div id="productGrid" class="view-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4 sm:gap-5 p-4 sm:p-6">
                    @include('admin.products.partials.items', [
                        'products' => $products,
                        'categories' => $categories,
                        'render' => 'cards'
                    ])
                </div>
            </div>

            {{-- PRODUCT MODALS --}}
            <div id="productModalContainer">
                @include('admin.products.partials.items', [
                    'products' => $products,
                    'categories' => $categories,
                    'render' => 'modals'
                ])
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

            <div id="endOfProducts" class="hidden py-5 text-center text-xs text-gray-400 dark:text-slate-500">
                You have reached the end of the product list.
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

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Product pagination">
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

    {{-- ADD PRODUCT MODAL --}}
    <div id="addProductModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-sm items-center justify-center p-4" onclick="if (event.target === this) toggleAddProductModal(false)">
        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl shadow-2xl p-6 text-left" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-4 mb-4 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add New Product</h3>
                <button type="button" onclick="toggleAddProductModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl text-xl font-bold">×</button>
            </div>

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-rose-300 border border-red-200 dark:border-rose-900 px-4 py-3 text-sm font-medium">
                        Please check the fields below and try again.
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Product SKU <span class="text-gray-400 font-normal">(optional — leave blank to auto-generate)</span></label>
                    <input type="text" id="modal_product_sku" name="product_sku" value="{{ old('product_sku') }}" placeholder="e.g. PRD-XYZ123" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100 font-mono">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Product Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="product_name" value="{{ old('product_name') }}" required data-product-name placeholder="e.g. Purified Drinking Water" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Category</label>
                        <div class="custom-dropdown relative w-full" id="add-category-dropdown">
                            <input type="hidden" id="modal_category_select" name="category_id" value="{{ old('category_id') }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100 cursor-pointer text-left">
                                <span class="dropdown-label truncate">
                                    @if (old('category_id'))
                                        {{ $categories->flatMap(fn($category) => collect([$category])->merge($category->children))->firstWhere('id', old('category_id'))?->name ?? 'Uncategorized (default)' }}
                                    @else
                                        Uncategorized (default)
                                    @endif
                                </span>
                                <span class="text-gray-400 dropdown-chevron transition-transform">⌄</span>
                            </button>

                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-[110] max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-xl shadow-xl py-1">
                                <button type="button" data-value="" data-label="Uncategorized (default)" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Uncategorized (default)</button>
                                @foreach ($categories as $category)
                                    <div class="mt-1">
                                        <div class="px-3.5 pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ $category->name }}</div>
                                        <button type="button" data-value="{{ $category->id }}" data-label="{{ $category->name }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $category->name }}</button>
                                        @foreach ($category->children as $child)
                                            <button type="button" data-value="{{ $child->id }}" data-label="{{ $child->name }}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $child->name }}</button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Approx. Volume</label>
                        <input type="text" name="approx_volume" value="{{ old('approx_volume') }}" data-alnum-plain placeholder="e.g. 20L, 5 gallons" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Reorder Point</label>
                        <input type="number" min="0" name="reorder_point" value="{{ old('reorder_point') }}" placeholder="e.g. 10" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Purchase Cost</label>
                        <input type="number" min="0" step="0.01" name="purchase_cost" value="{{ old('purchase_cost') }}" required placeholder="0.00" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Selling Price</label>
                        <input type="number" min="0" step="0.01" name="selling_price" value="{{ old('selling_price') }}" required placeholder="0.00" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Store Quantity</label>
                        <input type="number" min="0" name="store_quantity" value="{{ old('store_quantity', 0) }}" required class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Warehouse Quantity</label>
                        <input type="number" min="0" name="warehouse_quantity" value="{{ old('warehouse_quantity', 0) }}" required class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Description</label>
                    <textarea name="product_description" rows="2" data-alnum-plain placeholder="Enter product details or description..." class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">{{ old('product_description') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Product Image</label>
                    <div class="flex items-center gap-4">
                        <label class="bg-gray-100 hover:bg-gray-200 dark:bg-[#161B26] dark:hover:bg-[#1e2536] text-gray-700 dark:text-slate-300 text-xs font-semibold px-4 py-2 rounded-lg cursor-pointer border border-gray-200 dark:border-[#1e2536]">
                            Choose File
                            <input type="file" name="product_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" onchange="document.getElementById('fileNameLabel').textContent = this.files[0] ? this.files[0].name : 'No file chosen'">
                        </label>
                        <span id="fileNameLabel" class="text-xs text-gray-500 dark:text-slate-400">No file chosen</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleAddProductModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Save Product</button>
                </div>
            </form>
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

    #productResultsContainer[data-view="grid"] #productGrid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 16px !important;
        padding: 24px !important;
        width: 100%;
        box-sizing: border-box;
        align-items: stretch;
    }

    @media (min-width: 640px) { #productResultsContainer[data-view="grid"] #productGrid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; } }
    @media (min-width: 768px) { #productResultsContainer[data-view="grid"] #productGrid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; } }
    @media (min-width: 1024px) { #productResultsContainer[data-view="grid"] #productGrid { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; } }
    @media (min-width: 1280px) { #productResultsContainer[data-view="grid"] #productGrid { grid-template-columns: repeat(5, minmax(0, 1fr)) !important; } }

    #productResultsContainer[data-view="grid"] #productGrid .product-grid-card {
        width: 100% !important;
        max-width: none !important;
        min-width: 0 !important;
        margin: 0 !important;
        box-sizing: border-box;
        display: flex !important;
        flex-direction: column !important;
    }

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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productResultsContainer = document.getElementById('productResultsContainer');
    const productScrollContainer = document.getElementById('productScrollContainer');
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('productTableBody');
    const productGrid = document.getElementById('productGrid');
    const productModalContainer = document.getElementById('productModalContainer');
    const tableHead = document.getElementById('productTableHead');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');
    const endOfProducts = document.getElementById('endOfProducts');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    if (!productResultsContainer || !productScrollContainer || !tableBody || !productGrid) {
        console.error('Product page elements could not be initialized.');
        return;
    }

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
        if (productResultsContainer.dataset.view === 'grid') {
            return Array.from(productGrid.querySelectorAll('[data-product-row="true"]'));
        }
        return Array.from(tableBody.querySelectorAll('tr[data-product-row="true"]'));
    }

    function updateResultsCounter() {
        const rowsLoaded = getActiveItems().length;
        if (totalRecords === 0 || rowsLoaded === 0) {
            resultsText.textContent = 'No products found.';
            return;
        }
        const firstRecord = initialStart;
        const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
        resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> products`;
    }

    function getVisiblePage() {
        const items = getActiveItems();
        if (!items.length) return initialPage;
        const isAtBottom = (productScrollContainer.scrollHeight - productScrollContainer.scrollTop - productScrollContainer.clientHeight) <= 5;
        if (isAtBottom && lastPage > 0) return lastPage;
        const containerRect = productScrollContainer.getBoundingClientRect();
        const headerHeight = (productResultsContainer.dataset.view === 'list' && tableHead) ? tableHead.getBoundingClientRect().height : 0;
        const visibleTop = containerRect.top + headerHeight + 5;
        const visibleBottom = containerRect.bottom;
        let firstVisibleIndex = -1;
        items.forEach((item, index) => {
            const rect = item.getBoundingClientRect();
            if (firstVisibleIndex === -1 && rect.bottom > visibleTop && rect.top < visibleBottom) firstVisibleIndex = index;
        });
        if (firstVisibleIndex === -1) firstVisibleIndex = items.length - 1;
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
        if (!paginationContainer) return;
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

    async function loadMoreProducts() {
        if (!nextPageUrl || isLoading) return;
        isLoading = true;
        loadError?.classList.add('hidden');
        loadingIndicator?.classList.remove('hidden');

        try {
            const fetchUrl = new URL(nextPageUrl, window.location.origin);
            fetchUrl.searchParams.set('t', Date.now());
            const response = await fetch(fetchUrl.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Cache-Control': 'no-cache' } });
            if (!response.ok) throw new Error('Request failed.');
            const data = await response.json();
            const newPage = Number(data.current_page);

            if (data.rows_html) {
                const temp = document.createElement('tbody');
                temp.innerHTML = data.rows_html;
                tableBody.querySelector('[data-empty-row="true"]')?.remove();
                Array.from(temp.children).forEach(row => {
                    if (row.dataset.productRow === 'true') {
                        row.dataset.productPage = newPage;
                        tableBody.appendChild(row);
                    }
                });
            }

            if (data.cards_html) {
                const temp = document.createElement('div');
                temp.innerHTML = data.cards_html;
                Array.from(temp.children).forEach(card => {
                    if (card.dataset.productRow === 'true') {
                        card.dataset.productPage = newPage;
                        productGrid.appendChild(card);
                    }
                });
            }

            if (data.modals_html) {
                const temp = document.createElement('div');
                temp.innerHTML = data.modals_html;
                Array.from(temp.children).forEach(modal => {
                    if (modal.id) productModalContainer.appendChild(modal);
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
            loadError?.classList.remove('hidden');
        } finally {
            isLoading = false;
            loadingIndicator?.classList.add('hidden');
        }
    }

    productScrollContainer.addEventListener('scroll', function () {
        updatePaginationNumbers(getVisiblePage());
        const distanceFromBottom = productScrollContainer.scrollHeight - productScrollContainer.scrollTop - productScrollContainer.clientHeight;
        if (distanceFromBottom < 150 && nextPageUrl && !isLoading) loadMoreProducts();
    }, { passive: true });

    retryLoadButton?.addEventListener('click', function () { loadMoreProducts(); });

    function updateViewButtonStyles(view) {
        document.querySelectorAll('[data-set-view]').forEach(button => {
            const active = button.dataset.setView === view;
            button.classList.remove('bg-white', 'dark:bg-[#1E293B]', 'text-slate-900', 'dark:text-white', 'shadow-sm');
            if (active) button.classList.add('bg-white', 'dark:bg-[#1E293B]', 'text-slate-900', 'dark:text-white', 'shadow-sm');
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function setProductView(view) {
        if (!['list', 'grid'].includes(view)) view = 'grid';
        productResultsContainer.dataset.view = view;
        try { localStorage.setItem('product_view', view); } catch (error) {}
        updateViewButtonStyles(view);
        updateResultsCounter();
        updatePaginationNumbers(getVisiblePage());
    }

    let savedView = 'grid';
    try { savedView = localStorage.getItem('product_view') || 'grid'; } catch (error) {}
    productResultsContainer.dataset.view = ['list', 'grid'].includes(savedView) ? savedView : 'grid';
    updateViewButtonStyles(productResultsContainer.dataset.view);

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-set-view]');
        if (!button) return;
        event.preventDefault();
        setProductView(button.dataset.setView);
    });

    function initCustomDropdowns() {
        document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
            if (dropdown.dataset.initialized === 'true') return;
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const menu = dropdown.querySelector('.dropdown-menu');
            const chevron = dropdown.querySelector('.dropdown-chevron');
            const label = dropdown.querySelector('.dropdown-label');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            if (!trigger || !menu) return;

            function closeMenu() { menu.classList.add('hidden'); chevron?.classList.remove('rotate-180'); }
            function openMenu() {
                document.querySelectorAll('.custom-dropdown .dropdown-menu').forEach(otherMenu => { if (otherMenu !== menu) otherMenu.classList.add('hidden'); });
                document.querySelectorAll('.custom-dropdown .dropdown-chevron').forEach(otherChevron => { if (otherChevron !== chevron) otherChevron.classList.remove('rotate-180'); });
                menu.classList.remove('hidden'); chevron?.classList.add('rotate-180');
            }

            trigger.addEventListener('click', function (event) {
                event.preventDefault(); event.stopPropagation();
                menu.classList.contains('hidden') ? openMenu() : closeMenu();
            });

            menu.querySelectorAll('.dropdown-option').forEach(option => {
                option.addEventListener('click', function (event) {
                    event.preventDefault(); event.stopPropagation();
                    const value = this.dataset.value ?? '';
                    const text = this.dataset.label || this.textContent.trim();
                    if (hiddenInput) hiddenInput.value = value;
                    if (label) label.textContent = text;
                    closeMenu();
                    if (dropdown.id === 'add-category-dropdown') generateProductSku();
                    if (dropdown.id.startsWith('filter-') && filterForm) filterForm.submit();
                });
            });
            dropdown.dataset.initialized = 'true';
        });
    }
    initCustomDropdowns();

    document.addEventListener('click', function (event) {
        document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
            if (!dropdown.contains(event.target)) {
                dropdown.querySelector('.dropdown-menu')?.classList.add('hidden');
                dropdown.querySelector('.dropdown-chevron')?.classList.remove('rotate-180');
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
                dropdown.querySelector('.dropdown-menu')?.classList.add('hidden');
                dropdown.querySelector('.dropdown-chevron')?.classList.remove('rotate-180');
            });
        }
    });

    function generateProductSku() {
        const categoryLabel = document.querySelector('#add-category-dropdown .dropdown-label');
        const skuInput = document.getElementById('modal_product_sku');
        if (!skuInput) return;
        // Only auto-generate if the field is currently empty or contains an old auto-generated pattern
        if (skuInput.value.trim() !== '' && !skuInput.value.startsWith('PRD-')) return;
        
        let prefix = 'PRD';
        if (categoryLabel) {
            const text = categoryLabel.textContent.trim();
            if (text && !text.includes('Uncategorized')) {
                const cleaned = text.replace(/[^a-zA-Z]/g, '').toUpperCase();
                if (cleaned.length > 0) prefix = cleaned.substring(0, 3);
            }
        }
        skuInput.value = `${prefix}-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;
    }
    window.generateProductSku = generateProductSku;

    function updateBodyScroll() {
        const openModal = document.querySelector('#addProductModal:not(.hidden), [id^="editProductModal-"]:not(.hidden), [id^="deleteModal-"]:not(.hidden)');
        document.body.classList.toggle('overflow-hidden', !!openModal);
    }

    window.toggleAddProductModal = function (show) {
        const modal = document.getElementById('addProductModal');
        if (!modal) return;
        if (show) { generateProductSku(); modal.classList.remove('hidden'); modal.classList.add('flex'); }
        else { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        updateBodyScroll();
    };

    window.toggleEditProductModal = function (id, show) {
        const modal = document.getElementById(`editProductModal-${id}`);
        if (!modal) return;
        if (show) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        else { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        updateBodyScroll();
    };

    window.toggleDeleteModal = function (id, show) {
        const modal = document.getElementById(`deleteModal-${id}`);
        if (!modal) return;
        if (show) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        else { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        updateBodyScroll();
    };

    if (searchInput && filterForm) {
        let savedCursor = null;
        try { savedCursor = sessionStorage.getItem('productSearchCursorPos'); } catch (error) {}
        if (savedCursor !== null) {
            searchInput.focus();
            const position = Math.min(parseInt(savedCursor, 10) || 0, searchInput.value.length);
            searchInput.setSelectionRange(position, position);
            try { sessionStorage.removeItem('productSearchCursorPos'); } catch (error) {}
        } else if (searchInput.value.length > 0) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
        searchInput.addEventListener('input', function () {
            if (typeof debounceTimer !== 'undefined') clearTimeout(debounceTimer);
            try { sessionStorage.setItem('productSearchCursorPos', searchInput.selectionStart); } catch (error) {}
            debounceTimer = setTimeout(() => filterForm.submit(), 300);
        });
    }

    const clearFiltersButton = document.getElementById('clearFiltersButton');
    clearFiltersButton?.addEventListener('click', function () {
        window.location.href = @json(route('admin.products.index'));
    });

    @if ($errors->any())
        window.toggleAddProductModal(true);
    @endif

    updateResultsCounter();
    updatePaginationNumbers(initialPage);
});
</script>
@endpush