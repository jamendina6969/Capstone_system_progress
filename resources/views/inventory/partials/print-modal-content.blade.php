<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-[#1e2536] pb-4">
    <div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Inventory Stock Report</h3>
        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Generated on {{ date('F d, Y - h:i:s A') }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="window.print()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 sm:px-6 py-2 rounded-full transition-all duration-300 shadow-xs cursor-pointer inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Report Now
        </button>
    </div>
</div>

<div class="print-table-wrapper max-h-[60vh] overflow-y-auto custom-scrollbar border border-gray-200 dark:border-[#1e2536] rounded-2xl bg-white dark:bg-[#161B26]">
    <table class="w-full text-xs text-left border-collapse">
        <thead class="bg-gray-50 dark:bg-[#10141D] text-gray-700 dark:text-slate-300 uppercase tracking-wider font-semibold sticky top-0 z-10 border-b border-gray-200 dark:border-[#1e2536]">
            <tr>
                <th class="px-4 py-3.5">Product Name</th>
                <th class="px-4 py-3.5">SKU</th>
                <th class="px-4 py-3.5">Main Category</th>
                <th class="px-4 py-3.5">Subcategory</th>
                <th class="px-4 py-3.5 text-right">Cost</th>
                <th class="px-4 py-3.5 text-right">Price</th>
                <th class="px-4 py-3.5 text-right">Store Qty</th>
                <th class="px-4 py-3.5 text-right">Warehouse Qty</th>
                <th class="px-4 py-3.5 text-right">Total Qty</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-[#1e2536]">
            @forelse ($products as $product)
                @php
                    $storeQty = $product->store_quantity ?? 0;
                    $whQty = $product->warehouse_quantity ?? 0;
                    $totalQty = $storeQty + $whQty;

                    $category = $product->category;
                    $mainCategory = '—';
                    $subCategory = '—';

                    if ($category) {
                        if ($category->parent_id) {
                            $mainCategory = optional($category->parent)->name ?? '—';
                            $subCategory = $category->name;
                        } else {
                            $mainCategory = $category->name;
                            $subCategory = '—';
                        }
                    }
                @endphp
                <tr class="hover:bg-gray-50/80 dark:hover:bg-[#1e2536]/40 transition-colors">
                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $product->product_name }}</td>
                    <td class="px-4 py-3 font-mono text-gray-600 dark:text-slate-300">{{ $product->product_sku ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-slate-300">{{ $mainCategory }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-slate-300">{{ $subCategory }}</td>
                    <td class="px-4 py-3 text-right text-gray-600 dark:text-slate-300 font-mono">₱{{ number_format($product->purchase_cost ?? 0, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-600 dark:text-slate-300 font-mono">₱{{ number_format($product->selling_price ?? 0, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-600 dark:text-slate-300">{{ $storeQty }}</td>
                    <td class="px-4 py-3 text-right text-gray-600 dark:text-slate-300">{{ $whQty }}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">{{ $totalQty }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-400">No products found matching your current filter criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>