@forelse ($grouped as $batchId => $batch)
    @php
        $first = $batch->first_movement;
        $products = $batch->products;
    @endphp

    <div data-record-row="true" class="bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl shadow-xs overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50 dark:bg-[#10141D]/50">
            <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ ucwords(str_replace('_', ' ', $first->type)) }} Batch
                </p>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ $first->created_at->format('M d, Y - h:i A') }}</p>
            </div>

            <div class="text-xs text-gray-600 dark:text-slate-400 text-right">
                <p>Requested by: <span class="font-semibold text-gray-900 dark:text-white">{{ $first->requester_name ?? ($first->requester ? $first->requester->firstname . ' ' . $first->requester->lastname : 'Deleted User') }}</span></p>
                <p>Approved by: <span class="font-semibold text-gray-900 dark:text-white">{{ $first->approver_name ?? ($first->approver ? $first->approver->firstname . ' ' . $first->approver->lastname : '—') }}</span></p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-white dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs border-b border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Previous Qty</th>
                        <th class="px-6 py-3">Added / Changed</th>
                        <th class="px-6 py-3">New Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @foreach ($products as $movement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/30 transition-colors cursor-pointer"
                            onclick="openStockLogModal(this)"
                            data-product="{{ $movement->product->product_name ?? 'Deleted Product' }}"
                            data-category="{{ $movement->product->category->name ?? '—' }}"
                            data-type="{{ ucwords(str_replace('_', ' ', $first->type)) }}"
                            data-date="{{ $first->created_at->format('M d, Y - h:i A') }}"
                            data-prev="{{ $movement->quantity_before ?? '—' }}"
                            data-qty="{{ $movement->quantity }}"
                            data-new="{{ $movement->quantity_after ?? '—' }}"
                            data-requester="{{ $first->requester_name ?? ($first->requester ? $first->requester->firstname . ' ' . $first->requester->lastname : 'Deleted User') }}"
                            data-approver="{{ $first->approver_name ?? ($first->approver ? $first->approver->firstname . ' ' . $first->approver->lastname : '—') }}"
                            data-note="{{ $movement->note ?: ($first->note ?: 'No notes recorded.') }}">
                            <td class="px-6 py-3.5 font-medium text-gray-900 dark:text-white">
                                {{ $movement->product->product_name ?? 'Deleted Product' }}
                            </td>
                            <td class="px-6 py-3.5 text-gray-600 dark:text-slate-400">
                                {{ $movement->product->category->name ?? '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-gray-600 dark:text-slate-400">{{ $movement->quantity_before ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-emerald-600 dark:text-emerald-400 font-semibold">+{{ $movement->quantity }}</td>
                            <td class="px-6 py-3.5 text-gray-900 dark:text-white font-bold">{{ $movement->quantity_after ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div data-empty-row="true" class="py-12 text-center text-gray-400 dark:text-slate-500 font-medium">
        No stock movement logs found.
    </div>
@endforelse