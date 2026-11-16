@forelse ($records as $record)
    @php
        // Use saved category snapshots first, then current product categories.
        $mainCats = [];
        $subCats = [];

        foreach ($record->transactionItems as $item) {
            if (!empty($item->main_category_name)) {
                $mainCats[] = $item->main_category_name;

                if (!empty($item->brand_category_name)) {
                    $subCats[] = $item->brand_category_name;
                }
            } elseif ($item->product && $item->product->category) {
                $cat = $item->product->category;

                if ($cat->parent) {
                    $mainCats[] = $cat->parent->name;
                    $subCats[] = $cat->name;
                } else {
                    $mainCats[] = $cat->name;
                }
            }
        }

        $mainCatsStr = implode(', ', array_unique(array_filter($mainCats))) ?: 'N/A';
        $subCatsStr = implode(', ', array_unique(array_filter($subCats))) ?: '—';

        // Determine the cashier name and if they are deleted
        $cashierName = null;
        $isDeleted = false;

        if ($record->cashier_name) {
            $cashierName = $record->cashier_name;
            // If the user relationship is null but we have a snapshot name, they are deleted
            $isDeleted = !$record->user; 
        } elseif ($record->user) {
            // Fallback for very old records where snapshot wasn't saved but user still exists
            $cashierName = $record->user->firstname . ' ' . $record->user->lastname;
        } else {
            // Fallback for very old records where snapshot wasn't saved AND user is deleted
            $cashierName = 'Unknown';
            $isDeleted = true;
        }
    @endphp

    <tr data-record-row="true"
        data-record-id="{{ $record->id }}"
        class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">

        <td class="px-6 py-4 text-gray-600 dark:text-slate-400 whitespace-nowrap">
            {{ $record->created_at->format('M d, Y') }}
        </td>

        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
            {{ $record->transaction_id }}
        </td>

        <td class="px-6 py-4 text-gray-900 dark:text-white font-medium max-w-[150px] truncate"
            title="{{ $mainCatsStr }}">
            {{ $mainCatsStr }}
        </td>

        <td class="px-6 py-4 text-gray-500 dark:text-slate-400 max-w-[150px] truncate"
            title="{{ $subCatsStr }}">
            {{ $subCatsStr }}
        </td>

        <td class="px-6 py-4">
            @if ((int) $record->transaction_type === 0)
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">
                    POS
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900">
                    Imported
                </span>
            @endif
        </td>

        <td class="px-6 py-4 text-gray-900 dark:text-slate-100 font-medium whitespace-nowrap">
            ₱{{ number_format($record->total_amount, 2) }}
        </td>

        {{-- RECORDED BY COLUMN --}}
        <td class="px-6 py-4 text-gray-600 dark:text-slate-400 whitespace-nowrap">
            <div class="flex flex-col">
                @if($isDeleted && $cashierName === 'Unknown')
                    <span class="font-medium text-gray-500 dark:text-slate-400 italic">Unknown</span>
                @else
                    <span class="font-medium text-gray-900 dark:text-slate-100">{{ $cashierName }}</span>
                @endif

                @if($isDeleted)
                    <span class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider mt-0.5">(Deleted User)</span>
                @endif
            </div>
        </td>

        <td class="px-6 py-4 text-right">
            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('receipt', $record->id) }}"
                   class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs cursor-pointer">
                    View
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="8" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500">
            No sales records found.
        </td>
    </tr>
@endforelse