@forelse ($products as $product)
    <tr data-record-row="true" data-product-page="{{ $products->currentPage() }}" class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">
        <td class="px-8 py-4 align-middle">
            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                   class="row-checkbox rounded border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#10141D] text-blue-600 focus:ring-slate-400 w-4 h-4 cursor-pointer">
        </td>
        
        {{-- PRODUCT COLUMN --}}
        <td class="px-8 py-4 align-middle">
            <span class="font-bold text-gray-900 dark:text-white text-base">
                {{ $product->product_name }}
                @if($product->approx_volume)
                    <span class="text-xs text-gray-500 dark:text-slate-400 font-normal ml-1">({{ $product->approx_volume }})</span>
                @endif
            </span>
        </td>
        
        {{-- MAIN CATEGORY COLUMN --}}
        <td class="px-8 py-4 align-middle text-gray-600 dark:text-slate-400 font-medium">
            @if($product->category && $product->category->parent)
                {{ $product->category->parent->name }}
            @elseif($product->category)
                {{ $product->category->name }}
            @else
                —
            @endif
        </td>

        {{-- BRAND / SUBCATEGORY COLUMN --}}
        <td class="px-8 py-4 align-middle text-gray-600 dark:text-slate-400 font-medium">
            @if($product->category && $product->category->parent)
                {{ $product->category->name }}
            @elseif($product->category)
                <span class="text-xs text-gray-400 uppercase tracking-wider italic">Direct Item</span>
            @else
                —
            @endif
        </td>
        
        {{-- DEMAND COLUMN --}}
        <td class="px-8 py-4 align-middle text-gray-900 dark:text-slate-100 font-bold">
            {{ number_format($product->display_demand, 0) }}
            <span class="text-[10px] text-gray-400 font-normal ml-1">/ {{ rtrim($timeframe, 'ly') }}</span>
        </td>
        
        {{-- COSTS & EOQ --}}
        <td class="px-8 py-4 align-middle text-gray-600 dark:text-slate-400">₱{{ number_format($product->computed_ordering_cost, 2) }}</td>
        <td class="px-8 py-4 align-middle text-gray-600 dark:text-slate-400">₱{{ number_format($product->computed_holding_cost, 2) }}</td>
        <td class="px-8 py-4 align-middle font-bold text-gray-900 dark:text-slate-100">
            {{ $product->display_eoq > 0 ? ceil($product->display_eoq) . ' units' : '—' }}
        </td>
    </tr>
@empty
    <tr data-empty-row="true"><td colspan="8" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500 font-medium">No products found.</td></tr>
@endforelse