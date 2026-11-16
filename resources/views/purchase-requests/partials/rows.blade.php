@forelse($requests as $pr)
    <tr data-record-row="true" class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">
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
        <td class="px-8 py-4 text-right">
            <div class="flex justify-end gap-2">
                {{-- VIEW BUTTON --}}
                <button type="button"
                    onclick="openViewPRModal({{ json_encode($pr->load('user', 'items.product', 'supplier')) }})"
                    class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">
                    View
                </button>

                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                    @if($pr->status === 'pending')
                        <button type="button" 
                            onclick="openActionModal('approve', {{ $pr->id }}, '{{ $pr->pr_number }}')"
                            class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">
                            Approve
                        </button>
                        
                        <button type="button" 
                            onclick="openActionModal('reject', {{ $pr->id }}, '{{ $pr->pr_number }}')"
                            class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">
                            Reject
                        </button>
                    @endif

                    @if($pr->status === 'approved')
                        <button type="button" 
                            onclick="openConvertModal({{ $pr->id }}, '{{ $pr->pr_number }}')"
                            class="bg-white border-2 border-blue-600 text-blue-700 hover:bg-blue-600 hover:text-white dark:bg-[#10141D] dark:border-blue-600/80 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">
                            Convert to PO
                        </button>
                    @endif
                @endif
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