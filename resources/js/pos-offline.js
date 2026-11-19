// Hydrotech POS Offline Management & Auto-Recovery Engine
class HydrotechPosOffline {
    constructor() {
        this.STORAGE_CATALOG_KEY = 'hydrotech_offline_catalog';
        this.STORAGE_QUEUE_KEY = 'hydrotech_offline_queue';
        this.isSyncing = false;
        this.syncEndpoint = '/pos/sync-offline';

        this.init();
    }

    init() {
        // Initial network check
        this.updateNetworkStatus(navigator.onLine);

        // Window online / offline event listeners
        window.addEventListener('online', () => {
            this.updateNetworkStatus(true);
            this.autoSync();
        });

        window.addEventListener('offline', () => {
            this.updateNetworkStatus(false);
        });

        // Periodic check every 30 seconds
        setInterval(() => {
            if (navigator.onLine && this.getQueueCount() > 0 && !this.isSyncing) {
                this.autoSync();
            }
        }, 30000);

        // Update badge initially
        this.updateQueueBadge();
    }

    updateNetworkStatus(isOnline) {
        const banner = document.getElementById('offlineStatusBanner');
        if (banner) {
            if (isOnline) {
                banner.classList.add('hidden');
            } else {
                banner.classList.remove('hidden');
            }
        }
        this.updateQueueBadge();
    }

    getQueue() {
        try {
            const raw = localStorage.getItem(this.STORAGE_QUEUE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            console.error('Failed to parse offline queue:', e);
            return [];
        }
    }

    getQueueCount() {
        return this.getQueue().length;
    }

    saveQueue(queue) {
        localStorage.setItem(this.STORAGE_QUEUE_KEY, JSON.stringify(queue));
        this.updateQueueBadge();
    }

    updateQueueBadge() {
        const badge = document.getElementById('offlineQueueBadge');
        if (badge) {
            const count = this.getQueueCount();
            badge.textContent = `${count} Offline ${count === 1 ? 'Sale' : 'Sales'} Pending`;
            if (count > 0) {
                badge.classList.remove('opacity-50');
            } else {
                badge.classList.add('opacity-50');
            }
        }
    }

    cacheCatalog(products) {
        if (!Array.isArray(products)) return;
        try {
            const existing = this.getCatalog();
            products.forEach(p => {
                existing[p.id] = {
                    id: p.id,
                    product_name: p.product_name,
                    product_sku: p.product_sku,
                    selling_price: parseFloat(p.selling_price) || 0,
                    store_quantity: parseInt(p.store_quantity) || 0,
                    reorder_point: parseInt(p.reorder_point) || 10
                };
            });
            localStorage.setItem(this.STORAGE_CATALOG_KEY, JSON.stringify(existing));
        } catch (e) {
            console.warn('Failed to cache catalog:', e);
        }
    }

    getCatalog() {
        try {
            const raw = localStorage.getItem(this.STORAGE_CATALOG_KEY);
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            return {};
        }
    }

    /**
     * Record an offline order when internet is unavailable.
     */
    recordOfflineOrder(orderData) {
        const now = new Date();
        const timestampStr = now.toISOString().replace(/[-:T.]/g, '').slice(0, 14);
        const randomStr = Math.random().toString(36).substring(2, 7).toUpperCase();
        const invoiceNumber = orderData.invoice_number || `OFFLINE-${timestampStr}-${randomStr}`;

        const finalizedOrder = {
            invoice_number: invoiceNumber,
            customer_name: orderData.customer_name || 'Walk-in Customer (Offline)',
            items: orderData.items || [],
            subtotal: orderData.subtotal || 0,
            vat_amount: orderData.vat_amount || 0,
            total_amount: orderData.total_amount || 0,
            amount_paid: orderData.amount_paid || orderData.total_amount || 0,
            amount_change: Math.max(0, (orderData.amount_paid || orderData.total_amount || 0) - (orderData.total_amount || 0)),
            cart_discount_type: orderData.cart_discount_type || 'none',
            cart_discount_value: orderData.cart_discount_value || 0,
            created_at: now.toISOString()
        };

        // Add to queue
        const queue = this.getQueue();
        queue.push(finalizedOrder);
        this.saveQueue(queue);

        // Deduct local catalog stock and broadcast
        const catalog = this.getCatalog();
        const updatedProducts = [];

        finalizedOrder.items.forEach(item => {
            const pid = item.id;
            const qty = parseInt(item.quantity) || 1;
            if (catalog[pid]) {
                catalog[pid].store_quantity = Math.max(0, (catalog[pid].store_quantity || 0) - qty);
                updatedProducts.push(catalog[pid]);
            }
        });

        localStorage.setItem(this.STORAGE_CATALOG_KEY, JSON.stringify(catalog));

        if (window.HydrotechLiveSync && updatedProducts.length > 0) {
            window.HydrotechLiveSync.applyStockUpdates(updatedProducts);
            window.HydrotechLiveSync.broadcastLocalUpdate(updatedProducts);
        }

        return finalizedOrder;
    }

    /**
     * Synchronize queued offline orders to backend.
     */
    async autoSync() {
        if (this.isSyncing || !navigator.onLine) return;
        const queue = this.getQueue();
        if (queue.length === 0) return;

        this.isSyncing = true;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch(this.syncEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ orders: queue })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // Clear queue
                    localStorage.removeItem(this.STORAGE_QUEUE_KEY);
                    this.updateQueueBadge();

                    this.showSyncNotification(data.synced_count || queue.length);

                    // Trigger live sync to refresh balances
                    if (window.HydrotechLiveSync) {
                        window.HydrotechLiveSync.pollServer();
                    }
                }
            } else {
                console.warn('Sync offline orders returned non-200 status', response.status);
            }
        } catch (err) {
            console.warn('Network error while syncing offline orders', err);
        } finally {
            this.isSyncing = false;
        }
    }

    manualSync() {
        if (!navigator.onLine) {
            alert('Cannot sync: Your device is currently offline. Connect to the internet first.');
            return;
        }

        const count = this.getQueueCount();
        if (count === 0) {
            alert('There are no pending offline sales to synchronize.');
            return;
        }

        this.autoSync();
    }

    showSyncNotification(count) {
        const notif = document.createElement('div');
        notif.className = 'fixed bottom-6 right-6 z-50 bg-emerald-600 text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-sm font-bold border-2 border-emerald-400 animate-bounce';
        notif.innerHTML = `
            <svg class="w-5 h-5 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Synced ${count} offline ${count === 1 ? 'sale' : 'sales'} to server database!</span>
        `;
        document.body.appendChild(notif);

        setTimeout(() => {
            notif.remove();
        }, 5000);
    }
}

window.PosOffline = new HydrotechPosOffline();
export default window.PosOffline;
