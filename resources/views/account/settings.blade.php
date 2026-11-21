@extends('layouts.default')

@section('title', 'Account Settings')

@section('content')
    @php
        $isAdmin = $user->role === 'admin';
        $isManagerOrAdmin = in_array($user->role, ['admin', 'manager'], true);
        $roleTitles = [
            'admin' => 'System Administrator',
            'manager' => 'Operations Manager',
            'warehouse_staff' => 'Inventory Specialist (Warehouse)',
            'staff' => 'Cashier / POS Operator',
        ];
        $roleLabel = $roleTitles[$user->role] ?? ucwords(str_replace('_', ' ', $user->role));
        $roleWords = explode('_', $user->role);
        $initials = count($roleWords) > 1
            ? strtoupper(substr($roleWords[0], 0, 1) . substr($roleWords[1], 0, 1))
            : strtoupper(substr($user->role, 0, 2));
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Account Settings</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    Manage your profile, security credentials, active sessions, preferences, and personal activity.
                </p>
            </div>
            <span class="inline-flex items-center gap-2 self-start sm:self-auto px-4 py-2 rounded-full text-xs font-bold bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] text-gray-700 dark:text-slate-200 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                {{ $roleLabel }}
            </span>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-200 dark:border-rose-900 px-5 py-4 text-sm text-rose-700 dark:text-rose-300">
                <p class="font-bold mb-1">Please fix the following issues:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tabs Navigation --}}
        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-2 shadow-xs flex flex-wrap gap-1.5">
            @php
                $tabs = [
                    'profile'     => 'Profile',
                    'security'    => 'Security & Sessions',
                    'preferences' => 'Preferences',
                    'activity'    => 'My Activity',
                ];
            @endphp
            @foreach ($tabs as $tabKey => $tabLabel)
                <a href="{{ route('account.settings', ['tab' => $tabKey]) }}"
                   class="px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition-all {{ $activeTab === $tabKey ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-[#161B26] hover:text-gray-900 dark:hover:text-white' }}">
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        {{-- ===================================================================== --}}
        {{-- TAB 1: PROFILE --}}
        {{-- ===================================================================== --}}
        @if ($activeTab === 'profile')
            <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 sm:px-8 py-5 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Profile Identity</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                        @if ($isAdmin)
                            As System Administrator, you may update your full identity, email, and profile photo.
                        @else
                            You may update your email address and profile photo. Name, username, and role are managed by your System Administrator to protect audit trails and receipts.
                        @endif
                    </p>
                </div>

                <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Avatar Preview & Upload --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-5 pb-6 border-b border-gray-100 dark:border-[#1e2536]">
                        <div class="shrink-0">
                            @if ($user->avatar_url)
                                <img id="avatarPreviewImg" src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}"
                                     class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 dark:border-[#1e2536] shadow-xs">
                            @else
                                <div id="avatarPreviewFallback" class="w-20 h-20 rounded-full bg-blue-600 text-white font-bold text-xl flex items-center justify-center border-2 border-gray-200 dark:border-[#1e2536] shadow-xs">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2 flex-1">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Profile Photo</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">JPG, PNG, or WEBP up to 2MB. Stored directly in the database so it stays synced across deployments.</p>
                            <div class="flex flex-wrap items-center gap-3 pt-1">
                                <label class="inline-flex items-center gap-2 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] text-xs font-semibold px-4 py-2 rounded-full transition-all cursor-pointer">
                                    <span>Upload Photo</span>
                                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden"
                                           onchange="document.getElementById('avatarFileLabel').textContent = this.files[0] ? this.files[0].name : ''">
                                </label>
                                <span id="avatarFileLabel" class="text-xs text-gray-500 dark:text-slate-400"></span>
                                @if ($user->avatar_url)
                                    <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 cursor-pointer">
                                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-gray-300 text-rose-600">
                                        Remove current photo
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Identity Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">First Name</label>
                            @if ($isAdmin)
                                <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}" required data-name-only
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                            @else
                                <input type="text" value="{{ $user->firstname }}" disabled
                                       class="w-full rounded-xl bg-gray-100 dark:bg-[#161B26]/50 border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-500 dark:text-slate-400 cursor-not-allowed">
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Last Name</label>
                            @if ($isAdmin)
                                <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}" required data-name-only
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                            @else
                                <input type="text" value="{{ $user->lastname }}" disabled
                                       class="w-full rounded-xl bg-gray-100 dark:bg-[#161B26]/50 border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-500 dark:text-slate-400 cursor-not-allowed">
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Suffix</label>
                            @if ($isAdmin)
                                <select name="suffix" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                                    <option value="">None</option>
                                    @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $sfx)
                                        <option value="{{ $sfx }}" {{ old('suffix', $user->suffix) === $sfx ? 'selected' : '' }}>{{ $sfx }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" value="{{ $user->suffix ?: '—' }}" disabled
                                       class="w-full rounded-xl bg-gray-100 dark:bg-[#161B26]/50 border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-500 dark:text-slate-400 cursor-not-allowed">
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Username (Locked)</label>
                            <input type="text" value="{{ $user->username }}" disabled
                                   class="w-full rounded-xl bg-gray-100 dark:bg-[#161B26]/50 border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm font-mono text-gray-500 dark:text-slate-400 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-[#1e2536]">
                        <p class="text-xs text-gray-400 dark:text-slate-500">
                            Account created {{ $user->created_at?->format('M d, Y') ?? '—' }}
                        </p>
                        <button type="submit"
                                class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
                            Save Profile
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- ===================================================================== --}}
        {{-- TAB 2: SECURITY & SESSIONS --}}
        {{-- ===================================================================== --}}
        @if ($activeTab === 'security')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Change Login Password --}}
                <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Change Login Password</h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            Minimum 8 ASCII characters with uppercase, lowercase, number, and symbol. Other active devices will be signed out automatically.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('account.password.update') }}" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="clipboard-block-notice hidden rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 px-3.5 py-2 text-xs font-semibold text-amber-800 dark:text-amber-300">
                            Copy and paste are disabled on password fields for security. Please type your password manually or use the suggestion tool below.
                        </div>

                        <div>
                            <label for="acct_current_password" class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Current Password</label>
                            <div class="relative">
                                <input type="password" id="acct_current_password" name="current_password" required autocomplete="current-password" data-no-clipboard
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                                <button type="button" data-toggle-password="acct_current_password" aria-label="Toggle visibility"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 cursor-pointer">
                                    <svg class="eye-open h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off h-4 w-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Suggest Strong Password Helper (Same as First-Login Screen) --}}
                        <div class="rounded-xl bg-slate-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-3 space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-xs font-medium text-gray-600 dark:text-slate-300">Need a strong password idea?</span>
                                <button type="button"
                                        data-suggest-password="acctPasswordSuggestionBox"
                                        data-target-input="acct_new_password"
                                        data-confirm-input="acct_new_password_confirmation"
                                        class="inline-flex items-center gap-1.5 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-3 py-1.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Suggest Strong Password
                                </button>
                            </div>
                            <div id="acctPasswordSuggestionBox" class="hidden rounded-lg bg-white dark:bg-[#10141D] border border-emerald-300 dark:border-emerald-800 px-3 py-2 space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Auto-filled (Editable)</span>
                                    <span class="text-[10px] text-gray-400">Save or write this down securely</span>
                                </div>
                                <div class="suggested-value font-mono text-xs font-bold text-gray-900 dark:text-white break-all select-none"></div>
                            </div>
                        </div>

                        <div>
                            <label for="acct_new_password" class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">New Password</label>
                            <div class="relative">
                                <input type="password" id="acct_new_password" name="password" required autocomplete="new-password" data-no-clipboard data-ascii-password
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                                <button type="button" data-toggle-password="acct_new_password" aria-label="Toggle visibility"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 cursor-pointer">
                                    <svg class="eye-open h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off h-4 w-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="acct_new_password_confirmation" class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" id="acct_new_password_confirmation" name="password_confirmation" required autocomplete="new-password" data-no-clipboard data-ascii-password
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                                <button type="button" data-toggle-password="acct_new_password_confirmation" aria-label="Toggle visibility"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 cursor-pointer">
                                    <svg class="eye-open h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off h-4 w-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit"
                                    class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Manager Override PIN/Password (Admin & Manager Only) --}}
                @if ($isManagerOrAdmin)
                    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50 flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">Manager Override Password</h2>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Used to authorize POS overrides and staff stock receipts.</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border {{ $user->has_action_password ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900' }}">
                                {{ $user->has_action_password ? 'Configured' : 'Not Set' }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('account.actionPassword.update') }}" class="p-6 space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Your Login Password (Verification)</label>
                                <input type="password" name="current_login_password" required autocomplete="current-password" data-no-clipboard
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">New Override Password (min. 6 chars)</label>
                                <input type="password" name="action_password" required autocomplete="new-password" data-no-clipboard
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Confirm Override Password</label>
                                <input type="password" name="action_password_confirmation" required autocomplete="new-password" data-no-clipboard
                                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-slate-400">
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit"
                                        class="bg-white border-2 border-blue-600 text-blue-700 hover:bg-blue-600 hover:text-white dark:bg-[#10141D] dark:border-blue-600/80 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
                                    Save Override Password
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-6 flex flex-col justify-center">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Account Security Tips</h2>
                        <ul class="mt-3 space-y-2.5 text-xs text-gray-600 dark:text-slate-400 list-disc pl-5">
                            <li>Never share your login password with coworkers — every receipt, stock request, and GRN is permanently logged under your account.</li>
                            <li>Always sign out when stepping away from a shared POS or warehouse terminal.</li>
                            <li>If you spot an unfamiliar device below, revoke it immediately and update your password.</li>
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Active Sessions Card --}}
            <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 sm:px-8 py-5 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Active Sessions &amp; Devices</h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Devices currently signed into your account.</p>
                    </div>
                    @if ($sessions->where('is_current', false)->count() > 0)
                        <form method="POST" action="{{ route('account.sessions.revokeOthers') }}" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs cursor-pointer">
                                Sign Out All Other Devices
                            </button>
                        </form>
                    @endif
                </div>

                <div class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @forelse ($sessions as $sess)
                        <div class="px-6 sm:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-[#161B26] text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $sess->device_label }}</span>
                                        @if ($sess->is_current)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-900">
                                                This Device
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                                        IP: <span class="font-mono font-semibold">{{ $sess->ip_address }}</span> · Last active {{ $sess->last_active }}
                                    </p>
                                </div>
                            </div>

                            @unless ($sess->is_current)
                                <form method="POST" action="{{ route('account.sessions.revoke', $sess->id) }}" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-rose-600 hover:border-rose-600 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-1.5 rounded-full transition-all cursor-pointer">
                                        Sign Out
                                    </button>
                                </form>
                            @endunless
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-xs text-gray-400">No active session records found.</div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- ===================================================================== --}}
        {{-- TAB 3: PREFERENCES --}}
        {{-- ===================================================================== --}}
        @if ($activeTab === 'preferences')
            <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 sm:px-8 py-5 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Workspace &amp; Notification Preferences</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Saved to your user account so your setup follows you across devices.</p>
                </div>

                <form method="POST" action="{{ route('account.preferences.update') }}" class="p-6 sm:p-8 space-y-6"
                      onsubmit="try { var v = this.querySelector('select[name=default_view]'); if (v) localStorage.setItem('productView', v.value); } catch (e) {}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Appearance Theme</label>
                            <select name="theme" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white">
                                <option value="light" {{ ($user->theme ?? 'light') === 'light' ? 'selected' : '' }}>Light Mode</option>
                                <option value="dark" {{ ($user->theme ?? 'light') === 'dark' ? 'selected' : '' }}>Dark Mode</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 dark:text-slate-400 mb-1.5">Default Catalog / Inventory View</label>
                            @php $defaultView = $user->getPreference('default_view', 'list'); @endphp
                            <select name="default_view" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white">
                                <option value="list" {{ $defaultView === 'list' ? 'selected' : '' }}>Table / List View</option>
                                <option value="grid" {{ $defaultView === 'grid' ? 'selected' : '' }}>Card Grid View</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4 pt-2 border-t border-gray-100 dark:border-[#1e2536]">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="toast_alerts" value="1"
                                   {{ $user->getPreference('toast_alerts', true) ? 'checked' : '' }}
                                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white">Show Live Pop-up Toasts</span>
                                <span class="block text-xs text-gray-500 dark:text-slate-400">Display a brief non-intrusive toast in the bottom-right corner when a new personal alert arrives.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="mute_low_stock" value="1"
                                   {{ $user->getPreference('mute_low_stock', false) ? 'checked' : '' }}
                                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white">Mute Low-Stock Alerts in Notification Bell</span>
                                <span class="block text-xs text-gray-500 dark:text-slate-400">Hide routine low-stock queue reminders from your bell badge while keeping approvals, deliveries, and security alerts active.</span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-[#1e2536] flex justify-end">
                        <button type="submit"
                                class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
                            Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- ===================================================================== --}}
        {{-- TAB 4: MY ACTIVITY & ROLE SNAPSHOT --}}
        {{-- ===================================================================== --}}
        @if ($activeTab === 'activity')
            {{-- Role Snapshot --}}
            <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-6 sm:p-8">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">{{ $roleSnapshot['title'] }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ($roleSnapshot['stats'] as $stat)
                        <div class="rounded-2xl bg-gray-50 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                            <p class="text-xl font-black text-gray-900 dark:text-white mt-1">{{ $stat['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Personal Recent Audit Trail --}}
            <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 sm:px-8 py-5 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">My Recent Account Activity</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Your last 15 recorded actions across the system.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-[#161B26] text-xs uppercase text-gray-500 dark:text-slate-400 border-b border-gray-200 dark:border-[#1e2536]">
                            <tr>
                                <th class="px-6 py-3.5">Action</th>
                                <th class="px-6 py-3.5">Target</th>
                                <th class="px-6 py-3.5">IP &amp; Device</th>
                                <th class="px-6 py-3.5 text-right">When</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                            @forelse ($recentActivity as $act)
                                <tr>
                                    <td class="px-6 py-3.5">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ \App\Http\Controllers\AuditLogController::eventBadgeClass((string) $act->event) }}">
                                            {{ $act->friendly_event }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-gray-700 dark:text-slate-300 font-medium">
                                        {{ $act->auditable_type ? class_basename($act->auditable_type) . ($act->auditable_id ? ' #' . $act->auditable_id : '') : 'Session / Security' }}
                                    </td>
                                    <td class="px-6 py-3.5 text-xs text-gray-500 dark:text-slate-400">
                                        <span class="font-mono font-semibold text-gray-700 dark:text-slate-300">{{ $act->ip_address ?? '—' }}</span>
                                        <span class="block text-[11px]">{{ $act->device_label }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 text-right text-xs text-gray-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ $act->created_at?->format('M d, Y h:i A') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-xs text-gray-400">No recent activity recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    @include('partials.password-security-script')
@endpush
