@extends('layouts.default')

@section('title', 'Create Return')

@section('content')

    <div class="max-w-4xl mx-auto bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-8">
        
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Create Return to Supplier</h1>
            <a href="{{ route('supplier-returns.index') }}" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</a>
        </div>

        <form method="POST" action="{{ route('supplier-returns.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Supplier</label>
                    <select name="supplier_id" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm cursor-pointer">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reference Purchase Order (Optional)</label>
                    <select name="purchase_order_id" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm cursor-pointer">
                        <option value="">No Reference</option>
                        @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reason</label>
                    <select name="reason" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm cursor-pointer">
                        <option value="defective">Defective Items</option>
                        <option value="wrong_item">Wrong Item Received</option>
                        <option value="overstock">Overstock</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Source Location</label>
                    <select name="source_location" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm cursor-pointer">
                        <option value="warehouse" selected>Warehouse Stock</option>
                        <option value="defective_returns">Defective Customer Returns</option>
                        <option value="store">Store Shelf</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notes</label>
                    <input type="text" name="notes" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm">
                </div>
            </div>

            <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3">Items to Return</h3>
                <div id="itemsContainer" class="space-y-3">
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
                </div>
                <button type="button" id="addItemBtn" class="mt-4 text-sm font-semibold text-emerald-600 hover:text-emerald-700">+ Add Another Item</button>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <a href="{{ route('supplier-returns.index') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Cancel</a>
                <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Submit Return</button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
    let itemIndex = 1;
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
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
        container.appendChild(row);
        itemIndex++;
    });

    document.getElementById('itemsContainer').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) e.target.closest('.item-row').remove();
        }
    });
</script>
@endpush