/* ============================================================
   SUNUSCORE - App JS (public)
   ============================================================ */
(function () {
    'use strict';

    // Marque le lien actif selon l'URL courante
    const path = window.location.pathname.replace(/\/$/, '');
    document.querySelectorAll('.navbar-links a, .bottom-nav a').forEach(a => {
        const href = a.getAttribute('href') || '';
        const cleanHref = href.replace(/\/$/, '');
        if (cleanHref && (path === cleanHref || path.endsWith(cleanHref))) {
            a.classList.add('active');
        }
    });

    // Support tactile : assure la navigation au clavier
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-open').forEach(m => m.classList.remove('modal-open'));
        }
    });

    console.info('[SUNUSCORE] public app ready');
})();