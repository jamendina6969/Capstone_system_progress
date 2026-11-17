@forelse($returns as $return)
    <tr data-record-row="true" class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">
        <td class="px-8 py-4">
            <div class="font-bold text-gray-900 dark:text-white">{{ $return->return_number }}</div>
            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ $return->created_at->format('M d, Y · h:i A') }}</div>
        </td>

        <td class="px-8 py-4">
            <div class="font-semibold text-gray-900 dark:text-white">{{ $return->transaction->transaction_id ?? 'N/A' }}</div>
            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                {{ $return->transaction->customer_name ?? 'Walk-in' }}
            </div>
        </td>

        <td class="px-8 py-4 text-gray-600 dark:text-slate-400 max-w-[220px] truncate" title="{{ $return->reason }}">
            {{ $return->reason }}
        </td>

        <td class="px-8 py-4">
            <div class="font-bold text-gray-900 dark:text-white">₱{{ number_format($return->refund_amount, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ ucwords(str_replace('_', ' ', $return->refund_method)) }}</div>
        </td>

        <td class="px-8 py-4">
            @if($return->status === 'completed')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">Completed</span>
            @elseif($return->status === 'voided')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">Voided</span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">{{ ucfirst($return->status) }}</span>
            @endif
        </td>

        <td class="px-8 py-4 text-right">
            <div class="flex items-center justify-end gap-2">
                <button type="button"
                        data-return="{{ json_encode($return->load('items.product', 'transaction', 'user')) }}"
                        onclick="openViewReturnModal(this.dataset.return)"
                        class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all duration-300 shadow-xs">
                    View
                </button>

                @if($return->status === 'completed')
                    <button type="button"
                            onclick="openVoidReturnModal({{ $return->id }}, '{{ $return->return_number }}')"
                            class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-full transition-all shadow-xs">
                        Void
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="6" class="px-8 py-12 text-center text-gray-400 font-medium">No returns found.</td>
    </tr>
@endforelse