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
            <div class="inline-flex justify-end items-center" data-row-actions>
                <button type="button" onclick="openRowActionMenu(event, this)"
                        class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-white dark:bg-[#161B26] border-2 border-slate-300 dark:border-[#1e2536] text-slate-700 dark:text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-800 dark:hover:bg-white dark:hover:text-slate-900 transition-all shadow-xs cursor-pointer"
                        aria-label="Supplier Actions" title="Actions">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </button>
                <div class="row-action-items hidden">
                    <button type="button" data-tone="default" data-label="View Details"
                            onclick="openSupplierDetailModal({{ json_encode($supplier) }})">View Details</button>
                    <a href="{{ route('suppliers.ledger', $supplier) }}" data-tone="blue" data-label="Supplier Ledger">Supplier Ledger</a>
                    <button type="button" data-tone="emerald" data-label="Record Payment"
                            onclick="togglePaymentModal({{ $supplier->id }}, {{ Js::from($supplier->name) }}, {{ $outstandingBalance }})"
                            {{ $outstandingBalance <= 0 ? 'disabled' : '' }}>Record Payment</button>
                    <button type="button" data-tone="amber" data-label="Rate Supplier"
                            onclick="toggleRateModal({{ $supplier->id }}, {{ Js::from($supplier->name) }})">Rate Supplier</button>
                    <button type="button" data-tone="default" data-label="Edit Supplier"
                            data-supplier="{{ json_encode($supplier) }}"
                            onclick="openEditModal(this)">Edit Supplier</button>
                    <button type="button" data-tone="rose" data-label="Delete Supplier"
                            onclick="confirmDelete({{ $supplier->id }}, {{ Js::from($supplier->name) }})">Delete Supplier</button>
                </div>
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