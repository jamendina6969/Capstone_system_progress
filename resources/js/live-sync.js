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
            // Update POS Product Cards Stock Badge
            const posBadge = document.querySelector(`[data-pos-stock-id="${p.id}"]`);
            if (posBadge) {
                posBadge.textContent = `Store: ${p.store_quantity}`;
                if (p.store_quantity <= 0) {
                    posBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-400';
                    posBadge.textContent = 'Out of Stock';
                } else if (p.store_quantity <= (p.reorder_point || 10)) {
                    posBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/80 dark:text-amber-400';
                }
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
