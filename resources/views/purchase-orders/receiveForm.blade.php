@extends('layouts.default')

@section('title', 'Receive Purchase Order')

@section('content')

    <div class="max-w-4xl mx-auto bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-8">
        
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Goods Received Note (GRN)</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    PO #{{ $purchaseOrder->po_number }} — {{ $purchaseOrder->supplier->name }}
                </p>
            </div>
            <a href="{{ route('purchase-orders.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</a>
        </div>

        <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="rounded-xl bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-900 px-4 py-3 text-sm text-blue-800 dark:text-blue-300">
                Enter the actual quantity you received for each item. If you received less than ordered, the discrepancy will be automatically deducted from the supplier's balance.
            </div>

            <div class="space-y-3">
                @foreach($purchaseOrder->items as $item)
                    <div class="border-2 border-gray-200 dark:border-[#1e2536] rounded-xl p-4 grid grid-cols-1 sm:grid-cols-4 gap-4 items-center">
                        <div class="sm:col-span-2">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item->product->product_name ?? 'Deleted Product' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Unit Cost: ₱{{ number_format($item->unit_cost, 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ordered Qty</label>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item->quantity }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Received Qty</label>
                            <input type="number" name="received_quantities[{{ $item->id }}]" value="{{ $item->quantity }}" min="0" max="{{ $item->quantity }}" required
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        </div>
                    </div>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">GRN Notes</label>
                <textarea name="grn_notes" rows="3" 
                          class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400"
                          placeholder="E.g. 2 items damaged, 1 item missing, delivered on time..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <a href="{{ route('purchase-orders.index') }}" 
                   class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">
                    Confirm Receipt
                </button>
            </div>
        </form>
    </div>

@endsection