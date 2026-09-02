const cacheName = 'denardi-shell-v2';
const shell = ['/offline', '/manifest.webmanifest', '/admin.webmanifest', '/denardi-icon.svg', '/icons/icon-192.png', '/icons/icon-512.png', '/icons/maskable-512.png', '/icons/apple-touch-icon.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(cacheName).then((cache) => cache.addAll(shell)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== cacheName).map((key) => caches.delete(key)))));
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    if (event.request.method !== 'GET' || url.origin !== self.location.origin || url.pathname.startsWith('/api/') || event.request.mode !== 'navigate') return;
    event.respondWith(fetch(event.request)
        .then((response) => {
            const copy = response.clone();
            caches.open(cacheName).then((cache) => cache.put(event.request, copy));
            return response;
        })
        .catch(async () => (await caches.match(event.request)) || caches.match('/offline')));
});
