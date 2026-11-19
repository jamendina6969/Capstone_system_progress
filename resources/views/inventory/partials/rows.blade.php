{{-- resources/views/inventory/partials/rows.blade.php --}}

@forelse ($products as $product)
    @php
        $canTransfer = in_array(auth()->user()->role, ['admin', 'manager']);

        $storeQty = $product->store_quantity ?? 0;
        $whQty = $product->warehouse_quantity ?? 0;
        $totalQty = $storeQty + $whQty;
        $reorder = $product->reorder_point ?? 10;

        $storeStatus = $product->store_status ?? ($storeQty <= 0 ? 'Out of Stock' : ($storeQty <= $reorder ? 'Low Stock' : 'In Stock'));
        $whStatus = $product->warehouse_status ?? ($whQty <= 0 ? 'Out of Stock' : ($whQty <= $reorder ? 'Low Stock' : 'In Stock'));
        $totalStatus = $product->total_status ?? ($totalQty <= 0 ? 'Out of Stock' : ($totalQty <= $reorder ? 'Low Stock' : 'In Stock'));

        $statusClasses = [
            'Out of Stock' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-rose-400 border-red-200 dark:border-rose-900',
            'Low Stock' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 border-amber-200 dark:border-amber-900',
            'In Stock' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900',
        ];
    @endphp

    {{-- Main product row --}}
    <tr
        data-record-row="true"
        data-product-id="{{ $product->id }}"
        data-product-page="{{ $products->currentPage() }}"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer"
        onclick="openProductDetail({{ $product->id }})"
    >
        <td class="px-6 py-4 align-middle min-w-[180px]">
            <div class="font-semibold text-gray-900 dark:text-white text-sm leading-5">
                {{ $product->product_name }}
            </div>
            @if ($product->approx_volume)
                <div class="text-[11px] text-gray-400 mt-0.5 leading-4">
                    {{ $product->approx_volume }}
                </div>
            @endif
        </td>

        <td class="px-6 py-4 align-middle whitespace-nowrap font-mono text-xs font-semibold text-gray-800 dark:text-slate-200">
            {{ $product->product_sku ?? '—' }}
        </td>

        <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 dark:text-slate-400 font-medium">
            {{ $product->category->name ?? '—' }}
        </td>

        <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 dark:text-slate-400 font-medium">
            ₱{{ $product->purchase_cost !== null ? number_format($product->purchase_cost, 2) : '—' }}
        </td>

        <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-gray-600 dark:text-slate-400 font-medium">
            ₱{{ $product->selling_price !== null ? number_format($product->selling_price, 2) : '—' }}
        </td>

        <td data-col="store_quantity" class="px-6 py-4 align-middle text-sm text-gray-700 dark:text-slate-300 font-semibold">
            {{ $storeQty }}
        </td>

        <td data-col="warehouse_quantity" class="px-6 py-4 align-middle text-sm text-gray-700 dark:text-slate-300 font-semibold">
            {{ $whQty }}
        </td>

        <td class="px-6 py-4 align-middle">
            <span class="inline-flex items-center whitespace-nowrap border text-[10px] leading-4 font-semibold px-3 py-1 rounded-full {{ $statusClasses[$storeStatus] ?? $statusClasses['In Stock'] }}">
                {{ $storeStatus }}
            </span>
        </td>

        <td class="px-6 py-4 align-middle">
            <span class="inline-flex items-center whitespace-nowrap border text-[10px] leading-4 font-semibold px-3 py-1 rounded-full {{ $statusClasses[$whStatus] ?? $statusClasses['In Stock'] }}">
                {{ $whStatus }}
            </span>
        </td>

        <td class="px-6 py-4 align-middle">
            <span class="inline-flex items-center whitespace-nowrap border text-[10px] leading-4 font-semibold px-3 py-1 rounded-full {{ $statusClasses[$totalStatus] ?? $statusClasses['In Stock'] }}">
                {{ $totalStatus }}
            </span>
        </td>

        <td class="px-6 py-4 align-middle text-right whitespace-nowrap" onclick="event.stopPropagation()">
            <div class="flex justify-end items-center gap-2">
                <button
                    type="button"
                    onclick="toggleInventoryAction({{ $product->id }}, 'restock')"
                    class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs cursor-pointer"
                >
                    Restock
                </button>
                @if ($canTransfer)
                    <button
                        type="button"
                        onclick="toggleInventoryAction({{ $product->id }}, 'transfer')"
                        class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs cursor-pointer"
                    >
                        Transfer
                    </button>
                @endif
            </div>
        </td>
    </tr>

    {{-- Hidden Restock form row (toggled by toggleInventoryAction) --}}
    <tr id="restock-{{ $product->id }}" class="hidden">
        <td colspan="11" class="px-6 py-4 bg-gray-50 dark:bg-[#161B26]/60 border-t border-gray-100 dark:border-[#1e2536]">
            @include('partials.restockForm', ['product' => $product])
        </td>
    </tr>

    {{-- Hidden Transfer form row (toggled by toggleInventoryAction) --}}
    @if ($canTransfer)
        <tr id="transfer-{{ $product->id }}" class="hidden">
            <td colspan="11" class="px-6 py-4 bg-gray-50 dark:bg-[#161B26]/60 border-t border-gray-100 dark:border-[#1e2536]">
                @include('partials.transferForm', ['product' => $product])
            </td>
        </tr>
    @endif
@empty
    <tr data-empty-row="true">
        <td colspan="11" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500">
            No products found.
        </td>
    </tr>
@endforelse