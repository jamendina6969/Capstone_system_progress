@extends('layouts.default')

@section('title', 'Incoming Deliveries')

@section('content')
    <div class="space-y-6">

        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-200 dark:border-emerald-900 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-xl bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-200 dark:border-rose-900 px-4 py-3 text-sm text-rose-800 dark:text-rose-300">{{ session('error') }}</div>
        @endif

        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 lg:px-8 py-5 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Incoming Deliveries</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Approved Purchase Orders awaiting physical inspection and put-away. Count the cartons, then record a Goods Received Note.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase text-gray-500 dark:text-slate-400 bg-gray-50 dark:bg-[#161B26]">
                        <tr>
                            <th class="px-6 py-4">PO Number</th>
                            <th class="px-6 py-4">Supplier</th>
                            <th class="px-6 py-4">Items</th>
                            <th class="px-6 py-4">Units Ordered</th>
                            <th class="px-6 py-4">Ordered On</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                        @forelse($pending as $po)
                            <tr>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $po->po_number }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">{{ $po->supplier->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">{{ $po->items->count() }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">{{ $po->items->sum('quantity') }}</td>
                                <td class="px-6 py-4 text-gray-500 dark:text-slate-400">{{ $po->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('purchase-orders.receiveForm', $po) }}"
                                       class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all shadow-xs">
                                        Receive Order
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-slate-400">No deliveries are pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 lg:px-8 py-4 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Recently Received</h2>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                @forelse($recent as $po)
                    @php
                        $isWarehouse = auth()->user()->role === 'warehouse_staff';
                        $hasShortage = (float) ($po->discrepancy_amount ?? 0) > 0;
                    @endphp
                    <div class="px-6 lg:px-8 py-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $po->po_number }} <span class="font-normal text-gray-500 dark:text-slate-400">— {{ $po->supplier->name ?? '—' }}</span></span>
                            @if($hasShortage)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900">
                                    {{ $isWarehouse ? 'Discrepancy Logged' : 'Shortage: -₱' . number_format($po->discrepancy_amount, 2) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900">
                                    Complete
                                </span>
                            @endif
                        </div>
                        <span class="text-gray-500 dark:text-slate-400">{{ $po->received_quantity }} units • {{ optional($po->received_at)->format('M d, Y h:i A') }}</span>
                    </div>
                @empty
                    <div class="px-6 py-6 text-center text-gray-500 dark:text-slate-400 text-sm">No deliveries received yet.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
