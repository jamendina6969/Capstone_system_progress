@forelse ($requests as $req)
    <tr data-record-row="true" class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">
        <td class="px-6 py-4 text-gray-500 dark:text-slate-400 whitespace-nowrap">
            {{ $req->created_at->format('M d, Y - h:i A') }}
        </td>
        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
            {{ $req->requester_name ?? ($req->requester ? $req->requester->firstname . ' ' . $req->requester->lastname : 'Deleted User') }}
        </td>
        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
            {{ $req->product->product_name ?? 'Deleted Product' }}
        </td>
        <td class="px-6 py-4 text-gray-900 dark:text-slate-200 font-medium">
            {{ number_format($req->quantity) }}
        </td>
        <td class="px-6 py-4">
            @if ($req->status === 'pending')
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900">Pending</span>
            @elseif ($req->status === 'approved')
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-emerald-950/80 text-green-700 dark:text-emerald-300 border border-green-200 dark:border-emerald-900">Approved</span>
            @elseif ($req->status === 'rejected')
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-rose-950/80 text-red-700 dark:text-rose-300 border border-red-200 dark:border-rose-900">Rejected</span>
            @endif
        </td>
        <td class="px-6 py-4">
            @if ($req->status === 'rejected' || $req->delivery_status === 'not_approved')
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-rose-950/80 text-red-700 dark:text-rose-300 border border-red-200 dark:border-rose-900">Not Approved</span>
            @elseif ($req->delivery_status === 'received')
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-emerald-950/80 text-green-700 dark:text-emerald-300 border border-green-200 dark:border-emerald-900">Received</span>
            @elseif ($req->delivery_status === 'in_transit')
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">In Transit</span>
            @elseif ($req->delivery_status === 'pending')
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900">Pending</span>
            @else
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">{{ $req->delivery_status ?? '—' }}</span>
            @endif
        </td>
        <td class="px-6 py-4 text-gray-600 dark:text-slate-400">
            {{ $req->approver_name ?? ($req->approver ? $req->approver->firstname . ' ' . $req->approver->lastname : '—') }}
        </td>
        <td class="px-6 py-4 text-right">
            <div class="flex items-center justify-end gap-2">
                @if ($req->status === 'approved' && $req->delivery_status === 'in_transit')
                    <button type="button" data-request-id="{{ $req->id }}" onclick="openReceiveModal(this.dataset.requestId)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-3 py-2 rounded-xl transition-all duration-300 shadow-xs cursor-pointer inline-flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Mark as Received
                    </button>
                @endif

                <button type="button" data-request="{{ json_encode($req) }}" onclick="openViewModal(this.dataset.request)" class="bg-gray-100 border-2 border-gray-200 text-gray-700 hover:bg-gray-200 dark:bg-[#161B26] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#1e2536] text-xs font-semibold px-3 py-2 rounded-xl transition-all duration-300 shadow-xs cursor-pointer inline-flex items-center justify-center" title="View Details">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="8" class="px-6 py-10 text-center text-gray-400 dark:text-slate-500">
            No restock requests found matching your criteria.
        </td>
    </tr>
@endforelse