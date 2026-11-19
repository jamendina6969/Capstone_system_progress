@forelse ($logs as $log)
    <tr
        data-record-row="true"
        onclick="openAuditModal({{ $log->id }})"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer"
    >
        {{-- RECORDED BY COLUMN WITH RED DELETED TAG --}}
        <td class="px-8 py-4 font-semibold text-gray-900 dark:text-white">
            <div class="flex flex-col">
                @if($log->actor_name)
                    <span class="text-gray-900 dark:text-white">{{ $log->actor_name }}</span>
                @elseif($log->user)
                    <span class="text-gray-900 dark:text-white">{{ $log->user->full_name ?? $log->user->firstname . ' ' . $log->user->lastname }}</span>
                @else
                    <span class="text-gray-500 dark:text-slate-400 italic">System / Guest</span>
                @endif

                {{-- Show the red tag if the user relationship is gone --}}
                @if(!$log->user)
                    <span class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider mt-0.5">(Deleted User)</span>
                @endif
            </div>
        </td>

        <td class="px-8 py-4">
            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border
                {{ $log->event === 'created' ? 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-400 dark:border-emerald-900' : '' }}
                {{ $log->event === 'updated' ? 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-950 dark:text-blue-400 dark:border-blue-900' : '' }}
                {{ $log->event === 'deleted' ? 'bg-red-100 text-red-700 border-red-200 dark:bg-red-950 dark:text-rose-400 dark:border-red-900' : '' }}
                {{ $log->event === 'login' ? 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-900' : '' }}
                {{ $log->event === 'logout' ? 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700' : '' }}
                {{ !in_array($log->event, ['created', 'updated', 'deleted', 'login', 'logout']) ? 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-950 dark:text-purple-400 dark:border-purple-900' : '' }}"
            >
                {{ $log->event }}
            </span>
        </td>

        <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">
            {{ $log->auditable_type ? class_basename($log->auditable_type) : 'System Event' }}
            @if($log->auditable_id)
                #{{ $log->auditable_id }}
            @endif
        </td>

        <td class="px-8 py-4 text-gray-500 dark:text-slate-400 text-xs font-mono">
            {{ $log->ip_address ?? '—' }}
        </td>

        <td class="px-8 py-4 text-gray-500 dark:text-slate-400 text-xs">
            {{ $log->created_at?->format('M d, Y h:i A') ?? '—' }}
        </td>

        <td class="px-8 py-4 text-right" onclick="event.stopPropagation()">
            <button type="button"
                onclick="openAuditModal({{ $log->id }})"
                class="inline-flex items-center gap-1.5 bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 text-xs font-semibold px-3.5 py-1.5 rounded-xl shadow-xs transition-all duration-200 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <span>View</span>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500 font-medium">
            No audit log records found.
        </td>
    </tr>
@endforelse