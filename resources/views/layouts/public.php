<?php
/**
 * Layout public SUNUSCORE.
 * Variables attendues :
 *   $view  : chemin absolu de la vue à inclure
 *   $title : titre de page (optionnel)
 */
$title = $title ?? 'SUNUSCORE';
$appUrl = rtrim((string) config('app.url', ''), '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#071A36">
    <meta name="app-url" content="<?= e($appUrl) ?>">
    <title><?= e($title) ?> — SUNUSCORE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/responsive.css')) ?>">
    <!-- PWA Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#071A36">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="SUNUSCORE">
<link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">

<!-- ========================================== -->
<!-- FAVICON & ICÔNES PWA                       -->
<!-- ========================================== -->

<!-- Classique (PC) -->
<link rel="icon" type="image/x-icon" href="../public/assets/icons/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">

<!-- Apple (iPhone / iPad) -->
<link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png">
<meta name="apple-mobile-web-app-title" content="SUNUSCORE">

<!-- Manifest PWA (Android / Chrome) -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#071A36">
</head>
<body>
    <?php require dirname(__DIR__) . '/components/navbar.php'; ?>

    <main class="page">
        <div class="container">
            <?php require $view; ?>
        </div>
    </main>

    <?php require dirname(__DIR__) . '/components/bottom-nav.php'; ?>
    <?php require dirname(__DIR__) . '/components/footer.php'; ?>

    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
    <script src="<?= e(asset('js/api.js')) ?>" defer></script>

    <!-- Bannière d'installation PWA (Android/Chrome) -->
<div id="pwa-install-banner" style="display:none; position:fixed; bottom:20px; left:20px; right:20px; z-index:9999; background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.15); padding:16px; align-items:center; justify-content:space-between; gap:12px; max-width:500px; margin:0 auto; border:1px solid #EAF2FF;">
    <div style="display:flex; align-items:center; gap:12px;">
        <img src="/assets/icons/icon-192.png" alt="SUNUSCORE" style="width:40px; height:40px; border-radius:8px;">
        <div>
            <div style="font-weight:700; font-size:14px; color:#071A36;">Installez SUNUSCORE</div>
            <div style="font-size:12px; color:#64748B;">Suivez les matchs directement sur votre téléphone.</div>
        </div>
    </div>
    <div style="display:flex; gap:8px;">
        <button id="pwa-later-btn" style="background:transparent; border:none; color:#64748B; font-size:13px; cursor:pointer;">Plus tard</button>
        <button id="pwa-install-btn" style="background:#155EEF; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;">Installer</button>
    </div>
    <button id="pwa-close-btn" style="position:absolute; top:-10px; right:-10px; background:#fff; border:1px solid #EAF2FF; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#64748B; font-size:12px;">✕</button>
</div>

<!-- Instructions iOS (Safari) -->
<div id="pwa-ios-guide" style="display:none; position:fixed; bottom:0; left:0; right:0; z-index:9999; background:#fff; border-radius:20px 20px 0 0; box-shadow:0 -4px 20px rgba(0,0,0,0.1); padding:24px; text-align:center; flex-direction:column; align-items:center;">
    <div style="font-size:32px; margin-bottom:12px;">📲</div>
    <h3 style="margin:0 0 8px; color:#071A36; font-size:18px;">Installer SUNUSCORE</h3>
    <p style="margin:0 0 16px; color:#64748B; font-size:14px;">Ajoutez SUNUSCORE à votre écran d'accueil pour un accès rapide.</p>
    <div style="background:#F5F7FB; padding:16px; border-radius:12px; text-align:left; width:100%; margin-bottom:16px;">
        <div style="font-size:14px; margin-bottom:8px;"><strong>1.</strong> Appuyez sur <strong>Partager</strong> <span style="font-size:18px;">⎋</span></div>
        <div style="font-size:14px;"><strong>2.</strong> Choisissez <strong>Sur l'écran d'accueil</strong> <span style="font-size:18px;">➕</span></div>
    </div>
    <button onclick="document.getElementById('pwa-ios-guide').style.display='none'; localStorage.setItem('pwa-ios-dismissed', 'true');" style="background:#155EEF; color:#fff; border:none; padding:14px 24px; border-radius:8px; font-size:15px; font-weight:600; width:100%; cursor:pointer;">J'ai compris</button>
</div>

<!-- Script d'installation -->
<script src="/assets/js/install.js" defer></script>
</body>
</html>