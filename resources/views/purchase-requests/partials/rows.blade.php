@forelse($requests as $pr)
    <tr data-record-row="true"
        onclick="openViewPRModal({{ json_encode($pr->load('user', 'items.product', 'supplier')) }})"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer">
        <td class="px-8 py-4 font-bold text-gray-900 dark:text-white">
            {{ $pr->pr_number }}
        </td>
        
        {{-- UPDATED USER COLUMN WITH SNAPSHOT & RED TAG --}}
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            <div class="flex flex-col">
                @if($pr->requester_name)
                    <span class="font-medium text-gray-900 dark:text-white">{{ $pr->requester_name }}</span>
                @elseif($pr->user)
                    <span class="font-medium text-gray-900 dark:text-white">{{ $pr->user->firstname }} {{ $pr->user->lastname }}</span>
                @else
                    <span class="font-medium text-gray-500 dark:text-slate-400 italic">Unknown</span>
                @endif

                @if(!$pr->user)
                    <span class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider mt-0.5">(Deleted User)</span>
                @endif
            </div>
        </td>

        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            {{ $pr->created_at->format('M d, Y') }}
        </td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400 max-w-xs truncate">
            {{ \Illuminate\Support\Str::limit($pr->reason, 60) }}
        </td>
        <td class="px-8 py-4">
            @if($pr->status === 'pending')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
            @elseif($pr->status === 'approved')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">Approved</span>
            @elseif($pr->status === 'rejected')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">Rejected</span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">{{ ucfirst($pr->status) }}</span>
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
                        onclick="openViewPRModal({{ json_encode($pr->load('user', 'items.product', 'supplier')) }})">View Details</button>

                    @if(in_array(auth()->user()->role, ['admin', 'manager']))
                        @if($pr->status === 'pending')
                            <button type="button" data-tone="emerald" data-label="Approve Request"
                                onclick="openActionModal('approve', {{ $pr->id }}, '{{ $pr->pr_number }}')">Approve Request</button>
                            <button type="button" data-tone="rose" data-label="Reject Request"
                                onclick="openActionModal('reject', {{ $pr->id }}, '{{ $pr->pr_number }}')">Reject Request</button>
                        @endif

                        @if($pr->status === 'approved')
                            <button type="button" data-tone="blue" data-label="Convert to PO"
                                onclick="openConvertModal({{ $pr->id }}, '{{ $pr->pr_number }}')">Convert to PO</button>
                        @endif
                    @endif
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="6" class="px-8 py-12 text-center text-gray-400 font-medium">
            No purchase requests found.
        </td>
    </tr>
@endforelse