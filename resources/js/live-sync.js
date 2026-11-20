// Hydrotech Live Real-Time Synchronization Engine
class HydrotechLiveSync {
    constructor() {
        this.channel = typeof BroadcastChannel !== 'undefined' ? new BroadcastChannel('hydrotech_sync') : null;
        this.lastTimestamp = new Date().toISOString();
        this.pollInterval = 8000; // 8 seconds
        this.timer = null;
        this.isPolling = false;
        this.syncUrl = '/inventory/live-sync';

        this.init();
    }

    init() {
        // 1. Cross-Tab Instant Sync via BroadcastChannel
        if (this.channel) {
            this.channel.onmessage = (event) => {
                if (event.data && event.data.type === 'stock_update') {
                    this.applyStockUpdates(event.data.products);
                }
            };
        }

        // 2. Window focus trigger (immediate check when user switches back to tab)
        window.addEventListener('focus', () => this.pollServer());

        // 3. Periodic Background Server Poll
        this.startPolling();
    }

    startPolling() {
        if (this.timer) clearInterval(this.timer);
        this.timer = setInterval(() => this.pollServer(), this.pollInterval);
    }

    broadcastLocalUpdate(products) {
        if (this.channel && products) {
            try {
                this.channel.postMessage({
                    type: 'stock_update',
                    products: products,
                    timestamp: new Date().toISOString()
                });
            } catch (e) {
                console.warn('BroadcastChannel error', e);
            }
        }
    }

    async pollServer() {
        if (this.isPolling || !navigator.onLine) return;
        this.isPolling = true;

        try {
            const res = await fetch(`${this.syncUrl}?since=${encodeURIComponent(this.lastTimestamp)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });

            if (res.ok) {
                const data = await res.json();
                if (data.timestamp) {
                    this.lastTimestamp = data.timestamp;
                }
                if (data.products && data.products.length > 0) {
                    this.applyStockUpdates(data.products);
                }
            }
        } catch (err) {
            // Quiet fail during network drops
        } finally {
            this.isPolling = false;
        }
    }

    applyStockUpdates(products) {
        if (!Array.isArray(products) || products.length === 0) return;

        // Dispatch custom global event so any active page component can listen
        window.dispatchEvent(new CustomEvent('hydrotech:stock-updated', { detail: { products } }));

        products.forEach(p => {
            // Account for any quantity currently reserved in the active POS cart
            let reservedStoreQty = 0;
            if (window.currentPosCart && typeof window.currentPosCart === 'object') {
                Object.entries(window.currentPosCart).forEach(([cartKey, cartItem]) => {
                    if (!cartItem) return;
                    if ((cartItem.type || 'product') === 'project') {
                        const mats = cartItem.project_breakdown?.materials || [];
                        mats.forEach(m => {
                            if (String(m.product_id) === String(p.id) && m.source === 'store') {
                                reservedStoreQty += parseInt(m.qty, 10) || 0;
                            }
                        });
                    } else if (String(cartItem.id ?? cartKey) === String(p.id)) {
                        reservedStoreQty += parseInt(cartItem.quantity, 10) || 0;
                    }
                });
            }

            const availableStoreQty = Math.max(0, (parseInt(p.store_quantity, 10) || 0) - reservedStoreQty);
            const reorderPoint = parseInt(p.reorder_point, 10) || 10;

            let statusText = 'IN STOCK';
            let statusClass = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900';
            if (availableStoreQty <= 0) {
                statusText = 'OUT OF STOCK';
                statusClass = 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-900';
            } else if (availableStoreQty <= reorderPoint) {
                statusText = 'LOW STOCK';
                statusClass = 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-900';
            }

            // Update POS Grid Card Big Stock Number (preserve big-number typography)
            const posStockNum = document.querySelector(`[data-pos-stock-id="${p.id}"]`);
            if (posStockNum) {
                posStockNum.textContent = String(availableStoreQty);
                posStockNum.classList.remove('text-gray-400', 'text-gray-900', 'dark:text-white');
                if (availableStoreQty <= 0) {
                    posStockNum.classList.add('text-gray-400');
                } else {
                    posStockNum.classList.add('text-gray-900', 'dark:text-white');
                }
            }

            // Update POS Grid Card Status Pill
            const posStatusPill = document.querySelector(`[data-pos-status-id="${p.id}"]`);
            if (posStatusPill) {
                posStatusPill.textContent = statusText;
                posStatusPill.className = `text-[10px] font-bold px-2.5 py-1 rounded-full border ${statusClass} tracking-wide`;
            }

            // Update POS List View Status Pill
            const posListStatus = document.querySelector(`[data-pos-list-status-id="${p.id}"]`);
            if (posListStatus) {
                posListStatus.textContent = `${statusText} (${availableStoreQty})`;
                posListStatus.className = `text-[10px] font-bold px-2 py-1 rounded-full border ${statusClass} tracking-wide`;
            }

            // Update Inventory Table Rows if present
            const row = document.querySelector(`tr[data-product-id="${p.id}"]`);
            if (row) {
                const storeCell = row.querySelector('[data-col="store_quantity"]');
                const whCell = row.querySelector('[data-col="warehouse_quantity"]');
                const totalCell = row.querySelector('[data-col="total_quantity"]');
                if (storeCell) storeCell.textContent = p.store_quantity;
                if (whCell) whCell.textContent = p.warehouse_quantity;
                if (totalCell) totalCell.textContent = p.total_quantity;
            }
        });
    }
}

window.HydrotechLiveSync = new HydrotechLiveSync();
export default window.HydrotechLiveSync;
