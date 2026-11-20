@forelse ($products as $product)
    @php $canTransfer = in_array(auth()->user()->role, ['admin', 'manager']); @endphp
    <div data-record-row="true" class="border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl overflow-hidden bg-white dark:bg-[#161B26] flex flex-col shadow-xs">
        <div class="cursor-pointer" onclick="openProductDetail({{ $product->id }})">
            <div class="w-full h-32 bg-gray-100 dark:bg-[#10141D] flex items-center justify-center text-gray-300 dark:text-slate-600 overflow-hidden">
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->product_name }}"
                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                         class="w-full h-32 object-cover" loading="lazy">
                    <svg class="hidden h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                @else
                    <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                @endif
            </div>
            <div class="p-3">
                <p class="font-bold text-gray-900 dark:text-white text-xs line-clamp-1">{{ $product->product_name }}</p>
                <p class="text-[11px] font-mono text-gray-400 dark:text-slate-500 mt-0.5">{{ $product->product_sku ?? '—' }}</p>
                <p class="text-[11px] text-gray-400 dark:text-slate-400 mt-0.5 truncate">{{ $product->category->name ?? '—' }}</p>
                <p class="text-[11px] text-gray-500 dark:text-slate-400 mt-1 font-medium">Store {{ $product->store_quantity ?? 0 }} · WH {{ $product->warehouse_quantity ?? 0 }}</p>
                <div class="mt-2 scale-90 origin-left">@include('partials.stockBadge', ['product' => $product])</div>
            </div>
        </div>

        <div class="mt-auto p-3 pt-0">
            <div class="flex gap-1.5 text-xs font-medium">
                <button type="button" onclick="openItemSkusModal({{ $product->id }})" class="bg-white border-2 border-sky-600 text-sky-700 hover:bg-sky-600 hover:text-white dark:bg-[#10141D] dark:border-sky-600/80 dark:text-sky-400 dark:hover:bg-sky-600 dark:hover:text-white text-[10px] font-semibold px-2.5 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer text-center">SKUs</button>
                <button type="button" onclick="toggleInventoryAction({{ $product->id }}, 'card-restock')" class="flex-1 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-[10px] font-semibold px-2 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer text-center">Restock</button>
                @if ($canTransfer)
                    <button type="button" onclick="toggleInventoryAction({{ $product->id }}, 'card-transfer')" class="flex-1 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-[10px] font-semibold px-2 py-1.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer text-center">Transfer</button>
                @endif
            </div>
            <div id="card-restock-{{ $product->id }}" class="hidden mt-2 pt-2 border-t-2 border-gray-100 dark:border-[#1e2536]">
                @include('partials.restockForm', ['product' => $product])
            </div>
            @if ($canTransfer)
                <div id="card-transfer-{{ $product->id }}" class="hidden mt-2 pt-2 border-t-2 border-gray-100 dark:border-[#1e2536]">
                    @include('partials.transferForm', ['product' => $product])
                </div>
            @endif
        </div>
    </div>
@empty
    <p class="col-span-full text-center text-gray-400 dark:text-slate-500 py-12 font-medium">No products found.</p>
@endforelse