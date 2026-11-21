@extends('layouts.guest')

@section('title', 'Set New Password — Steven Hydrotech Exponent')
@section('robots', 'noindex, nofollow, noarchive')

@section('content')
    <div class="min-h-screen w-full flex flex-col lg:flex-row bg-[#0b0e14] dark:bg-[#10141D] overflow-x-hidden">

        <!-- LEFT SIDE: Brand Panel (desktop only) -->
        <div class="hidden lg:flex lg:w-1/2 min-h-screen bg-[#0b0e14] text-white flex-col justify-between p-8 lg:p-12 relative overflow-hidden border-r border-slate-800/80">
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex-1 flex flex-col items-center justify-center text-center space-y-6 relative z-10">
                <div class="space-y-2">
                    <h2 class="text-4xl lg:text-5xl font-black tracking-wider text-white drop-shadow-md">
                        STEVEN HYDROTECH
                    </h2>
                    <div class="flex items-center justify-center gap-3">
                        <div class="h-[2px] w-14 bg-sky-400"></div>
                        <span class="text-sm uppercase tracking-[0.35em] text-sky-300 font-extrabold">E X P O N E N T</span>
                        <div class="h-[2px] w-14 bg-sky-400"></div>
                    </div>
                </div>

                <div class="w-36 h-36 flex items-center justify-center p-2 my-2">
                    <img src="{{ asset('images/logo.svg') }}" alt="Steven Hydrotech Logo" class="w-full h-full object-contain drop-shadow-2xl">
                </div>

                <div class="max-w-md space-y-2 pt-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/15 border border-amber-400/30 text-amber-300 text-xs font-bold uppercase tracking-wider">
                        Account Security Verification
                    </span>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        For your protection, all newly created or reset accounts must establish a private, strong password before accessing company inventory and sales data.
                    </p>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-800/80 text-center text-xs text-slate-400 relative z-10">
                Signed in as <strong class="text-slate-200">{{ $user->full_name }}</strong> ({{ '@' . $user->username }})
            </div>
        </div>

        <!-- RIGHT SIDE: Force Password Change Form -->
        <div class="w-full lg:w-1/2 min-h-screen flex flex-col justify-center items-center p-4 sm:p-8 lg:p-14 overflow-y-auto py-8 bg-white dark:bg-[#10141D]">
            <div class="w-full max-w-md space-y-5">

                {{-- Mobile brand header --}}
                <div class="flex lg:hidden flex-col items-center text-center space-y-2 mb-2">
                    <div class="w-16 h-16 flex items-center justify-center">
                        <img src="{{ asset('images/logo.svg') }}" alt="Steven Hydrotech Logo" class="w-full h-full object-contain">
                    </div>
                    <h2 class="text-lg font-black tracking-wider text-gray-900 dark:text-white">STEVEN HYDROTECH</h2>
                </div>

                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-xs font-bold mb-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Password Change Required
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">Create Your Password</h1>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                        Hi <strong class="text-gray-800 dark:text-slate-200">{{ $user->firstname }}</strong>, replace your temporary password with a new strong password to continue.
                    </p>
                </div>

                @if (session('info'))
                    <div class="rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900 px-4 py-3 text-sm">
                        {{ session('info') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-lg bg-red-100 dark:bg-rose-950/85 text-red-700 dark:text-rose-300 border border-red-200 dark:border-rose-900 px-4 py-3 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.force.update') }}" class="space-y-4">
                    @csrf

                    <!-- Clipboard Disabled Warning -->
                    <div class="clipboard-block-notice hidden rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 px-3.5 py-2 text-xs font-semibold text-amber-800 dark:text-amber-300">
                        Copy and paste are disabled on password fields for security. Please type your password manually.
                    </div>

                    <!-- Current Temporary Password (Official System Input UI) -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">
                            Current Temporary Password
                        </label>
                        <div class="relative">
                            <input type="password" name="current_password" id="current_password" required autocomplete="current-password"
                                   placeholder="Enter the password given by admin"
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-3 pr-10 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500
                                          focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <button type="button" data-toggle-password="current_password" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Suggested Strong Password Helper -->
                    <div class="rounded-xl bg-slate-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-3 space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-medium text-gray-600 dark:text-slate-300">Need a strong password idea?</span>
                            <button type="button"
                                    data-suggest-password="forcePasswordSuggestionBox"
                                    data-target-input="force_new_password"
                                    data-confirm-input="force_new_password_confirmation"
                                    class="inline-flex items-center gap-1.5 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-3 py-1.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Suggest Strong Password
                            </button>
                        </div>
                        <div id="forcePasswordSuggestionBox" class="hidden pt-1.5 border-t border-gray-200 dark:border-[#1e2536]">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-[11px] text-gray-500 dark:text-slate-400">Applied to New Password:</span>
                                <code data-suggested-value class="select-none px-2.5 py-1 rounded-lg bg-white dark:bg-[#10141D] border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 font-mono text-xs font-bold tracking-wider"></code>
                            </div>
                            <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-1">Automatically filled into New Password (you can still edit it). Retype manually in Confirm New Password.</p>
                        </div>
                    </div>

                    <!-- New Password (Official System Input UI) -->
                    <div>
                        <label for="force_new_password" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">
                            New Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="force_new_password" required minlength="8" maxlength="72"
                                   data-no-clipboard data-ascii-password autocomplete="new-password"
                                   placeholder="Enter new strong password"
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-3 pr-10 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500
                                          focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <button type="button" data-toggle-password="force_new_password" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm New Password (Official System Input UI) -->
                    <div>
                        <label for="force_new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="force_new_password_confirmation" required minlength="8" maxlength="72"
                                   data-no-clipboard data-ascii-password autocomplete="new-password"
                                   placeholder="Retype your new password manually"
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] px-3.5 py-3 pr-10 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500
                                          focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <button type="button" data-toggle-password="force_new_password_confirmation" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Live Password Requirements Checklist -->
                    <div id="forcePasswordChecklist" class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-[11px] font-medium px-1">
                        <span data-rule="len" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>8–72 characters</span>
                        <span data-rule="upper" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Uppercase (A–Z)</span>
                        <span data-rule="lower" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Lowercase (a–z)</span>
                        <span data-rule="num" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Number (0–9)</span>
                        <span data-rule="sym" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Special char (@#$!...)</span>
                        <span data-rule="match" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>Passwords match</span>
                    </div>

                    <!-- Official System Submit Button -->
                    <button type="submit"
                            class="w-full bg-white border-2 border-sky-600 text-sky-700 hover:bg-sky-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-sky-600/80 dark:text-sky-400 dark:hover:bg-sky-600 dark:hover:text-white font-semibold py-3.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Save New Password &amp; Continue
                    </button>
                </form>

                <div class="pt-3 border-t border-gray-100 dark:border-[#1e2536] flex items-center justify-between text-xs">
                    <span class="text-gray-400 dark:text-slate-500">Not {{ $user->firstname }}?</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-1.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                            Sign out
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.password-security-script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.bindPasswordChecklist(
                'force_new_password',
                'force_new_password_confirmation',
                'forcePasswordChecklist'
            );
        });
    </script>
@endpush
