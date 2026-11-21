<?php
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$active = fn(string $prefix): string => str_contains($currentPath, $prefix) ? 'active' : '';
?>
<nav class="bottom-nav" aria-label="Navigation mobile">
    <a href="<?= e(url('/')) ?>" class="<?= $active('/') && $currentPath === '/sunuscore/public/' ? 'active' : '' ?>">
        <span class="icon">🏠</span><span>Accueil</span>
    </a>
    <a href="<?= e(url('/matches')) ?>" class="<?= $active('/matches') ?>">
        <span class="icon">⚽</span><span>Matchs</span>
    </a>
    <a href="<?= e(url('/rankings')) ?>" class="<?= $active('/rankings') ?>">
        <span class="icon">🏆</span><span>Class.</span>
    </a>
    <a href="<?= e(url('/statistics')) ?>" class="<?= $active('/statistics') ?>">
        <span class="icon">📊</span><span>Stats</span>
    </a>
</nav>