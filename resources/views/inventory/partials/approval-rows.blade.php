@forelse ($pending as $movement)
    <tr data-record-row="true"
        data-request="{{ json_encode($movement) }}"
        onclick="openViewModal(this.dataset.request)"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer">
        <td class="px-8 py-4 font-medium text-gray-900 dark:text-white">
            {{ $movement->product->product_name ?? 'Deleted Product' }}
        </td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            {{ ucwords(str_replace('_', ' ', $movement->type)) }}
        </td>
        <td class="px-8 py-4 text-gray-900 dark:text-slate-100 font-semibold">
            +{{ $movement->quantity }}
        </td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            {{ $movement->requester->firstname ?? 'N/A' }} {{ $movement->requester->lastname ?? '' }}
        </td>
        <td class="px-8 py-4 text-gray-500 dark:text-slate-400 text-xs">
            {{ $movement->created_at->format('M d, Y - h:i A') }}
        </td>
        <td class="px-8 py-4 min-w-[190px] whitespace-nowrap">
            @if ($movement->status === 'pending')
                <span class="inline-block whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900">Pending</span>
            @elseif ($movement->status === 'approved')
                <span class="inline-block whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">Approved (In Transit)</span>
            @endif
        </td>
        <td class="px-8 py-4 text-gray-500 dark:text-slate-400">
            {{ $movement->note ?? '—' }}
        </td>
        <td class="px-8 py-4 text-right" onclick="event.stopPropagation()">
            <div class="flex justify-end gap-2 items-center">
                @if ($movement->status === 'pending')
                    <button type="button" onclick="openApproveModal('{{ route('inventory.approve', $movement) }}')" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Approve</button>
                    <button type="button" onclick="toggleRejectForm({{ $movement->id }})" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Reject</button>
                @else
                    <span class="text-gray-400 dark:text-slate-600 text-xs italic mr-2">Awaiting Receipt</span>
                @endif

                <button type="button" data-request="{{ json_encode($movement) }}" onclick="openViewModal(this.dataset.request)" class="inline-flex items-center gap-1.5 bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 text-xs font-semibold px-3.5 py-1.5 rounded-xl shadow-xs transition-all duration-200 cursor-pointer" title="View Details">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span>View</span>
                </button>
            </div>
        </td>
    </tr>

    @if ($movement->status === 'pending')
        <tr id="reject-row-{{ $movement->id }}" class="reject-form-row hidden bg-red-50/50 dark:bg-red-950/20">
            <td colspan="8" class="px-8 py-4 border-t border-red-100 dark:border-red-900/30">
                <form action="{{ route('inventory.reject', $movement) }}" method="POST" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 uppercase mb-1">Reason for rejection</label>
                        <input type="text" name="rejection_reason" required placeholder="e.g. Quantity seems incorrect, please recheck" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                    </div>
                    <button type="submit" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs whitespace-nowrap cursor-pointer">Confirm Reject</button>
                    <button type="button" onclick="toggleRejectForm({{ $movement->id }})" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Cancel</button>
                </form>
            </td>
        </tr>
    @endif
@empty
    <tr data-empty-row="true">
        <td colspan="8" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500 font-medium">
            No pending approvals found matching your criteria.
        </td>
    </tr>
@endforelse