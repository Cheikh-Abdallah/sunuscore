/* ============================================================
   SUNUSCORE - API wrapper (fetch)
   ============================================================ */
window.SunuApi = (function () {
    'use strict';
    const BASE = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || '';

    async function request(method, path, body) {
        const opts = {
            method,
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        };
        if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        const res = await fetch(BASE + path, opts);
        let data;
        try { data = await res.json(); } catch (_) { data = { success:false, message:'Réponse invalide' }; }
        if (!res.ok || !data.success) {
            const err = new Error(data.message || 'Erreur réseau');
            err.status = res.status;
            err.errors = data.errors || [];
            throw err;
        }
        return data;
    }

    return {
        get:  (p)    => request('GET', p),
        post: (p, b) => request('POST', p, b),
    };
})();