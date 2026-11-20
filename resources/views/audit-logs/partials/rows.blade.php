@forelse ($logs as $log)
    @php
        $roleLabel = \App\Http\Controllers\AuditLogController::formatRoleLabel($log->display_role ?? $log->actor_role ?? $log->user?->role);
        $rolePillClass = match($log->display_role ?? $log->actor_role ?? $log->user?->role) {
            'admin'           => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900',
            'manager'         => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-900',
            'warehouse_staff' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900',
            'staff'           => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900',
            default           => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
        };
    @endphp
    <tr
        data-record-row="true"
        onclick="openAuditModal({{ $log->id }})"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer"
    >
        {{-- ACTOR + ROLE PILL --}}
        <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-gray-900 dark:text-white">{{ $log->display_actor ?? $log->actor_name ?? 'System / Guest' }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $rolePillClass }}">
                        {{ $roleLabel }}
                    </span>
                </div>
                @if($log->is_deleted_user ?? ($log->user_id !== null && !$log->user))
                    <span class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider">(Deleted Account)</span>
                @endif
            </div>
        </td>

        {{-- HUMAN-READABLE ACTION BADGE --}}
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $log->display_badge ?? \App\Http\Controllers\AuditLogController::eventBadgeClass((string) $log->event) }}">
                {{ $log->display_event ?? \App\Http\Controllers\AuditLogController::formatEventLabel((string) $log->event) }}
            </span>
        </td>

        {{-- TARGET + 1-LINE SUMMARY --}}
        <td class="px-6 py-4">
            <div class="font-semibold text-gray-800 dark:text-slate-200 text-xs">
                {{ $log->display_target ?? ($log->auditable_type ? class_basename($log->auditable_type) . ($log->auditable_id ? ' #' . $log->auditable_id : '') : 'System Event') }}
            </div>
            @if(!empty($log->display_summary))
                <p class="text-[11px] text-gray-500 dark:text-slate-400 mt-0.5 line-clamp-1 max-w-md">
                    {{ $log->display_summary }}
                </p>
            @endif
        </td>

        {{-- REAL CLIENT IP + DEVICE --}}
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-gray-700 dark:text-slate-300 text-xs font-mono font-semibold">
                {{ $log->ip_address ?? '—' }}
            </div>
            <div class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5">
                {{ $log->display_device ?? 'Unknown Device' }}
            </div>
        </td>

        {{-- TIMESTAMP --}}
        <td class="px-6 py-4 text-gray-500 dark:text-slate-400 text-xs whitespace-nowrap">
            {{ $log->created_at?->format('M d, Y h:i A') ?? '—' }}
        </td>

        {{-- VIEW ACTION --}}
        <td class="px-6 py-4 text-right whitespace-nowrap" onclick="event.stopPropagation()">
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
            No audit log records found matching your filters.
        </td>
    </tr>
@endforelse
