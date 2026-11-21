<!-- Product Detail Modal (shared, populated via JS) -->
<div id="productDetailModal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="pdm-name"
     onclick="if(event.target === this) closeProductDetail()"
     class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-900/70 backdrop-blur-xs p-3 sm:p-4">
    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl w-full max-w-lg max-h-[92vh] sm:max-h-[90vh] flex flex-col overflow-hidden" onclick="event.stopPropagation()">

        {{-- Sticky Header --}}
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b-2 border-gray-100 dark:border-[#1e2536] shrink-0">
            <div class="flex items-center gap-2.5 min-w-0 pr-2">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white truncate" id="pdm-name">Product Details</h3>
                <span id="pdm-sku" class="shrink-0 font-mono text-[11px] font-bold px-2 py-0.5 rounded-md bg-gray-100 dark:bg-[#161B26] text-gray-700 dark:text-slate-300 border border-gray-200 dark:border-[#1e2536]"></span>
            </div>
            <button type="button" onclick="closeProductDetail()" aria-label="Close"
                    class="p-1.5 -mr-1.5 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors shrink-0 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Scrollable Body --}}
        <div class="p-5 sm:p-6 space-y-4 overflow-y-auto custom-scrollbar">
            <img id="pdm-image" src="" alt=""
                 onerror="this.src=''; this.classList.add('hidden');"
                 class="w-full h-44 sm:h-52 object-cover rounded-2xl border-2 border-gray-200 dark:border-[#1e2536] hidden">

            <div class="bg-gray-50/70 dark:bg-[#161B26]/60 p-3.5 rounded-2xl border border-gray-100 dark:border-[#1e2536]">
                <p class="text-[10px] text-gray-400 dark:text-slate-500 uppercase tracking-wider font-bold">Description</p>
                <p id="pdm-description" class="text-sm text-gray-700 dark:text-slate-300 mt-1 leading-relaxed"></p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 text-sm">
                <div class="bg-gray-50/70 dark:bg-[#161B26]/60 p-3 rounded-2xl border border-gray-100 dark:border-[#1e2536]">
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 uppercase tracking-wider font-bold">Category</p>
                    <p id="pdm-category" class="text-gray-900 dark:text-white mt-1 font-semibold"></p>
                </div>
                <div class="bg-gray-50/70 dark:bg-[#161B26]/60 p-3 rounded-2xl border border-gray-100 dark:border-[#1e2536]">
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 uppercase tracking-wider font-bold">Approx. Volume</p>
                    <p id="pdm-volume" class="text-gray-900 dark:text-white mt-1 font-semibold"></p>
                </div>
                <div id="pdm-cost-card" class="bg-gray-50/70 dark:bg-[#161B26]/60 p-3 rounded-2xl border border-gray-100 dark:border-[#1e2536]">
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 uppercase tracking-wider font-bold">Purchase Cost</p>
                    <p id="pdm-cost" class="text-gray-900 dark:text-white mt-1 font-semibold"></p>
                </div>
                <div id="pdm-price-card" class="bg-gray-50/70 dark:bg-[#161B26]/60 p-3 rounded-2xl border border-gray-100 dark:border-[#1e2536]">
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 uppercase tracking-wider font-bold">Selling Price</p>
                    <p id="pdm-price" class="text-emerald-600 dark:text-emerald-400 mt-1 font-bold"></p>
                </div>
            </div>

            {{-- Inventory Breakdown --}}
            <div class="border-2 border-gray-100 dark:border-[#1e2536] rounded-2xl p-4 bg-gray-50/50 dark:bg-[#161B26]/40">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200 dark:border-[#1e2536]">
                    <span class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Stock Levels</span>
                    <span id="pdm-status" class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full border"></span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-white dark:bg-[#10141D] p-2.5 rounded-xl border border-gray-200 dark:border-[#1e2536]">
                        <p class="text-[10px] text-gray-400 uppercase font-semibold">Store</p>
                        <p id="pdm-store-qty" class="text-base font-bold text-gray-900 dark:text-white mt-0.5"></p>
                    </div>
                    <div class="bg-white dark:bg-[#10141D] p-2.5 rounded-xl border border-gray-200 dark:border-[#1e2536]">
                        <p class="text-[10px] text-gray-400 uppercase font-semibold">Warehouse</p>
                        <p id="pdm-warehouse-qty" class="text-base font-bold text-gray-900 dark:text-white mt-0.5"></p>
                    </div>
                    <div class="bg-white dark:bg-[#10141D] p-2.5 rounded-xl border border-gray-200 dark:border-[#1e2536]">
                        <p class="text-[10px] text-gray-400 uppercase font-semibold">Total / ROP</p>
                        <p id="pdm-total-qty" class="text-base font-bold text-gray-900 dark:text-white mt-0.5"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 sm:px-6 py-3.5 border-t-2 border-gray-100 dark:border-[#1e2536] flex justify-end shrink-0">
            <button type="button" onclick="closeProductDetail()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">
                Close
            </button>
        </div>

    </div>
</div>

<script>
    function openProductDetail(productId) {
        if (!productId) return;
        fetch(`{{ url('/inventory') }}/${productId}/details`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(res => {
                if (!res.ok) throw new Error('Request failed');
                return res.json();
            })
            .then(data => {
                document.getElementById('pdm-name').textContent = data.name || 'Product';
                document.getElementById('pdm-sku').textContent = data.sku || '—';
                document.getElementById('pdm-description').textContent = data.description || 'No description provided.';
                document.getElementById('pdm-category').textContent = data.category || 'Uncategorized';
                document.getElementById('pdm-volume').textContent = data.approx_volume || '—';
                const costCard = document.getElementById('pdm-cost-card');
                const priceCard = document.getElementById('pdm-price-card');
                if (data.purchase_cost === null || data.purchase_cost === undefined) {
                    if (costCard) costCard.classList.add('hidden');
                    if (priceCard) priceCard.classList.add('col-span-2');
                } else {
                    if (costCard) costCard.classList.remove('hidden');
                    if (priceCard) priceCard.classList.remove('col-span-2');
                    document.getElementById('pdm-cost').textContent = '₱' + data.purchase_cost;
                }
                document.getElementById('pdm-price').textContent = '₱' + data.selling_price;
                document.getElementById('pdm-store-qty').textContent = data.store_quantity;
                document.getElementById('pdm-warehouse-qty').textContent = data.warehouse_quantity;
                document.getElementById('pdm-total-qty').textContent = `${data.total_quantity} (ROP: ${data.reorder_point})`;

                const statusEl = document.getElementById('pdm-status');
                statusEl.textContent = data.status;
                if (data.status === 'In Stock') {
                    statusEl.className = 'inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full border bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
                } else if (data.status === 'Low Stock') {
                    statusEl.className = 'inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full border bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800';
                } else {
                    statusEl.className = 'inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full border bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-rose-400 border-red-200 dark:border-rose-900';
                }

                const img = document.getElementById('pdm-image');
                if (data.image) {
                    img.src = data.image;
                    img.classList.remove('hidden');
                } else {
                    img.src = '';
                    img.classList.add('hidden');
                }

                const modal = document.getElementById('productDetailModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            })
            .catch(err => {
                console.error('Error fetching product details:', err);
            });
    }

    function closeProductDetail() {
        const modal = document.getElementById('productDetailModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('productDetailModal');
            if (modal && !modal.classList.contains('hidden')) closeProductDetail();
        }
    });

    window.openProductDetail = openProductDetail;
    window.closeProductDetail = closeProductDetail;
</script>