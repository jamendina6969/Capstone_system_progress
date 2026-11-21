@forelse($returns as $return)
    <tr data-record-row="true"
        data-return="{{ json_encode($return->load('items.product.category.parent', 'items.product.category', 'supplier')) }}"
        onclick="openViewReturnModal(this.dataset.return)"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer">
        <td class="px-8 py-4 font-bold text-gray-900 dark:text-white">{{ $return->return_number }}</td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">{{ $return->supplier->name }}</td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">{{ ucwords(str_replace('_', ' ', $return->reason)) }}</td>
        <td class="px-8 py-4 font-bold">₱{{ number_format($return->total_amount, 2) }}</td>
        <td class="px-8 py-4">
            @if($return->status === 'pending')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
            @elseif($return->status === 'completed')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">Completed</span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">Cancelled</span>
            @endif
        </td>
        <td class="px-8 py-4 text-right" onclick="event.stopPropagation()">
            <div class="inline-flex items-center justify-end" data-row-actions>
                <button type="button" onclick="openRowActionMenu(event, this)"
                        class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 transition-all shadow-xs cursor-pointer"
                        aria-label="Actions" title="Actions">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </button>

                @if($return->status === 'pending')
                    <form action="{{ route('supplier-returns.approve', $return) }}" method="POST" id="process-form-{{ $return->id }}" class="hidden">@csrf</form>
                    <form action="{{ route('supplier-returns.reject', $return) }}" method="POST" id="cancel-form-{{ $return->id }}" class="hidden">@csrf</form>
                @endif

                <div class="row-action-items hidden">
                    <button type="button" data-tone="default" data-label="View Details"
                            data-return="{{ json_encode($return->load('items.product.category.parent', 'items.product.category', 'supplier')) }}"
                            onclick="openViewReturnModal(this.dataset.return)">View Details</button>

                    @if($return->status === 'pending')
                        <button type="button" data-tone="emerald" data-label="Process / Approve"
                                onclick="openActionModal('process', 'process-form-{{ $return->id }}')">Process / Approve</button>
                        <button type="button" data-tone="rose" data-label="Cancel Return"
                                onclick="openActionModal('cancel', 'cancel-form-{{ $return->id }}')">Cancel Return</button>
                    @endif
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="6" class="px-8 py-12 text-center text-gray-400 font-medium">No returns found.</td>
    </tr>
@endforelse