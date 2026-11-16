@extends('layouts.default')

@section('title', 'Create Purchase Order')

@section('content')

    <div class="max-w-4xl mx-auto bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-8">
        
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Create Purchase Order</h1>
                @if($purchaseRequest)
                    <p class="text-sm text-blue-600 dark:text-blue-400 mt-1 font-semibold">
                        Converting from Purchase Request: <span class="font-mono">{{ $purchaseRequest->pr_number }}</span>
                    </p>
                @endif
            </div>
            <a href="{{ route('purchase-orders.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</a>
        </div>

        <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6" id="poForm">
            @csrf
            
            <!-- Hidden input to track the PR if converted -->
            @if($purchaseRequest)
                <input type="hidden" name="from_pr" value="{{ $purchaseRequest->id }}">
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Supplier</label>
                    <select name="supplier_id" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm cursor-pointer">
                        <option value="">Select a Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ ($purchaseRequest && $purchaseRequest->supplier_id == $supplier->id) ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Expected Delivery Date</label>
                    <input type="date" name="expected_delivery_date" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm">
                </div>
            </div>

            <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3">Order Items</h3>
                <div id="itemsContainer" class="space-y-3">
                    @if($purchaseRequest && $purchaseRequest->items->count() > 0)
                        @foreach($purchaseRequest->items as $index => $prItem)
                            <div class="flex gap-3 items-end item-row">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500 mb-1">Product</label>
                                    <select name="items[{{ $index }}][product_id]" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm cursor-pointer">
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ $product->id == $prItem->product_id ? 'selected' : '' }}>
                                                {{ $product->product_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs text-gray-500 mb-1">Qty</label>
                                    <input type="number" name="items[{{ $index }}][quantity]" required min="1" value="{{ $prItem->quantity }}" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
                                </div>
                                <div class="w-32">
                                    <label class="block text-xs text-gray-500 mb-1">Unit Cost</label>
                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" required min="0" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
                                </div>
                                <button type="button" class="remove-item bg-rose-100 text-rose-600 p-2 rounded-xl hover:bg-rose-200 mb-0.5">&times;</button>
                            </div>
                        @endforeach
                    @else
                        {{-- Default empty row --}}
                        <div class="flex gap-3 items-end item-row">
                            <div class="flex-1">
                                <label class="block text-xs text-gray-500 mb-1">Product</label>
                                <select name="items[0][product_id]" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm cursor-pointer">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-24">
                                <label class="block text-xs text-gray-500 mb-1">Qty</label>
                                <input type="number" name="items[0][quantity]" required min="1" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
                            </div>
                            <div class="w-32">
                                <label class="block text-xs text-gray-500 mb-1">Unit Cost</label>
                                <input type="number" step="0.01" name="items[0][unit_cost]" required min="0" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
                            </div>
                            <button type="button" class="remove-item bg-rose-100 text-rose-600 p-2 rounded-xl hover:bg-rose-200 mb-0.5">&times;</button>
                        </div>
                    @endif
                </div>
                <button type="button" id="addItemBtn" class="mt-4 text-sm font-semibold text-emerald-600 hover:text-emerald-700">+ Add Another Item</button>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notes / Instructions</label>
                <textarea name="notes" rows="2" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm" placeholder="E.g. Please deliver to the back entrance...">{{ $purchaseRequest ? $purchaseRequest->reason : '' }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <a href="{{ route('purchase-orders.index') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Cancel</a>
                <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">
                    {{ $purchaseRequest ? 'Convert & Create PO' : 'Create Order' }}
                </button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Start index based on whether we pre-filled from a PR
        let itemIndex = {{ $purchaseRequest ? $purchaseRequest->items->count() : 1 }};
        const itemsContainer = document.getElementById('itemsContainer');
        const addItemBtn = document.getElementById('addItemBtn');

        addItemBtn.addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'flex gap-3 items-end item-row';
            row.innerHTML = `
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1">Product</label>
                    <select name="items[${itemIndex}][product_id]" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm cursor-pointer">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <label class="block text-xs text-gray-500 mb-1">Qty</label>
                    <input type="number" name="items[${itemIndex}][quantity]" required min="1" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
                </div>
                <div class="w-32">
                    <label class="block text-xs text-gray-500 mb-1">Unit Cost</label>
                    <input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" required min="0" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
                </div>
                <button type="button" class="remove-item bg-rose-100 text-rose-600 p-2 rounded-xl hover:bg-rose-200 mb-0.5">&times;</button>
            `;
            itemsContainer.appendChild(row);
            itemIndex++;
        });

        itemsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                const rows = itemsContainer.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    e.target.closest('.item-row').remove();
                }
            }
        });
    });
</script>
@endpush