@extends('layouts.default')

@section('title', 'Forecast Settings')

@section('content')

    @php
        // Determine initial labels for the dropdowns
        $currentScope = old('scope', 'category');
        $scopeOptions = [
            'category' => 'Product Group (Category)',
            'product' => 'Specific Product',
        ];
        $selectedScopeLabel = $scopeOptions[$currentScope] ?? 'Product Group (Category)';

        $currentCategoryId = old('category_id');
        $selectedCategoryLabel = 'Select category';
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

        $currentProductId = old('product_id');
        $selectedProductLabel = 'Select product';
        if ($currentProductId) {
            foreach ($products as $prod) {
                if ((string) $currentProductId === (string) $prod->id) {
                    $selectedProductLabel = $prod->product_name . ($prod->approx_volume ? ' (' . $prod->approx_volume . ')' : '');
                    break;
                }
            }
        }
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Forecast Settings</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage Simple Moving Average (SMA) windows for accurate demand forecasting</p>
        </div>
        <a href="{{ route('eoq.calibration') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
            Go to EOQ Calibration &rarr;
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-2 border-rose-200 dark:border-rose-900 px-6 py-4 text-sm font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-8">

        <!-- Global Default -->
        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50 dark:bg-[#161B26]/50">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Global Default</h2>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Applies to every product unless an override is set below.</p>
            </div>
            
            <div class="p-8">
                <div class="mb-6 p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-900 text-sm text-blue-800 dark:text-blue-300">
                    <span class="font-bold">What it does:</span> Sets the system-wide Simple Moving Average (SMA) lookback timeframe in days used for product sales predictions.
                </div>

                <form method="POST" action="{{ route('forecast.settings.global') }}" class="flex items-end gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">SMA Window (days)</label>
                        <input type="number" name="sma_window_days" min="1" max="365"
                               value="{{ old('sma_window_days', $global->sma_window_days ?? 30) }}"
                               class="w-40 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 font-semibold">
                    </div>
                    <button type="submit" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                        Save Global
                    </button>
                </form>
            </div>
        </div>

        <!-- Add Override (Removed overflow-hidden to prevent dropdown clipping) -->
        <div class="relative z-20 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm">
            <div class="px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50 dark:bg-[#161B26]/50 rounded-t-3xl">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Add Override</h2>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Assign custom forecast periods to specific categories or individual items.</p>
            </div>

            <div class="p-8">
                <div class="mb-6 p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-900 text-sm text-blue-800 dark:text-blue-300">
                    <span class="font-bold">What it does:</span> Overrides the global default for a specific target. Useful for seasonal items or fast-moving categories.
                </div>

                <form method="POST" action="{{ route('forecast.settings.store') }}" class="flex flex-col sm:flex-row flex-wrap items-end gap-4">
                    @csrf

                    <!-- Custom Apply To Dropdown -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Apply to</label>
                        <div class="custom-dropdown relative w-full sm:w-64" id="scope-dropdown">
                            <input type="hidden" name="scope" id="scopeHiddenInput" value="{{ $currentScope }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                <span class="dropdown-label truncate">{{ $selectedScopeLabel }}</span>
                                <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                @foreach ($scopeOptions as $value => $label)
                                    <button type="button" data-value="{{ $value }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Custom Category Dropdown -->
                    <div id="categoryField" class="w-full sm:w-auto {{ $currentScope === 'category' ? '' : 'hidden' }}">
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Category</label>
                        <div class="custom-dropdown relative w-full sm:w-64" id="category-dropdown">
                            <input type="hidden" name="category_id" value="{{ $currentCategoryId }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                <span class="dropdown-label truncate">{{ $selectedCategoryLabel }}</span>
                                <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                    Select category
                                </button>
                                @foreach ($categories->whereNull('parent_id') as $mainCat)
                                    <div class="mt-1">
                                        <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                                            {{ $mainCat->name }}
                                        </div>
                                        <button type="button" data-value="{{ $mainCat->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                            {{ $mainCat->name }} (Main)
                                        </button>
                                        @foreach ($categories->where('parent_id', $mainCat->id)->sortBy('name') as $subCat)
                                            <button type="button" data-value="{{ $subCat->id }}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                                {{ $subCat->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Custom Product Dropdown -->
                    <div id="productField" class="w-full sm:w-auto {{ $currentScope === 'product' ? '' : 'hidden' }}">
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Product (Grouped by Category)</label>
                        <div class="custom-dropdown relative w-full sm:w-72" id="product-dropdown">
                            <input type="hidden" name="product_id" value="{{ $currentProductId }}">
                            <button type="button" class="dropdown-trigger w-full flex items-center justify-between gap-2 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer text-left">
                                <span class="dropdown-label truncate">{{ $selectedProductLabel }}</span>
                                <svg class="dropdown-chevron w-4 h-4 shrink-0 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 right-0 top-full mt-2 z-50 max-h-72 overflow-y-auto custom-scrollbar bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-lg py-1">
                                <button type="button" data-value="" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                    Select product
                                </button>
                                @foreach ($categories->whereNull('parent_id') as $mainCat)
                                    @php
                                        $mainProducts = $products->where('category_id', $mainCat->id);
                                        $subCategories = $categories->where('parent_id', $mainCat->id);
                                        $hasProducts = $mainProducts->count() > 0 || $subCategories->contains(fn($sub) => $products->where('category_id', $sub->id)->count() > 0);
                                    @endphp

                                    @if($hasProducts)
                                        <div class="mt-1">
                                            <div class="px-3.5 pt-1 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                                                {{ $mainCat->name }}
                                            </div>
                                            
                                            @foreach ($mainProducts->sortBy('product_name') as $product)
                                                <button type="button" data-value="{{ $product->id }}" class="dropdown-option w-full text-left px-3.5 py-2 text-sm text-gray-900 dark:text-slate-100 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                                    {{ $product->product_name }} {{ $product->approx_volume ? '(' . $product->approx_volume . ')' : '' }}
                                                </button>
                                            @endforeach

                                            @foreach ($subCategories->sortBy('name') as $subCat)
                                                @php
                                                    $subProducts = $products->where('category_id', $subCat->id)->sortBy('product_name');
                                                @endphp
                                                @if($subProducts->count() > 0)
                                                    <div class="px-3.5 pt-2 pb-1 text-[10px] font-bold text-gray-500 dark:text-slate-400 italic">
                                                        &nbsp;&nbsp;&mdash; {{ $subCat->name }}
                                                    </div>
                                                    @foreach ($subProducts as $product)
                                                        <button type="button" data-value="{{ $product->id }}" class="dropdown-option w-full text-left pl-7 pr-3.5 py-2 text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                                                            {{ $product->product_name }} {{ $product->approx_volume ? '(' . $product->approx_volume . ')' : '' }}
                                                        </button>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">SMA Window (days)</label>
                        <input type="number" name="sma_window_days" min="1" max="365" value="{{ old('sma_window_days', 30) }}"
                               class="w-full sm:w-32 rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 font-semibold">
                    </div>

                    <button type="submit" class="w-full sm:w-auto bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                        Add Override
                    </button>
                </form>
            </div>
        </div>

        <!-- Category Overrides List -->
        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Category Overrides</h2>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Active overrides applied to entire product categories.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs border-b-2 border-gray-200 dark:border-[#1e2536]">
                        <tr>
                            <th class="px-8 py-4">Scope</th>
                            <th class="px-8 py-4">Category</th>
                            <th class="px-8 py-4">SMA Window</th>
                            <th class="px-8 py-4">Updated By</th>
                            <th class="px-8 py-4">Last Updated</th>
                            <th class="px-8 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                        @forelse ($categorySettings as $setting)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">
                                <td class="px-8 py-4">
                                    <span class="inline-block bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-blue-200 dark:border-blue-900">Category</span>
                                </td>
                                <td class="px-8 py-4 font-semibold text-gray-900 dark:text-white">{{ $setting->category->name ?? '—' }}</td>
                                <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">{{ $setting->sma_window_days }} days</td>
                                <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">{{ $setting->updatedBy->full_name ?? $setting->updatedBy->name ?? 'System' }}</td>
                                <td class="px-8 py-4 text-gray-500 dark:text-slate-400 text-xs">{{ $setting->updated_at ? $setting->updated_at->format('M d, Y h:i A') : '—' }}</td>
                                <td class="px-8 py-4 text-right">
                                    <form action="{{ route('forecast.settings.destroy', $setting) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Remove this override?');" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500 font-medium">No category overrides set.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Product Overrides List -->
        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Product Overrides</h2>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Active overrides applied to individual products.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs border-b-2 border-gray-200 dark:border-[#1e2536]">
                        <tr>
                            <th class="px-8 py-4">Scope</th>
                            <th class="px-8 py-4">Product</th>
                            <th class="px-8 py-4">Category</th>
                            <th class="px-8 py-4">SMA Window</th>
                            <th class="px-8 py-4">Updated By</th>
                            <th class="px-8 py-4">Last Updated</th>
                            <th class="px-8 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                        @forelse ($productSettings as $setting)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">
                                <td class="px-8 py-4">
                                    <span class="inline-block bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-purple-200 dark:border-purple-900">Product</span>
                                </td>
                                <td class="px-8 py-4 font-semibold text-gray-900 dark:text-white">
                                    {{ $setting->product->product_name ?? '—' }}
                                    @if($setting->product->approx_volume)
                                        <span class="text-xs text-gray-500 font-normal ml-1">({{ $setting->product->approx_volume }})</span>
                                    @endif
                                </td>
                                <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">{{ $setting->product->category->name ?? '—' }}</td>
                                <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">{{ $setting->sma_window_days }} days</td>
                                <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">{{ $setting->updatedBy->full_name ?? $setting->updatedBy->name ?? 'System' }}</td>
                                <td class="px-8 py-4 text-gray-500 dark:text-slate-400 text-xs">{{ $setting->updated_at ? $setting->updated_at->format('M d, Y h:i A') : '—' }}</td>
                                <td class="px-8 py-4 text-right">
                                    <form action="{{ route('forecast.settings.destroy', $setting) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Remove this override?');" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500 font-medium">No product overrides set.</td></tr>
                        @endforelse
                    </tbody>
                </table>
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
</style>
@endpush

@push('scripts')
<script>
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

                    // Trigger change event to handle scope toggling
                    if (hiddenInput) {
                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
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

    document.addEventListener('DOMContentLoaded', initCustomDropdowns);

    // =====================================================
    // SCOPE TOGGLE LOGIC (Preserved from original)
    // =====================================================
    const scopeHiddenInput = document.getElementById('scopeHiddenInput');
    const categoryField = document.getElementById('categoryField');
    const productField = document.getElementById('productField');

    if (scopeHiddenInput && categoryField && productField) {
        scopeHiddenInput.addEventListener('change', function() {
            const scope = this.value;
            categoryField.classList.toggle('hidden', scope !== 'category');
            productField.classList.toggle('hidden', scope !== 'product');
        });
    }
</script>
@endpush