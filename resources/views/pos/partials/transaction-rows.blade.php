@forelse ($transactions as $item)

    <tr
        data-record-row="true"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer"
        onclick="window.location.href='{{ $item->record_type === 'sale' ? route('receipt', $item->id) : route('transaction.payment.receipt', $item->id) }}'"
    >

        <!-- Date -->
        <td class="px-6 py-4 text-gray-600 dark:text-slate-400 whitespace-nowrap">
            {{ $item->created_at->format('M d, Y') }}
        </td>

        <!-- Reference ID -->
        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
            @if($item->record_type === 'sale')
                {{ $item->transaction_id }}
            @else
                PAY-{{ str_pad($item->id, 6, '0', STR_PAD_LEFT) }}
            @endif
        </td>

        <!-- Entity / Description -->
        <td class="px-6 py-4 text-gray-900 dark:text-white max-w-[250px]">
            @if($item->record_type === 'sale')
                <div class="flex flex-col">
                    <div class="flex items-center gap-1">
                        <span class="text-gray-500 text-xs">
                            {{ (int) $item->transaction_type === 2 ? 'Processed by:' : 'Cashier:' }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $item->cashier_name ?? ($item->user ? $item->user->firstname . ' ' . $item->user->lastname : 'Unknown') }}
                        </span>
                    </div>
                    @if(!$item->user)
                        <span class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider mt-0.5">(Deleted User)</span>
                    @endif
                </div>
            @else
                <span class="font-bold">{{ $item->supplier->name ?? 'Supplier' }}</span>
                <p class="text-xs text-gray-500 truncate mt-0.5" title="{{ $item->description }}">
                    {{ $item->description }}
                </p>
            @endif
        </td>

        <!-- Source / Type -->
        <td class="px-6 py-4">
            @if($item->record_type === 'sale')
                @if((int) $item->transaction_type === 0)
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">
                        POS
                    </span>
                @elseif((int) $item->transaction_type === 2)
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
                        Refund
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900">
                        Imported
                    </span>
                @endif
            @else
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
                    Supplier Payment
                </span>
            @endif
        </td>

        <!-- Total Amount -->
        <td class="px-6 py-4 font-medium whitespace-nowrap {{ $item->record_type === 'sale' ? 'text-gray-900 dark:text-slate-100' : 'text-rose-600 dark:text-rose-400' }}">
            @if($item->record_type === 'sale')
                @if((float) $item->total_amount < 0)
                    <span class="text-rose-600 dark:text-rose-400">-₱{{ number_format(abs((float) $item->total_amount), 2) }}</span>
                @else
                    ₱{{ number_format((float) $item->total_amount, 2) }}
                @endif
            @else
                - ₱{{ number_format((float) $item->amount, 2) }}
            @endif
        </td>

        <!-- Recorded By -->
        <td class="px-6 py-4 text-gray-600 dark:text-slate-400 whitespace-nowrap">
            @if($item->record_type === 'sale')
                <div class="flex flex-col">
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $item->cashier_name ?? ($item->user ? $item->user->firstname . ' ' . $item->user->lastname : 'Unknown') }}
                    </span>
                    @if(!$item->user)
                        <span class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider mt-0.5">(Deleted User)</span>
                    @endif
                </div>
            @else
                System Admin
            @endif
        </td>

        <!-- Actions -->
        <td class="px-6 py-4 text-right" onclick="event.stopPropagation()">
            <div class="flex items-center justify-end gap-2">

                @if($item->record_type === 'sale')

                    <a
                        href="{{ route('receipt', $item->id) }}"
                        class="inline-flex items-center gap-1.5 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all duration-300 shadow-xs cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>View</span>
                    </a>

                    {{-- Return button — only for actual sales (type 0 or 1) --}}
                    @if(in_array((int) $item->transaction_type, [0, 1], true) && auth()->user()->role !== 'staff')
                        <button type="button"
                            data-action="{{ route('transactions.return', $item->id) }}"
                            data-items-url="{{ route('transactions.return.items', $item->id) }}"
                            data-receipt="{{ $item->transaction_id }}"
                            onclick="openReturnModal(this)"
                            class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all shadow-xs cursor-pointer"
                        >
                            Return
                        </button>
                    @endif

                @else

                    <a
                        href="{{ route('transaction.payment.receipt', $item->id) }}"
                        class="bg-white border-2 border-rose-400 text-rose-600 hover:bg-rose-500 hover:text-white dark:bg-[#10141D] dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs cursor-pointer"
                    >
                        Voucher
                    </a>

                @endif

            </div>
        </td>

    </tr>

@empty

    <tr>
        <td colspan="7" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500">
            No transactions found.
        </td>
    </tr>

@endforelse