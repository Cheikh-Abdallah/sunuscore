<?php
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$isActive = fn(string $prefix): string => str_contains($currentPath, $prefix) ? 'active' : '';
?>
<header class="navbar">
    <div class="container">
        <a href="<?= e(url('/')) ?>" class="navbar-brand" aria-label="SUNUSCORE accueil">
            <span class="badge-s">S</span>
            <span>SUNUSCORE</span>
        </a>
        <nav class="navbar-links" aria-label="Navigation principale">
            <a href="<?= e(url('/')) ?>"            class="<?= $isActive('/') && $currentPath === '/sunuscore/public/' ? 'active' : '' ?>">Accueil</a>
            <a href="<?= e(url('/competitions')) ?>" class="<?= $isActive('/competitions') ?>">Compétitions</a>
            <a href="<?= e(url('/matches')) ?>"      class="<?= $isActive('/matches') ?>">Matchs</a>
            <a href="<?= e(url('/rankings')) ?>"     class="<?= $isActive('/rankings') ?>">Classement</a>
            <a href="<?= e(url('/teams')) ?>"        class="<?= $isActive('/teams') ?>">Équipes</a>
            <a href="<?= e(url('/players')) ?>"      class="<?= $isActive('/players') ?>">Joueurs</a>
        </nav>
    </div>
</header>