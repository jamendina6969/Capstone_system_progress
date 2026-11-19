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
            <button type="button" 
                    onclick="openDiscrepancyModal({{ json_encode($po->load('items.product.category.parent', 'items.product.category', 'supplier')) }})"
                    class="inline-flex items-center gap-1.5 bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 text-xs font-semibold px-3.5 py-1.5 rounded-xl shadow-xs transition-all duration-200 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <span>View</span>
            </button>
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