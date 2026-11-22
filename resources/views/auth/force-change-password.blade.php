@extends('layouts.guest')

@section('title', 'Set New Password — Steven Hydrotech Exponent')
@section('robots', 'noindex, nofollow, noarchive')

@section('content')
    <style>
        @keyframes hydroLogoFloat {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-7px) scale(1.03); }
        }
        @keyframes hydroOrbitCw {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes hydroOrbitCcw {
            0% { transform: rotate(360deg); }
            100% { transform: rotate(0deg); }
        }
        @keyframes hydroTextShimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes hydroLaserPulse {
            0%, 100% { opacity: 0.6; transform: scaleX(0.92); }
            50% { opacity: 1; transform: scaleX(1.08); }
        }
        @keyframes hydroBtnSheen {
            0% { transform: translateX(-140%) skewX(-20deg); }
            35%, 100% { transform: translateX(240%) skewX(-20deg); }
        }
        @keyframes hydroCaretBlink {
            0%, 49% { opacity: 1; transform: scaleY(1); }
            50%, 95% { opacity: 0.25; transform: scaleY(0.88); }
            100% { opacity: 1; transform: scaleY(1); }
        }
        @keyframes liftoffCore {
            0% { opacity: 0; transform: scale(0.55); filter: blur(12px); }
            65% { opacity: 1; transform: scale(1.06); filter: blur(0px); }
            100% { opacity: 1; transform: scale(1); filter: blur(0px); }
        }
        @keyframes liftoffOrbitRing {
            0% { opacity: 0; transform: scale(0.35) rotate(-45deg); }
            100% { opacity: 1; transform: scale(1) rotate(0deg); }
        }
        @keyframes liftoffUnblurUp {
            0% { opacity: 0; transform: translateY(16px) scale(0.97); filter: blur(8px); }
            100% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0px); }
        }
        @keyframes liftoffLaserDraw {
            0% { opacity: 0; transform: scaleX(0); }
            100% { opacity: 1; transform: scaleX(1); }
        }
        @keyframes liftoffCardEnter {
            0% { opacity: 0; transform: translateY(22px) scale(0.985); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes liftoffCascadeItem {
            0% { opacity: 0; transform: translateY(14px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .hydro-logo-float {
            animation: liftoffCore 0.75s cubic-bezier(0.16, 1, 0.3, 1) forwards,
                       hydroLogoFloat 4.8s ease-in-out 0.75s infinite;
        }
        .hydro-orbit-cw {
            animation: liftoffOrbitRing 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.08s backwards,
                       hydroOrbitCw 22s linear 0.93s infinite;
        }
        .hydro-orbit-ccw {
            animation: liftoffOrbitRing 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.16s backwards,
                       hydroOrbitCcw 30s linear 1.01s infinite;
        }
        .hydro-title-shimmer {
            background: linear-gradient(110deg, #ffffff 20%, #bae6fd 45%, #38bdf8 50%, #bae6fd 55%, #ffffff 80%);
            background-size: 220% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: liftoffUnblurUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) 0.18s backwards,
                       hydroTextShimmer 6.5s ease-in-out 0.95s infinite;
        }
        .hydro-laser-line {
            animation: liftoffLaserDraw 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.32s backwards,
                       hydroLaserPulse 3.6s ease-in-out 1.05s infinite;
        }
        .liftoff-exponent { animation: liftoffUnblurUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.28s backwards; }
        .liftoff-subtitle { animation: liftoffUnblurUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.38s backwards; }
        .liftoff-login-card { animation: liftoffCardEnter 0.75s cubic-bezier(0.16, 1, 0.3, 1) 0.22s backwards; }
        .liftoff-item-1 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.36s backwards; }
        .liftoff-item-2 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.44s backwards; }
        .liftoff-item-3 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.52s backwards; }
        .liftoff-item-4 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.60s backwards; }
        .liftoff-item-5 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.68s backwards; }
        .liftoff-item-6 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.76s backwards; }

        .hydro-caret { animation: hydroCaretBlink 1.15s ease-in-out infinite; }
        .hydro-field-group { transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1); }
        .hydro-field-group:focus-within { transform: translateY(-1.5px); }
        .hydro-input:focus { box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.16); }

        .hydro-signin-btn { position: relative; overflow: hidden; will-change: transform; }
        .hydro-signin-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(130px circle at var(--btn-x, -200px) var(--btn-y, -200px), rgba(56, 189, 248, 0.28), transparent 70%);
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }
        .hydro-signin-btn:hover::before { opacity: 1; }
        .hydro-signin-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 45%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(56, 189, 248, 0.18), transparent);
            animation: hydroBtnSheen 5.5s ease-in-out 1.5s infinite;
            pointer-events: none;
        }
        @keyframes hydroBtnRipple {
            0% { transform: translate(-50%, -50%) scale(0); opacity: 0.65; }
            100% { transform: translate(-50%, -50%) scale(3.4); opacity: 0; }
        }
        .hydro-btn-ripple {
            position: absolute;
            width: 110px;
            height: 110px;
            border-radius: 9999px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.55) 0%, rgba(14, 165, 233, 0.15) 60%, transparent 100%);
            pointer-events: none;
            animation: hydroBtnRipple 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes hydroErrorShake {
            0%, 100% { transform: translateX(0); }
            16% { transform: translateX(-8px); }
            32% { transform: translateX(7px); }
            48% { transform: translateX(-5px); }
            64% { transform: translateX(4px); }
            80% { transform: translateX(-2px); }
        }
        .hydro-error-shake {
            animation: hydroErrorShake 0.55s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
            box-shadow: 0 0 24px -4px rgba(244, 63, 94, 0.32);
        }
        #forceBrandCluster {
            transition: transform 0.18s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform;
        }
    </style>

    <div id="forceOuterStage" class="min-h-dvh w-full flex flex-col lg:flex-row items-center lg:items-stretch justify-center bg-[#04060b] lg:bg-slate-100/90 lg:dark:bg-[#080a10] p-3 sm:p-5 lg:p-4 gap-3 sm:gap-5 lg:gap-4 relative overflow-x-hidden">

        <!-- LEFT / FULL-SCREEN MOBILE VORTEX HERO -->
        <div id="forceVortexPanel" class="flex w-full lg:w-[50%] xl:w-[52%] lg:min-h-[calc(100dvh-2rem)] lg:rounded-[32px] lg:bg-[#04060b] text-white flex-col justify-between px-2 py-2 sm:p-4 lg:p-8 xl:p-11 relative lg:overflow-hidden lg:border lg:border-white/10 lg:shadow-2xl select-none z-10">

            <canvas id="forceVortexCanvas" class="fixed inset-0 lg:absolute lg:inset-0 w-full h-full pointer-events-none z-0"></canvas>

            <div class="hidden lg:block absolute -top-28 -left-28 w-80 h-80 bg-sky-500/10 rounded-full blur-3xl pointer-events-none z-0"></div>
            <div class="hidden lg:block absolute -bottom-28 -right-28 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none z-0"></div>

            <div class="flex-1 flex flex-col items-center justify-center text-center relative z-10 py-1 sm:py-3 lg:py-4">
                <div id="forceBrandCluster" class="flex flex-col items-center justify-center px-2 py-1 sm:px-6 sm:py-3">

                    <div class="space-y-1 sm:space-y-2">
                        <h2 class="hydro-title-shimmer whitespace-nowrap text-xl sm:text-3xl md:text-4xl lg:text-3xl xl:text-5xl font-black tracking-wider drop-shadow-[0_4px_24px_rgba(0,0,0,0.95)]">
                            STEVEN HYDROTECH
                        </h2>
                        <div class="liftoff-exponent flex items-center justify-center gap-2 sm:gap-3">
                            <div class="hydro-laser-line h-[2px] w-8 sm:w-14 bg-gradient-to-r from-transparent via-sky-400 to-sky-300 origin-right"></div>
                            <span class="text-[9px] sm:text-xs lg:text-xs xl:text-sm uppercase tracking-[0.35em] text-sky-300 font-extrabold drop-shadow-[0_2px_10px_rgba(56,189,248,0.45)]">E X P O N E N T</span>
                            <div class="hydro-laser-line h-[2px] w-8 sm:w-14 bg-gradient-to-l from-transparent via-sky-400 to-sky-300 origin-left"></div>
                        </div>
                    </div>

                    <div class="relative w-16 h-16 sm:w-24 sm:h-24 lg:w-32 lg:h-32 xl:w-36 xl:h-36 flex items-center justify-center my-1.5 sm:my-3 lg:my-4">
                        <div class="hydro-orbit-cw absolute inset-0 rounded-full border border-dashed border-sky-400/30 pointer-events-none"></div>
                        <div class="hydro-orbit-ccw absolute inset-2 sm:inset-3 rounded-full border border-amber-400/25 pointer-events-none"></div>
                        <div class="absolute inset-3 sm:inset-4 rounded-full bg-sky-500/15 blur-xl pointer-events-none"></div>

                        <div class="hydro-logo-float w-12 h-12 sm:w-18 sm:h-18 lg:w-24 lg:h-24 xl:w-28 xl:h-28 flex items-center justify-center">
                            <img src="{{ asset('images/logo.svg') }}" alt="Steven Hydrotech Logo" draggable="false" class="w-full h-full object-contain drop-shadow-[0_0_25px_rgba(56,189,248,0.5)]">
                        </div>
                    </div>

                    <div class="liftoff-subtitle max-w-md space-y-2 pt-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/15 border border-amber-400/30 text-amber-300 text-[9px] sm:text-xs font-bold uppercase tracking-wider">
                            Account Security Verification
                        </span>
                        <p class="text-xs text-slate-300 leading-relaxed hidden lg:block">
                            For your protection, all newly created or reset accounts must establish a private, strong password before accessing company inventory and sales data.
                        </p>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block pt-4 border-t border-white/10 text-center text-xs text-slate-400 relative z-10">
                Signed in as <strong class="text-slate-200">{{ $user->full_name }}</strong> ({{ '@' . $user->username }})
            </div>
        </div>

        <!-- RIGHT / FLOATING FORCE PASSWORD CARD -->
        <div class="liftoff-login-card w-full max-w-md lg:max-w-none lg:flex-1 flex flex-col justify-center items-center p-5 sm:p-8 lg:p-10 xl:p-14 bg-white/95 dark:bg-[#10141D]/92 lg:bg-white lg:dark:bg-[#10141D] backdrop-blur-xl rounded-[26px] sm:rounded-[30px] lg:rounded-[32px] border border-white/20 lg:border-slate-200/80 dark:border-[#1e2536] shadow-[0_24px_60px_rgba(0,0,0,0.65)] lg:shadow-sm relative z-10 overflow-y-auto">
            <div class="w-full max-w-md space-y-4 sm:space-y-5 my-auto relative z-10">

                <div class="liftoff-item-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-[10px] sm:text-xs font-bold mb-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Password Change Required
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-1">
                        <span id="forceWelcomeTypewriter" data-text="Create Your Password">Create Your Password</span>
                        <span aria-hidden="true" class="hydro-caret inline-block w-[3px] h-6 sm:h-7 rounded-full bg-gradient-to-b from-sky-400 via-indigo-500 to-emerald-400"></span>
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400 mt-1">
                        Hi <strong class="text-gray-800 dark:text-slate-200">{{ $user->firstname }}</strong>, replace your temporary password with a new strong password to continue.
                    </p>
                </div>

                @if (session('info'))
                    <div class="rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900 px-4 py-3 text-sm">
                        {{ session('info') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div data-hydro-error-alert class="hydro-error-shake rounded-xl bg-red-100 dark:bg-rose-950/85 text-red-700 dark:text-rose-300 border border-red-200 dark:border-rose-900 px-4 py-3 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="forcePasswordForm" method="POST" action="{{ route('password.force.update') }}" class="space-y-3.5 sm:space-y-4">
                    @csrf

                    <!-- Clipboard Disabled Warning -->
                    <div class="clipboard-block-notice hidden rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 px-3.5 py-2 text-xs font-semibold text-amber-800 dark:text-amber-300">
                        Copy and paste are disabled on password fields for security. Please type your password manually.
                    </div>

                    <!-- Current Temporary Password -->
                    <div class="liftoff-item-2 hydro-field-group">
                        <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-1">
                            Current Temporary Password
                        </label>
                        <div class="relative">
                            <input type="password" name="current_password" id="current_password" required autocomplete="current-password"
                                   placeholder="Enter the password given by admin"
                                   class="hydro-input w-full rounded-xl bg-slate-50/70 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2.5 sm:py-3 pr-10 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500
                                          focus:outline-none focus:border-sky-500 transition-all">
                            <button type="button" data-toggle-password="current_password" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Suggested Strong Password Helper -->
                    <div class="liftoff-item-3 rounded-xl bg-slate-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-3 space-y-2">
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

                    <!-- New Password -->
                    <div class="liftoff-item-4 hydro-field-group">
                        <label for="force_new_password" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-1">
                            New Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="force_new_password" required minlength="8" maxlength="72"
                                   data-no-clipboard data-ascii-password autocomplete="new-password"
                                   placeholder="Enter new strong password"
                                   class="hydro-input w-full rounded-xl bg-slate-50/70 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2.5 sm:py-3 pr-10 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500
                                          focus:outline-none focus:border-sky-500 transition-all">
                            <button type="button" data-toggle-password="force_new_password" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="liftoff-item-4 hydro-field-group">
                        <label for="force_new_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-1">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="force_new_password_confirmation" required minlength="8" maxlength="72"
                                   data-no-clipboard data-ascii-password autocomplete="new-password"
                                   placeholder="Retype your new password manually"
                                   class="hydro-input w-full rounded-xl bg-slate-50/70 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2.5 sm:py-3 pr-10 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500
                                          focus:outline-none focus:border-sky-500 transition-all">
                            <button type="button" data-toggle-password="force_new_password_confirmation" aria-label="Toggle visibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 cursor-pointer">
                                <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="eye-off h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Live Password Requirements Checklist -->
                    <div id="forcePasswordChecklist" class="liftoff-item-5 grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-[11px] font-medium px-1">
                        <span data-rule="len" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>8–72 characters</span>
                        <span data-rule="upper" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Uppercase (A–Z)</span>
                        <span data-rule="lower" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Lowercase (a–z)</span>
                        <span data-rule="num" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Number (0–9)</span>
                        <span data-rule="sym" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>1 Special char (@#$!...)</span>
                        <span data-rule="match" class="text-gray-400 dark:text-slate-500"><span class="rule-icon mr-1">•</span>Passwords match</span>
                    </div>

                    <!-- Official System Submit Button -->
                    <div class="liftoff-item-6">
                        <button type="submit" id="forceSignInBtn"
                                class="hydro-signin-btn group w-full bg-white border-2 border-sky-600 text-sky-700 hover:bg-sky-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-sky-600/80 dark:text-sky-400 dark:hover:bg-sky-600 dark:hover:text-white font-bold py-3 sm:py-3.5 rounded-full transition-all duration-300 ease-in-out shadow-xs hover:shadow-[0_8px_25px_-5px_rgba(14,165,233,0.45)] cursor-pointer flex items-center justify-center gap-2 text-sm">
                            <svg id="forceSignInIcon" class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <svg id="forceSignInSpinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span id="forceSignInText">Save New Password &amp; Continue</span>
                        </button>
                    </div>
                </form>

                <div class="liftoff-item-6 pt-3 border-t border-gray-100 dark:border-[#1e2536] flex items-center justify-between text-xs">
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

        // Magnetic Submit Button with Cursor-Following Glow & Tactile Water-Droplet Click Ripple
        (function () {
            const btn = document.getElementById('forceSignInBtn');
            if (!btn) return;
            btn.addEventListener('pointermove', function (e) {
                const r = btn.getBoundingClientRect();
                const x = e.clientX - r.left;
                const y = e.clientY - r.top;
                btn.style.setProperty('--btn-x', x + 'px');
                btn.style.setProperty('--btn-y', y + 'px');
                if (window.innerWidth >= 1024) {
                    const dx = (x - r.width * 0.5) * 0.06;
                    const dy = (y - r.height * 0.5) * 0.14;
                    btn.style.transform = `translate3d(${dx.toFixed(2)}px, ${dy.toFixed(2)}px, 0)`;
                }
            });
            btn.addEventListener('pointerleave', function () {
                btn.style.transform = '';
            });
            btn.addEventListener('pointerdown', function (e) {
                const r = btn.getBoundingClientRect();
                const ripple = document.createElement('span');
                ripple.className = 'hydro-btn-ripple';
                ripple.style.left = (e.clientX - r.left) + 'px';
                ripple.style.top = (e.clientY - r.top) + 'px';
                btn.appendChild(ripple);
                setTimeout(function () { ripple.remove(); }, 620);
            });
        })();

        // Typewriter Reveal for "Create Your Password|"
        (function () {
            const el = document.getElementById('forceWelcomeTypewriter');
            if (!el) return;
            const fullText = el.getAttribute('data-text') || 'Create Your Password';
            el.textContent = '';
            let idx = 0;
            setTimeout(function step() {
                if (idx <= fullText.length) {
                    el.textContent = fullText.slice(0, idx);
                    idx++;
                    if (idx <= fullText.length) {
                        setTimeout(step, 38);
                    }
                }
            }, 420);
        })();

        // Interactive 60fps 3D Hollow-Eye Particle Vortex + Typing Resonance + Hyperdrive Liftoff
        (function () {
            const panel = document.getElementById('forceVortexPanel');
            const outerStage = document.getElementById('forceOuterStage');
            const canvas = document.getElementById('forceVortexCanvas');
            const brandCluster = document.getElementById('forceBrandCluster');
            if (!panel || !canvas) return;

            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            let width = 0;
            let height = 0;
            let dpr = Math.min(window.devicePixelRatio || 1, 2);

            const STAR_COUNT = 105;
            const stars = [];
            const bubbles = [];
            const particles = [];
            const shockwaves = [];

            const startTime = performance.now();
            let initialShockwaveFired = false;
            let typingEnergy = 0;
            let hyperdriveActive = false;
            let hyperdriveProgress = 0;
            let lastCenterX = 0;
            let lastCenterY = 0;

            const palette = [
                { r: 79,  g: 143, b: 247 }, // Cobalt Blue
                { r: 56,  g: 189, b: 248 }, // Hydrotech Sky
                { r: 99,  g: 102, b: 241 }, // Electric Indigo
                { r: 251, g: 191, b: 36  }, // Security Amber Accent
                { r: 129, g: 140, b: 248 }, // Soft Violet
            ];

            const pointer = {
                x: -9999,
                y: -9999,
                smoothX: -9999,
                smoothY: -9999,
                vx: 0,
                vy: 0,
                prevX: -9999,
                prevY: -9999,
                active: false,
                radius: 170
            };

            function initStars() {
                stars.length = 0;
                for (let i = 0; i < STAR_COUNT; i++) {
                    stars.push({
                        x: Math.random(),
                        y: Math.random(),
                        size: 0.55 + Math.random() * 0.95,
                        alpha: 0.08 + Math.random() * 0.25,
                        twinklePhase: Math.random() * Math.PI * 2
                    });
                }
            }

            function initBubbles() {
                bubbles.length = 0;
                const bCount = window.innerWidth < 768 ? 24 : 42;
                for (let i = 0; i < bCount; i++) {
                    bubbles.push({
                        x: Math.random(),
                        y: Math.random(),
                        r: 1.4 + Math.random() * 3.6,
                        speed: 0.00055 + Math.random() * 0.00125,
                        wobble: Math.random() * Math.PI * 2,
                        alpha: 0.16 + Math.random() * 0.34
                    });
                }
            }

            function initParticles() {
                particles.length = 0;
                const isCompact = window.innerWidth < 768;
                const count = isCompact ? 240 : 400;
                const goldenAngle = Math.PI * (3 - Math.sqrt(5));

                for (let i = 0; i < count; i++) {
                    const t = i / count;
                    const inclination = Math.acos(1 - 2 * t);
                    const azimuth = goldenAngle * i;
                    const ringLayer = 0.72 + 0.36 * ((i % 10) / 9);
                    const col = palette[i % palette.length];

                    particles.push({
                        bx: Math.sin(inclination) * Math.cos(azimuth) * ringLayer,
                        by: Math.sin(inclination) * Math.sin(azimuth) * ringLayer,
                        bz: Math.cos(inclination) * ringLayer,
                        ox: 0,
                        oy: 0,
                        vx: 0,
                        vy: 0,
                        color: col,
                        sizeScale: 0.72 + (i % 4) * 0.11,
                        phase: Math.random() * Math.PI * 2
                    });
                }
            }

            function getCanvasHostRect() {
                if (window.innerWidth < 1024) {
                    return { left: 0, top: 0, width: window.innerWidth, height: window.innerHeight };
                }
                return panel.getBoundingClientRect();
            }

            function resize() {
                const rect = getCanvasHostRect();
                width = Math.max(1, rect.width);
                height = Math.max(1, rect.height);
                dpr = Math.min(window.devicePixelRatio || 1, 2);
                canvas.width = Math.floor(width * dpr);
                canvas.height = Math.floor(height * dpr);
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                pointer.radius = Math.max(115, Math.min(width, height) * 0.3);
            }

            function updatePointer(clientX, clientY) {
                const rect = getCanvasHostRect();
                const nx = clientX - rect.left;
                const ny = clientY - rect.top;

                if (nx < 0 || ny < 0 || nx > rect.width || ny > rect.height) {
                    clearPointer();
                    return;
                }

                if (pointer.prevX > -1000) {
                    pointer.vx = (nx - pointer.prevX) * 0.45;
                    pointer.vy = (ny - pointer.prevY) * 0.45;
                }
                pointer.x = nx;
                pointer.y = ny;
                if (pointer.smoothX < -1000) {
                    pointer.smoothX = nx;
                    pointer.smoothY = ny;
                }
                pointer.prevX = nx;
                pointer.prevY = ny;
                pointer.active = true;

                if (brandCluster && rect.width > 0 && rect.height > 0) {
                    const normX = ((nx / rect.width) - 0.5) * 2;
                    const normY = ((ny / rect.height) - 0.5) * 2;
                    brandCluster.style.transform = `translate3d(${(normX * 7).toFixed(2)}px, ${(normY * 7).toFixed(2)}px, 0)`;
                }
            }

            function clearPointer() {
                pointer.active = false;
                pointer.x = -9999;
                pointer.y = -9999;
                pointer.prevX = -9999;
                pointer.prevY = -9999;
                pointer.vx = 0;
                pointer.vy = 0;
                if (brandCluster) {
                    brandCluster.style.transform = 'translate3d(0px, 0px, 0)';
                }
            }

            function spawnShockwave(x, y, maxRadius, strength) {
                shockwaves.push({
                    x: x,
                    y: y,
                    r: 8,
                    maxR: maxRadius || Math.min(width, height) * 0.52,
                    alpha: 1,
                    strength: strength || 1
                });
            }

            let lastKeyPulseAt = 0;
            ['current_password', 'force_new_password', 'force_new_password_confirmation'].forEach(function (id) {
                const inputEl = document.getElementById(id);
                if (!inputEl) return;
                inputEl.addEventListener('input', function () {
                    const now = Date.now();
                    typingEnergy = Math.min(1, typingEnergy + 0.32);
                    if (now - lastKeyPulseAt > 160) {
                        lastKeyPulseAt = now;
                        spawnShockwave(lastCenterX || width * 0.5, lastCenterY || height * 0.43, Math.min(width, height) * 0.42, 0.38);
                    }
                });
            });

            const forceForm = document.getElementById('forcePasswordForm');
            if (forceForm) {
                forceForm.addEventListener('submit', function () {
                    hyperdriveActive = true;
                    spawnShockwave(lastCenterX || width * 0.5, lastCenterY || height * 0.43, Math.max(width, height) * 0.85, 1.6);
                    const btn = document.getElementById('forceSignInBtn');
                    const icon = document.getElementById('forceSignInIcon');
                    const spinner = document.getElementById('forceSignInSpinner');
                    const text = document.getElementById('forceSignInText');
                    if (btn) btn.classList.add('opacity-90', 'pointer-events-none');
                    if (icon) icon.classList.add('hidden');
                    if (spinner) spinner.classList.remove('hidden');
                    if (text) text.textContent = 'Activating Account...';
                });
            }

            const eventTarget = outerStage || panel;
            eventTarget.addEventListener('pointermove', function (e) { updatePointer(e.clientX, e.clientY); });
            eventTarget.addEventListener('mousemove', function (e) { updatePointer(e.clientX, e.clientY); });
            eventTarget.addEventListener('pointerleave', clearPointer);
            eventTarget.addEventListener('mouseleave', clearPointer);
            eventTarget.addEventListener('touchstart', function (e) {
                if (e.touches && e.touches[0]) updatePointer(e.touches[0].clientX, e.touches[0].clientY);
            }, { passive: true });
            eventTarget.addEventListener('touchmove', function (e) {
                if (e.touches && e.touches[0]) updatePointer(e.touches[0].clientX, e.touches[0].clientY);
            }, { passive: true });
            eventTarget.addEventListener('touchend', clearPointer, { passive: true });

            panel.addEventListener('click', function (e) {
                const rect = getCanvasHostRect();
                spawnShockwave(e.clientX - rect.left, e.clientY - rect.top);
            });

            window.addEventListener('resize', resize);
            if (typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(resize).observe(panel);
            }

            initStars();
            initBubbles();
            initParticles();
            resize();

            let time = 0;

            function render(now) {
                requestAnimationFrame(render);
                if (document.hidden) return;
                if (width <= 1 || height <= 1) resize();

                typingEnergy *= 0.94;
                if (hyperdriveActive) {
                    hyperdriveProgress = Math.min(1, hyperdriveProgress + 0.045);
                }

                time += 0.0058 + typingEnergy * 0.0065 + hyperdriveProgress * 0.025;
                ctx.clearRect(0, 0, width, height);

                const elapsed = Math.max(0, (now || performance.now()) - startTime);
                const introRaw = Math.min(1, elapsed / 1150);
                const introSpring = introRaw >= 1
                    ? 1
                    : 0.06 + 0.94 * (1 - Math.pow(1 - introRaw, 3) * Math.cos(introRaw * Math.PI * 1.25));

                const isDesktop = window.innerWidth >= 1024;
                let cx = width * 0.5;
                let cy = height * (isDesktop ? 0.43 : 0.22);
                if (!isDesktop && brandCluster) {
                    const bRect = brandCluster.getBoundingClientRect();
                    if (bRect.height > 0) {
                        cx = bRect.left + bRect.width * 0.5;
                        cy = bRect.top + bRect.height * 0.52;
                    }
                }
                lastCenterX = cx;
                lastCenterY = cy;

                if (!initialShockwaveFired && elapsed > 180) {
                    initialShockwaveFired = true;
                    spawnShockwave(cx, cy, Math.min(width, height) * 0.58, 1);
                }

                for (let i = 0; i < stars.length; i++) {
                    const s = stars[i];
                    const twinkle = 0.72 + 0.28 * Math.sin(time * 2.8 + s.twinklePhase);
                    ctx.fillStyle = `rgba(186, 215, 255, ${(s.alpha * twinkle * introRaw).toFixed(3)})`;
                    ctx.beginPath();
                    ctx.arc(s.x * width, s.y * height, s.size, 0, Math.PI * 2);
                    ctx.fill();
                }

                // Hydro-Tech Hybrid Layer 1: Translucent Multi-Layer Water Surface Wave Floor
                const waveBaseRatio = isDesktop ? 0.72 : 0.78;
                for (let w = 0; w < 3; w++) {
                    ctx.beginPath();
                    const baseY = height * (waveBaseRatio + w * 0.075);
                    ctx.moveTo(0, height);
                    const stepX = Math.max(14, Math.floor(width / 44));
                    for (let x = 0; x <= width + stepX; x += stepX) {
                        const cursorLift = pointer.active
                            ? Math.exp(-Math.pow((x - pointer.x) / 115, 2)) * -18
                            : 0;
                        const y = baseY
                            + Math.sin(x * 0.0085 + time * (1.85 + w * 0.42) + w * 1.9) * (11 + w * 4.5 + typingEnergy * 6)
                            + cursorLift;
                        ctx.lineTo(x, y);
                    }
                    ctx.lineTo(width, height);
                    ctx.closePath();
                    const waveGrad = ctx.createLinearGradient(0, baseY - 24, 0, height);
                    const waveAlpha = (w === 0 ? 0.16 : (w === 1 ? 0.12 : 0.09)) * introRaw;
                    if (w === 0) {
                        waveGrad.addColorStop(0, `rgba(56, 189, 248, ${waveAlpha.toFixed(3)})`);
                    } else if (w === 1) {
                        waveGrad.addColorStop(0, `rgba(14, 165, 233, ${waveAlpha.toFixed(3)})`);
                    } else {
                        waveGrad.addColorStop(0, `rgba(251, 191, 36, ${(waveAlpha * 0.7).toFixed(3)})`);
                    }
                    waveGrad.addColorStop(1, 'rgba(4, 6, 11, 0)');
                    ctx.fillStyle = waveGrad;
                    ctx.fill();
                }

                // Hydro-Tech Hybrid Layer 2: Concentric Water-Drop Sonar Ripples
                const sonarSpan = Math.min(width, height) * (isDesktop ? 0.42 : 0.36);
                const sonarStart = Math.min(width, height) * 0.08;
                for (let k = 0; k < 2; k++) {
                    const sonarPhase = ((time * 48 + k * (sonarSpan * 0.5)) % sonarSpan);
                    const sonarR = sonarStart + sonarPhase;
                    const sonarAlpha = 0.24 * (1 - sonarPhase / sonarSpan) * introRaw;
                    if (sonarAlpha > 0.01) {
                        ctx.strokeStyle = `rgba(56, 189, 248, ${sonarAlpha.toFixed(3)})`;
                        ctx.lineWidth = 1.35;
                        ctx.beginPath();
                        ctx.arc(cx, cy, sonarR, 0, Math.PI * 2);
                        ctx.stroke();
                    }
                }

                // Hydro-Tech Hybrid Layer 3: Rising Purified H2O Micro-Bubbles
                for (let i = 0; i < bubbles.length; i++) {
                    const b = bubbles[i];
                    b.y -= b.speed * (1 + typingEnergy * 0.8 + hyperdriveProgress * 2.5);
                    if (b.y < -0.04) {
                        b.y = 1.04;
                        b.x = Math.random();
                    }
                    let bx = b.x * width + Math.sin(time * 2.1 + b.wobble) * 11;
                    let by = b.y * height;
                    if (pointer.active) {
                        const bdx = bx - pointer.x;
                        const bdy = by - pointer.y;
                        const bDist = Math.sqrt(bdx * bdx + bdy * bdy) || 1;
                        if (bDist < 95) {
                            bx += (bdx / bDist) * (95 - bDist) * 0.24;
                        }
                    }
                    const bRad = b.r * introSpring;
                    const bAlpha = b.alpha * introRaw;
                    ctx.fillStyle = `rgba(56, 189, 248, ${(bAlpha * 0.24).toFixed(3)})`;
                    ctx.strokeStyle = `rgba(186, 230, 253, ${bAlpha.toFixed(3)})`;
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.arc(bx, by, bRad, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.stroke();
                    if (bRad >= 2.2) {
                        ctx.fillStyle = `rgba(255, 255, 255, ${(bAlpha * 0.8).toFixed(3)})`;
                        ctx.beginPath();
                        ctx.arc(bx - bRad * 0.3, by - bRad * 0.3, bRad * 0.26, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }

                for (let s = shockwaves.length - 1; s >= 0; s--) {
                    const sw = shockwaves[s];
                    sw.r += (sw.maxR - sw.r) * 0.08 + 3.2;
                    sw.alpha = Math.max(0, 1 - (sw.r / sw.maxR));
                    ctx.strokeStyle = `rgba(125, 211, 252, ${(sw.alpha * 0.32).toFixed(3)})`;
                    ctx.lineWidth = 1.4;
                    ctx.beginPath();
                    ctx.arc(sw.x, sw.y, sw.r, 0, Math.PI * 2);
                    ctx.stroke();
                    if (sw.alpha <= 0.02 || sw.r >= sw.maxR) {
                        shockwaves.splice(s, 1);
                    }
                }

                const warpExpand = 1 + hyperdriveProgress * 0.65;
                const baseRadius = Math.min(width, height) * (isDesktop ? 0.43 : 0.44) * introSpring * warpExpand;
                const innerEyeRadius = baseRadius * (isDesktop ? 0.42 : 0.36);
                const fov = 560;

                const introSpinBonus = (1 - introRaw) * 1.65;
                const rotY = time * 0.58 + introSpinBonus;
                const rotX = Math.sin(time * 0.42) * 0.28 + 0.2;
                const cosY = Math.cos(rotY);
                const sinY = Math.sin(rotY);
                const cosX = Math.cos(rotX);
                const sinX = Math.sin(rotX);

                pointer.vx *= 0.9;
                pointer.vy *= 0.9;

                const projected = [];
                for (let i = 0; i < particles.length; i++) {
                    const p = particles[i];
                    const breath = 1 + (0.035 + typingEnergy * 0.025) * Math.sin(time * 2.0 + p.phase);
                    const x0 = p.bx * baseRadius * breath;
                    const y0 = p.by * baseRadius * breath;
                    const z0 = p.bz * baseRadius * breath;

                    const x1 = x0 * cosY - z0 * sinY;
                    const z1 = z0 * cosY + x0 * sinY;
                    const y2 = y0 * cosX - z1 * sinX;
                    const z2 = z1 * cosX + y0 * sinX;

                    const scale = fov / (fov + z2 + 120);
                    let homeX = cx + x1 * scale;
                    let homeY = cy + y2 * scale;

                    const cdx = homeX - cx;
                    const cdy = homeY - cy;
                    const cDist = Math.sqrt(cdx * cdx + cdy * cdy) || 1;
                    if (cDist < innerEyeRadius) {
                        const pushFactor = innerEyeRadius / cDist;
                        homeX = cx + cdx * (0.52 + 0.48 * pushFactor);
                        homeY = cy + cdy * (0.52 + 0.48 * pushFactor);
                    }

                    const curX = homeX + p.ox;
                    const curY = homeY + p.oy;

                    let cursorGlow = typingEnergy * 0.28 + hyperdriveProgress * 0.55;
                    if (pointer.active) {
                        const dx = curX - pointer.x;
                        const dy = curY - pointer.y;
                        const distSq = dx * dx + dy * dy;
                        const maxR = pointer.radius;

                        if (distSq < maxR * maxR && distSq > 0.01) {
                            const dist = Math.sqrt(distSq);
                            const falloff = Math.pow(1 - dist / maxR, 1.85);
                            const nx = dx / dist;
                            const ny = dy / dist;

                            p.vx += nx * 2.6 * falloff + (-ny) * 0.7 * falloff + pointer.vx * 0.15 * falloff;
                            p.vy += ny * 2.6 * falloff + (nx) * 0.7 * falloff + pointer.vy * 0.15 * falloff;
                            cursorGlow = Math.max(cursorGlow, falloff);
                        }
                    }

                    for (let s = 0; s < shockwaves.length; s++) {
                        const sw = shockwaves[s];
                        const sdx = curX - sw.x;
                        const sdy = curY - sw.y;
                        const sDist = Math.sqrt(sdx * sdx + sdy * sdy) || 1;
                        const bandDiff = Math.abs(sDist - sw.r);
                        if (bandDiff < 42) {
                            const waveFalloff = (1 - bandDiff / 42) * sw.alpha * (sw.strength || 1);
                            p.vx += (sdx / sDist) * 1.45 * waveFalloff;
                            p.vy += (sdy / sDist) * 1.45 * waveFalloff;
                            cursorGlow = Math.max(cursorGlow, Math.min(1, waveFalloff * 0.9));
                        }
                    }

                    p.vx += -p.ox * 0.055;
                    p.vy += -p.oy * 0.055;
                    p.vx *= 0.87;
                    p.vy *= 0.87;
                    p.ox += p.vx;
                    p.oy += p.vy;

                    projected.push({
                        p: p,
                        x: homeX + p.ox,
                        y: homeY + p.oy,
                        z: z2,
                        scale: scale,
                        cursorGlow: cursorGlow
                    });
                }

                projected.sort(function (a, b) { return b.z - a.z; });

                ctx.lineCap = 'round';
                const safeBaseR = Math.max(1, baseRadius);
                for (let i = 0; i < projected.length; i++) {
                    const item = projected[i];
                    const p = item.p;

                    const rx = item.x - cx;
                    const ry = item.y - cy;
                    const rLen = Math.sqrt(rx * rx + ry * ry) || 1;
                    const eyeFade = Math.max(0.12, Math.min(1, (rLen - innerEyeRadius * 0.5) / (Math.max(1, innerEyeRadius) * 0.5)));

                    const depthNorm = Math.max(-1, Math.min(1, -item.z / safeBaseR));
                    const dirX = (rx / rLen) + p.vx * 0.26;
                    const dirY = (ry / rLen) + p.vy * 0.26;
                    const angle = Math.atan2(dirY, dirX);
                    const speed = Math.sqrt(p.vx * p.vx + p.vy * p.vy);

                    const dashLen = (2.3 + (depthNorm + 1) * 1.9 + Math.min(speed * 1.25, 8.5) + typingEnergy * 1.6 + hyperdriveProgress * 7.5) * item.scale * p.sizeScale;
                    const thickness = Math.max(1.05, (1.2 + (depthNorm + 1) * 0.75 + item.cursorGlow * 0.9) * item.scale * p.sizeScale);

                    const baseAlpha = (0.2 + (depthNorm + 1) * 0.35) * eyeFade * Math.min(1, introRaw * 1.4);
                    const alpha = Math.min(0.98, baseAlpha + item.cursorGlow * 0.38);

                    const r = Math.min(255, Math.round(p.color.r + item.cursorGlow * 75));
                    const g = Math.min(255, Math.round(p.color.g + item.cursorGlow * 65));
                    const b = Math.min(255, Math.round(p.color.b + item.cursorGlow * 35));

                    const halfLen = dashLen * 0.5;
                    const cosA = Math.cos(angle) * halfLen;
                    const sinA = Math.sin(angle) * halfLen;

                    ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, ${alpha.toFixed(3)})`;
                    ctx.lineWidth = thickness;
                    ctx.beginPath();
                    ctx.moveTo(item.x - cosA, item.y - sinA);
                    ctx.lineTo(item.x + cosA, item.y + sinA);
                    ctx.stroke();
                }
            }

            requestAnimationFrame(render);
        })();
    </script>
@endpush
