<?php
use App\Core\Auth;
use App\Core\Csrf;

$title = $title ?? 'Administration';
$appUrl = rtrim((string) config('app.url', ''), '/');
$user = Auth::user();
$csrfField = Csrf::field();
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$active = fn(string $prefix): string => str_contains($currentPath, $prefix) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071A36">
    <title><?= e($title) ?> — SUNUSCORE Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e($appUrl) ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= e($appUrl) ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= e($appUrl) ?>/assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-shell">
        <?php require dirname(__DIR__) . '/components/admin-sidebar.php'; ?>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <h1><?= e($title) ?></h1>
                </div>
                <div class="admin-topbar-right">
                    <span class="admin-user">
                        <span class="admin-avatar"><?= e(strtoupper(mb_substr($user['username'] ?? '?', 0, 1))) ?></span>
                        <span class="admin-user-name">
                            <strong><?= e($user['username'] ?? '') ?></strong>
                            <small><?= e(strtoupper($user['role'] ?? '')) ?></small>
                        </span>
                    </span>
                    <form method="POST" action="<?= e($appUrl) ?>/logout" style="display:inline">
                        <?= $csrfField ?>
                        <button type="submit" class="btn-logout" title="Se déconnecter">
                            <span aria-hidden="true">↪</span> Déconnexion
                        </button>
                    </form>
                </div>
            </header>

            <main class="admin-content">
                <?php require $view; ?>
            </main>
        </div>
    </div>
</body>
</html>