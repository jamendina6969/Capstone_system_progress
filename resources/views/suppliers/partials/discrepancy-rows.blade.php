@forelse($ordersWithDiscrepancies as $po)
    <tr data-record-row="true"
        onclick="openDiscrepancyModal({{ json_encode($po->load('items.product.category.parent', 'items.product.category', 'supplier')) }})"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer">
        <td class="px-8 py-4 font-bold text-gray-900 dark:text-white">
            {{ $po->po_number }}
        </td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            {{ $po->supplier->name }}
        </td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            {{ $po->received_at ? $po->received_at->format('M d, Y') : '—' }}
        </td>
        <td class="px-8 py-4 text-right text-gray-600 dark:text-slate-400">
            ₱{{ number_format($po->total_amount, 2) }}
        </td>
        <td class="px-8 py-4 text-right">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900/50">
                - ₱{{ number_format($po->discrepancy_amount, 2) }}
            </span>
        </td>
        <td class="px-8 py-4 text-right" onclick="event.stopPropagation()">
            <div class="inline-flex items-center justify-end" data-row-actions>
                <button type="button" onclick="openRowActionMenu(event, this)"
                        class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 transition-all shadow-xs cursor-pointer"
                        aria-label="Actions" title="Actions">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </button>
                <div class="row-action-items hidden">
                    <button type="button" data-tone="default" data-label="View Discrepancy"
                            onclick="openDiscrepancyModal({{ json_encode($po->load('items.product.category.parent', 'items.product.category', 'supplier')) }})">View Discrepancy</button>
                    <a href="{{ route('supplier-returns.index', ['supplier_id' => $po->supplier_id, 'purchase_order_id' => $po->id]) }}"
                       data-tone="amber" data-label="File Supplier Return">File Supplier Return</a>
                    <a href="{{ route('suppliers.ledger', $po->supplier_id) }}"
                       data-tone="blue" data-label="View Supplier Ledger">View Supplier Ledger</a>
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="6" class="px-8 py-12 text-center text-gray-400 font-medium">
            <div class="flex flex-col items-center justify-center">
                <svg class="w-12 h-12 text-emerald-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p>No discrepancies found matching your criteria.</p>
            </div>
        </td>
    </tr>
@endforelse