@extends('layouts.default')

@section('title', 'Receive Purchase Order')

@section('content')
    @php $isWarehouse = auth()->user()->role === 'warehouse_staff'; $backRoute = $isWarehouse ? 'purchase-orders.deliveries' : 'purchase-orders.index'; @endphp

    <div class="max-w-4xl mx-auto bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-8">
        
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Goods Received Note (GRN)</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    PO #{{ $purchaseOrder->po_number }} — {{ $purchaseOrder->supplier->name }}
                </p>
            </div>
            <a href="{{ route($backRoute) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</a>
        </div>

        <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="rounded-xl bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-900 px-4 py-3 text-sm text-blue-800 dark:text-blue-300">
                Enter the actual quantity you received for each item. Serialized unit SKUs will be generated automatically for received stock. Any item received below the ordered quantity will be automatically logged to the <strong>Discrepancy Report</strong> and deducted from the supplier's balance.
            </div>

            <div id="liveShortageAlert" class="hidden rounded-xl bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-200 dark:border-rose-900 px-4 py-3 text-sm text-rose-800 dark:text-rose-300 flex items-start gap-2.5">
                <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <div>
                    <p class="font-bold">Delivery Shortage Detected (<span id="liveShortageTotalUnits">0</span> unit(s) missing)</p>
                    <p class="text-xs mt-0.5">Upon confirmation, this delivery will automatically appear in the <strong>Discrepancy Report</strong> and the missing value will be credited against the supplier ledger.</p>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($purchaseOrder->items as $item)
                    <div class="grn-item-card border-2 border-gray-200 dark:border-[#1e2536] rounded-xl p-4 grid grid-cols-1 sm:grid-cols-4 gap-4 items-center transition-colors">
                        <div class="sm:col-span-2">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item->product->product_name ?? 'Deleted Product' }}</p>
                            @unless($isWarehouse)<p class="text-xs text-gray-500 mt-0.5">Unit Cost: ₱{{ number_format($item->unit_cost, 2) }}</p>@endunless
                            <span class="grn-shortage-badge hidden mt-1.5 inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-900"></span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ordered Qty</label>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item->quantity }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Received Qty</label>
                            <input type="number" name="received_quantities[{{ $item->id }}]" value="{{ $item->quantity }}" min="0" max="{{ $item->quantity }}" required
                                   data-ordered="{{ $item->quantity }}"
                                   class="grn-received-input w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Put Away To</label>
                            <select name="destinations[{{ $item->id }}]" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                                <option value="warehouse" selected>Warehouse (Backroom)</option>
                                <option value="store">Store Shelf</option>
                            </select>
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
                <a href="{{ route($backRoute) }}" 
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.grn-received-input');
    const alertBox = document.getElementById('liveShortageAlert');
    const totalUnitsEl = document.getElementById('liveShortageTotalUnits');

    function updateShortages() {
        let totalShort = 0;
        inputs.forEach(input => {
            const ordered = parseInt(input.dataset.ordered || '0', 10);
            const received = Math.max(0, parseInt(input.value || '0', 10));
            const short = Math.max(0, ordered - received);
            const card = input.closest('.grn-item-card');
            const badge = card ? card.querySelector('.grn-shortage-badge') : null;

            if (short > 0) {
                totalShort += short;
                if (badge) {
                    badge.textContent = `Short by ${short} unit(s) — will be logged to Discrepancy Report`;
                    badge.classList.remove('hidden');
                }
                if (card) {
                    card.classList.add('border-rose-300', 'dark:border-rose-900/70', 'bg-rose-50/30', 'dark:bg-rose-950/10');
                }
            } else {
                if (badge) badge.classList.add('hidden');
                if (card) {
                    card.classList.remove('border-rose-300', 'dark:border-rose-900/70', 'bg-rose-50/30', 'dark:bg-rose-950/10');
                }
            }
        });

        if (totalUnitsEl) totalUnitsEl.textContent = totalShort;
        if (alertBox) {
            if (totalShort > 0) alertBox.classList.remove('hidden');
            else alertBox.classList.add('hidden');
        }
    }

    inputs.forEach(input => input.addEventListener('input', updateShortages));
    updateShortages();
});
</script>
@endpush
