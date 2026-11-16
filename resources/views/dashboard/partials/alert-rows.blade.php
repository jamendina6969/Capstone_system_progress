@forelse ($lowStockProducts as $product)
    @php
        $storeQty = $product->store_quantity ?? 0;
        $whQty = $product->warehouse_quantity ?? 0;
        $totalQty = $storeQty + $whQty;
        $reorder = $product->reorder_point ?? 10;

        $storeStatus = $storeQty == 0 ? 'Out of Stock' : ($storeQty <= $reorder ? 'Low Stock' : 'In Stock');
        $whStatus = $whQty == 0 ? 'Out of Stock' : ($whQty <= $reorder ? 'Low Stock' : 'In Stock');
        $totalStatus = $totalQty == 0 ? 'Out of Stock' : ($totalQty <= $reorder ? 'Low Stock' : 'In Stock');
    @endphp

    <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors group relative">
        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
            <a href="{{ route('inventory.index', ['search' => $product->product_sku]) }}" class="after:absolute after:inset-0 after:z-10 after:cursor-pointer">
                {{ $product->product_name }}
            </a>
        </td>
        <td class="px-6 py-4 text-gray-600 dark:text-slate-400">
            {{ $product->category->name ?? '—' }}
        </td>

        <td class="px-6 py-4">
            @if ($storeStatus === 'Out of Stock')
                <span class="inline-block bg-red-100 text-red-700 dark:bg-red-950 dark:text-rose-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-red-200 dark:border-rose-900">Out of Stock</span>
            @elseif ($storeStatus === 'Low Stock')
                <span class="inline-block bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-amber-200 dark:border-amber-900">Low Stock</span>
            @else
                <span class="inline-block bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-emerald-200 dark:border-emerald-900">In Stock</span>
            @endif
        </td>

        <td class="px-6 py-4">
            @if ($whStatus === 'Out of Stock')
                <span class="inline-block bg-red-100 text-red-700 dark:bg-red-950 dark:text-rose-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-red-200 dark:border-rose-900">Out of Stock</span>
            @elseif ($whStatus === 'Low Stock')
                <span class="inline-block bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-amber-200 dark:border-amber-900">Low Stock</span>
            @else
                <span class="inline-block bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-emerald-200 dark:border-emerald-900">In Stock</span>
            @endif
        </td>

        <td class="px-6 py-4">
            @if ($totalStatus === 'Out of Stock')
                <span class="inline-block bg-red-100 text-red-700 dark:bg-red-950 dark:text-rose-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-red-200 dark:border-rose-900">Out of Stock</span>
            @elseif ($totalStatus === 'Low Stock')
                <span class="inline-block bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-amber-200 dark:border-amber-900">Low Stock</span>
            @else
                <span class="inline-block bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-emerald-200 dark:border-emerald-900">In Stock</span>
            @endif
        </td>

        <td class="px-6 py-4 text-right text-gray-600 dark:text-slate-300 font-medium">
            Store {{ $storeQty }} · WH {{ $whQty }}
            <span class="text-gray-400 text-xs block"
                  title="Reorder Point — reorder this product when stock drops to {{ $reorder }}">
                Reorder at {{ $reorder }}
            </span>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="6" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500">
            No inventory alerts at this time. All products are well-stocked!
        </td>
    </tr>
@endforelse