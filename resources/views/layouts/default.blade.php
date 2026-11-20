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
        // Check and apply product view mode immediately to prevent layout shifts
        try {
            if (localStorage.getItem('productView') === 'grid') {
                document.documentElement.dataset.productView = 'grid';
            }
        } catch (e) {}

        // Initialize sidebar state from localStorage or default viewport rules
        (function () {
            try {
                var savedState = localStorage.getItem('sidebarState');
                var isDesktop = window.innerWidth >= 1024;
                if (isDesktop && (savedState === 'open' || !savedState)) {
                    document.documentElement.classList.add('sidebar-open');
                } else {
                    // Mobile: never auto-open the drawer
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

    <div class="flex h-screen overflow-hidden">

        @include('layouts.partials.sidenav')

        <!-- Main content container area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50 dark:bg-[#161B26]">

            <!-- Enterprise Topbar -->
            <header class="h-16 bg-white dark:bg-[#10141D] border-b border-slate-200/80 dark:border-[#1e2536] px-3 sm:px-4 lg:px-6 flex items-center justify-between relative z-50 shadow-2xs">
                
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
                    <!-- Quick Refresh -->
                    <button onclick="window.location.reload();" class="p-2 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#1e2536]/60 rounded-lg transition-colors cursor-pointer" title="Refresh Data">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>

                    <!-- Notification Bell & Dropdown -->
                    <div class="relative">
                        @php
                            // PERFORMANCE FIX: Cache the full payload for 5 minutes
                            // Using the exact same cache key as the sidenav
                            $notifData = \Illuminate\Support\Facades\Cache::remember('topbar_notifications_' . auth()->id(), 300, function () {
                                if (!in_array(auth()->user()->role, ['admin', 'manager'])) {
                                    return ['prCount' => 0, 'approvalCount' => 0, 'recentPRs' => collect(), 'recentApprovals' => collect()];
                                }
                                
                                return [
                                    'prCount' => \App\Models\PurchaseRequest::where('status', 'pending')->count(),
                                    'approvalCount' => \App\Models\StockMovement::where('status', 'pending')->count(),
                                    'recentPRs' => \App\Models\PurchaseRequest::with('user')->where('status', 'pending')->orderBy('created_at', 'desc')->take(3)->get(),
                                    'recentApprovals' => \App\Models\StockMovement::with('product', 'requester')->where('status', 'pending')->orderBy('created_at', 'desc')->take(3)->get(),
                                ];
                            });

                            $pendingPRCount = $notifData['prCount'] ?? 0;
                            $pendingApprovalCount = $notifData['approvalCount'] ?? 0;
                            $totalNotifications = $pendingPRCount + $pendingApprovalCount;
                        @endphp

                        <button id="notificationBtn" class="relative p-2 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#1e2536]/60 rounded-lg transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            @if($totalNotifications > 0)
                                <span class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-rose-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center px-1">
                                    {{ $totalNotifications }}
                                </span>
                            @else
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-blue-600 rounded-full"></span>
                            @endif
                        </button>

                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 max-w-[calc(100vw-1.5rem)] bg-white dark:bg-[#10141D] border border-slate-200 dark:border-[#1e2536] rounded-xl shadow-xl py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 dark:border-[#1e2536] flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Notifications</span>
                                @if($totalNotifications > 0)
                                    <span class="text-[10px] bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300 px-2 py-0.5 rounded-full font-semibold">{{ $totalNotifications }} New</span>
                                @else
                                    <span class="text-[10px] bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300 px-2 py-0.5 rounded-full font-semibold">0 New</span>
                                @endif
                            </div>
                            
                            <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-[#1e2536]">
                                {{-- Pending Purchase Requests --}}
                                @if($pendingPRCount > 0 && in_array(auth()->user()->role, ['admin', 'manager']))
                                    @foreach($notifData['recentPRs'] ?? collect() as $pr)
                                        <a href="{{ route('purchase-requests.index', ['id' => $pr->id]) }}" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">New Purchase Request</p>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 pl-4">
                                                {{ $pr->user ? ($pr->user->full_name ?? $pr->user->firstname . ' ' . $pr->user->lastname) : 'Deleted User' }} 
                                                submitted {{ $pr->pr_number }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 mt-1 pl-4">{{ $pr->created_at->diffForHumans() }}</p>
                                        </a>
                                    @endforeach
                                @endif

                                {{-- Pending Inventory Approvals --}}
                                @if($pendingApprovalCount > 0 && in_array(auth()->user()->role, ['admin', 'manager']))
                                    @foreach($notifData['recentApprovals'] ?? collect() as $movement)
                                        <a href="{{ route('inventory.approvals', ['id' => $movement->id]) }}" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Pending Approval</p>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 pl-4">
                                                {{ $movement->requester_name ?? ($movement->requester ? $movement->requester->firstname . ' ' . $movement->requester->lastname : 'Deleted User') }} 
                                                requested restock of {{ $movement->product->product_name ?? 'Item' }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 mt-1 pl-4">{{ $movement->created_at->diffForHumans() }}</p>
                                        </a>
                                    @endforeach
                                @endif

                                {{-- Empty State --}}
                                @if($totalNotifications == 0)
                                    <div class="px-4 py-6 text-center text-xs text-slate-400">
                                        No pending notifications
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="h-5 w-px bg-slate-200 dark:bg-[#1e2536] mx-1"></div>

                    @php
                        $resolvedRole = Auth::user()->email === 'admin@example.com' ? 'admin' : Auth::user()->role;
                        $roleWords = explode('_', $resolvedRole);
                        $roleAcronym = count($roleWords) > 1 
                            ? strtoupper(substr($roleWords[0], 0, 1) . substr($roleWords[1], 0, 1))
                            : strtoupper(substr($resolvedRole, 0, 2));
                    @endphp

                    <!-- User Info & Role Adaptation for Light/Dark Mode -->
                    <div class="flex items-center space-x-3">
                        <div class="text-right hidden sm:block">
                            <span class="block text-xs font-semibold text-slate-800 dark:text-slate-100">{{ Auth::user()->name }}</span>
                            <span class="block text-[10px] text-slate-900 dark:text-white font-bold uppercase tracking-wider">
                                {{ ucwords(str_replace('_', ' ', $resolvedRole)) }}
                            </span>
                            <span class="block text-[9px] text-slate-400 dark:text-slate-500 font-medium">
                                @php
                                    $friendlySubtitles = [
                                        'admin' => 'System Administrator',
                                        'manager' => 'Operations Manager',
                                        'warehouse_staff' => 'Inventory Specialist',
                                        'staff' => 'System Operator'
                                    ];
                                    echo $friendlySubtitles[$resolvedRole] ?? 'Authorized User';
                                @endphp
                            </span>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs border-2 border-slate-200 dark:border-[#1e2536]">
                            {{ $roleAcronym }}
                        </div>
                    </div>
                </div>

            </header>

            <!-- Page content background area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-100 dark:bg-[#161B26] p-3 sm:p-6 lg:p-8">
                
                @if (session('error'))
                    <div class="mb-6 rounded-lg bg-red-50 text-red-800 border border-red-200 px-4 py-3 text-sm flex items-center shadow-2xs">
                        <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 px-4 py-3 text-sm flex items-center shadow-2xs">
                        <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ session('success') }}
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

            // Notification dropdown
            const notifBtn = document.getElementById('notificationBtn');
            const notifDropdown = document.getElementById('notificationDropdown');
            if (notifBtn && notifDropdown) {
                notifBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    notifDropdown.classList.toggle('hidden');
                });
                document.addEventListener('click', function (e) {
                    if (!notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
                        notifDropdown.classList.add('hidden');
                    }
                });
            }

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