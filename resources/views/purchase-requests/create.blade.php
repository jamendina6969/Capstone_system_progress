@extends('layouts.default')

@section('title', 'Create Purchase Request')

@section('content')

    <div class="max-w-4xl mx-auto bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-8">
        
        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Create Purchase Request</h1>
                <p class="text-sm text-gray-500 mt-1">Submit a request for items that need to be purchased</p>
            </div>
            <a href="{{ route('purchase-requests.index') }}" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</a>
        </div>

        <form method="POST" action="{{ route('purchase-requests.store') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reason for Request</label>
                <textarea name="reason" required rows="3" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm" placeholder="E.g. Current stock running low, upcoming big order..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Preferred Supplier (Optional)</label>
                <select name="supplier_id" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm cursor-pointer">
                    <option value="">Any Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="border-t-2 border-gray-200 dark:border-[#1e2536] pt-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-gray-900 dark:text-white">Items Needed</h3>
                    <button type="button" id="addItemBtn" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">+ Add Item</button>
                </div>
                <div id="itemsContainer" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 item-row">
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Product</label>
                            <select name="items[0][product_id]" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm cursor-pointer">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Qty</label>
                            <input type="number" name="items[0][quantity]" required min="1" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <a href="{{ route('purchase-requests.index') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Cancel</a>
                <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">
                    Submit Request
                </button>
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
        row.className = 'grid grid-cols-1 sm:grid-cols-3 gap-3 item-row';
        row.innerHTML = `
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Product</label>
                <select name="items[${itemIndex}][product_id]" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm cursor-pointer">
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Qty</label>
                <input type="number" name="items[${itemIndex}][quantity]" required min="1" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-3 py-2 text-sm">
            </div>
        `;
        container.appendChild(row);
        itemIndex++;
    });
</script>
@endpush