@extends('layouts.default')

@section('title', 'Users Management')

@section('content')

    @php
        $suffixOptions = $allowedSuffixes ?? ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
        $addModalHasErrors = $errors->any() && old('modal_type') === 'add';
        $editModalHasErrors = $errors->any() && old('modal_type') === 'edit';
    @endphp

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">User Management</h1>

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-140px)] lg:min-h-[500px] mb-4">

        <!-- Header -->
        <div class="shrink-0 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">System Users</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage admin, manager, warehouse staff and staff accounts</p>
            </div>

            <button type="button" onclick="openAddUserModal()"
               class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer inline-flex items-center">
                + Add User
            </button>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full min-w-[760px] text-sm text-left relative">
                <thead id="usersTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Name</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Username</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Email</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Role</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Status</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('admin.users.partials.rows', ['users' => $users])
                </tbody>
            </table>

            {{-- LOADING --}}
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more users...
            </div>

            {{-- ERROR --}}
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more users. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($users->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $users->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $users->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $users->total() }}</span>
                    users
                @else
                    No users found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="User pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                        @if ($page == $users->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- Add User Popup Modal                                             -->
    <!-- ================================================================ -->
    <div id="addUserModal" class="fixed inset-0 z-50 {{ $addModalHasErrors ? '' : 'hidden' }} overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-start sm:items-center justify-center p-4">
        <div class="relative w-full max-w-2xl my-6 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 sm:p-8 max-h-[92vh] overflow-y-auto custom-scrollbar">

            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New User</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">New accounts are required to change their temporary password upon first login.</p>
                </div>
                <button type="button" onclick="closeAddUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="modal_type" value="add">

                @if ($addModalHasErrors)
                    <div class="rounded-xl bg-red-100 dark:bg-rose-950/70 text-red-700 dark:text-rose-300 border-2 border-red-200 dark:border-rose-900 px-4 py-3 text-sm font-medium">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="clipboard-block-notice hidden rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 px-3.5 py-2 text-xs font-semibold text-amber-800 dark:text-amber-300">
                    Copy and paste are disabled on password fields for security. Please type manually.
                </div>

                <!-- Name & Suffix -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="add_firstname" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">First Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="firstname" id="add_firstname" value="{{ $addModalHasErrors ? old('firstname') : '' }}" required minlength="2" maxlength="50"
                               placeholder="e.g. Juan"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label for="add_lastname" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Last Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="lastname" id="add_lastname" value="{{ $addModalHasErrors ? old('lastname') : '' }}" required minlength="2" maxlength="50"
                               placeholder="e.g. Dela Cruz"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label for="add_suffix" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Suffix <span class="text-gray-400 font-normal">(optional)</span></label>
                        <select name="suffix" id="add_suffix"
                                class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                            <option value="">None</option>
                            @foreach ($suffixOptions as $sfx)
                                <option value="{{ $sfx }}" {{ ($addModalHasErrors && old('suffix') === $sfx) ? 'selected' : '' }}>{{ $sfx }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Username & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add_username" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Username <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" id="add_username" value="{{ $addModalHasErrors ? old('username') : '' }}" required minlength="4" maxlength="30"
                               pattern="[A-Za-z0-9][A-Za-z0-9._-]*"
                               title="4-30 characters, no spaces. Letters, numbers, dots, underscores, or hyphens only."
                               placeholder="e.g. juan.delacruz"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        <p class="text-[10px] text-gray-400 mt-1">4–30 chars, no spaces (auto-lowercased).</p>
                    </div>
                    <div>
                        <label for="add_email" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" id="add_email" value="{{ $addModalHasErrors ? old('email') : '' }}" required maxlength="150"
                               placeholder="e.g. juan@stevenhydrotech.com"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <!-- Role & Active Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add_role" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role <span class="text-rose-500">*</span></label>
                        <select name="role" id="add_role"
                                class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                            <option value="staff" {{ ($addModalHasErrors ? old('role') : 'staff') === 'staff' ? 'selected' : '' }}>Staff (POS Cashier)</option>
                            <option value="warehouse_staff" {{ ($addModalHasErrors && old('role') === 'warehouse_staff') ? 'selected' : '' }}>Warehouse Staff</option>
                            <option value="manager" {{ ($addModalHasErrors && old('role') === 'manager') ? 'selected' : '' }}>Manager</option>
                            <option value="admin" {{ ($addModalHasErrors && old('role') === 'admin') ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 mt-2 sm:mt-7">
                        <input type="checkbox" name="is_active" id="add_is_active" value="1" {{ ($addModalHasErrors ? old('is_active') : true) ? 'checked' : '' }} class="rounded border-gray-300 text-gray-800 w-4 h-4 cursor-pointer">
                        <label for="add_is_active" class="text-sm font-semibold text-gray-700 dark:text-slate-300 cursor-pointer">Active account</label>
                    </div>
                </div>

                <!-- Suggested Strong Password Helper -->
                <div class="rounded-xl bg-slate-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-3 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-gray-600 dark:text-slate-300">Temporary Login Password</span>
                        <button type="button" data-suggest-password="addPasswordSuggestionBox" data-target-input="add_password" data-confirm-input="add_password_confirmation"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Suggest Strong Password
                        </button>
                    </div>
                    <div id="addPasswordSuggestionBox" class="hidden pt-1 border-t border-gray-200 dark:border-[#1e2536]">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] text-gray-500 dark:text-slate-400">Applied to Password:</span>
                            <code data-suggested-value class="select-none px-2.5 py-1 rounded-lg bg-white dark:bg-[#10141D] border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 font-mono text-xs font-bold tracking-wider"></code>
                        </div>
                        <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-1">Automatically filled into Password (you can still edit it). Retype manually in Confirm Password.</p>
                    </div>
                </div>

                <!-- Login Password & Confirm -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add_password" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Password <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="add_password" required minlength="8" maxlength="72"
                                   data-no-clipboard data-ascii-password autocomplete="new-password"
                                   placeholder="Enter strong password"
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                            <button type="button" data-toggle-password="add_password" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="add_password_confirmation" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Confirm Password <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="add_password_confirmation" required minlength="8" maxlength="72"
                                   data-no-clipboard data-ascii-password autocomplete="new-password"
                                   placeholder="Retype password manually"
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                            <button type="button" data-toggle-password="add_password_confirmation" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Live Password Requirements Checklist -->
                <div id="addPasswordChecklist" class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[11px] font-medium px-1">
                    <span data-rule="len" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>8–72 characters</span>
                    <span data-rule="upper" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Uppercase</span>
                    <span data-rule="lower" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Lowercase</span>
                    <span data-rule="num" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Number</span>
                    <span data-rule="sym" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Special char</span>
                    <span data-rule="match" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>Passwords match</span>
                </div>

                <!-- Role-Aware Manager Override Password (Admin / Manager only) -->
                <div id="addOverrideSection" class="hidden rounded-2xl bg-purple-50/60 dark:bg-purple-950/20 border-2 border-purple-200/80 dark:border-purple-900/60 p-4 space-y-3">
                    <div>
                        <p class="text-xs font-bold text-purple-900 dark:text-purple-200 uppercase">Manager Override Password <span class="text-rose-500">*</span></p>
                        <p class="text-[11px] text-purple-700/80 dark:text-purple-300/80 mt-0.5">Used to authorize stock transfers &amp; high-level actions. Must be 6–32 chars and different from the login password.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="add_action_password" class="block text-[11px] font-bold text-gray-600 dark:text-slate-400 uppercase mb-1">Override Password</label>
                            <div class="relative">
                                <input type="password" name="action_password" id="add_action_password" minlength="6" maxlength="32"
                                       data-no-clipboard data-ascii-password autocomplete="new-password"
                                       placeholder="Min 6 characters"
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                                <button type="button" data-toggle-password="add_action_password" aria-label="Toggle visibility"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                                    <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="add_action_password_confirmation" class="block text-[11px] font-bold text-gray-600 dark:text-slate-400 uppercase mb-1">Confirm Override Password</label>
                            <div class="relative">
                                <input type="password" name="action_password_confirmation" id="add_action_password_confirmation" minlength="6" maxlength="32"
                                       data-no-clipboard data-ascii-password autocomplete="new-password"
                                       placeholder="Retype override password"
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                                <button type="button" data-toggle-password="add_action_password_confirmation" aria-label="Toggle visibility"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                                    <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-[#1e2536]">
                    <button type="button" onclick="closeAddUserModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- Edit User Popup Modal                                            -->
    <!-- ================================================================ -->
    <div id="editUserModal" class="fixed inset-0 z-50 {{ $editModalHasErrors ? '' : 'hidden' }} overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-start sm:items-center justify-center p-4">
        <div class="relative w-full max-w-2xl my-6 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-6 sm:p-8 max-h-[92vh] overflow-y-auto custom-scrollbar">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit User</h3>
                <button type="button" onclick="closeEditUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form id="editUserForm" method="POST" action="{{ $editModalHasErrors && old('edit_user_id') ? url('/admin/users/' . old('edit_user_id')) : '' }}" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="modal_type" value="edit">
                <input type="hidden" name="edit_user_id" id="edit_user_id" value="{{ $editModalHasErrors ? old('edit_user_id') : '' }}">
                <input type="hidden" name="edit_original_role" id="edit_original_role" value="{{ $editModalHasErrors ? old('edit_original_role') : '' }}">
                <input type="hidden" name="edit_has_action_password" id="edit_has_action_password" value="{{ $editModalHasErrors ? old('edit_has_action_password') : '0' }}">

                @if ($editModalHasErrors)
                    <div class="rounded-xl bg-red-100 dark:bg-rose-950/70 text-red-700 dark:text-rose-300 border-2 border-red-200 dark:border-rose-900 px-4 py-3 text-sm font-medium">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="clipboard-block-notice hidden rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 px-3.5 py-2 text-xs font-semibold text-amber-800 dark:text-amber-300">
                    Copy and paste are disabled on password fields for security. Please type manually.
                </div>

                <div id="editSelfNotice" class="hidden rounded-xl bg-sky-50 dark:bg-sky-950/50 border border-sky-200 dark:border-sky-800 px-3.5 py-2 text-xs font-medium text-sky-800 dark:text-sky-300">
                    You are editing your own account. Role and Active status are locked to prevent accidental lockout.
                </div>

                <!-- Name & Suffix -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="edit_firstname" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">First Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="firstname" id="edit_firstname" value="{{ $editModalHasErrors ? old('firstname') : '' }}" required minlength="2" maxlength="50"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label for="edit_lastname" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Last Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="lastname" id="edit_lastname" value="{{ $editModalHasErrors ? old('lastname') : '' }}" required minlength="2" maxlength="50"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label for="edit_suffix" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Suffix <span class="text-gray-400 font-normal">(optional)</span></label>
                        <select name="suffix" id="edit_suffix"
                                class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                            <option value="">None</option>
                            @foreach ($suffixOptions as $sfx)
                                <option value="{{ $sfx }}" {{ ($editModalHasErrors && old('suffix') === $sfx) ? 'selected' : '' }}>{{ $sfx }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Username & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_username" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Username <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" id="edit_username" value="{{ $editModalHasErrors ? old('username') : '' }}" required minlength="4" maxlength="30"
                               pattern="[A-Za-z0-9][A-Za-z0-9._-]*"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label for="edit_email" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" id="edit_email" value="{{ $editModalHasErrors ? old('email') : '' }}" required maxlength="150"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <!-- Role & Active Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_role" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role <span class="text-rose-500">*</span></label>
                        <select name="role" id="edit_role"
                                data-original-role="{{ $editModalHasErrors ? old('edit_original_role') : '' }}"
                                data-has-action-password="{{ $editModalHasErrors ? old('edit_has_action_password') : '0' }}"
                                class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="staff" {{ ($editModalHasErrors && old('role') === 'staff') ? 'selected' : '' }}>Staff (POS Cashier)</option>
                            <option value="warehouse_staff" {{ ($editModalHasErrors && old('role') === 'warehouse_staff') ? 'selected' : '' }}>Warehouse Staff</option>
                            <option value="manager" {{ ($editModalHasErrors && old('role') === 'manager') ? 'selected' : '' }}>Manager</option>
                            <option value="admin" {{ ($editModalHasErrors && old('role') === 'admin') ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 mt-2 sm:mt-7">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" {{ ($editModalHasErrors && old('is_active')) ? 'checked' : '' }} class="rounded border-gray-300 text-gray-800 w-4 h-4 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                        <label for="edit_is_active" class="text-sm font-semibold text-gray-700 dark:text-slate-300 cursor-pointer">Active account</label>
                    </div>
                </div>

                <!-- Suggested Strong Password Helper (Edit) -->
                <div class="rounded-xl bg-slate-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-3 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-gray-600 dark:text-slate-300">Reset Password <span class="font-normal text-gray-400">(optional — leave blank to keep current)</span></span>
                        <button type="button" data-suggest-password="editPasswordSuggestionBox" data-target-input="edit_password" data-confirm-input="edit_password_confirmation"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Suggest Strong Password
                        </button>
                    </div>
                    <div id="editPasswordSuggestionBox" class="hidden pt-1 border-t border-gray-200 dark:border-[#1e2536]">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] text-gray-500 dark:text-slate-400">Applied to Password:</span>
                            <code data-suggested-value class="select-none px-2.5 py-1 rounded-lg bg-white dark:bg-[#10141D] border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 font-mono text-xs font-bold tracking-wider"></code>
                        </div>
                        <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-1">Automatically filled into New Password (you can still edit it). Retype manually in Confirm New Password.</p>
                    </div>
                </div>

                <!-- New Password & Confirm -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_password" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">New Password <span class="text-gray-400 font-normal">(optional)</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="edit_password" minlength="8" maxlength="72"
                                   data-no-clipboard data-ascii-password autocomplete="new-password"
                                   placeholder="Leave blank to keep current"
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                            <button type="button" data-toggle-password="edit_password" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="edit_password_confirmation" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="edit_password_confirmation" minlength="8" maxlength="72"
                                   data-no-clipboard data-ascii-password autocomplete="new-password"
                                   placeholder="Retype new password manually"
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                            <button type="button" data-toggle-password="edit_password_confirmation" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Live Password Requirements Checklist (Edit) -->
                <div id="editPasswordChecklist" class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[11px] font-medium px-1">
                    <span data-rule="len" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>8–72 characters</span>
                    <span data-rule="upper" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Uppercase</span>
                    <span data-rule="lower" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Lowercase</span>
                    <span data-rule="num" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Number</span>
                    <span data-rule="sym" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Special char</span>
                    <span data-rule="match" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>Passwords match</span>
                </div>

                <!-- Role-Aware Manager Override Password (Edit) -->
                <div id="editOverrideSection" class="hidden rounded-2xl bg-purple-50/60 dark:bg-purple-950/20 border-2 border-purple-200/80 dark:border-purple-900/60 p-4 space-y-3">
                    <div>
                        <p class="text-xs font-bold text-purple-900 dark:text-purple-200 uppercase">
                            Manager Override Password <span id="editOverrideHint" class="font-normal text-purple-600 dark:text-purple-400 normal-case">(leave blank to keep current)</span>
                        </p>
                        <p class="text-[11px] text-purple-700/80 dark:text-purple-300/80 mt-0.5">Used to authorize stock transfers &amp; high-level actions. Automatically cleared if demoted to Staff or Warehouse Staff.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="edit_action_password" class="block text-[11px] font-bold text-gray-600 dark:text-slate-400 uppercase mb-1">Override Password</label>
                            <div class="relative">
                                <input type="password" name="action_password" id="edit_action_password" minlength="6" maxlength="32"
                                       data-no-clipboard data-ascii-password autocomplete="new-password"
                                       placeholder="Enter new override password"
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                                <button type="button" data-toggle-password="edit_action_password" aria-label="Toggle visibility"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                                    <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="edit_action_password_confirmation" class="block text-[11px] font-bold text-gray-600 dark:text-slate-400 uppercase mb-1">Confirm Override Password</label>
                            <div class="relative">
                                <input type="password" name="action_password_confirmation" id="edit_action_password_confirmation" minlength="6" maxlength="32"
                                       data-no-clipboard data-ascii-password autocomplete="new-password"
                                       placeholder="Retype override password"
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                                <button type="button" data-toggle-password="edit_action_password_confirmation" aria-label="Toggle visibility"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                                    <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-[#1e2536]">
                    <button type="button" onclick="closeEditUserModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div id="deleteUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left" onclick="event.stopPropagation()">
            <div class="flex items-center gap-4 mb-6">
                <span class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete User</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Are you sure you want to delete <strong id="deleteUserName" class="text-gray-900 dark:text-white"></strong>? This action cannot be undone.</p>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="closeDeleteUserModal()"
                        class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out cursor-pointer">
                    Cancel
                </button>
                <form id="deleteUserForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-6 py-2.5 rounded-full transition-all duration-300 ease-in-out cursor-pointer shadow-xs">
                        Yes, Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Detail Modal -->
    <div id="userDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeUserDetailModal()">
        <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-6 sm:p-8 text-left" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-4 mb-4 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] flex items-center justify-center font-bold text-gray-900 dark:text-white" id="udmInitial">
                        U
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="udmFullName">User Details</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5" id="udmUsername">@username</p>
                    </div>
                </div>
                <button type="button" onclick="closeUserDetailModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Email Address</span>
                    <span id="udmEmail" class="font-medium text-gray-900 dark:text-white"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">System Role</span>
                    <span id="udmRole" class="font-bold"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Account Status</span>
                    <span id="udmStatus" class="font-bold"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Password Status</span>
                    <span id="udmPasswordStatus" class="font-semibold text-xs"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Created Date</span>
                    <span id="udmCreatedAt" class="text-xs text-gray-600 dark:text-slate-400"></span>
                </div>
            </div>
            <div class="flex items-center justify-between pt-5 border-t-2 border-gray-200 dark:border-[#1e2536] mt-4">
                <button type="button" id="udmEditBtn" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">
                    Edit User
                </button>
                <button type="button" onclick="closeUserDetailModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Close</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #334155; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
</style>
@endpush

@push('scripts')
@include('partials.password-security-script')
<script>
const CURRENT_AUTH_USER_ID = {{ (int) auth()->id() }};

const ADD_OVERRIDE_CONFIG = {
    mode: 'add',
    roleSelectId: 'add_role',
    sectionId: 'addOverrideSection',
    inputId: 'add_action_password',
    confirmId: 'add_action_password_confirmation',
};

const EDIT_OVERRIDE_CONFIG = {
    mode: 'edit',
    roleSelectId: 'edit_role',
    sectionId: 'editOverrideSection',
    inputId: 'edit_action_password',
    confirmId: 'edit_action_password_confirmation',
    hintId: 'editOverrideHint',
};

document.addEventListener('DOMContentLoaded', function () {
    // =====================================================
    // PASSWORD CHECKLISTS & ROLE-AWARE OVERRIDE TOGGLES
    // =====================================================
    window.bindPasswordChecklist('add_password', 'add_password_confirmation', 'addPasswordChecklist');
    window.bindPasswordChecklist('edit_password', 'edit_password_confirmation', 'editPasswordChecklist');

    const addRole = document.getElementById('add_role');
    const editRole = document.getElementById('edit_role');

    if (addRole) {
        addRole.addEventListener('change', () => window.syncOverridePasswordVisibility(ADD_OVERRIDE_CONFIG));
        window.syncOverridePasswordVisibility(ADD_OVERRIDE_CONFIG);
    }

    if (editRole) {
        editRole.addEventListener('change', () => window.syncOverridePasswordVisibility(EDIT_OVERRIDE_CONFIG));
        window.syncOverridePasswordVisibility(EDIT_OVERRIDE_CONFIG);
    }

    const editActionPass = document.getElementById('edit_action_password');
    const editActionConfirm = document.getElementById('edit_action_password_confirmation');
    if (editActionPass && editActionConfirm) {
        editActionPass.addEventListener('input', () => window.syncOverridePasswordVisibility(EDIT_OVERRIDE_CONFIG));
    }

    // =====================================================
    // INFINITE SCROLL
    // =====================================================
    const tableContainer = document.getElementById('tableScrollContainer');
    const tableBody = document.getElementById('tableBody');
    const tableHead = document.getElementById('usersTableHead');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    let nextPageUrl = @json($users->nextPageUrl());
    let currentPage = {{ $users->currentPage() }};
    let lastPage = {{ $users->lastPage() }};
    let totalRecords = {{ $users->total() }};
    let perPage = {{ $users->perPage() }};
    let initialPage = {{ $users->currentPage() }};
    let initialStart = {{ $users->firstItem() ?? 0 }} || 0;

    let isLoading = false;
    let visualPage = initialPage;

    function updateResultsCounter() {
        const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
        if (totalRecords === 0 || rowsLoaded === 0) { resultsText.textContent = 'No users found.'; return; }
        const firstRecord = initialStart;
        const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
        resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> users`;
    }

    function getVisiblePage() {
        const rows = tableBody.querySelectorAll('tr[data-record-row="true"]');
        if (!rows.length) return initialPage;

        const isAtBottom = (tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight) <= 5;
        if (isAtBottom && lastPage > 0) return lastPage;

        const containerRect = tableContainer.getBoundingClientRect();
        const headerHeight = tableHead ? tableHead.getBoundingClientRect().height : 0;
        const visibleTop = containerRect.top + headerHeight;
        const visibleBottom = containerRect.bottom;

        let firstVisibleIndex = -1;
        rows.forEach((row, index) => {
            const rect = row.getBoundingClientRect();
            if (firstVisibleIndex === -1 && rect.bottom > visibleTop && rect.top < visibleBottom) firstVisibleIndex = index;
        });

        if (firstVisibleIndex === -1) firstVisibleIndex = rows.length - 1;
        const pageOffset = Math.floor(firstVisibleIndex / perPage);
        return Math.min(lastPage, Math.max(1, initialPage + pageOffset));
    }

    function updatePaginationNumbers(pageToShow) {
        const page = Math.min(lastPage, Math.max(1, pageToShow));
        if (visualPage === page && paginationContainer.children.length > 0) return;
        visualPage = page;
        let html = '';
        const startPage = Math.max(1, page - 2);
        const endPage = Math.min(lastPage, page + 2);
        for (let i = startPage; i <= endPage; i++) {
            if (i === page) html += `<span class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">${i}</span>`;
            else html += `<button type="button" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">${i}</button>`;
        }
        paginationContainer.innerHTML = html;
    }

    async function loadMoreRecords() {
        if (!nextPageUrl || isLoading) return;
        isLoading = true;
        loadError.classList.add('hidden');
        loadingIndicator.classList.remove('hidden');

        try {
            const fetchUrl = new URL(nextPageUrl, window.location.origin);
            fetchUrl.searchParams.set('t', Date.now());
            const response = await fetch(fetchUrl.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Cache-Control': 'no-cache' } });
            if (!response.ok) throw new Error('Request failed.');
            const data = await response.json();

            const newPage = Number(data.current_page);
            if (data.html) {
                const temp = document.createElement('tbody');
                temp.innerHTML = data.html;
                tableBody.querySelector('[data-empty-row="true"]')?.remove();
                Array.from(temp.children).forEach(row => {
                    if (row.dataset.recordRow === 'true') tableBody.appendChild(row);
                });
            }

            nextPageUrl = data.next_page_url;
            currentPage = newPage;
            lastPage = data.last_page;
            totalRecords = data.total_records;
            perPage = data.per_page;
            updateResultsCounter();
            updatePaginationNumbers(getVisiblePage());
        } catch (error) {
            console.error('Error loading users:', error);
            loadError.classList.remove('hidden');
        } finally {
            isLoading = false;
            loadingIndicator.classList.add('hidden');
        }
    }

    tableContainer.addEventListener('scroll', function () {
        updatePaginationNumbers(getVisiblePage());
        const distanceFromBottom = tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight;
        if (distanceFromBottom < 150 && nextPageUrl && !isLoading) loadMoreRecords();
    }, { passive: true });

    retryLoadButton?.addEventListener('click', () => loadMoreRecords());

    updateResultsCounter();
    updatePaginationNumbers(initialPage);
});

// =====================================================
// MODAL FUNCTIONS (Global)
// =====================================================
function openAddUserModal() {
    window.syncOverridePasswordVisibility(ADD_OVERRIDE_CONFIG);
    document.getElementById('addUserModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

let currentUserDetailData = null;
function openUserDetailModal(userJson) {
    const user = typeof userJson === 'string' ? JSON.parse(userJson) : userJson;
    currentUserDetailData = user;
    const name = user.full_name || (user.firstname + ' ' + user.lastname);
    document.getElementById('udmFullName').textContent = name;
    document.getElementById('udmInitial').textContent = (name.charAt(0) || 'U').toUpperCase();
    document.getElementById('udmUsername').textContent = '@' + user.username;
    document.getElementById('udmEmail').textContent = user.email || '—';
    document.getElementById('udmRole').textContent = (user.role || '').replace('_', ' ').toUpperCase();
    document.getElementById('udmStatus').innerHTML = user.is_active
        ? '<span class="text-emerald-600 dark:text-emerald-400">Active</span>'
        : '<span class="text-rose-600 dark:text-rose-400">Inactive</span>';
    document.getElementById('udmPasswordStatus').innerHTML = user.must_change_password
        ? '<span class="text-amber-600 dark:text-amber-400">Must change on next login</span>'
        : '<span class="text-emerald-600 dark:text-emerald-400">Personal password set</span>';
    document.getElementById('udmCreatedAt').textContent = user.created_at ? new Date(user.created_at).toLocaleDateString() : '—';

    document.getElementById('udmEditBtn').onclick = function() {
        closeUserDetailModal();
        openEditUserModal(currentUserDetailData);
    };

    const modal = document.getElementById('userDetailModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeUserDetailModal() {
    const modal = document.getElementById('userDetailModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    document.body.classList.remove('overflow-hidden');
}

function applyEditSelfLock(isSelf) {
    const roleEl = document.getElementById('edit_role');
    const activeEl = document.getElementById('edit_is_active');
    const noticeEl = document.getElementById('editSelfNotice');
    if (roleEl) roleEl.disabled = isSelf;
    if (activeEl) activeEl.disabled = isSelf;
    if (noticeEl) noticeEl.classList.toggle('hidden', !isSelf);
}

function ensureSuffixOption(selectEl, value) {
    if (!selectEl || !value) return;
    const exists = Array.from(selectEl.options).some(opt => opt.value === value);
    if (!exists) {
        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = value;
        opt.dataset.legacySuffix = '1';
        selectEl.appendChild(opt);
    }
}

function openEditUserModal(userJson) {
    const user = typeof userJson === 'string' ? JSON.parse(userJson) : userJson;
    if (!user) return;

    const form = document.getElementById('editUserForm');
    form.action = `/admin/users/${user.id}`;

    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_original_role').value = user.role || 'staff';
    document.getElementById('edit_has_action_password').value = user.has_action_password ? '1' : '0';

    document.getElementById('edit_firstname').value = user.firstname || '';
    document.getElementById('edit_lastname').value = user.lastname || '';

    const suffixSelect = document.getElementById('edit_suffix');
    suffixSelect.querySelectorAll('option[data-legacy-suffix="1"]').forEach(o => o.remove());
    ensureSuffixOption(suffixSelect, user.suffix || '');
    suffixSelect.value = user.suffix || '';

    document.getElementById('edit_username').value = user.username || '';
    document.getElementById('edit_email').value = user.email || '';

    const roleSelect = document.getElementById('edit_role');
    roleSelect.dataset.originalRole = user.role || 'staff';
    roleSelect.dataset.hasActionPassword = user.has_action_password ? '1' : '0';
    roleSelect.value = user.role || 'staff';

    document.getElementById('edit_is_active').checked = user.is_active == 1;

    // Clear password fields on fresh open
    ['edit_password', 'edit_password_confirmation', 'edit_action_password', 'edit_action_password_confirmation'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('editPasswordSuggestionBox')?.classList.add('hidden');

    applyEditSelfLock(Number(user.id) === CURRENT_AUTH_USER_ID);
    window.syncOverridePasswordVisibility(EDIT_OVERRIDE_CONFIG);

    document.getElementById('editUserModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openDeleteUserModal(userId, userName) {
    const form = document.getElementById('deleteUserForm');
    form.action = `/admin/users/${userId}`;
    document.getElementById('deleteUserName').innerText = userName;

    document.getElementById('deleteUserModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeDeleteUserModal() {
    document.getElementById('deleteUserModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserDetailModal();
        closeAddUserModal();
        closeEditUserModal();
        closeDeleteUserModal();
    }
});

// Auto-open appropriate modal if validation failed
@if ($addModalHasErrors)
    document.addEventListener('DOMContentLoaded', function () {
        openAddUserModal();
    });
@elseif ($editModalHasErrors)
    document.addEventListener('DOMContentLoaded', function () {
        const editId = Number(document.getElementById('edit_user_id')?.value || 0);
        applyEditSelfLock(editId === CURRENT_AUTH_USER_ID);
        window.syncOverridePasswordVisibility(EDIT_OVERRIDE_CONFIG);
        document.getElementById('editUserModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    });
@endif
</script>
@endpush
