const CACHE_NAME = 'simapres-cache-v1';
const OFFLINE_URL = '/offline.html';

// File-file yang akan di-cache saat install
const PRECACHE_URLS = [
    '/',
    '/offline.html',
    '/manifest.json'
];

// Event: Install - cache file penting
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[SW] Pre-caching offline assets');
            return cache.addAll(PRECACHE_URLS);
        })
    );
    self.skipWaiting();
});

// Event: Activate - hapus cache lama
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Event: Fetch - Network First, fallback ke cache
self.addEventListener('fetch', (event) => {
    // Hanya handle GET request
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Jangan cache request API, sanctum, livewire, atau storage
    if (
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/sanctum/') ||
        url.pathname.startsWith('/livewire/') ||
        url.pathname.startsWith('/storage/') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/logout') ||
        url.pathname.startsWith('/sso/')
    ) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Jangan cache response yang bukan 200 OK
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                // Clone response dan simpan ke cache
                const responseClone = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseClone);
                });
                return response;
            })
            .catch(() => {
                // Jika offline, cari di cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Jika halaman navigasi, tampilkan halaman offline
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                });
            })
    );
});
