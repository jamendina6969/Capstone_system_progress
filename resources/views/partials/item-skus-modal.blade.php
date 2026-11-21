<!-- Global Serialized Unit Item SKUs Inspector Modal -->
<div id="itemSkusModal" class="fixed inset-0 z-[95] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeItemSkusModal()">
    <div class="relative w-full max-w-3xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[88vh]" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b-2 border-gray-200 dark:border-[#1e2536] flex items-center justify-between">
            <div>
                <h3 id="skuModalTitle" class="text-lg font-bold text-gray-900 dark:text-white">Serialized Item SKUs</h3>
                <p id="skuModalSubtitle" class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Unit-level traceability across Store, Warehouse, POS Sales, and Returns</p>
            </div>
            <button type="button" onclick="closeItemSkusModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Summary Counters & Filter Bar -->
        <div class="px-6 py-3 bg-gray-50/70 dark:bg-[#161B26]/70 border-b border-gray-200 dark:border-[#1e2536] space-y-2.5">
            <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold">
                <button type="button" onclick="filterItemSkus('', '')" data-sku-tab="all" class="sku-tab px-3 py-1 rounded-full border-2 border-slate-800 bg-slate-800 text-white cursor-pointer">All</button>
                <button type="button" onclick="filterItemSkus('in_stock', 'store')" data-sku-tab="store" class="sku-tab px-3 py-1 rounded-full border-2 border-emerald-200 dark:border-emerald-900 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 cursor-pointer">Store: <span id="skuCntStore">0</span></button>
                <button type="button" onclick="filterItemSkus('in_stock', 'warehouse')" data-sku-tab="warehouse" class="sku-tab px-3 py-1 rounded-full border-2 border-blue-200 dark:border-blue-900 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 cursor-pointer">Warehouse: <span id="skuCntWh">0</span></button>
                <button type="button" onclick="filterItemSkus('sold', '')" data-sku-tab="sold" class="sku-tab px-3 py-1 rounded-full border-2 border-gray-200 dark:border-[#1e2536] bg-white dark:bg-[#10141D] text-gray-600 dark:text-slate-300 cursor-pointer">Sold: <span id="skuCntSold">0</span></button>
                <button type="button" onclick="filterItemSkus('returned_defective', '')" data-sku-tab="defective" class="sku-tab px-3 py-1 rounded-full border-2 border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 cursor-pointer">Defective: <span id="skuCntDefective">0</span></button>
                <button type="button" onclick="filterItemSkus('returned_to_supplier', '')" data-sku-tab="supplier" class="sku-tab px-3 py-1 rounded-full border-2 border-rose-200 dark:border-rose-900 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 cursor-pointer">Returned to Supplier: <span id="skuCntSupplier">0</span></button>
            </div>
            <input type="text" id="skuModalSearch" oninput="loadItemSkus()" placeholder="Search specific unit item_sku (e.g. PRD-0001)..."
                   class="w-full rounded-xl bg-white dark:bg-[#10141D] border border-gray-300 dark:border-[#1e2536] px-3.5 py-2 text-xs text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>

        <!-- SKU Table -->
        <div class="flex-1 overflow-y-auto overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[540px] text-xs text-left">
                <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-500 dark:text-slate-400 uppercase text-[10px] sticky top-0 border-b border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-5 py-3">Item SKU</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Traceability (PO / Receipt)</th>
                        <th class="px-5 py-3 text-right">Copy</th>
                    </tr>
                </thead>
                <tbody id="skuModalBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Loading SKUs...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3.5 border-t-2 border-gray-200 dark:border-[#1e2536] flex justify-end">
            <button type="button" onclick="closeItemSkusModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] text-xs font-semibold px-5 py-2 rounded-full transition-all cursor-pointer">Close</button>
        </div>
    </div>
</div>

<script>
(function () {
    let currentSkuProductId = null;
    let currentSkuStatus = '';
    let currentSkuLocation = '';

    window.openItemSkusModal = function (productId) {
        currentSkuProductId = productId;
        currentSkuStatus = '';
        currentSkuLocation = '';
        const searchEl = document.getElementById('skuModalSearch');
        if (searchEl) searchEl.value = '';
        document.getElementById('itemSkusModal')?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        window.loadItemSkus();
    };

    window.closeItemSkusModal = function () {
        document.getElementById('itemSkusModal')?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    window.filterItemSkus = function (status, location) {
        currentSkuStatus = status;
        currentSkuLocation = location;
        window.loadItemSkus();
    };

    window.copyUnitSku = function (sku, btn) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(sku).then(() => {
                const orig = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => { btn.textContent = orig; }, 1200);
            }).catch(() => {});
        }
    };

    window.loadItemSkus = async function () {
        if (!currentSkuProductId) return;
        const tbody = document.getElementById('skuModalBody');
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Loading serialized SKUs...</td></tr>';

        const params = new URLSearchParams();
        if (currentSkuStatus) params.set('status', currentSkuStatus);
        if (currentSkuLocation) params.set('location', currentSkuLocation);
        const q = document.getElementById('skuModalSearch')?.value.trim();
        if (q) params.set('search', q);

        try {
            const res = await fetch(`/products/${currentSkuProductId}/item-skus?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('Failed to load SKUs');
            const data = await res.json();

            document.getElementById('skuModalTitle').textContent = `${data.product.product_name} — Unit SKUs`;
            document.getElementById('skuModalSubtitle').textContent = `Master SKU: ${data.product.product_sku} • Store: ${data.product.store_quantity} • Warehouse: ${data.product.warehouse_quantity}`;
            document.getElementById('skuCntStore').textContent = data.counts.store_in_stock;
            document.getElementById('skuCntWh').textContent = data.counts.warehouse_in_stock;
            document.getElementById('skuCntSold').textContent = data.counts.sold;
            document.getElementById('skuCntDefective').textContent = data.counts.returned_defective;
            document.getElementById('skuCntSupplier').textContent = data.counts.returned_to_supplier;

            if (!data.skus || data.skus.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No matching item SKUs found.</td></tr>';
                return;
            }

            const statusBadges = {
                in_stock: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-900',
                sold: 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                returned_defective: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-900',
                returned_to_supplier: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-900',
            };

            tbody.innerHTML = data.skus.map(u => {
                const badge = statusBadges[u.status] || statusBadges.sold;
                const traces = [];
                if (u.po_number) traces.push(`PO: ${u.po_number}`);
                if (u.transaction_id) traces.push(`Receipt: ${u.transaction_id}`);
                if (u.supplier_return_number) traces.push(`Return: ${u.supplier_return_number}`);
                const traceHtml = traces.length ? traces.join(' · ') : '<span class="text-gray-400">—</span>';
                return `
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40">
                        <td class="px-5 py-2.5 font-mono font-bold text-gray-900 dark:text-white">${u.item_sku}</td>
                        <td class="px-4 py-2.5 uppercase font-semibold text-gray-600 dark:text-slate-300">${u.location}</td>
                        <td class="px-4 py-2.5"><span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border ${badge}">${u.status.replace(/_/g, ' ').toUpperCase()}</span></td>
                        <td class="px-4 py-2.5 text-[11px] text-gray-600 dark:text-slate-400">${traceHtml}</td>
                        <td class="px-5 py-2.5 text-right">
                            <button type="button" onclick="copyUnitSku('${u.item_sku}', this)" class="text-[11px] font-semibold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer">Copy</button>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-rose-500">Could not load item SKUs.</td></tr>';
        }
    };
})();
</script>
