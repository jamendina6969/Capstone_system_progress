@forelse($purchaseOrders as $po)
    <tr data-record-row="true"
        onclick="openViewPOModal({{ json_encode($po->load('items.product.category.parent', 'items.product.category', 'supplier', 'user')) }})"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer">
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
        <td class="px-8 py-4 text-right" onclick="event.stopPropagation()">
            <div class="inline-flex justify-end items-center" data-row-actions>
                <button type="button" onclick="openRowActionMenu(event, this)"
                        class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 transition-all shadow-xs cursor-pointer"
                        aria-label="Actions" title="Actions">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </button>
                <div class="row-action-items hidden">
                    <button type="button" data-tone="default" data-label="View Details"
                        onclick="openViewPOModal({{ json_encode($po->load('items.product.category.parent', 'items.product.category', 'supplier', 'user')) }})">View Details</button>

                    @if($po->status === 'pending')
                        <button type="button" data-tone="emerald" data-label="Receive Order (GRN)"
                            data-receive-url="{{ route('purchase-orders.receive', $po->id) }}"
                            onclick="openReceiveModal({{ json_encode($po->load('items.product', 'supplier')) }}, this.dataset.receiveUrl)">Receive Order (GRN)</button>
                    @endif

                    <a href="{{ route('purchase-orders.pdf', $po) }}" data-tone="blue" data-label="Download PDF">Download PDF</a>
                </div>
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