@forelse($purchaseOrders as $po)
    <tr data-record-row="true" class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">
        <td class="px-8 py-4 font-bold text-gray-900 dark:text-white">
            {{ $po->po_number }}
        </td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            {{ $po->supplier->name ?? 'N/A' }}
        </td>
        
        {{-- REMOVED BLUE COLOR: Inherits the gray text color from the td --}}
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            @if($po->expected_delivery_date)
                {{ $po->expected_delivery_date->format('M d, Y') }}
            @else
                <span class="text-gray-400 italic text-xs">Not set</span>
            @endif
        </td>

        <td class="px-8 py-4 font-bold">
            ₱{{ number_format($po->total_amount, 2) }}
        </td>
        <td class="px-8 py-4">
            @if($po->status === 'pending')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
            @elseif($po->status === 'received')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">Received</span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">{{ ucfirst($po->status) }}</span>
            @endif
        </td>
        <td class="px-8 py-4 text-right">
            <div class="flex justify-end gap-2">
                <button type="button"
                    onclick="openViewPOModal({{ json_encode($po->load('items.product.category.parent', 'items.product.category', 'supplier', 'user')) }})"
                    class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">
                    View
                </button>

                @if($po->status === 'pending')
                    <button type="button"
                        data-receive-url="{{ route('purchase-orders.receive', $po->id) }}"
                        onclick="openReceiveModal({{ json_encode($po->load('items.product', 'supplier')) }}, this.dataset.receiveUrl)"
                        class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">
                        Receive Order
                    </button>
                @endif

                <a href="{{ route('purchase-orders.pdf', $po) }}"
                    class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">
                    PDF
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="6" class="px-8 py-12 text-center text-gray-400 font-medium">
            No purchase orders found.
        </td>
    </tr>
@endforelse