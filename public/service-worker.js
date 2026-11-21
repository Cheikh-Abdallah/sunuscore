/**
 * SUNUSCORE - Service Worker
 * Gère le cache pour le mode hors connexion et l'installation PWA.
 * 
 * Stratégies :
 * - API LIVE : Network First (jamais de cache stale pour les scores).
 * - Pages HTML : Stale-While-Revalidate (rapide, mais mise à jour en fond).
 * - Assets statiques (CSS/JS/img) : Cache First.
 * - Hors connexion : Fallback vers /offline.html.
 */
const VERSION = 'sunuscore-v1.0.0';
const STATIC_CACHE = VERSION + '-static';
const PAGES_CACHE  = VERSION + '-pages';
const API_CACHE    = VERSION + '-api';

// Ressources à précacher lors de l'installation
const PRECACHE_URLS = [
    '/',
    '/offline.html',
    '/manifest.json',
    '/assets/css/style.css',
    '/assets/js/app.js',
    '/assets/js/live-score.js',
    // --- Ajout des icônes ---
    '/assets/icons/favicon.ico',
    '/assets/icons/apple-touch-icon.png',
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png'
];

// 1. INSTALLATION : Mise en cache des ressources de base
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            return cache.addAll(PRECACHE_URLS);
        }).then(() => self.skipWaiting())
    );
});

// 2. ACTIVATION : Nettoyage des anciens caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (!cacheName.startsWith(VERSION)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. INTERCEPTION DES REQUÊTES
self.addEventListener('fetch', (event) => {
    const req = event.request;
    const url = new URL(req.url);

    // On ne gère que les requêtes GET et notre propre domaine
    if (req.method !== 'GET' || url.origin !== self.location.origin) return;

    // A. API LIVE : Network First (Toujours les données fraîches si le réseau est là)
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(req, API_CACHE, 3000));
        return;
    }

    // B. Assets Statiques (CSS, JS, Images) : Cache First
    if (/\.(css|js|png|jpg|jpeg|svg|webp|woff2?|ttf|ico)$/i.test(url.pathname)) {
        event.respondWith(cacheFirst(req, STATIC_CACHE));
        return;
    }

    // C. Pages HTML : Stale-While-Revalidate + Fallback offline
    if (req.headers.get('accept')?.includes('text/html')) {
        event.respondWith(staleWhileRevalidate(req, PAGES_CACHE));
        return;
    }

    // D. Par défaut : Réseau avec fallback
    event.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
});

// --- FONCTIONS DE STRATÉGIE ---

async function cacheFirst(req, cacheName) {
    const cached = await caches.match(req);
    if (cached) return cached;
    try {
        const resp = await fetch(req);
        if (resp.ok) {
            const cache = await caches.open(cacheName);
            cache.put(req, resp.clone());
        }
        return resp;
    } catch (e) {
        return new Response('', { status: 504, statusText: 'Offline' });
    }
}

async function networkFirst(req, cacheName, timeoutMs) {
    const cache = await caches.open(cacheName);
    try {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), timeoutMs);
        const resp = await fetch(req, { signal: controller.signal });
        clearTimeout(timer);
        
        if (resp.ok) cache.put(req, resp.clone());
        return resp;
    } catch (e) {
        const cached = await cache.match(req);
        if (cached) return cached;
        
        // Si pas de cache et pas de réseau, on renvoie une erreur JSON propre pour l'API
        return new Response(JSON.stringify({
            success: false,
            message: 'Réseau indisponible',
            data: null,
            errors: []
        }), { status: 503, headers: { 'Content-Type': 'application/json' } });
    }
}

async function staleWhileRevalidate(req, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(req);
    
    const fetchPromise = fetch(req).then((resp) => {
        if (resp.ok) cache.put(req, resp.clone());
        return resp;
    }).catch(() => null);

    if (cached) return cached;
    
    const fresh = await fetchPromise;
    if (fresh) return fresh;
    
    const offline = await caches.match('/offline.html');
    if (offline) return offline;
    
    return new Response('<h1>Vous êtes hors ligne</h1>', {
        status: 503,
        headers: { 'Content-Type': 'text/html; charset=utf-8' }
    });
}