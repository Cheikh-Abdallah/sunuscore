/**
 * SUNUSCORE - PWA Installer
 * Gère l'enregistrement du Service Worker et l'affichage de la bannière d'installation.
 */
(function () {
    'use strict';

    // 1. Enregistrement du Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
                .then((reg) => {
                    // Vérifier les mises à jour toutes les heures
                    setInterval(() => reg.update(), 60 * 60 * 1000);
                })
                .catch((err) => console.warn('Erreur Service Worker:', err));
        });
    }

    // 2. Gestion de l'installation (Android / Chrome)
    let deferredPrompt = null;
    const banner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('pwa-install-btn');
    const laterBtn = document.getElementById('pwa-later-btn');
    const closeBtn = document.getElementById('pwa-close-btn');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        // On affiche la bannière seulement si l'utilisateur ne l'a pas fermée récemment
        if (!localStorage.getItem('pwa-dismissed') && banner) {
            banner.style.display = 'flex';
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                banner.style.display = 'none';
            }
            deferredPrompt = null;
        });
    }

    function dismissBanner() {
        if (banner) banner.style.display = 'none';
        localStorage.setItem('pwa-dismissed', 'true');
    }

    if (laterBtn) laterBtn.addEventListener('click', dismissBanner);
    if (closeBtn) closeBtn.addEventListener('click', dismissBanner);

    // 3. Instructions iOS (Safari)
    const isIos = () => /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
    const isInStandaloneMode = () => ('standalone' in window.navigator) && (window.navigator.standalone);

    if (isIos() && !isInStandaloneMode()) {
        // Afficher les instructions iOS si ce n'est pas déjà installé
        const iosGuide = document.getElementById('pwa-ios-guide');
        if (iosGuide && !localStorage.getItem('pwa-ios-dismissed')) {
            iosGuide.style.display = 'flex';
        }
    }
})();