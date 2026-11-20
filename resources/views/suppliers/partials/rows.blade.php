@forelse ($suppliers as $supplier)
    @php
        $balance = (float) $supplier->balance;
        $outstandingBalance = max($balance, 0);
    @endphp

    <tr data-record-row="true"
        onclick="openSupplierDetailModal({{ json_encode($supplier) }})"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer">
        <td class="px-8 py-4 font-medium text-gray-900 dark:text-white">
            {{ $supplier->name }}
            <p class="text-xs text-gray-500 mt-0.5 truncate max-w-[200px]">{{ $supplier->address }}</p>
            <div class="flex flex-wrap items-center gap-1.5 mt-1.5" onclick="event.stopPropagation()">
                @php
                    $acc = $supplier->delivery_accuracy;
                    $discCount = $supplier->discrepancy_count;
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $acc >= 90 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900' }}">
                    {{ $acc }}% Accuracy
                </span>
                @if($discCount > 0)
                    <a href="{{ route('suppliers.discrepancy', ['supplier_id' => $supplier->id]) }}"
                       class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-600 hover:text-white dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900 transition-colors">
                        {{ $discCount }} {{ Str::plural('Discrepancy', $discCount) }}
                    </a>
                @endif
            </div>
        </td>

        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            {{ $supplier->contact_person ?? '—' }}
        </td>

        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">
            <p class="truncate max-w-[150px]">{{ $supplier->email ?? '—' }}</p>
            <p class="text-xs">{{ $supplier->phone ?? '—' }}</p>
        </td>

        {{-- OUTSTANDING BALANCE --}}
        <td class="px-8 py-4 font-bold">
            @if ($balance > 0)
                <span class="text-rose-600 dark:text-rose-400">₱{{ number_format($balance, 2) }}</span>
            @elseif ($balance < 0)
                <span class="text-emerald-600 dark:text-emerald-400">₱{{ number_format(abs($balance), 2) }}</span>
            @else
                <span class="text-gray-900 dark:text-white">₱0.00</span>
            @endif
        </td>

        <td class="px-8 py-4 text-right" onclick="event.stopPropagation()">
            <div class="flex justify-end gap-2 items-center">
                <a href="{{ route('suppliers.ledger', $supplier) }}" class="bg-white border-2 border-blue-600 text-blue-700 hover:bg-blue-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-blue-600/80 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all duration-300 shadow-xs">
                    Ledger
                </a>

                <button
                    type="button"
                    onclick="togglePaymentModal({{ $supplier->id }}, {{ Js::from($supplier->name) }}, {{ $outstandingBalance }})"
                    {{ $outstandingBalance <= 0 ? 'disabled' : '' }}
                    class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all duration-300 shadow-xs disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-emerald-700 dark:disabled:hover:bg-[#10141D] dark:disabled:hover:text-emerald-400"
                >
                    Payment
                </button>

                <button type="button" onclick="openActionMenu(event, {{ $supplier->id }}, {{ Js::from($supplier->name) }}, {{ $supplier->average_rating }}, {{ Js::from($supplier) }})" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold w-[38px] h-[38px] flex items-center justify-center rounded-full transition-all duration-300 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="5" class="px-8 py-12 text-center text-gray-400 font-medium">
            No suppliers found.
        </td>
    </tr>
@endforelse