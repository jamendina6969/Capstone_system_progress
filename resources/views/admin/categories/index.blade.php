@extends('layouts.default')

@section('title', 'Category Management')

@section('content')

    @php
        // Determine initial labels for filter dropdowns
        $mainGroupOptions = ['' => 'Filter by Main Group (All)'];
        foreach ($mainCategories as $mainCat) {
            $mainGroupOptions[$mainCat->id] = $mainCat->name;
        }
        $currentMainGroup = request('main_group', '');
        $selectedMainGroupLabel = $mainGroupOptions[$currentMainGroup] ?? 'Filter by Main Group (All)';

        $typeOptions = [
            'all' => 'All Types',
            'product' => 'Product',
            'service' => 'Service',
        ];
        $currentType = request('type', 'all');
        $selectedTypeLabel = $typeOptions[$currentType] ?? 'All Types';

        $categoryTypeOptions = [
            'product' => 'Product',
            'service' => 'Service',
        ];
    @endphp

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Category Management</h1>

    @if (session('error'))
        <div class="mb-6 rounded-2xl bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-rose-300 border-2 border-red-200 dark:border-rose-900 px-6 py-4 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-140px)] min-h-[500px] mb-4">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] gap-4 shrink-0">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Main & Sub-Categories Hierarchy</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage top-level categories and their corresponding brand subcategories</p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                @include('partials.viewToggle')
                <button type="button" onclick="toggleAddProductModal(true)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer whitespace-nowrap">
                    + Add Product
                </button>
                <button type="button" onclick="toggleAddCategoryModal(true)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer whitespace-nowrap">
                    + Create Category
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50 shrink-0">
            <form id="filterForm" method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-col sm:flex-row flex-wrap items-center gap-4">
                
                <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search by name or code..." autocomplete="off" class="flex-1 min-w-[200px] rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:border-slate-400">

                <!-- Custom Main Group Dropdown -->
                <div class="custom-dropdown relative w-full sm:w-56 shrink-0" id="filter-maingroup-dropdown">
                    <input type="hidden" name="main_group" value="{{ request('main_group') }}">
                    <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                        <span class="dropdown-label truncate">{{ $selectedMainGroupLabel }}</span>
                        <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                        @foreach ($mainGroupOptions as $value => $label)
                            <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <!-- Custom Type Dropdown -->
                <div class="custom-dropdown relative w-full sm:w-44 shrink-0" id="filter-type-dropdown">
                    <input type="hidden" name="type" value="{{ request('type', 'all') }}">
                    <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                        <span class="dropdown-label truncate">{{ $selectedTypeLabel }}</span>
                        <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                        @foreach ($typeOptions as $value => $label)
                            <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

            </form>
        </div>

        <!-- Scrollable Body -->
        <div id="categoryScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <div id="categoryResultsContainer" data-view="grid">
                
                {{-- LIST VIEW --}}
                <div class="view-list overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs border-b-2 border-gray-200 dark:border-[#1e2536] sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Main Category & Brand Subcategories</th>
                                <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Code</th>
                                <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Type</th>
                                <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Total Products</th>
                                <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Total Valuation</th>
                                <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoryTableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536] align-top">
                            @include('admin.categories.partials.rows', ['categories' => $categories])
                        </tbody>
                    </table>
                </div>

                {{-- GRID VIEW --}}
                <div id="categoryGrid" class="view-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-6 justify-items-center sm:justify-items-stretch">
                    @include('admin.categories.partials.cards', ['categories' => $categories])
                </div>

            </div>

            {{-- MODALS --}}
            <div id="categoryModalContainer">
                @include('admin.categories.partials.modals', ['categories' => $categories])
            </div>

            {{-- LOADING --}}
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more categories...
            </div>

            {{-- ERROR --}}
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more categories. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($categories->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $categories->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $categories->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $categories->total() }}</span>
                    categories
                @else
                    No categories found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Category pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($categories->getUrlRange(max(1, $categories->currentPage() - 2), min($categories->lastPage(), $categories->currentPage() + 2)) as $page => $url)
                        @if ($page == $categories->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- Create Category or Subcategory Modal Popup -->
    <div id="addCategoryModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left">
            
            <div class="flex items-center justify-between pb-6 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Create Category or Subcategory</h3>
                <button type="button" onclick="toggleAddCategoryModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors text-xl font-bold">
                    &times;
                </button>
            </div>

            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-5">
                @csrf
                @if ($errors->any() && old('name'))
                    <div class="rounded-2xl bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-rose-300 border-2 border-red-200 dark:border-rose-900 px-6 py-4 text-sm font-medium">
                        Please check the fields below and try again.
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Electric / Liquids" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Code <span class="text-gray-400 font-normal">(Auto-generated)</span></label>
                        <input type="text" readonly value="Auto-generated" class="w-full rounded-xl bg-gray-100 dark:bg-[#161B26]/50 border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-400 dark:text-slate-500 cursor-not-allowed focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Parent Group <span class="text-gray-400 font-normal">(leave blank if top-level)</span></label>
                        <div class="custom-dropdown relative w-full" id="add-cat-parent-dropdown">
                            <input type="hidden" name="parent_id" value="{{ old('parent_id') }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                <span class="dropdown-label truncate">
                                    @if(old('parent_id'))
                                        {{ $mainCategories->firstWhere('id', old('parent_id'))->name ?? 'None (Top-Level Category)' }}
                                    @else
                                        None (Top-Level Category)
                                    @endif
                                </span>
                                <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">None (Top-Level Category)</button>
                                @foreach ($mainCategories as $mainCat)
                                    <button type="button" data-value="{{ $mainCat->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $mainCat->name }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Type</label>
                        <div class="custom-dropdown relative w-full" id="add-cat-type-dropdown">
                            <input type="hidden" name="type" value="{{ old('type', 'product') }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                <span class="dropdown-label truncate">{{ ucfirst(old('type', 'product')) }}</span>
                                <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-40 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                @foreach ($categoryTypeOptions as $value => $label)
                                    <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Description of category or subcategory..." class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleAddCategoryModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD PRODUCT MODAL --}}
    <div id="addProductModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-sm items-center justify-center p-4" onclick="if (event.target === this) toggleAddProductModal(false)">
        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl shadow-2xl p-6 text-left" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-4 mb-4 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add New Product</h3>
                <button type="button" onclick="toggleAddProductModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                @if ($errors->any() && !old('name'))
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
                        <input type="text" name="product_name" value="{{ old('product_name') }}" required placeholder="e.g. Purified Drinking Water" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Category</label>
                        <div class="custom-dropdown relative w-full" id="add-category-dropdown">
                            <input type="hidden" id="modal_category_select" name="category_id" value="{{ old('category_id') }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100 cursor-pointer text-left">
                                <span class="dropdown-label truncate">
                                    @if (old('category_id'))
                                        {{ $mainCategories->flatMap(fn($cat) => collect([$cat])->merge($cat->children))->firstWhere('id', old('category_id'))?->name ?? 'Uncategorized (default)' }}
                                    @else
                                        Uncategorized (default)
                                    @endif
                                </span>
                                <span class="text-gray-400 dropdown-chevron transition-transform">⌄</span>
                            </button>

                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-[110] max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-xl shadow-xl py-1">
                                <button type="button" data-value="" data-label="Uncategorized (default)" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">Uncategorized (default)</button>
                                @foreach ($mainCategories as $mainCat)
                                    <div class="mt-1">
                                        <div class="px-3.5 pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ $mainCat->name }}</div>
                                        <button type="button" data-value="{{ $mainCat->id }}" data-label="{{ $mainCat->name }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">{{ $mainCat->name }}</button>
                                        @foreach ($mainCat->children as $child)
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
                        <input type="text" name="approx_volume" value="{{ old('approx_volume') }}" placeholder="e.g. 20L, 5 gallons" class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">
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
                    <textarea name="product_description" rows="2" placeholder="Enter product details or description..." class="w-full rounded-lg bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100">{{ old('product_description') }}</textarea>
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

                <div class="flex items-center justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleAddProductModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Save Product</button>
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
    
    /* View visibility logic */
    #categoryResultsContainer .view-list, #categoryResultsContainer .view-grid { display: none !important; }
    #categoryResultsContainer[data-view="list"] .view-list { display: block !important; }
    #categoryResultsContainer[data-view="grid"] .view-grid { display: grid !important; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoryResultsContainer = document.getElementById('categoryResultsContainer');
    const categoryScrollContainer = document.getElementById('categoryScrollContainer');
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('categoryTableBody');
    const categoryGrid = document.getElementById('categoryGrid');
    const categoryModalContainer = document.getElementById('categoryModalContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    if (!categoryResultsContainer || !categoryScrollContainer || !tableBody || !categoryGrid) {
        console.error('Category page elements could not be initialized.');
        return;
    }

    // =====================================================
    // PAGINATION STATE
    // =====================================================
    let nextPageUrl = @json($categories->nextPageUrl());
    let currentPage = {{ $categories->currentPage() }};
    let lastPage = {{ $categories->lastPage() }};
    let totalRecords = {{ $categories->total() }};
    let perPage = {{ $categories->perPage() }};
    let initialPage = {{ $categories->currentPage() }};
    let initialStart = {{ $categories->firstItem() ?? 0 }} || 0;

    let isLoading = false;
    let visualPage = initialPage;

    function getActiveItems() {
        if (categoryResultsContainer.dataset.view === 'grid') {
            return Array.from(categoryGrid.querySelectorAll('[data-category-row="true"]'));
        }
        return Array.from(tableBody.querySelectorAll('[data-category-row="true"]'));
    }

    function updateResultsCounter() {
        const rowsLoaded = getActiveItems().length;
        if (totalRecords === 0 || rowsLoaded === 0) {
            resultsText.textContent = 'No categories found.';
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
            categories
        `;
    }

    function getVisiblePage() {
        const items = getActiveItems();
        if (!items.length) return initialPage;

        // Bottom-scroll fix
        const isAtBottom = (categoryScrollContainer.scrollHeight - categoryScrollContainer.scrollTop - categoryScrollContainer.clientHeight) <= 5;
        if (isAtBottom && lastPage > 0) return lastPage;

        const containerRect = categoryScrollContainer.getBoundingClientRect();
        const visibleTop = containerRect.top;
        const visibleBottom = containerRect.bottom;

        let firstVisibleIndex = -1;

        items.forEach((item, index) => {
            const rect = item.getBoundingClientRect();
            if (firstVisibleIndex === -1 && rect.bottom > visibleTop && rect.top < visibleBottom) {
                firstVisibleIndex = index;
            }
        });

        if (firstVisibleIndex === -1) firstVisibleIndex = items.length - 1;

        let rowsPerVisualLine = 1;
        if (categoryResultsContainer.dataset.view === 'grid') {
            const computedStyle = window.getComputedStyle(categoryGrid);
            const columns = computedStyle.gridTemplateColumns.split(' ').length;
            rowsPerVisualLine = columns;
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

    async function loadMoreCategories() {
        if (!nextPageUrl || isLoading) return;

        isLoading = true;
        loadError?.classList.add('hidden');
        loadingIndicator?.classList.remove('hidden');

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

            // Append Rows
            if (data.rows_html) {
                const temp = document.createElement('tbody');
                temp.innerHTML = data.rows_html;
                tableBody.querySelector('[data-empty-row="true"]')?.remove();
                Array.from(temp.children).forEach(row => {
                    if (row.dataset.categoryRow === 'true') {
                        row.dataset.categoryPage = newPage;
                        tableBody.appendChild(row);
                    }
                });
            }

            // Append Cards
            if (data.cards_html) {
                const temp = document.createElement('div');
                temp.innerHTML = data.cards_html;
                Array.from(temp.children).forEach(card => {
                    if (card.dataset.categoryRow === 'true') {
                        card.dataset.categoryPage = newPage;
                        categoryGrid.appendChild(card);
                    }
                });
            }

            // Append Modals
            if (data.modals_html) {
                const temp = document.createElement('div');
                temp.innerHTML = data.modals_html;
                Array.from(temp.children).forEach(modal => {
                    if (modal.id) categoryModalContainer.appendChild(modal);
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
            console.error('Error loading categories:', error);
            loadError?.classList.remove('hidden');
        } finally {
            isLoading = false;
            loadingIndicator?.classList.add('hidden');
        }
    }

    categoryScrollContainer.addEventListener('scroll', function () {
        updatePaginationNumbers(getVisiblePage());
        const distanceFromBottom = categoryScrollContainer.scrollHeight - categoryScrollContainer.scrollTop - categoryScrollContainer.clientHeight;
        if (distanceFromBottom < 150 && nextPageUrl && !isLoading) loadMoreCategories();
    }, { passive: true });

    retryLoadButton?.addEventListener('click', function () { loadMoreCategories(); });

    // =====================================================
    // VIEW TOGGLE
    // =====================================================
    function updateViewButtonStyles(view) {
        document.querySelectorAll('[data-set-view]').forEach(button => {
            const active = button.dataset.setView === view;
            button.classList.toggle('bg-slate-800', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('dark:bg-[#1E293B]', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function setProductView(view) {
        if (!['list', 'grid'].includes(view)) view = 'grid';
        categoryResultsContainer.dataset.view = view;
        try { localStorage.setItem('category_view', view); } catch (error) {}
        updateViewButtonStyles(view);
        updateResultsCounter();
        updatePaginationNumbers(getVisiblePage());
    }

    let savedView = 'grid';
    try { savedView = localStorage.getItem('category_view') || 'grid'; } catch (error) {}
    categoryResultsContainer.dataset.view = ['list', 'grid'].includes(savedView) ? savedView : 'grid';
    updateViewButtonStyles(categoryResultsContainer.dataset.view);

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

                    if (dropdown.id === 'add-category-dropdown') {
                        generateProductSku();
                    }

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
    // MODAL HELPERS
    // =====================================================
    function generateProductSku() {
        const categoryLabel = document.querySelector('#add-category-dropdown .dropdown-label');
        const skuInput = document.getElementById('modal_product_sku');
        if (!skuInput) return;
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
        const openModal = document.querySelector('.fixed.inset-0.z-50:not(.hidden), .fixed.inset-0.z-\[100\]:not(.hidden)');
        document.body.classList.toggle('overflow-hidden', !!openModal);
    }

    window.toggleAddCategoryModal = function (show) {
        const modal = document.getElementById('addCategoryModal');
        if (!modal) return;
        if (show) { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); initCustomDropdowns(); }
        else { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
    };

    window.toggleAddProductModal = function (show) {
        const modal = document.getElementById('addProductModal');
        if (!modal) return;
        if (show) { 
            generateProductSku();
            modal.classList.remove('hidden'); 
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden'); 
            initCustomDropdowns(); 
        } else { 
            modal.classList.add('hidden'); 
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden'); 
        }
    };

    window.toggleAddBrandModal = function (categoryId, show) {
        const modal = document.getElementById(`addBrandModal-${categoryId}`);
        if (!modal) return;
        if (show) { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
        else { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
    };

    window.toggleEditCategoryModal = function (categoryId, show) {
        const modal = document.getElementById(`editCategoryModal-${categoryId}`);
        if (!modal) return;
        if (show) { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); initCustomDropdowns(); }
        else { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
    };

    window.toggleDeleteCategoryModal = function (categoryId, show) {
        const modal = document.getElementById(`deleteCategoryModal-${categoryId}`);
        if (!modal) return;
        if (show) { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
        else { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
    };

    window.toggleEditSubcategoryModal = function (subcatId, show) {
        const modal = document.getElementById(`editSubcategoryModal-${subcatId}`);
        if (!modal) return;
        if (show) { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
        else { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
    };

    window.toggleDeleteSubcategoryModal = function (subcatId, show) {
        const modal = document.getElementById(`deleteSubcategoryModal-${subcatId}`);
        if (!modal) return;
        if (show) { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
        else { modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
    };

    // =====================================================
    // LIVE SEARCH
    // =====================================================
    if (searchInput && filterForm) {
        let savedCursor = null;
        try { savedCursor = sessionStorage.getItem('categorySearchCursorPos'); } catch (error) {}

        if (savedCursor !== null) {
            searchInput.focus();
            const position = Math.min(parseInt(savedCursor, 10) || 0, searchInput.value.length);
            searchInput.setSelectionRange(position, position);
            try { sessionStorage.removeItem('categorySearchCursorPos'); } catch (error) {}
        } else if (searchInput.value.length > 0) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }

        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            try { sessionStorage.setItem('categorySearchCursorPos', searchInput.selectionStart); } catch (error) {}
            debounceTimer = setTimeout(() => filterForm.submit(), 300);
        });
    }

    @if ($errors->any() && old('name'))
        window.toggleAddCategoryModal(true);
    @elseif ($errors->any() && old('product_name'))
        window.toggleAddProductModal(true);
    @endif

    // Init Counters
    updateResultsCounter();
    updatePaginationNumbers(initialPage);
});
</script>
@endpush