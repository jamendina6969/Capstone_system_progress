// Hydrotech Live Real-Time Synchronization Engine
class HydrotechLiveSync {
    constructor() {
        this.channel = typeof BroadcastChannel !== 'undefined' ? new BroadcastChannel('hydrotech_sync') : null;
        this.lastTimestamp = new Date().toISOString();
        this.pollInterval = 8000; // 8 seconds
        this.notifPollInterval = 20000; // 20 seconds
        this.timer = null;
        this.notifTimer = null;
        this.isPolling = false;
        this.syncUrl = '/inventory/live-sync';
        this.notifUrl = '/notifications/poll';
        this.seenNotifIds = new Set();
        this.notifInitialized = false;

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
        window.addEventListener('focus', () => {
            this.pollServer();
            this.pollNotifications();
        });

        // 3. Periodic Background Server Poll
        this.startPolling();
        this.startNotificationPolling();
    }

    startNotificationPolling() {
        if (!document.getElementById('notificationBtn')) return;
        this.pollNotifications();
        if (this.notifTimer) clearInterval(this.notifTimer);
        this.notifTimer = setInterval(() => this.pollNotifications(), this.notifPollInterval);
    }

    async pollNotifications() {
        if (!navigator.onLine || !document.getElementById('notificationBtn')) return;
        try {
            const res = await fetch(this.notifUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (res.status === 401) {
                const body = await res.json().catch(() => ({}));
                if (body && body.force_logout) {
                    window.location.href = body.redirect || '/login';
                    return;
                }
            }
            if (!res.ok) return;
            const data = await res.json();
            this.renderNotifications(data);
        } catch (err) {
            // Silent fail on transient network error
        }
    }

    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    renderNotifications(data) {
        const badge = document.getElementById('notifBadgeCount');
        const headerPill = document.getElementById('notifHeaderPill');
        const body = document.getElementById('notifDropdownBody');
        const total = Number(data.total_count || 0);

        if (badge) {
            badge.textContent = total > 99 ? '99+' : String(total);
            badge.classList.toggle('hidden', total <= 0);
        }
        if (headerPill) {
            headerPill.textContent = `${total} Active`;
        }

        // Update any live sidebar badges
        if (data.badges) {
            Object.entries(data.badges).forEach(([key, count]) => {
                document.querySelectorAll(`[data-live-badge="${key}"]`).forEach(el => {
                    el.textContent = String(count);
                    el.classList.toggle('hidden', Number(count) <= 0);
                });
            });
        }

        const dotClass = (sev) => ({
            danger: 'bg-rose-500',
            warning: 'bg-amber-500',
            success: 'bg-emerald-500',
            info: 'bg-blue-500',
        }[sev] || 'bg-blue-500');

        // Detect brand-new unread notifications for subtle toast alert
        const inbox = Array.isArray(data.inbox) ? data.inbox : [];
        if (this.notifInitialized && data.toast_enabled) {
            inbox.forEach(item => {
                if (item.is_unread && !this.seenNotifIds.has(item.id)) {
                    this.showToast(item);
                }
            });
        }
        inbox.forEach(item => this.seenNotifIds.add(item.id));
        this.notifInitialized = true;

        if (!body) return;
        const queues = Array.isArray(data.queues) ? data.queues : [];
        let html = '';

        if (queues.length > 0) {
            html += `<div class="px-4 py-1.5 bg-slate-50/80 dark:bg-[#161B26]/40 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Action Required</div>`;
            queues.forEach(q => {
                html += `<a href="${this.escapeHtml(q.url)}" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-[#1e2536]/40 transition-colors">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full shrink-0 ${dotClass(q.severity)}"></span>
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">${this.escapeHtml(q.title)}</p>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 pl-4 leading-snug">${this.escapeHtml(q.message)}</p>
                </a>`;
            });
        }

        if (inbox.length > 0) {
            html += `<div class="px-4 py-1.5 bg-slate-50/80 dark:bg-[#161B26]/40 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Recent Alerts</div>`;
            inbox.forEach(item => {
                const bg = item.is_unread
                    ? 'bg-blue-50/40 dark:bg-blue-950/20 hover:bg-blue-50 dark:hover:bg-blue-950/30'
                    : 'hover:bg-slate-50 dark:hover:bg-[#1e2536]/40 opacity-80';
                const dot = item.is_unread ? dotClass(item.severity) : 'bg-slate-300 dark:bg-slate-600';
                const newPill = item.is_unread
                    ? `<span class="shrink-0 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">New</span>`
                    : '';
                html += `<a href="${this.escapeHtml(item.read_url)}" class="block px-4 py-3 transition-colors ${bg}">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2 h-2 rounded-full shrink-0 ${dot}"></span>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 truncate">${this.escapeHtml(item.title)}</p>
                        </div>
                        ${newPill}
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 pl-4 leading-snug">${this.escapeHtml(item.message)}</p>
                    <p class="text-[10px] text-slate-400 mt-1 pl-4">${this.escapeHtml(item.time_ago)}</p>
                </a>`;
            });
        }

        if (queues.length === 0 && inbox.length === 0) {
            html = `<div class="px-4 py-8 text-center text-xs text-slate-400 dark:text-slate-500">You're all caught up — no pending notifications.</div>`;
        }

        body.innerHTML = html;
    }

    showToast(item) {
        const container = document.getElementById('notifToastContainer');
        if (!container) return;

        const borderColor = {
            danger: 'border-rose-300 dark:border-rose-800',
            warning: 'border-amber-300 dark:border-amber-800',
            success: 'border-emerald-300 dark:border-emerald-800',
            info: 'border-blue-300 dark:border-blue-800',
        }[item.severity] || 'border-slate-300 dark:border-[#1e2536]';

        const toast = document.createElement('a');
        toast.href = item.read_url || '#';
        toast.className = `pointer-events-auto block bg-white dark:bg-[#10141D] border-2 ${borderColor} rounded-2xl shadow-xl p-3.5 transition-all duration-300`;
        toast.innerHTML = `
            <p class="text-xs font-bold text-slate-900 dark:text-white">${this.escapeHtml(item.title)}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">${this.escapeHtml(item.message)}</p>
        `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 6000);
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

            if (res.status === 401) {
                const body = await res.json().catch(() => ({}));
                if (body && body.force_logout) {
                    window.location.href = body.redirect || '/login';
                    return;
                }
            }

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
