@extends('layouts.guest')

@section('title', 'Steven Hydrotech Exponent | Hydrotech Predictive Inventory & POS System')
@section('robots', 'index, follow')
@section('canonical', url('/'))

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

        /* Cursor-Tracking Glass Spotlight on 4 Feature Cards */
        .hydro-glass-card {
            position: relative;
            overflow: hidden;
        }
        .hydro-glass-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(180px circle at var(--mouse-x, -200px) var(--mouse-y, -200px), rgba(56, 189, 248, 0.18), transparent 75%);
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }
        .hydro-glass-card:hover::before {
            opacity: 1;
        }

        /* Right-Side Input Focus Aura & Micro-Lift */
        .hydro-field-group {
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hydro-field-group:focus-within {
            transform: translateY(-1.5px);
        }
        .hydro-field-group:focus-within label {
            color: #0284c7;
        }
        html.dark .hydro-field-group:focus-within label {
            color: #38bdf8;
        }
        .hydro-input:focus {
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.16);
        }

        /* Sign In Button Periodic Light Sheen, Cursor-Following Spotlight & Click Ripple */
        .hydro-signin-btn {
            position: relative;
            overflow: hidden;
            will-change: transform;
        }
        .hydro-signin-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(130px circle at var(--btn-x, -200px) var(--btn-y, -200px), rgba(56, 189, 248, 0.28), transparent 70%);
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }
        .hydro-signin-btn:hover::before {
            opacity: 1;
        }
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
            background: radial-gradient(circle, rgba(56, 189, 248, 0.55) 0%, rgba(37, 99, 235, 0.15) 60%, transparent 100%);
            pointer-events: none;
            animation: hydroBtnRipple 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Password Eye Toggle Spring Morph */
        @keyframes hydroEyePop {
            0% { transform: scale(0.6) rotate(-25deg); opacity: 0.3; }
            65% { transform: scale(1.18) rotate(6deg); opacity: 1; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        .hydro-eye-pop {
            animation: hydroEyePop 0.34s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Elastic Error Shake & Crimson Pulse */
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

        /* Seamless Cross-Portal Warp Transition */
        .hydro-portal-warp-out {
            transform: scale(0.94) translateY(-10px) !important;
            filter: blur(8px) !important;
            opacity: 0 !important;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), filter 0.22s ease, opacity 0.2s ease !important;
        }

        /* Progressive 5-Tap Orbital Charge Ring */
        .hydro-tap-arc {
            stroke-dasharray: 289;
            stroke-dashoffset: 289;
            transition: stroke-dashoffset 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
        }

        #hydroBrandCluster {
            transition: transform 0.18s cubic-bezier(0.22, 1, 0.36, 1), filter 0.22s ease, opacity 0.2s ease;
            will-change: transform;
        }
    </style>

    <div id="hydrotechOuterStage" class="min-h-dvh w-full flex flex-col lg:flex-row items-center lg:items-stretch justify-center bg-[#04060b] lg:bg-slate-100/90 lg:dark:bg-[#080a10] p-3 sm:p-5 lg:p-4 gap-3 sm:gap-5 lg:gap-4 relative overflow-x-hidden">

        <!-- LEFT / FULL-SCREEN MOBILE VORTEX HERO -->
        <div id="hydrotechVortexPanel" class="flex w-full lg:w-[52%] xl:w-[54%] lg:min-h-[calc(100dvh-2rem)] lg:rounded-[32px] lg:bg-[#04060b] text-white flex-col justify-between px-2 py-2 sm:p-4 lg:p-8 xl:p-11 relative lg:overflow-hidden lg:border lg:border-white/10 lg:shadow-2xl select-none z-10">

            <!-- Interactive 60fps 3D Hollow-Eye Particle Vortex Canvas -->
            <canvas id="hydrotechVortexCanvas" class="fixed inset-0 lg:absolute lg:inset-0 w-full h-full pointer-events-none z-0"></canvas>

            <div class="hidden lg:block absolute -top-28 -left-28 w-80 h-80 bg-sky-500/10 rounded-full blur-3xl pointer-events-none z-0"></div>
            <div class="hidden lg:block absolute -bottom-28 -right-28 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none z-0"></div>

            <!-- Centered Branding & Hero Section -->
            <div class="flex-1 flex flex-col items-center justify-center text-center relative z-10 py-1 sm:py-3 lg:py-4">
                <div id="hydroBrandCluster" class="flex flex-col items-center justify-center px-2 py-1 sm:px-6 sm:py-3">

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

                    <!-- Prominent Centered Standalone Logo (5-taps-in-3s strictly on Mobile & Tablet ONLY) -->
                    <div data-admin-portal-logo class="relative w-16 h-16 sm:w-24 sm:h-24 lg:w-32 lg:h-32 xl:w-36 xl:h-36 flex items-center justify-center my-1.5 sm:my-3 lg:my-4 touch-manipulation transition-transform duration-200">
                        <div class="hydro-orbit-cw absolute inset-0 rounded-full border border-dashed border-sky-400/30 pointer-events-none"></div>
                        <div class="hydro-orbit-ccw absolute inset-2 sm:inset-3 rounded-full border border-indigo-400/25 pointer-events-none"></div>
                        <!-- Progressive 5-Tap Orbital Charge Ring (Mobile & Tablet Visual Feedback) -->
                        <svg class="absolute inset-0 w-full h-full -rotate-90 pointer-events-none" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="46" fill="none" stroke="rgba(56, 189, 248, 0.85)" stroke-width="2.75" stroke-linecap="round" class="hydro-tap-arc opacity-0 drop-shadow-[0_0_8px_rgba(56,189,248,0.85)]"></circle>
                        </svg>
                        <div class="absolute inset-3 sm:inset-4 rounded-full bg-sky-500/15 blur-xl pointer-events-none"></div>

                        <div class="hydro-logo-float w-12 h-12 sm:w-18 sm:h-18 lg:w-24 lg:h-24 xl:w-28 xl:h-28 flex items-center justify-center">
                            <img src="{{ asset('images/logo.svg') }}" alt="Steven Hydrotech Logo" draggable="false" class="w-full h-full object-contain drop-shadow-[0_0_25px_rgba(56,189,248,0.5)]">
                        </div>
                    </div>

                    <p class="liftoff-subtitle text-[9px] sm:text-[11px] xl:text-xs text-sky-200/95 font-bold tracking-[0.2em] sm:tracking-[0.28em] uppercase drop-shadow-[0_2px_12px_rgba(0,0,0,0.95)]">
                        WATER TREATMENT AND SUPPLY SERVICES
                    </p>
                </div>
            </div>

            <!-- Bottom Frosted Glass Features Grid with Cursor-Tracking Spotlight -->
            <div class="hidden lg:grid grid-cols-2 xl:grid-cols-4 gap-2.5 relative z-10 pt-2">
                <div class="hydro-glass-card group liftoff-pillar-1 p-3 rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-sky-400/30 backdrop-blur-md space-y-1 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center gap-1.5 text-sky-400 relative z-10">
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        <h4 class="text-[11px] font-bold text-slate-100 truncate">Predict Demand</h4>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-snug line-clamp-2 relative z-10">Forecast requirements using analytics.</p>
                </div>
                <div class="hydro-glass-card group liftoff-pillar-2 p-3 rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-sky-400/30 backdrop-blur-md space-y-1 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center gap-1.5 text-sky-400 relative z-10">
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <h4 class="text-[11px] font-bold text-slate-100 truncate">Optimize Orders</h4>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-snug line-clamp-2 relative z-10">Determine ideal restock quantities.</p>
                </div>
                <div class="hydro-glass-card group liftoff-pillar-3 p-3 rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-sky-400/30 backdrop-blur-md space-y-1 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center gap-1.5 text-sky-400 relative z-10">
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h4 class="text-[11px] font-bold text-slate-100 truncate">Reduce Costs</h4>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-snug line-clamp-2 relative z-10">Lower holding costs &amp; avoid stockouts.</p>
                </div>
                <div class="hydro-glass-card group liftoff-pillar-4 p-3 rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-sky-400/30 backdrop-blur-md space-y-1 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center gap-1.5 text-sky-400 relative z-10">
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <h4 class="text-[11px] font-bold text-slate-100 truncate">Smart Decisions</h4>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-snug line-clamp-2 relative z-10">Real-time insights for management.</p>
                </div>
            </div>
        </div>

        <!-- RIGHT / FLOATING LOGIN CARD -->
        <div id="hydroLoginCard" class="liftoff-login-card w-full max-w-md lg:max-w-none lg:flex-1 flex flex-col justify-between items-center p-5 sm:p-8 lg:p-10 xl:p-16 bg-white/95 dark:bg-[#10141D]/92 lg:bg-white lg:dark:bg-[#10141D] backdrop-blur-xl rounded-[26px] sm:rounded-[30px] lg:rounded-[32px] border border-white/20 lg:border-slate-200/80 dark:border-[#1e2536] shadow-[0_24px_60px_rgba(0,0,0,0.65)] lg:shadow-sm relative z-10 overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-sky-500/[0.06] dark:bg-sky-500/[0.08] rounded-full blur-3xl pointer-events-none"></div>

            <div class="hidden lg:block"></div>

            <div class="w-full max-w-md space-y-4 sm:space-y-6 my-auto relative z-10">
                <div class="text-center lg:text-left space-y-1.5 sm:space-y-2">
                    <div class="liftoff-item-1 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-50 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800/80 text-sky-700 dark:text-sky-300 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                        Predictive Inventory &amp; POS Portal
                    </div>
                    <h1 class="liftoff-item-2 text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 dark:text-white tracking-tight flex items-center justify-center lg:justify-start gap-1">
                        <span id="welcomeTypewriter" data-text="Welcome">Welcome</span>
                        <span aria-hidden="true" class="hydro-caret inline-block w-[3px] h-6 sm:h-8 rounded-full bg-gradient-to-b from-sky-400 via-indigo-500 to-emerald-400"></span>
                    </h1>
                    <p class="liftoff-item-2 text-xs sm:text-sm text-gray-500 dark:text-slate-400">Sign in to your account to continue</p>
                </div>

                @if (session('info'))
                    <div class="rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900 px-4 py-3 text-sm">
                        {{ session('info') }}
                    </div>
                @endif

                @if (session('error'))
                    <div data-hydro-error-alert class="hydro-error-shake rounded-xl bg-red-50 dark:bg-rose-950/60 text-red-700 dark:text-rose-300 border border-red-200 dark:border-rose-900 px-4 py-3 text-sm">
                        {{ session('error') }}
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

                <form id="hydroLoginForm" method="POST" action="{{ route('login') }}" class="space-y-3.5 sm:space-y-4">
                    @csrf

                    <div class="liftoff-item-3 hydro-field-group">
                        <label for="loginname" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-1.5 transition-colors">
                            Email address or Username
                        </label>
                        <div class="relative">
                            <input type="text" name="loginname" id="loginname" value="{{ old('loginname') }}" required
                                   placeholder="staff@stevenhydrotech.com"
                                   class="hydro-input w-full rounded-xl bg-slate-50/70 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2.5 sm:py-3 pr-9 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500
                                          focus:outline-none focus:bg-white dark:focus:bg-[#161B26] focus:border-sky-500 dark:focus:border-sky-500 transition-all">
                            <span id="loginnameReadyDot" aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center opacity-0 scale-50 transition-all duration-300">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.9)]"></span>
                            </span>
                        </div>
                    </div>

                    <div class="liftoff-item-4 hydro-field-group">
                        <label for="loginpassword" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-1.5 transition-colors">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" name="loginpassword" id="loginpassword" required
                                   placeholder="••••••••••••"
                                   class="hydro-input w-full rounded-xl bg-slate-50/70 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3.5 py-2.5 sm:py-3 pr-10 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500
                                          focus:outline-none focus:bg-white dark:focus:bg-[#161B26] focus:border-sky-500 dark:focus:border-sky-500 transition-all">

                            <button type="button" id="togglePassword" aria-label="Toggle password visibility"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-sky-500 dark:hover:text-sky-400 transition-colors cursor-pointer">
                                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeOff" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="liftoff-item-5 flex items-center justify-between text-sm pt-0.5">
                        <label for="remember" class="inline-flex items-center gap-2.5 cursor-pointer select-none group">
                            <span class="relative flex items-center justify-center w-4 h-4">
                                <input type="checkbox" name="remember" id="remember"
                                       class="peer sr-only">
                                <span class="w-4 h-4 rounded-md border-2 border-gray-300 dark:border-[#2a344a] bg-white dark:bg-[#161B26] peer-checked:bg-sky-500 peer-checked:border-sky-500 peer-focus-visible:ring-2 peer-focus-visible:ring-sky-400 transition-all duration-200 group-hover:border-sky-400"></span>
                                <svg class="w-3 h-3 text-white absolute pointer-events-none opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-200" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span class="text-gray-600 dark:text-slate-300 group-hover:text-gray-900 dark:group-hover:text-white font-medium text-xs sm:text-sm transition-colors">Remember Me</span>
                        </label>
                    </div>

                    <!-- Official Hydrotech Outlined Blue Pill Button with Shimmer Sheen, Icon Glide & Hyperdrive Liftoff State -->
                    <div class="liftoff-item-6">
                        <button type="submit" id="hydroSignInBtn"
                                class="hydro-signin-btn group w-full bg-white border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-blue-500/80 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white font-bold py-3 sm:py-3.5 rounded-full transition-all duration-300 ease-in-out shadow-xs hover:shadow-[0_8px_25px_-5px_rgba(37,99,235,0.45)] cursor-pointer flex items-center justify-center gap-2 text-sm">
                            <svg id="hydroSignInIcon" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            <svg id="hydroSignInSpinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span id="hydroSignInText">Sign In to System</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Subtle Executive Footer -->
            <div class="liftoff-item-7 w-full max-w-md pt-4 sm:pt-6 mt-4 border-t border-gray-100 dark:border-[#1e2536] flex items-center justify-between text-[10px] sm:text-[11px] text-gray-400 dark:text-slate-500 relative z-10">
                <span>&copy; {{ date('Y') }} Steven Hydrotech Exponent</span>
                <span>Authorized Personnel Only</span>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    const toggleButton = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('loginpassword');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeOff = document.getElementById('eyeOff');

    if (toggleButton && passwordInput) {
        toggleButton.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            eyeOpen?.classList.toggle('hidden', isHidden);
            eyeOff?.classList.toggle('hidden', !isHidden);
            const activeIcon = isHidden ? eyeOff : eyeOpen;
            if (activeIcon) {
                activeIcon.classList.remove('hydro-eye-pop');
                void activeIcon.offsetWidth;
                activeIcon.classList.add('hydro-eye-pop');
            }
        });
    }

    // Live Field Readiness Indicator on Username / Email Input
    (function () {
        const loginInput = document.getElementById('loginname');
        const readyDot = document.getElementById('loginnameReadyDot');
        if (!loginInput || !readyDot) return;
        function syncReadyState() {
            const valid = (loginInput.value || '').trim().length >= 3;
            readyDot.classList.toggle('opacity-100', valid);
            readyDot.classList.toggle('scale-100', valid);
            readyDot.classList.toggle('opacity-0', !valid);
            readyDot.classList.toggle('scale-50', !valid);
        }
        loginInput.addEventListener('input', syncReadyState);
        syncReadyState();
    })();

    // Magnetic Sign-In Button with Cursor-Following Glow & Tactile Water-Droplet Click Ripple
    (function () {
        const btn = document.getElementById('hydroSignInBtn');
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

    // Cursor-Tracking Glass Spotlight on the 4 Bottom Feature Cards
    document.querySelectorAll('.hydro-glass-card').forEach(function (card) {
        card.addEventListener('pointermove', function (e) {
            const r = card.getBoundingClientRect();
            card.style.setProperty('--mouse-x', (e.clientX - r.left) + 'px');
            card.style.setProperty('--mouse-y', (e.clientY - r.top) + 'px');
        });
    });

    // Typewriter Entrance Reveal for "Welcome|"
    (function () {
        const el = document.getElementById('welcomeTypewriter');
        if (!el) return;
        const fullText = el.getAttribute('data-text') || 'Welcome';
        el.textContent = '';
        let idx = 0;
        setTimeout(function step() {
            if (idx <= fullText.length) {
                el.textContent = fullText.slice(0, idx);
                idx++;
                if (idx <= fullText.length) {
                    setTimeout(step, 65);
                }
            }
        }, 420);
    })();

    // Strict Device-Separated Secret Admin Portal Triggers + Progressive 5-Tap Orbital Charge Ring & Cross-Portal Warp:
    // 1. Desktop / Laptop / PC: Ctrl + Shift + S + A keyboard shortcut ONLY
    // 2. Mobile & Tablet: Tap 5 times within 3 seconds on the Hydrotech logo ONLY
    (function () {
        const portalUrl = @json(route('admin.portal.login'));
        const heldKeys = new Set();
        let sArmedUntil = 0;
        let drainTimer = null;

        function isMobileOrTabletDevice() {
            if (window.innerWidth <= 1024) return true;
            if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) return true;
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|Tablet/i.test(navigator.userAgent || '');
        }

        function updateTapChargeRing(tapCount) {
            const circumference = 289;
            const progress = Math.max(0, Math.min(1, tapCount / 5));
            const offset = circumference * (1 - progress);
            document.querySelectorAll('.hydro-tap-arc').forEach(function (arc) {
                arc.style.strokeDashoffset = offset.toFixed(1);
                arc.style.opacity = tapCount > 0 ? '1' : '0';
            });
        }

        function launchAdminPortalWithWarp() {
            if (window.hydrotechTriggerHyperdrive) window.hydrotechTriggerHyperdrive();
            const card = document.getElementById('hydroLoginCard');
            const cluster = document.getElementById('hydroBrandCluster');
            if (card) card.classList.add('hydro-portal-warp-out');
            if (cluster) cluster.classList.add('hydro-portal-warp-out');
            setTimeout(function () {
                window.location.href = portalUrl;
            }, 210);
        }

        window.addEventListener('keydown', function (e) {
            const code = e.code || '';
            const key = (e.key || '').toLowerCase();
            const isS = code === 'KeyS' || key === 's';
            const isA = code === 'KeyA' || key === 'a';

            if (!e.ctrlKey || !e.shiftKey) {
                heldKeys.clear();
                sArmedUntil = 0;
                return;
            }

            if (isS) {
                e.preventDefault();
                e.stopPropagation();
                heldKeys.add('s');
                sArmedUntil = Date.now() + 1500;
            }

            if (isA && (heldKeys.has('s') || Date.now() <= sArmedUntil)) {
                e.preventDefault();
                e.stopPropagation();
                heldKeys.clear();
                sArmedUntil = 0;
                launchAdminPortalWithWarp();
            } else if (isA) {
                heldKeys.add('a');
            }

            if (isS && heldKeys.has('a')) {
                e.preventDefault();
                e.stopPropagation();
                heldKeys.clear();
                sArmedUntil = 0;
                launchAdminPortalWithWarp();
            }
        }, true);

        window.addEventListener('keyup', function (e) {
            const code = e.code || '';
            const key = (e.key || '').toLowerCase();
            if (code === 'KeyS' || key === 's') heldKeys.delete('s');
            if (code === 'KeyA' || key === 'a') heldKeys.delete('a');
            if (!e.ctrlKey || !e.shiftKey) {
                heldKeys.clear();
                sArmedUntil = 0;
            }
        }, true);

        window.addEventListener('blur', function () {
            heldKeys.clear();
            sArmedUntil = 0;
        });

        // Mobile & Tablet ONLY: 5 taps on the Hydrotech logo within 3 seconds with Progressive Orbital Charge Ring
        const tapTimestamps = [];
        document.querySelectorAll('[data-admin-portal-logo]').forEach(function (logoEl) {
            logoEl.addEventListener('click', function (e) {
                if (!isMobileOrTabletDevice()) return;
                e.stopPropagation();
                const now = Date.now();
                tapTimestamps.push(now);
                while (tapTimestamps.length > 0 && now - tapTimestamps[0] > 3000) {
                    tapTimestamps.shift();
                }

                updateTapChargeRing(tapTimestamps.length);
                logoEl.style.transform = 'scale(1.1)';
                setTimeout(function () { logoEl.style.transform = ''; }, 160);

                if (drainTimer) clearTimeout(drainTimer);
                drainTimer = setTimeout(function () {
                    tapTimestamps.length = 0;
                    updateTapChargeRing(0);
                }, 3000);

                if (window.hydrotechPulseCenter) {
                    window.hydrotechPulseCenter(0.45 + tapTimestamps.length * 0.12);
                }
                if (tapTimestamps.length >= 5) {
                    if (drainTimer) clearTimeout(drainTimer);
                    tapTimestamps.length = 0;
                    launchAdminPortalWithWarp();
                }
            });
        });
    })();

    // Interactive 60fps 3D Hollow-Eye Particle Vortex + Typing Resonance + Security Lock Shift + Error Pulse + Hyperdrive Liftoff
    (function () {
        const panel = document.getElementById('hydrotechVortexPanel');
        const outerStage = document.getElementById('hydrotechOuterStage');
        const canvas = document.getElementById('hydrotechVortexCanvas');
        const brandCluster = document.getElementById('hydroBrandCluster');
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
        let securityModeTarget = 0;
        let securityBlend = 0;
        let errorPulseEnergy = document.querySelector('[data-hydro-error-alert]') ? 1.0 : 0;
        let hyperdriveActive = false;
        let hyperdriveProgress = 0;
        let lastCenterX = 0;
        let lastCenterY = 0;

        const palette = [
            { r: 79,  g: 143, b: 247 }, // Cobalt Blue (#4f8ff7)
            { r: 59,  g: 130, b: 246 }, // Royal Blue (#3b82f6)
            { r: 99,  g: 102, b: 241 }, // Electric Indigo (#6366f1)
            { r: 56,  g: 189, b: 248 }, // Hydrotech Sky (#38bdf8)
            { r: 129, g: 140, b: 248 }, // Soft Violet-Blue (#818cf8)
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

        // Expose Typing Resonance & Hyperdrive hooks
        window.hydrotechPulseCenter = function (strength) {
            typingEnergy = Math.min(1.1, typingEnergy + (strength || 0.35));
            spawnShockwave(lastCenterX || width * 0.5, lastCenterY || height * 0.43, Math.min(width, height) * 0.46, strength || 0.45);
        };

        window.hydrotechTriggerHyperdrive = function () {
            hyperdriveActive = true;
            spawnShockwave(lastCenterX || width * 0.5, lastCenterY || height * 0.43, Math.max(width, height) * 0.85, 1.6);
        };

        // Wire Typing Resonance & Password Security Lock Shift to Right-Side Login Inputs
        let lastKeyPulseAt = 0;
        ['loginname', 'loginpassword'].forEach(function (id) {
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
            if (id === 'loginpassword') {
                inputEl.addEventListener('focus', function () {
                    securityModeTarget = 1;
                    spawnShockwave(lastCenterX || width * 0.5, lastCenterY || height * 0.43, Math.min(width, height) * 0.48, 0.55);
                });
                inputEl.addEventListener('blur', function () {
                    securityModeTarget = 0;
                });
            }
        });

        // Wire Hyperdrive Liftoff on Form Submit
        const loginForm = document.getElementById('hydroLoginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function () {
                window.hydrotechTriggerHyperdrive();
                const btn = document.getElementById('hydroSignInBtn');
                const icon = document.getElementById('hydroSignInIcon');
                const spinner = document.getElementById('hydroSignInSpinner');
                const text = document.getElementById('hydroSignInText');
                if (btn) btn.classList.add('opacity-90', 'pointer-events-none');
                if (icon) icon.classList.add('hidden');
                if (spinner) spinner.classList.remove('hidden');
                if (text) text.textContent = 'Signing In...';
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
            securityBlend += (securityModeTarget - securityBlend) * 0.08;
            errorPulseEnergy *= 0.965;
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
                spawnShockwave(cx, cy, Math.min(width, height) * 0.58, errorPulseEnergy > 0.2 ? 1.45 : 1);
            }

            if (pointer.active) {
                pointer.smoothX += (pointer.x - pointer.smoothX) * 0.14;
                pointer.smoothY += (pointer.y - pointer.smoothY) * 0.14;
                const spotGrad = ctx.createRadialGradient(pointer.smoothX, pointer.smoothY, 4, pointer.smoothX, pointer.smoothY, pointer.radius * 1.15);
                spotGrad.addColorStop(0, 'rgba(56, 189, 248, 0.09)');
                spotGrad.addColorStop(0.5, 'rgba(99, 102, 241, 0.04)');
                spotGrad.addColorStop(1, 'rgba(4, 6, 11, 0)');
                ctx.fillStyle = spotGrad;
                ctx.beginPath();
                ctx.arc(pointer.smoothX, pointer.smoothY, pointer.radius * 1.15, 0, Math.PI * 2);
                ctx.fill();
            }

            for (let i = 0; i < stars.length; i++) {
                const s = stars[i];
                const twinkle = 0.72 + 0.28 * Math.sin(time * 2.8 + s.twinklePhase);
                ctx.fillStyle = `rgba(186, 215, 255, ${(s.alpha * twinkle * introRaw).toFixed(3)})`;
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
            const innerEyeRadius = baseRadius * ((isDesktop ? 0.42 : 0.36) - securityBlend * 0.055);
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

            projected.sort(function (a, b) {
                return b.z - a.z;
            });

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

                const r = Math.min(255, Math.max(0, Math.round(p.color.r + item.cursorGlow * 75 + securityBlend * 48 + errorPulseEnergy * 145)));
                const g = Math.min(255, Math.max(0, Math.round(p.color.g + item.cursorGlow * 65 - securityBlend * 18 - errorPulseEnergy * 55)));
                const b = Math.min(255, Math.max(0, Math.round(p.color.b + item.cursorGlow * 35 + securityBlend * 42 - errorPulseEnergy * 45)));

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
