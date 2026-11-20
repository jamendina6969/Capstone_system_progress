// Hydrotech POS Full Offline Management & Auto-Recovery Engine
class HydrotechPosOffline {
    constructor() {
        this.STORAGE_CATALOG_KEY = 'hydrotech_offline_catalog';
        this.STORAGE_QUEUE_KEY = 'hydrotech_offline_queue';
        this.STORAGE_CART_KEY = 'hydrotech_offline_cart';
        this.isSyncing = false;
        this.syncEndpoint = '/pos/sync-offline';

        this.init();
    }

    init() {
        const ready = () => {
            this.updateNetworkStatus(navigator.onLine);
            if (navigator.onLine && this.getQueueCount() > 0) {
                this.autoSync();
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }

        window.addEventListener('online', () => {
            this.updateNetworkStatus(true);
            this.autoSync();
        });

        window.addEventListener('offline', () => {
            this.updateNetworkStatus(false);
        });

        // Periodic sync retry every 20 seconds when online with pending sales
        setInterval(() => {
            if (navigator.onLine && this.getQueueCount() > 0 && !this.isSyncing) {
                this.autoSync();
            }
        }, 20000);
    }

    updateNetworkStatus(isOnline) {
        const banner = document.getElementById('offlineStatusBanner');
        const bannerTitle = document.getElementById('offlineStatusTitle');
        const bannerText = document.getElementById('offlineStatusMessage');
        const iconWrap = document.getElementById('offlineStatusIconWrap');
        const queueCount = this.getQueueCount();

        if (banner) {
            if (!isOnline || queueCount > 0) {
                banner.classList.remove('hidden');
            } else {
                banner.classList.add('hidden');
            }
        }

        if (bannerTitle && bannerText) {
            if (!isOnline) {
                bannerTitle.textContent = 'Offline Mode Active';
                bannerText.textContent = 'Internet connection interrupted. Retail sales are safely stored locally and will auto-sync once reconnected.';
                if (iconWrap) {
                    iconWrap.className = 'w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border-2 border-amber-200 dark:border-amber-900/70 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0';
                }
            } else if (queueCount > 0) {
                bannerTitle.textContent = 'Connection Restored — Ready to Sync';
                bannerText.textContent = 'Queued offline retail sales are ready to synchronize with the server database.';
                if (iconWrap) {
                    iconWrap.className = 'w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border-2 border-emerald-200 dark:border-emerald-900/70 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0';
                }
            }
        }

        // Match official Counter POS / Parked Orders pill geometry (border-2, px-3 sm:px-4, py-1.5 sm:py-2, text-sm)
        const pill = document.getElementById('posConnectionPill');
        if (pill) {
            const dot = pill.querySelector('[data-conn-dot]');
            const label = pill.querySelector('[data-conn-label]');
            if (isOnline) {
                pill.className = 'inline-flex items-center gap-2.5 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] px-3 sm:px-4 py-1.5 sm:py-2 rounded-full shadow-xs';
                if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse';
                if (label) {
                    label.className = 'text-sm font-bold text-emerald-700 dark:text-emerald-400';
                    label.textContent = 'Counter POS';
                }
            } else {
                pill.className = 'inline-flex items-center gap-2.5 bg-white dark:bg-[#10141D] border-2 border-amber-200 dark:border-amber-900/60 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full shadow-xs';
                if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse';
                if (label) {
                    label.className = 'text-sm font-bold text-amber-700 dark:text-amber-400';
                    label.textContent = 'Offline POS';
                }
            }
        }

        this.updateQueueBadge();
    }

    getQueue() {
        try {
            const raw = localStorage.getItem(this.STORAGE_QUEUE_KEY);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    getQueueCount() {
        return this.getQueue().length;
    }

    saveQueue(queue) {
        localStorage.setItem(this.STORAGE_QUEUE_KEY, JSON.stringify(queue));
        this.updateNetworkStatus(navigator.onLine);
    }

    updateQueueBadge() {
        const badge = document.getElementById('offlineQueueBadge');
        if (badge) {
            const count = this.getQueueCount();
            badge.textContent = `${count} Offline ${count === 1 ? 'Sale' : 'Sales'} Pending`;
            badge.classList.toggle('opacity-50', count === 0);
        }
    }

    // ---- Offline Cart Persistence ----
    saveOfflineCart(cartObj) {
        try {
            localStorage.setItem(this.STORAGE_CART_KEY, JSON.stringify(cartObj || {}));
        } catch (e) {}
    }

    getOfflineCart() {
        try {
            const raw = localStorage.getItem(this.STORAGE_CART_KEY);
            const parsed = raw ? JSON.parse(raw) : {};
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (e) {
            return {};
        }
    }

    clearOfflineCart() {
        try {
            localStorage.removeItem(this.STORAGE_CART_KEY);
        } catch (e) {}
    }

    // ---- Offline Product Catalog ----
    cacheCatalog(products) {
        if (!Array.isArray(products)) return;
        try {
            // Only overwrite cached catalog when online (avoid resetting offline stock deductions after an offline refresh!)
            if (!navigator.onLine) return;

            const existing = this.getCatalog();
            const pendingQueue = this.getQueue();

            // Compute quantities already sold in unsynced offline orders so we don't overstate local shelf stock
            const pendingSold = {};
            pendingQueue.forEach(order => {
                (order.items || []).forEach(it => {
                    const pid = String(it.id);
                    pendingSold[pid] = (pendingSold[pid] || 0) + (parseInt(it.quantity, 10) || 0);
                });
            });

            products.forEach(p => {
                const pid = String(p.id);
                const serverQty = parseInt(p.store_quantity, 10) || 0;
                const adjustedQty = Math.max(0, serverQty - (pendingSold[pid] || 0));
                existing[pid] = {
                    id: p.id,
                    product_name: p.product_name,
                    product_sku: p.product_sku || '',
                    selling_price: parseFloat(p.selling_price) || 0,
                    store_quantity: adjustedQty,
                    reorder_point: parseInt(p.reorder_point, 10) || 10,
                    unit_of_measure: p.unit_of_measure || 'PIECE',
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

    getProduct(productId) {
        const catalog = this.getCatalog();
        return catalog[String(productId)] || null;
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
            subtotal: parseFloat(orderData.subtotal) || 0,
            vat_amount: parseFloat(orderData.vat_amount) || 0,
            total_amount: parseFloat(orderData.total_amount) || 0,
            amount_paid: parseFloat(orderData.amount_paid ?? orderData.total_amount) || 0,
            amount_change: Math.max(0, (parseFloat(orderData.amount_paid ?? orderData.total_amount) || 0) - (parseFloat(orderData.total_amount) || 0)),
            cart_discount_type: orderData.cart_discount_type || 'none',
            cart_discount_value: parseFloat(orderData.cart_discount_value) || 0,
            created_at: now.toISOString(),
        };

        // Add to offline sync queue
        const queue = this.getQueue();
        queue.push(finalizedOrder);
        this.saveQueue(queue);

        // Clear the active offline cart now that it's checked out
        this.clearOfflineCart();

        // Deduct local catalog stock and update UI cards
        const catalog = this.getCatalog();
        const updatedProducts = [];

        finalizedOrder.items.forEach(item => {
            const pid = String(item.id);
            const qty = parseInt(item.quantity, 10) || 1;
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
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ orders: queue }),
            });

            if (response.ok) {
                const data = await response.json();
                const syncedInvoices = Array.isArray(data.synced_invoices) ? data.synced_invoices : [];

                if (data.success && syncedInvoices.length > 0) {
                    const remaining = queue.filter(o => !syncedInvoices.includes(o.invoice_number));
                    if (remaining.length === 0) {
                        localStorage.removeItem(this.STORAGE_QUEUE_KEY);
                    } else {
                        localStorage.setItem(this.STORAGE_QUEUE_KEY, JSON.stringify(remaining));
                    }

                    this.updateNetworkStatus(navigator.onLine);
                    this.showSyncNotification(syncedInvoices.length);

                    if (window.HydrotechLiveSync) {
                        window.HydrotechLiveSync.pollServer();
                    }
                } else if (data.success) {
                    localStorage.removeItem(this.STORAGE_QUEUE_KEY);
                    this.updateNetworkStatus(navigator.onLine);
                    this.showSyncNotification(data.synced_count || queue.length);
                }
            } else {
                console.warn('Sync offline orders returned status', response.status);
            }
        } catch (err) {
            console.warn('Network error while syncing offline orders', err);
        } finally {
            this.isSyncing = false;
        }
    }

    manualSync() {
        if (!navigator.onLine) {
            alert('Cannot sync: Your device is currently offline. Reconnect to the internet first.');
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
        notif.className = 'fixed bottom-6 right-6 z-50 bg-white dark:bg-[#10141D] text-gray-900 dark:text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-sm font-bold border-2 border-emerald-500/80 dark:border-emerald-600/80';
        notif.innerHTML = `
            <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </span>
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
