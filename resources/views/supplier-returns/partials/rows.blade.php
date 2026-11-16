@forelse($returns as $return)
    <tr data-record-row="true" class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">
        <td class="px-8 py-4 font-bold text-gray-900 dark:text-white">{{ $return->return_number }}</td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">{{ $return->supplier->name }}</td>
        <td class="px-8 py-4 text-gray-600 dark:text-slate-400">{{ ucwords(str_replace('_', ' ', $return->reason)) }}</td>
        <td class="px-8 py-4 font-bold">₱{{ number_format($return->total_amount, 2) }}</td>
        <td class="px-8 py-4">
            @if($return->status === 'pending')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
            @elseif($return->status === 'completed')
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">Completed</span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">Cancelled</span>
            @endif
        </td>
        <td class="px-8 py-4 text-right">
            <div class="flex items-center justify-end gap-2">
                {{-- VIEW RETURN DETAILS BUTTON --}}
                <button type="button"
                        data-return="{{ json_encode($return->load('items.product.category.parent', 'items.product.category', 'supplier')) }}"
                        onclick="openViewReturnModal(this.dataset.return)"
                        class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-full transition-all duration-300 shadow-xs">View</button>

                @if($return->status === 'pending')
                    {{-- Process Form --}}
                    <form action="{{ route('supplier-returns.approve', $return) }}" method="POST" id="process-form-{{ $return->id }}" class="m-0">
                        @csrf
                        <button type="button" onclick="openActionModal('process', 'process-form-{{ $return->id }}')" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-full transition-all shadow-xs">Process</button>
                    </form>
                    
                    {{-- Cancel Form --}}
                    <form action="{{ route('supplier-returns.reject', $return) }}" method="POST" id="cancel-form-{{ $return->id }}" class="m-0">
                        @csrf
                        <button type="button" onclick="openActionModal('cancel', 'cancel-form-{{ $return->id }}')" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-full transition-all shadow-xs">Cancel</button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr data-empty-row="true">
        <td colspan="6" class="px-8 py-12 text-center text-gray-400 font-medium">No returns found.</td>
    </tr>
@endforelse