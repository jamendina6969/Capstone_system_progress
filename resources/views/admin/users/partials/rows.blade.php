@forelse ($users as $user)
    <tr data-record-row="true"
        data-user="{{ json_encode($user) }}"
        onclick="openUserDetailModal(this.dataset.user)"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer">
        <td class="px-8 py-4 font-medium text-gray-900 dark:text-white">
            <div class="flex items-center gap-2">
                <span>{{ $user->full_name }}</span>
                @if ($user->id === auth()->id())
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 dark:bg-sky-950/80 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                        You
                    </span>
                @endif
            </div>
        </td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            {{ $user->username }}
        </td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            {{ $user->email }}
        </td>
        <td class="px-8 py-4">
            @php
                $roleColors = [
                    'admin' => 'bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900',
                    'manager' => 'bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900',
                    'warehouse_staff' => 'bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900',
                    'staff' => 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300 border border-gray-200 dark:border-slate-700',
                ];
            @endphp
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $roleColors[$user->role] ?? 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-300' }}">
                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
            </span>
        </td>
        <td class="px-8 py-4">
            <div class="flex flex-wrap items-center gap-1.5">
                @if ($user->is_active)
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900">
                        Active
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-rose-950/80 text-red-700 dark:text-rose-300 border border-red-200 dark:border-rose-900">
                        Inactive
                    </span>
                @endif

                @if ($user->must_change_password)
                    <span title="User must change temporary password on next login"
                          class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        Pwd Reset Pending
                    </span>
                @endif
            </div>
        </td>
        <td class="px-8 py-4 text-right" onclick="event.stopPropagation()">
            <div class="flex justify-end gap-2 items-center">
                <button type="button"
                        data-user="{{ json_encode($user) }}"
                        onclick="openEditUserModal(this.dataset.user)"
                        class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Edit
                </button>

                @if ($user->id !== auth()->id())
                    <button type="button"
                            onclick="openDeleteUserModal({{ $user->id }}, '{{ addslashes($user->full_name) }}')"
                            class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                        Delete
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="6" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500 font-medium">
            No users found.
        </td>
    </tr>
@endforelse
