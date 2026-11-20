@extends('layouts.default')

@section('title', 'Notifications Center')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications Center</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    Live action queues and personal event alerts tailored to your role.
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.readAll') }}" class="m-0">
                        @csrf
                        <button type="submit"
                                class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs cursor-pointer">
                            Mark All Read ({{ $unreadCount }})
                        </button>
                    </form>
                @endif
                <a href="{{ route('account.settings', ['tab' => 'preferences']) }}"
                   class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] text-xs font-semibold px-4 py-2 rounded-full transition-all shadow-xs">
                    Alert Preferences
                </a>
            </div>
        </div>

        {{-- Live Action Queues --}}
        @if (count($queues) > 0)
            <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-4">Action Required Now (Live Queues)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    @foreach ($queues as $q)
                        @php
                            $border = match($q['severity'] ?? 'info') {
                                'danger'  => 'border-rose-200 dark:border-rose-900/70 bg-rose-50/40 dark:bg-rose-950/20',
                                'warning' => 'border-amber-200 dark:border-amber-900/70 bg-amber-50/40 dark:bg-amber-950/20',
                                'success' => 'border-emerald-200 dark:border-emerald-900/70 bg-emerald-50/40 dark:bg-emerald-950/20',
                                default   => 'border-blue-200 dark:border-blue-900/70 bg-blue-50/40 dark:bg-blue-950/20',
                            };
                        @endphp
                        <a href="{{ $q['url'] }}" class="block rounded-2xl border-2 {{ $border }} p-4 hover:shadow-xs transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $q['title'] }}</p>
                                <span class="text-xs font-bold text-gray-600 dark:text-slate-300">Open →</span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-slate-400 mt-1">{{ $q['message'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Persistent Event Inbox --}}
        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('notifications.index', array_filter(['tab' => 'all', 'category' => $category])) }}"
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold {{ $tab !== 'unread' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-200/60 dark:hover:bg-[#1e2536]' }}">
                        All Alerts
                    </a>
                    <a href="{{ route('notifications.index', array_filter(['tab' => 'unread', 'category' => $category])) }}"
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold {{ $tab === 'unread' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-gray-600 dark:text-slate-400 hover:bg-gray-200/60 dark:hover:bg-[#1e2536]' }}">
                        Unread ({{ $unreadCount }})
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-1.5">
                    @php
                        $categories = [
                            ''          => 'All Categories',
                            'orders'    => 'Orders & POs',
                            'inventory' => 'Inventory',
                            'returns'   => 'Returns',
                            'security'  => 'Security',
                            'system'    => 'System',
                        ];
                    @endphp
                    @foreach ($categories as $catKey => $catLabel)
                        <a href="{{ route('notifications.index', array_filter(['tab' => $tab, 'category' => $catKey])) }}"
                           class="px-3 py-1 rounded-full text-[11px] font-semibold border transition-colors {{ $category === $catKey ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-[#161B26] text-gray-600 dark:text-slate-300 border-gray-200 dark:border-[#1e2536] hover:bg-gray-100 dark:hover:bg-[#1e2536]' }}">
                            {{ $catLabel }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                @forelse ($notifications as $item)
                    @php
                        $isUnread = is_null($item->read_at);
                        $dotColor = match($item->severity) {
                            'danger'  => 'bg-rose-500',
                            'warning' => 'bg-amber-500',
                            'success' => 'bg-emerald-500',
                            default   => 'bg-blue-500',
                        };
                    @endphp
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 {{ $isUnread ? 'bg-blue-50/35 dark:bg-blue-950/15' : '' }}">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0 {{ $isUnread ? $dotColor : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $item->title }}</p>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-gray-100 dark:bg-[#161B26] text-gray-500 dark:text-slate-400">
                                        {{ $item->category }}
                                    </span>
                                    @if ($isUnread)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">Unread</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600 dark:text-slate-400 mt-1">{{ $item->message }}</p>
                                <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1">{{ $item->created_at?->format('M d, Y h:i A') }} ({{ $item->created_at?->diffForHumans() }})</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('notifications.read', $item) }}"
                               class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] text-xs font-semibold px-4 py-1.5 rounded-full transition-all">
                                {{ $item->action_url ? 'Open' : ($isUnread ? 'Mark Read' : 'Read') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-14 text-center text-sm text-gray-400 dark:text-slate-500">
                        No notifications match the selected filter.
                    </div>
                @endforelse
            </div>

            @if ($notifications->hasPages())
                <div class="px-6 py-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
