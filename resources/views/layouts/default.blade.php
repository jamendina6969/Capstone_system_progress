<!DOCTYPE html>
<html lang="en" class="{{ auth()->check() && auth()->user()->theme === 'dark' ? 'dark' : '' }} h-full bg-[#10141D]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#10141D">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>@yield('title', 'Hydrotech Inventory System')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    
    <script>
        // Check and apply product view mode immediately (respects user DB preference; defaults to grid on mobile phones if unset)
        try {
            var defaultUserView = @json(auth()->check() ? auth()->user()->getPreference('default_view', 'list') : 'list');
            var savedProductView = localStorage.getItem('productView');
            var activeProductView = savedProductView || (window.innerWidth < 640 ? 'grid' : defaultUserView);
            if (activeProductView === 'grid') {
                document.documentElement.dataset.productView = 'grid';
            }
        } catch (e) {}

        // Initialize sidebar state from localStorage or default viewport rules
        (function () {
            try {
                var savedState = localStorage.getItem('sidebarState');
                var isDesktop = window.innerWidth >= 1024;
                var isWideDesktop = window.innerWidth >= 1280;
                if (isDesktop && (savedState === 'open' || (!savedState && isWideDesktop))) {
                    document.documentElement.classList.add('sidebar-open');
                } else {
                    // Mobile/Tablet (<1024) or Compact Laptop (1024-1279 without explicit open): keep collapsed
                    document.documentElement.classList.remove('sidebar-open');
                }
            } catch (e) {}
        })();
    </script>
    <style>
        /* Sidebar transition and width handling */
        #sidebar {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s ease;
        }

        @media (min-width: 1024px) {
            /* Default closed state: slim icon-only strip */
            #sidebar {
                width: 4.5rem !important;
                min-width: 4.5rem !important;
            }
            /* Expanded state when sidebar-open class is active */
            html.sidebar-open #sidebar {
                width: 17rem !important;
                min-width: 17rem !important;
            }

            /* Hide text and badge counts when collapsed */
            html:not(.sidebar-open) #sidebar .sidebar-text,
            html:not(.sidebar-open) #sidebar .sidebar-header-text,
            html:not(.sidebar-open) #sidebar .badge-count {
                display: none !important;
            }

            /* Center icons perfectly when collapsed */
            html:not(.sidebar-open) #sidebar nav a,
            html:not(.sidebar-open) #sidebar button#themeToggle,
            html:not(.sidebar-open) #sidebar form button {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            /* Tooltip on hover for collapsed state */
            html:not(.sidebar-open) #sidebar nav a {
                position: relative;
            }
            html:not(.sidebar-open) #sidebar nav a:hover::after {
                content: attr(title);
                position: absolute;
                left: calc(100% + 12px);
                top: 50%;
                transform: translateY(-50%);
                background-color: #1e2536;
                color: #ffffff;
                padding: 6px 12px;
                font-size: 0.75rem;
                font-weight: 500;
                border-radius: 6px;
                white-space: nowrap;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
                z-index: 50;
                pointer-events: none;
            }
        }
    </style>
</head>
<body class="h-full font-['Inter',sans-serif] antialiased text-slate-800 bg-[#10141D] dark:text-slate-200 dark:bg-[#10141D]">

    <div id="sidebarOverlay"></div>

    <div class="flex h-dvh overflow-hidden">

        @include('layouts.partials.sidenav')

        <!-- Main content container area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50 dark:bg-[#161B26]">

            <!-- Enterprise Topbar -->
            <header class="h-16 bg-white dark:bg-[#10141D] border-b border-slate-200/80 dark:border-[#1e2536] px-3 sm:px-4 lg:px-6 flex items-center justify-between relative z-40 shadow-2xs shrink-0">
                
                <div class="flex items-center gap-3">
                    {{-- Mobile hamburger (hidden on desktop) --}}
                    <button type="button" data-sidebar-toggle aria-label="Open menu"
                            class="lg:hidden p-2 -ml-1 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#1e2536]/60 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div id="mobileBrandHeader" class="flex lg:hidden items-center gap-2">
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-6 w-auto">
                        <span class="text-xs font-bold text-slate-900 dark:text-white truncate max-w-[130px] sm:max-w-none">Hydrotech</span>
                    </div>
                </div>

                <!-- Right Utility Actions -->
                <div class="flex items-center space-x-3">
                    <!-- Smart Live Refresh (works for all roles, offline-safe) -->
                    <button id="globalRefreshBtn" type="button" onclick="window.HydrotechLiveSync ? window.HydrotechLiveSync.manualRefresh(this) : window.location.reload();" class="p-2 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#1e2536]/60 rounded-lg transition-colors cursor-pointer" title="Refresh & Sync Data">
                        <svg class="refresh-icon w-5 h-5 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>

                    <!-- Role-Based Hybrid Notification Bell & Dropdown -->
                    <div class="relative">
                        @php
                            $notifPayload = app(\App\Services\NotificationService::class)->getDropdownPayload(auth()->user(), 8);
                            $totalNotifications = $notifPayload['total_count'] ?? 0;
                            $unreadInboxCount = $notifPayload['unread_inbox_count'] ?? 0;
                            $notifQueues = $notifPayload['queues'] ?? [];
                            $notifInbox = $notifPayload['inbox'] ?? collect();
                        @endphp

                        <button id="notificationBtn" type="button" title="Notifications"
                                class="relative p-2 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#1e2536]/60 rounded-lg transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span id="notifBadgeCount"
                                  class="{{ $totalNotifications > 0 ? '' : 'hidden' }} absolute top-1 right-1 min-w-[18px] h-[18px] bg-rose-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center px-1">
                                {{ $totalNotifications > 99 ? '99+' : $totalNotifications }}
                            </span>
                        </button>

                        <div id="notificationDropdown" class="hidden fixed sm:absolute left-3 right-3 sm:left-auto sm:right-0 top-16 sm:top-auto sm:mt-2 w-auto sm:w-96 max-w-[calc(100vw-1.5rem)] bg-white dark:bg-[#10141D] border-2 border-slate-200 dark:border-[#1e2536] rounded-2xl shadow-xl overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-[#1e2536] flex items-center justify-between gap-2 bg-slate-50/70 dark:bg-[#161B26]/60">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Notifications</span>
                                    <span id="notifHeaderPill" class="text-[10px] {{ $totalNotifications > 0 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-slate-100 text-slate-600 dark:bg-[#1e2536] dark:text-slate-400' }} px-2 py-0.5 rounded-full font-bold">
                                        {{ $totalNotifications }} Active
                                    </span>
                                </div>
                                @if($unreadInboxCount > 0)
                                    <form method="POST" action="{{ route('notifications.readAll') }}" class="m-0">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                            Mark all read
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div id="notifDropdownBody" class="max-h-96 overflow-y-auto divide-y divide-slate-100 dark:divide-[#1e2536]">
                                {{-- Live Action Queues --}}
                                @if(count($notifQueues) > 0)
                                    <div class="px-4 py-1.5 bg-slate-50/80 dark:bg-[#161B26]/40 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                        Action Required
                                    </div>
                                    @foreach($notifQueues as $qItem)
                                        @php
                                            $dotColor = match($qItem['severity'] ?? 'info') {
                                                'danger'  => 'bg-rose-500',
                                                'warning' => 'bg-amber-500',
                                                'success' => 'bg-emerald-500',
                                                default   => 'bg-blue-500',
                                            };
                                        @endphp
                                        <a href="{{ $qItem['url'] }}" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-[#1e2536]/40 transition-colors">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full shrink-0 {{ $dotColor }}"></span>
                                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $qItem['title'] }}</p>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 pl-4 leading-snug">{{ $qItem['message'] }}</p>
                                        </a>
                                    @endforeach
                                @endif

                                {{-- Personal Persistent Event Inbox --}}
                                @if($notifInbox->count() > 0)
                                    <div class="px-4 py-1.5 bg-slate-50/80 dark:bg-[#161B26]/40 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                        Recent Alerts
                                    </div>
                                    @foreach($notifInbox as $item)
                                        @php
                                            $isUnread = is_null($item->read_at);
                                            $dotColor = match($item->severity) {
                                                'danger'  => 'bg-rose-500',
                                                'warning' => 'bg-amber-500',
                                                'success' => 'bg-emerald-500',
                                                default   => 'bg-blue-500',
                                            };
                                        @endphp
                                        <a href="{{ route('notifications.read', $item) }}"
                                           class="block px-4 py-3 transition-colors {{ $isUnread ? 'bg-blue-50/40 dark:bg-blue-950/20 hover:bg-blue-50 dark:hover:bg-blue-950/30' : 'hover:bg-slate-50 dark:hover:bg-[#1e2536]/40 opacity-80' }}">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $isUnread ? $dotColor : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $item->title }}</p>
                                                </div>
                                                @if($isUnread)
                                                    <span class="shrink-0 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">New</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 pl-4 leading-snug">{{ $item->message }}</p>
                                            <p class="text-[10px] text-slate-400 mt-1 pl-4">{{ $item->created_at?->diffForHumans() }}</p>
                                        </a>
                                    @endforeach
                                @endif

                                @if(count($notifQueues) === 0 && $notifInbox->count() === 0)
                                    <div class="px-4 py-8 text-center text-xs text-slate-400 dark:text-slate-500">
                                        You're all caught up — no pending notifications.
                                    </div>
                                @endif
                            </div>

                            <div class="px-4 py-2.5 border-t border-slate-100 dark:border-[#1e2536] bg-slate-50/70 dark:bg-[#161B26]/60 flex items-center justify-between">
                                <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    View all notifications →
                                </a>
                                <a href="{{ route('account.settings', ['tab' => 'preferences']) }}" class="text-[11px] text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                                    Preferences
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="h-5 w-px bg-slate-200 dark:bg-[#1e2536] mx-1"></div>

                    @php
                        $authUser = Auth::user();
                        $resolvedRole = $authUser->email === 'admin@example.com' ? 'admin' : $authUser->role;
                        $roleWords = explode('_', $resolvedRole);
                        $roleAcronym = count($roleWords) > 1
                            ? strtoupper(substr($roleWords[0], 0, 1) . substr($roleWords[1], 0, 1))
                            : strtoupper(substr($resolvedRole, 0, 2));
                        $friendlySubtitles = [
                            'admin' => 'System Administrator',
                            'manager' => 'Operations Manager',
                            'warehouse_staff' => 'Inventory Specialist',
                            'staff' => 'Cashier / POS Operator',
                        ];
                        $roleBadgeBg = [
                            'admin' => 'bg-blue-600',
                            'manager' => 'bg-indigo-600',
                            'warehouse_staff' => 'bg-amber-600',
                            'staff' => 'bg-emerald-600',
                        ][$resolvedRole] ?? 'bg-blue-600';
                    @endphp

                    <!-- Interactive User Profile Dropdown -->
                    <div class="relative">
                        <button id="profileMenuBtn" type="button"
                                class="flex items-center space-x-3 p-1 pr-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-[#1e2536]/60 transition-colors cursor-pointer text-left">
                            <div class="text-right hidden sm:block">
                                <span class="block text-xs font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ $authUser->full_name }}</span>
                                <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-semibold leading-tight mt-0.5">
                                    {{ $friendlySubtitles[$resolvedRole] ?? ucwords(str_replace('_', ' ', $resolvedRole)) }}
                                </span>
                            </div>
                            @if($authUser->avatar_url)
                                <img src="{{ $authUser->avatar_url }}" alt="{{ $authUser->full_name }}"
                                     onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                     class="w-9 h-9 rounded-full object-cover shadow-xs border-2 border-slate-200 dark:border-[#1e2536]">
                                <div class="hidden w-9 h-9 rounded-full {{ $roleBadgeBg }} text-white font-bold text-xs flex items-center justify-center shadow-xs border-2 border-slate-200 dark:border-[#1e2536]">
                                    {{ $roleAcronym }}
                                </div>
                            @else
                                <div class="w-9 h-9 rounded-full {{ $roleBadgeBg }} text-white font-bold text-xs flex items-center justify-center shadow-xs border-2 border-slate-200 dark:border-[#1e2536]">
                                    {{ $roleAcronym }}
                                </div>
                            @endif
                        </button>

                        <div id="profileMenuDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white dark:bg-[#10141D] border-2 border-slate-200 dark:border-[#1e2536] rounded-2xl shadow-xl overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-[#1e2536] bg-slate-50/70 dark:bg-[#161B26]/60">
                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $authUser->full_name }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $authUser->email }}</p>
                                <span class="inline-block mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-200/80 text-slate-700 dark:bg-[#1e2536] dark:text-slate-300">
                                    {{ $friendlySubtitles[$resolvedRole] ?? $resolvedRole }}
                                </span>
                            </div>
                            <div class="py-1.5">
                                <a href="{{ route('account.settings', ['tab' => 'profile']) }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#161B26]">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Account Settings
                                </a>
                                <a href="{{ route('account.settings', ['tab' => 'security']) }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#161B26]">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Password &amp; Sessions
                                </a>
                                <a href="{{ route('notifications.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#161B26]">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    Notifications Center
                                </a>
                                <a href="{{ route('account.settings', ['tab' => 'activity']) }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#161B26]">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    My Activity
                                </a>
                            </div>
                            <div class="border-t border-slate-100 dark:border-[#1e2536] py-1.5">
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer text-left">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </header>

            <!-- Page content background area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-100 dark:bg-[#161B26] p-3 sm:p-6 lg:p-8">
                
                @if (session('error'))
                    <div data-flash-banner="error" class="mb-5 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-200 border-2 border-rose-200 dark:border-rose-900 px-4 sm:px-5 py-3.5 text-sm font-medium flex items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="break-words">{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-white font-bold text-base leading-none px-1 cursor-pointer shrink-0" aria-label="Dismiss">&times;</button>
                    </div>
                @endif

                @if (session('warning'))
                    <div data-flash-banner="warning" class="mb-5 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-200 border-2 border-amber-200 dark:border-amber-900 px-4 sm:px-5 py-3.5 text-sm font-medium flex items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path></svg>
                            <span class="break-words">{{ session('warning') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700 dark:text-amber-400 dark:hover:text-white font-bold text-base leading-none px-1 cursor-pointer shrink-0" aria-label="Dismiss">&times;</button>
                    </div>
                @endif

                @if (session('success'))
                    <div data-flash-banner="success" class="mb-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-200 border-2 border-emerald-200 dark:border-emerald-900 px-4 sm:px-5 py-3.5 text-sm font-medium flex items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="break-words">{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-white font-bold text-base leading-none px-1 cursor-pointer shrink-0" aria-label="Dismiss">&times;</button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.documentElement;

            // Helper: close drawer only on mobile
            function closeMobileDrawer() {
                if (window.innerWidth < 1024) {
                    root.classList.remove('sidebar-open');
                }
            }

            // Toggle buttons (hamburger in topbar + chevron in sidenav header)
            document.querySelectorAll('[data-sidebar-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    root.classList.toggle('sidebar-open');
                    // Only persist the "open/collapsed" preference on desktop.
                    // On mobile the drawer is transient — never remember it open.
                    if (window.innerWidth >= 1024) {
                        const isOpen = root.classList.contains('sidebar-open');
                        try {
                            localStorage.setItem('sidebarState', isOpen ? 'open' : 'collapsed');
                        } catch (err) {}
                    }
                });
            });

            // Overlay click closes drawer
            const overlay = document.getElementById('sidebarOverlay');
            if (overlay) {
                overlay.addEventListener('click', function () {
                    root.classList.remove('sidebar-open');
                    if (window.innerWidth >= 1024) {
                        try { localStorage.setItem('sidebarState', 'collapsed'); } catch (err) {}
                    }
                });
            }

            // Auto-close drawer when a nav link is clicked (mobile only)
            document.querySelectorAll('#sidebar a').forEach(function (link) {
                link.addEventListener('click', closeMobileDrawer);
            });

            // Escape key closes drawer (mobile only)
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeMobileDrawer();
            });

            // Notification & Profile dropdowns
            const notifBtn = document.getElementById('notificationBtn');
            const notifDropdown = document.getElementById('notificationDropdown');
            const profileBtn = document.getElementById('profileMenuBtn');
            const profileDropdown = document.getElementById('profileMenuDropdown');

            if (notifBtn && notifDropdown) {
                notifBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (profileDropdown) profileDropdown.classList.add('hidden');
                    notifDropdown.classList.toggle('hidden');
                });
            }
            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (notifDropdown) notifDropdown.classList.add('hidden');
                    profileDropdown.classList.toggle('hidden');
                });
            }
            document.addEventListener('click', function (e) {
                if (notifDropdown && notifBtn && !notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
                    notifDropdown.classList.add('hidden');
                }
                if (profileDropdown && profileBtn && !profileDropdown.contains(e.target) && !profileBtn.contains(e.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (notifDropdown) notifDropdown.classList.add('hidden');
                    if (profileDropdown) profileDropdown.classList.add('hidden');
                }
            });

            requestAnimationFrame(function () {
                root.classList.add('sidebar-ready');
            });

            const themeBtn = document.getElementById('themeToggle');
            if (themeBtn) {
                themeBtn.addEventListener('click', function () {
                    window.dispatchEvent(new Event('themechange')); 
                    const isDark = document.documentElement.classList.toggle('dark');

                    fetch("{{ route('theme.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ theme: isDark ? 'dark' : 'light' })
                    }).catch(function () {});
                });
            }

            document.querySelectorAll('[data-set-view]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const mode = btn.dataset.setView;
                    if (mode === 'grid') {
                        document.documentElement.dataset.productView = 'grid';
                    } else {
                        delete document.documentElement.dataset.productView;
                    }
                    try { localStorage.setItem('productView', mode); } catch (e) {}
                });
            });
        });
    </script>

    <div id="notifToastContainer" class="fixed bottom-5 right-5 z-[95] flex flex-col gap-2.5 max-w-sm pointer-events-none"></div>

    @include('partials.row-action-menu')
    @include('partials.item-skus-modal')
    @include('partials.global-input-guards')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js').catch(function () {});
            });
        }
    </script>
    @stack('scripts')
</body>
</html>