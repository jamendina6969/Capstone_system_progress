@extends('layouts.guest')

@section('title', 'System Administrator Portal — Steven Hydrotech Exponent')
@section('robots', 'noindex, nofollow, noarchive')

@section('content')
    <div class="min-h-screen w-full flex flex-col lg:flex-row bg-[#0b0e14] text-slate-100 overflow-x-hidden">

        <!-- LEFT SIDE: Restricted Admin Brand Panel (desktop only) -->
        <div class="hidden lg:flex lg:w-1/2 min-h-screen bg-[#0b0e14] text-white flex-col justify-between p-8 lg:p-12 relative overflow-hidden border-r border-slate-800/80">
            <!-- Subtle Background Glow -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Centered Branding & Hero Section -->
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

                <!-- Standalone Logo -->
                <div class="w-36 h-36 flex items-center justify-center p-2 my-2">
                    <img src="{{ asset('images/logo.svg') }}" alt="Steven Hydrotech Logo" class="w-full h-full object-contain drop-shadow-2xl">
                </div>

                <div class="space-y-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-purple-500/15 border border-purple-400/30 text-purple-300 text-xs font-bold uppercase tracking-widest">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Restricted System Administration
                    </span>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
                        Dedicated authentication gateway for System Administrator accounts. All sign-in attempts are rate-limited and recorded in the security audit log.
                    </p>
                </div>
            </div>

            <!-- Bottom Governance Cards (4 Admin Pillars) -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 pt-6 border-t border-slate-800/80 relative z-10">
                <div class="p-3 rounded-xl bg-slate-900/50 border border-slate-800/80 backdrop-blur-xs space-y-1">
                    <div class="text-sky-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-200">User Governance</h4>
                    <p class="text-[10px] text-slate-400 leading-snug">Manage roles, access &amp; credentials.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-900/50 border border-slate-800/80 backdrop-blur-xs space-y-1">
                    <div class="text-sky-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-200">System Backups</h4>
                    <p class="text-[10px] text-slate-400 leading-snug">Database snapshots &amp; recovery.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-900/50 border border-slate-800/80 backdrop-blur-xs space-y-1">
                    <div class="text-sky-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-200">Audit Oversight</h4>
                    <p class="text-[10px] text-slate-400 leading-snug">Track logins, IPs &amp; system changes.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-900/50 border border-slate-800/80 backdrop-blur-xs space-y-1">
                    <div class="text-sky-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-200">Access Control</h4>
                    <p class="text-[10px] text-slate-400 leading-snug">Isolated admin gateway &amp; lockout.</p>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: Distinct Dark Slate System Administrator Login -->
        <div class="w-full lg:w-1/2 min-h-screen flex flex-col justify-center items-center p-4 sm:p-8 lg:p-16 overflow-y-auto py-8 bg-[#10141D]">
            <div class="w-full max-w-md space-y-6">

                {{-- Mobile-only brand header --}}
                <div class="flex lg:hidden flex-col items-center text-center space-y-3 mb-2">
                    <div class="w-16 h-16 flex items-center justify-center">
                        <img src="{{ asset('images/logo.svg') }}" alt="Steven Hydrotech Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="space-y-1">
                        <h2 class="text-xl font-black tracking-wider text-white">STEVEN HYDROTECH</h2>
                        <div class="flex items-center justify-center gap-2">
                            <div class="h-[2px] w-8 bg-sky-400"></div>
                            <span class="text-[10px] uppercase tracking-[0.3em] text-sky-300 font-extrabold">E X P O N E N T</span>
                            <div class="h-[2px] w-8 bg-sky-400"></div>
                        </div>
                    </div>
                </div>

                <!-- Portal Badge & Heading -->
                <div class="text-center lg:text-left space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-300 text-xs font-bold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                        System Administrator Portal
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Admin Sign In</h1>
                    <p class="text-sm text-slate-400">Authorized System Administrators only</p>
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

                <!-- Dark Slate Form Card -->
                <form method="POST" action="{{ route('admin.portal.authenticate') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="admin_loginname" class="block text-sm font-medium text-slate-300 mb-1">
                            Administrator Username or Email
                        </label>
                        <input type="text" name="loginname" id="admin_loginname" value="{{ old('loginname') }}" required autofocus autocomplete="username"
                               placeholder="admin@stevenhydrotech.com"
                               class="w-full rounded-xl bg-[#161B26] border border-[#1e2536] px-3.5 py-3 text-sm text-slate-100 placeholder-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div>
                        <label for="admin_loginpassword" class="block text-sm font-medium text-slate-300 mb-1">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" name="loginpassword" id="admin_loginpassword" required autocomplete="current-password"
                                   placeholder="••••••••••••"
                                   class="w-full rounded-xl bg-[#161B26] border border-[#1e2536] px-3.5 py-3 pr-10 text-sm text-slate-100 placeholder-slate-500
                                          focus:outline-none focus:ring-2 focus:ring-sky-500">

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

                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="remember" id="admin_remember"
                                   class="rounded border-slate-700 bg-[#161B26] text-sky-600 focus:ring-sky-500">
                            <label for="admin_remember" class="text-slate-300">Remember Me</label>
                        </div>
                        <span class="text-[11px] text-slate-500">Max 5 attempts / 15 min</span>
                    </div>

                    <!-- Official Hydrotech Outlined Sky Button -->
                    <button type="submit"
                            class="w-full bg-[#10141D] border-2 border-sky-600/80 text-sky-400 hover:bg-sky-600 hover:text-white font-semibold py-3.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Authenticate as Administrator
                    </button>
                </form>

                <!-- Footer: Return link & Security Notice -->
                <div class="pt-4 border-t border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                    <span class="text-slate-500">All access attempts are logged by IP.</span>
                    <a href="{{ route('login') }}"
                       class="text-slate-400 hover:text-sky-400 font-semibold transition-colors inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Standard Login
                    </a>
                </div>
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
</script>
@endpush
