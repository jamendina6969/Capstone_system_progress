@extends('layouts.guest')

@section('title', 'System Administrator Portal — Steven Hydrotech Exponent')
@section('robots', 'noindex, nofollow, noarchive')

@section('content')
    <style>
        /* ==================== 1. CONTINUOUS AMBIENT ANIMATIONS ==================== */
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

        /* ==================== 2. 4-STAGE "HYDROTECH LIFTOFF" ENTRANCE ==================== */
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
            background: linear-gradient(110deg, #ffffff 20%, #e9d5ff 45%, #a855f7 50%, #bae6fd 55%, #ffffff 80%);
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
        .liftoff-exponent {
            animation: liftoffUnblurUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.28s backwards;
        }
        .liftoff-subtitle {
            animation: liftoffUnblurUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.38s backwards;
        }
        .liftoff-pillar-1 { animation: liftoffCascadeItem 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.48s backwards; }
        .liftoff-pillar-2 { animation: liftoffCascadeItem 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.56s backwards; }
        .liftoff-pillar-3 { animation: liftoffCascadeItem 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.64s backwards; }
        .liftoff-pillar-4 { animation: liftoffCascadeItem 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.72s backwards; }

        .liftoff-login-card {
            animation: liftoffCardEnter 0.75s cubic-bezier(0.16, 1, 0.3, 1) 0.22s backwards;
        }
        .liftoff-item-1 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.36s backwards; }
        .liftoff-item-2 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.44s backwards; }
        .liftoff-item-3 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.52s backwards; }
        .liftoff-item-4 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.60s backwards; }
        .liftoff-item-5 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.68s backwards; }
        .liftoff-item-6 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.76s backwards; }
        .liftoff-item-7 { animation: liftoffCascadeItem 0.55s cubic-bezier(0.16, 1, 0.3, 1) 0.84s backwards; }

        .hydro-caret {
            animation: hydroCaretBlink 1.15s ease-in-out infinite;
        }

        /* Cursor-Tracking Glass Spotlight */
        .hydro-glass-card {
            position: relative;
            overflow: hidden;
        }
        .hydro-glass-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(180px circle at var(--mouse-x, -200px) var(--mouse-y, -200px), rgba(168, 85, 247, 0.2), transparent 75%);
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }
        .hydro-glass-card:hover::before {
            opacity: 1;
        }

        .hydro-field-group {
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hydro-field-group:focus-within {
            transform: translateY(-1.5px);
        }
        .hydro-field-group:focus-within label {
            color: #38bdf8;
        }
        .hydro-input:focus {
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.16);
        }

        .hydro-signin-btn {
            position: relative;
            overflow: hidden;
        }
        .hydro-signin-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 45%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(56, 189, 248, 0.2), transparent);
            animation: hydroBtnSheen 5.5s ease-in-out 1.5s infinite;
            pointer-events: none;
        }

        #adminBrandCluster {
            transition: transform 0.18s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform;
        }
    </style>

    <div id="adminOuterStage" class="min-h-dvh w-full flex flex-col lg:flex-row items-center lg:items-stretch justify-center bg-[#04060b] lg:bg-[#080a10] text-slate-100 p-3 sm:p-5 lg:p-4 gap-3 sm:gap-5 lg:gap-4 relative overflow-x-hidden">

        <!-- LEFT / FULL-SCREEN MOBILE VORTEX HERO -->
        <div id="adminVortexPanel" class="flex w-full lg:w-[52%] xl:w-[54%] lg:min-h-[calc(100dvh-2rem)] lg:rounded-[32px] lg:bg-[#04060b] text-white flex-col justify-between px-2 py-2 sm:p-4 lg:p-8 xl:p-11 relative lg:overflow-hidden lg:border lg:border-white/10 lg:shadow-2xl select-none z-10">

            <canvas id="adminVortexCanvas" class="fixed inset-0 lg:absolute lg:inset-0 w-full h-full pointer-events-none z-0"></canvas>

            <div class="hidden lg:block absolute -top-28 -left-28 w-80 h-80 bg-sky-500/10 rounded-full blur-3xl pointer-events-none z-0"></div>
            <div class="hidden lg:block absolute -bottom-28 -right-28 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl pointer-events-none z-0"></div>

            <!-- Centered Branding & Hero Section -->
            <div class="flex-1 flex flex-col items-center justify-center text-center relative z-10 py-1 sm:py-3 lg:py-4">
                <div id="adminBrandCluster" class="flex flex-col items-center justify-center px-2 py-1 sm:px-6 sm:py-3">

                    <div class="space-y-1 sm:space-y-2">
                        <h2 class="hydro-title-shimmer whitespace-nowrap text-xl sm:text-3xl md:text-4xl lg:text-3xl xl:text-5xl font-black tracking-wider drop-shadow-[0_4px_24px_rgba(0,0,0,0.95)]">
                            STEVEN HYDROTECH
                        </h2>
                        <div class="liftoff-exponent flex items-center justify-center gap-2 sm:gap-3">
                            <div class="hydro-laser-line h-[2px] w-8 sm:w-14 bg-gradient-to-r from-transparent via-sky-400 to-purple-400 origin-right"></div>
                            <span class="text-[9px] sm:text-xs lg:text-xs xl:text-sm uppercase tracking-[0.35em] text-sky-300 font-extrabold drop-shadow-[0_2px_10px_rgba(168,85,247,0.45)]">E X P O N E N T</span>
                            <div class="hydro-laser-line h-[2px] w-8 sm:w-14 bg-gradient-to-l from-transparent via-sky-400 to-purple-400 origin-left"></div>
                        </div>
                    </div>

                    <!-- Standalone Logo with Anti-Gravity Levitation & Orbital Energy Rings -->
                    <div class="relative w-16 h-16 sm:w-24 sm:h-24 lg:w-32 lg:h-32 xl:w-36 xl:h-36 flex items-center justify-center my-1.5 sm:my-3 lg:my-4">
                        <div class="hydro-orbit-cw absolute inset-0 rounded-full border border-dashed border-purple-400/30 pointer-events-none"></div>
                        <div class="hydro-orbit-ccw absolute inset-2 sm:inset-3 rounded-full border border-sky-400/25 pointer-events-none"></div>
                        <div class="absolute inset-3 sm:inset-4 rounded-full bg-purple-500/15 blur-xl pointer-events-none"></div>

                        <div class="hydro-logo-float w-12 h-12 sm:w-18 sm:h-18 lg:w-24 lg:h-24 xl:w-28 xl:h-28 flex items-center justify-center">
                            <img src="{{ asset('images/logo.svg') }}" alt="Steven Hydrotech Logo" draggable="false" class="w-full h-full object-contain drop-shadow-[0_0_25px_rgba(168,85,247,0.5)]">
                        </div>
                    </div>

                    <div class="liftoff-subtitle space-y-1.5 pt-0.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-500/15 border border-purple-400/30 text-purple-300 text-[9px] sm:text-xs font-bold uppercase tracking-widest">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Restricted System Administration
                        </span>
                        <p class="text-[11px] sm:text-xs text-slate-400 max-w-sm mx-auto leading-relaxed hidden lg:block">
                            Dedicated authentication gateway for System Administrator accounts. All sign-in attempts are rate-limited and recorded in the security audit log.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Frosted Glass Governance Cards with Cursor-Tracking Spotlight -->
            <div class="hidden lg:grid grid-cols-2 xl:grid-cols-4 gap-2.5 relative z-10 pt-2">
                <div class="hydro-glass-card group liftoff-pillar-1 p-3 rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-purple-400/30 backdrop-blur-md space-y-1 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center gap-1.5 text-sky-400 relative z-10">
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <h4 class="text-[11px] font-bold text-slate-100 truncate">User Governance</h4>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-snug line-clamp-2 relative z-10">Manage roles, access &amp; credentials.</p>
                </div>
                <div class="hydro-glass-card group liftoff-pillar-2 p-3 rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-purple-400/30 backdrop-blur-md space-y-1 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center gap-1.5 text-sky-400 relative z-10">
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                        <h4 class="text-[11px] font-bold text-slate-100 truncate">System Backups</h4>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-snug line-clamp-2 relative z-10">Database snapshots &amp; recovery.</p>
                </div>
                <div class="hydro-glass-card group liftoff-pillar-3 p-3 rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-purple-400/30 backdrop-blur-md space-y-1 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center gap-1.5 text-sky-400 relative z-10">
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <h4 class="text-[11px] font-bold text-slate-100 truncate">Audit Oversight</h4>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-snug line-clamp-2 relative z-10">Track logins, IPs &amp; system changes.</p>
                </div>
                <div class="hydro-glass-card group liftoff-pillar-4 p-3 rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-purple-400/30 backdrop-blur-md space-y-1 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center gap-1.5 text-sky-400 relative z-10">
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <h4 class="text-[11px] font-bold text-slate-100 truncate">Access Control</h4>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-snug line-clamp-2 relative z-10">Isolated admin gateway &amp; lockout.</p>
                </div>
            </div>
        </div>

        <!-- RIGHT / FLOATING ADMIN LOGIN CARD -->
        <div class="liftoff-login-card w-full max-w-md lg:max-w-none lg:flex-1 flex flex-col justify-between items-center p-5 sm:p-8 lg:p-10 xl:p-16 bg-[#10141D]/92 lg:bg-[#10141D] backdrop-blur-xl rounded-[26px] sm:rounded-[30px] lg:rounded-[32px] border border-[#1e2536] shadow-[0_24px_60px_rgba(0,0,0,0.75)] lg:shadow-2xl relative z-10 overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-purple-500/[0.08] rounded-full blur-3xl pointer-events-none"></div>

            <div class="hidden lg:block"></div>

            <div class="w-full max-w-md space-y-4 sm:space-y-6 my-auto relative z-10">
                <div class="text-center lg:text-left space-y-1.5 sm:space-y-2">
                    <div class="liftoff-item-1 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-300 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                        System Administrator Portal
                    </div>
                    <h1 class="liftoff-item-2 text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight flex items-center justify-center lg:justify-start gap-1">
                        <span id="adminWelcomeTypewriter" data-text="Admin Sign In">Admin Sign In</span>
                        <span aria-hidden="true" class="hydro-caret inline-block w-[3px] h-6 sm:h-8 rounded-full bg-gradient-to-b from-sky-400 via-purple-400 to-indigo-500"></span>
                    </h1>
                    <p class="liftoff-item-2 text-xs sm:text-sm text-slate-400">Authorized System Administrators only</p>
                </div>

                @if (session('info'))
                    <div class="rounded-xl bg-blue-950/60 text-blue-300 border border-blue-900 px-4 py-3 text-sm">
                        {{ session('info') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="rounded-xl bg-rose-950/60 text-rose-300 border border-rose-900 px-4 py-3 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-xl bg-rose-950/80 text-rose-300 border border-rose-900 px-4 py-3 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="adminLoginForm" method="POST" action="{{ route('admin.portal.authenticate') }}" class="space-y-3.5 sm:space-y-4">
                    @csrf

                    <div class="liftoff-item-3 hydro-field-group">
                        <label for="admin_loginname" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5 transition-colors">
                            Administrator Username or Email
                        </label>
                        <input type="text" name="loginname" id="admin_loginname" value="{{ old('loginname') }}" required autofocus autocomplete="username"
                               placeholder="admin@stevenhydrotech.com"
                               class="hydro-input w-full rounded-xl bg-[#161B26] border-2 border-[#1e2536] px-3.5 py-2.5 sm:py-3 text-sm text-slate-100 placeholder-slate-500
                                      focus:outline-none focus:border-sky-500 transition-all">
                    </div>

                    <div class="liftoff-item-4 hydro-field-group">
                        <label for="admin_loginpassword" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5 transition-colors">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" name="loginpassword" id="admin_loginpassword" required autocomplete="current-password"
                                   placeholder="••••••••••••"
                                   class="hydro-input w-full rounded-xl bg-[#161B26] border-2 border-[#1e2536] px-3.5 py-2.5 sm:py-3 pr-10 text-sm text-slate-100 placeholder-slate-500
                                          focus:outline-none focus:border-sky-500 transition-all">

                            <button type="button" id="toggleAdminPassword" aria-label="Toggle password visibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-200 cursor-pointer">
                                <svg id="adminEyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="adminEyeOff" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="liftoff-item-5 flex flex-wrap items-center justify-between gap-2 text-sm pt-0.5">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="remember" id="admin_remember"
                                   class="rounded border-slate-700 bg-[#161B26] text-sky-600 focus:ring-sky-500">
                            <label for="admin_remember" class="text-slate-300 font-medium text-xs sm:text-sm">Remember Me</label>
                        </div>
                        <span class="text-[11px] text-slate-500">Max 5 attempts / 15 min</span>
                    </div>

                    <!-- Official Hydrotech Outlined Sky Pill Button with Sheen, Icon Glide & Hyperdrive Liftoff State -->
                    <div class="liftoff-item-6">
                        <button type="submit" id="adminSignInBtn"
                                class="hydro-signin-btn group w-full bg-[#10141D] border-2 border-sky-500/80 text-sky-400 hover:bg-sky-600 hover:text-white font-bold py-3 sm:py-3.5 rounded-full transition-all duration-300 ease-in-out shadow-xs hover:shadow-[0_8px_25px_-5px_rgba(14,165,233,0.45)] cursor-pointer flex items-center justify-center gap-2 text-sm">
                            <svg id="adminSignInIcon" class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <svg id="adminSignInSpinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span id="adminSignInText">Authenticate as Administrator</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer: Return link & Security Notice -->
            <div class="liftoff-item-7 w-full max-w-md pt-4 sm:pt-5 mt-4 border-t border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] sm:text-xs relative z-10">
                <span class="text-slate-500">All access attempts are logged by IP.</span>
                <a href="{{ route('login') }}"
                   class="text-slate-400 hover:text-sky-400 font-semibold transition-colors inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Standard Login
                </a>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    const toggleBtn = document.getElementById('toggleAdminPassword');
    const passwordInput = document.getElementById('admin_loginpassword');
    const eyeOpen = document.getElementById('adminEyeOpen');
    const eyeOff = document.getElementById('adminEyeOff');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            eyeOpen?.classList.toggle('hidden', isHidden);
            eyeOff?.classList.toggle('hidden', !isHidden);
        });
    }

    // Cursor-Tracking Glass Spotlight on the 4 Bottom Cards
    document.querySelectorAll('.hydro-glass-card').forEach(function (card) {
        card.addEventListener('pointermove', function (e) {
            const r = card.getBoundingClientRect();
            card.style.setProperty('--mouse-x', (e.clientX - r.left) + 'px');
            card.style.setProperty('--mouse-y', (e.clientY - r.top) + 'px');
        });
    });

    // Typewriter Entrance Reveal for "Admin Sign In|"
    (function () {
        const el = document.getElementById('adminWelcomeTypewriter');
        if (!el) return;
        const fullText = el.getAttribute('data-text') || 'Admin Sign In';
        el.textContent = '';
        let idx = 0;
        setTimeout(function step() {
            if (idx <= fullText.length) {
                el.textContent = fullText.slice(0, idx);
                idx++;
                if (idx <= fullText.length) {
                    setTimeout(step, 50);
                }
            }
        }, 420);
    })();

    // Interactive 60fps 3D Hollow-Eye Particle Vortex + Typing Resonance + Hyperdrive Liftoff (Admin Portal Edition)
    (function () {
        const panel = document.getElementById('adminVortexPanel');
        const outerStage = document.getElementById('adminOuterStage');
        const canvas = document.getElementById('adminVortexCanvas');
        const brandCluster = document.getElementById('adminBrandCluster');
        if (!panel || !canvas) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let width = 0;
        let height = 0;
        let dpr = Math.min(window.devicePixelRatio || 1, 2);

        const STAR_COUNT = 125;
        const stars = [];
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
            { r: 99,  g: 102, b: 241 }, // Electric Indigo (#6366f1)
            { r: 168, g: 85,  b: 247 }, // Admin Purple (#a855f7)
            { r: 79,  g: 143, b: 247 }, // Cobalt Blue (#4f8ff7)
            { r: 56,  g: 189, b: 248 }, // Hydrotech Sky (#38bdf8)
            { r: 129, g: 140, b: 248 }, // Soft Violet (#818cf8)
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

        function initParticles() {
            particles.length = 0;
            const isCompact = window.innerWidth < 768;
            const count = isCompact ? 240 : 420;
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
        ['admin_loginname', 'admin_loginpassword'].forEach(function (id) {
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

        const adminForm = document.getElementById('adminLoginForm');
        if (adminForm) {
            adminForm.addEventListener('submit', function () {
                hyperdriveActive = true;
                spawnShockwave(lastCenterX || width * 0.5, lastCenterY || height * 0.43, Math.max(width, height) * 0.85, 1.6);
                const btn = document.getElementById('adminSignInBtn');
                const icon = document.getElementById('adminSignInIcon');
                const spinner = document.getElementById('adminSignInSpinner');
                const text = document.getElementById('adminSignInText');
                if (btn) btn.classList.add('opacity-90', 'pointer-events-none');
                if (icon) icon.classList.add('hidden');
                if (spinner) spinner.classList.remove('hidden');
                if (text) text.textContent = 'Authenticating...';
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

            if (pointer.active) {
                pointer.smoothX += (pointer.x - pointer.smoothX) * 0.14;
                pointer.smoothY += (pointer.y - pointer.smoothY) * 0.14;
                const spotGrad = ctx.createRadialGradient(pointer.smoothX, pointer.smoothY, 4, pointer.smoothX, pointer.smoothY, pointer.radius * 1.15);
                spotGrad.addColorStop(0, 'rgba(168, 85, 247, 0.09)');
                spotGrad.addColorStop(0.5, 'rgba(56, 189, 248, 0.04)');
                spotGrad.addColorStop(1, 'rgba(4, 6, 11, 0)');
                ctx.fillStyle = spotGrad;
                ctx.beginPath();
                ctx.arc(pointer.smoothX, pointer.smoothY, pointer.radius * 1.15, 0, Math.PI * 2);
                ctx.fill();
            }

            for (let i = 0; i < stars.length; i++) {
                const s = stars[i];
                const twinkle = 0.72 + 0.28 * Math.sin(time * 2.8 + s.twinklePhase);
                ctx.fillStyle = `rgba(196, 181, 253, ${(s.alpha * twinkle * introRaw).toFixed(3)})`;
                ctx.beginPath();
                ctx.arc(s.x * width, s.y * height, s.size, 0, Math.PI * 2);
                ctx.fill();
            }

            for (let s = shockwaves.length - 1; s >= 0; s--) {
                const sw = shockwaves[s];
                sw.r += (sw.maxR - sw.r) * 0.08 + 3.2;
                sw.alpha = Math.max(0, 1 - (sw.r / sw.maxR));
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
