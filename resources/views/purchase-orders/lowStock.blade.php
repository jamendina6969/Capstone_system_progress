@extends('layouts.default')

@section('title', 'Low Stock Suggestions')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Low Stock Suggestions</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Products that need restocking based on their reorder point</p>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
            ← Back to Purchase Orders
        </a>
    </div>

    @if(count($grouped) > 0)
        <div class="space-y-6">
            @foreach($grouped as $categoryId => $products)
                @php
                    $category = $products->first()->category ?? null;
                @endphp
                <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden">
                    <div class="px-8 py-4 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50 dark:bg-[#161B26]/50">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $category->name ?? 'Uncategorized' }}
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">{{ $products->count() }} product(s) need restocking</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs">
                                <tr>
                                    <th class="px-8 py-3">Product</th>
                                    <th class="px-8 py-3">SKU</th>
                                    <th class="px-8 py-3">Store Qty</th>
                                    <th class="px-8 py-3">Warehouse Qty</th>
                                    <th class="px-8 py-3">Reorder Point</th>
                                    <th class="px-8 py-3 text-right">Suggested Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                                @foreach($products as $product)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/30 transition-colors">
                                        <td class="px-8 py-4 font-medium text-gray-900 dark:text-white">
                                            {{ $product->product_name }}
                                        </td>
                                        <td class="px-8 py-4 font-mono text-xs text-gray-600 dark:text-slate-400">
                                            {{ $product->product_sku ?? '—' }}
                                        </td>
                                        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">{{ $product->store_quantity ?? 0 }}</td>
                                        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">{{ $product->warehouse_quantity ?? 0 }}</td>
                                        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">{{ $product->reorder_point ?? 10 }}</td>
                                        <td class="px-8 py-4 text-right">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/50">
                                                Order {{ $product->suggested_qty }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-12 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-50 dark:bg-emerald-950/50 mb-4">
                <svg class="h-8 w-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-gray-900 dark:text-white font-bold text-lg">All stocked up!</p>
            <p class="text-gray-500 dark:text-slate-400 font-medium">All products are adequately stocked. No restocking needed.</p>
        </div>
    @endif

@endsection