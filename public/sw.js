const ASSET_CACHE = 'hydrotech-assets-v1';
const PAGE_CACHE = 'hydrotech-pages-v1';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(ASSET_CACHE).then((cache) => {
            return cache.addAll(['/images/logo.svg']).catch(() => {});
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== ASSET_CACHE && key !== PAGE_CACHE)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CLEAR_POS_CACHE') {
        caches.delete(PAGE_CACHE);
    }
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    const url = new URL(req.url);

    // Clear cached authenticated POS page whenever the user logs out
    if (url.origin === self.location.origin && url.pathname === '/logout') {
        caches.delete(PAGE_CACHE);
        return;
    }

    // Never intercept non-GET requests or cross-origin traffic
    if (req.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // 1. Built Vite assets & static images: Cache-first with background refresh
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/')) {
        event.respondWith(
            caches.open(ASSET_CACHE).then((cache) =>
                cache.match(req).then((cached) => {
                    const networkFetch = fetch(req)
                        .then((res) => {
                            if (res && res.ok && res.type === 'basic') {
                                cache.put(req, res.clone());
                            }
                            return res;
                        })
                        .catch(() => cached);

                    return cached || networkFetch;
                })
            )
        );
        return;
    }

    // 2. POS page navigation (/pos): Network-first with offline cache fallback
    if (req.mode === 'navigate' && url.pathname === '/pos') {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    // Only cache genuine 200 POS responses (never cache redirects to /login)
                    if (res && res.ok && res.type === 'basic' && !res.redirected) {
                        const clone = res.clone();
                        caches.open(PAGE_CACHE).then((cache) => {
                            cache.put('/pos', clone);
                        });
                    }
                    return res;
                })
                .catch(() =>
                    caches.open(PAGE_CACHE).then((cache) =>
                        cache.match('/pos').then((cached) => cached || Response.error())
                    )
                )
        );
    }
});
