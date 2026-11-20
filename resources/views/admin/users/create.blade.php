@extends('layouts.default')

@section('title', 'Add User')

@section('content')
    @php
        $suffixOptions = $allowedSuffixes ?? ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
    @endphp

    <div class="max-w-2xl mx-auto bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-8">

        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Add New User</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Create a new system account. New users must change their password upon first login.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-rose-300 border-2 border-red-200 dark:border-rose-900 px-6 py-4 text-sm font-medium">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <div class="clipboard-block-notice hidden rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 px-3.5 py-2 text-xs font-semibold text-amber-800 dark:text-amber-300">
                Copy and paste are disabled on password fields for security. Please type manually.
            </div>

            <!-- Name fields -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="firstname" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">First Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="firstname" id="firstname" value="{{ old('firstname') }}" required minlength="2" maxlength="50"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>

                <div>
                    <label for="lastname" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Last Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="lastname" id="lastname" value="{{ old('lastname') }}" required minlength="2" maxlength="50"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>

                <div>
                    <label for="suffix" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Suffix <span class="text-gray-400 font-normal">(optional)</span></label>
                    <select name="suffix" id="suffix"
                            class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                        <option value="">None</option>
                        @foreach ($suffixOptions as $sfx)
                            <option value="{{ $sfx }}" {{ old('suffix') === $sfx ? 'selected' : '' }}>{{ $sfx }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Username & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required minlength="4" maxlength="30"
                           pattern="[A-Za-z0-9][A-Za-z0-9._-]*"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required maxlength="150"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>
            </div>

            <!-- Role & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="create_role" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role <span class="text-rose-500">*</span></label>
                    <select name="role" id="create_role" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                        <option value="staff" {{ old('role', 'staff') === 'staff' ? 'selected' : '' }}>Staff (POS Cashier)</option>
                        <option value="warehouse_staff" {{ old('role') === 'warehouse_staff' ? 'selected' : '' }}>Warehouse Staff</option>
                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 mt-2 sm:mt-7">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-gray-800 w-4 h-4 cursor-pointer">
                    <label for="is_active" class="text-sm font-semibold text-gray-700 dark:text-slate-300 cursor-pointer">Active account</label>
                </div>
            </div>

            <!-- Suggested Strong Password Helper -->
            <div class="rounded-xl bg-slate-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-3 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-600 dark:text-slate-300">Temporary Login Password</span>
                    <button type="button" data-suggest-password="createPasswordSuggestionBox" data-target-input="password" data-confirm-input="password_confirmation"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer">
                        Suggest Strong Password
                    </button>
                </div>
                <div id="createPasswordSuggestionBox" class="hidden pt-1 border-t border-gray-200 dark:border-[#1e2536]">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] text-gray-500 dark:text-slate-400">Applied to Password:</span>
                        <code data-suggested-value class="select-none px-2.5 py-1 rounded-lg bg-white dark:bg-[#10141D] border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 font-mono text-xs font-bold tracking-wider"></code>
                    </div>
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-1">Automatically filled into Password (you can still edit it). Retype manually in Confirm Password.</p>
                </div>
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Password <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required minlength="8" maxlength="72"
                               data-no-clipboard data-ascii-password autocomplete="new-password"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        <button type="button" data-toggle-password="password" aria-label="Toggle visibility"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                            <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Confirm Password <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" maxlength="72"
                               data-no-clipboard data-ascii-password autocomplete="new-password"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        <button type="button" data-toggle-password="password_confirmation" aria-label="Toggle visibility"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 cursor-pointer">
                            <svg class="eye-open w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Live Password Checklist -->
            <div id="createPasswordChecklist" class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[11px] font-medium px-1">
                <span data-rule="len" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>8–72 characters</span>
                <span data-rule="upper" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Uppercase</span>
                <span data-rule="lower" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Lowercase</span>
                <span data-rule="num" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Number</span>
                <span data-rule="sym" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Special char</span>
                <span data-rule="match" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>Passwords match</span>
            </div>

            <!-- Role-Aware Manager Override Password -->
            <div id="createOverrideSection" class="hidden rounded-2xl bg-purple-50/60 dark:bg-purple-950/20 border-2 border-purple-200/80 dark:border-purple-900/60 p-4 space-y-3">
                <div>
                    <p class="text-xs font-bold text-purple-900 dark:text-purple-200 uppercase">Manager Override Password <span class="text-rose-500">*</span></p>
                    <p class="text-[11px] text-purple-700/80 dark:text-purple-300/80 mt-0.5">Used for stock transfers &amp; approvals (6–32 chars, must differ from login password).</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="action_password" class="block text-[11px] font-bold text-gray-600 dark:text-slate-400 uppercase mb-1">Override Password</label>
                        <input type="password" name="action_password" id="action_password" minlength="6" maxlength="32" data-no-clipboard data-ascii-password autocomplete="new-password"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label for="action_password_confirmation" class="block text-[11px] font-bold text-gray-600 dark:text-slate-400 uppercase mb-1">Confirm Override Password</label>
                        <input type="password" name="action_password_confirmation" id="action_password_confirmation" minlength="6" maxlength="32" data-no-clipboard data-ascii-password autocomplete="new-password"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <a href="{{ route('admin.users.index') }}"
                   class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Add User
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @include('partials.password-security-script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.bindPasswordChecklist('password', 'password_confirmation', 'createPasswordChecklist');
            const cfg = {
                mode: 'add',
                roleSelectId: 'create_role',
                sectionId: 'createOverrideSection',
                inputId: 'action_password',
                confirmId: 'action_password_confirmation',
            };
            document.getElementById('create_role')?.addEventListener('change', () => window.syncOverridePasswordVisibility(cfg));
            window.syncOverridePasswordVisibility(cfg);
        });
    </script>
@endpush
