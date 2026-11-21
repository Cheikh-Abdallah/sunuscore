<?php
$appUrl = rtrim((string) config('app.url', ''), '/');
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$active = fn(string $prefix): string => str_contains($currentPath, $prefix) ? 'active' : '';
$role = \App\Core\Auth::role();
?>
<aside class="admin-sidebar">
    <div class="admin-brand">
        <span class="admin-brand-badge">S</span>
        <span class="admin-brand-name">SUNUSCORE</span>
    </div>

    <nav class="admin-nav" aria-label="Navigation admin">
        <div class="admin-nav-section">Général</div>
        <a href="<?= e($appUrl) ?>/admin" class="<?= $active('/admin') && !$active('/admin/') ? 'active' : '' ?>">
            <span class="icon">📊</span> Tableau de bord
        </a>

        <div class="admin-nav-section">Compétition</div>
        <a href="<?= e($appUrl) ?>/admin/competitions" class="<?= $active('/admin/competitions') ?>">
            <span class="icon">🏆</span> Compétitions
        </a>
        <a href="<?= e($appUrl) ?>/admin/teams" class="<?= $active('/admin/teams') ?>">
            <span class="icon">🛡️</span> Équipes
        </a>
        <a href="<?= e($appUrl) ?>/admin/players" class="<?= $active('/admin/players') ?>">
            <span class="icon">👤</span> Joueurs
        </a>
        <a href="<?= e($appUrl) ?>/admin/matches" class="<?= $active('/admin/matches') ?>">
            <span class="icon">⚽</span> Matchs
        </a>

        <?php if ($role === 'super_admin'): ?>
        <div class="admin-nav-section">Système</div>
        <a href="<?= e($appUrl) ?>/admin/users" class="<?= $active('/admin/users') ?>">
            <span class="icon">🔐</span> Utilisateurs
        </a>
        <?php endif; ?>

        <div class="admin-nav-section">Rapports</div>
<a href="<?= e($appUrl) ?>/statistics" target="_blank" rel="noopener">
    <span class="icon">📊</span> Statistiques
</a>
<a href="<?= e($appUrl) ?>/top-scorers" target="_blank" rel="noopener">
    <span class="icon">⚽</span> Buteurs
</a>
<a href="<?= e($appUrl) ?>/man-of-the-match" target="_blank" rel="noopener">
    <span class="icon">🏅</span> Hommes du match
</a>
        <div class="admin-nav-section">Public</div>
        <a href="<?= e($appUrl) ?>/" target="_blank" rel="noopener">
            <span class="icon">🌐</span> Voir le site
        </a>
    </nav>
</aside>