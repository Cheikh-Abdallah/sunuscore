<?php
/** @var array $competition */
/** @var array $matches */
$filter = $_GET['filter'] ?? 'all';
$filters = [
    'all'       => 'Tous',
    'live'      => 'En direct',
    'upcoming'  => 'À venir',
    'finished'  => 'Terminés',
    'postponed' => 'Reportés',
];
?>
<h1 class="mb-1">Matchs</h1>
<p class="text-muted text-small mb-2"><?= e($competition['name']) ?></p>

<div class="chips mb-2">
    <?php foreach ($filters as $k => $label): ?>
        <a class="chip <?= $filter === $k ? 'active' : '' ?>"
           href="<?= e(url('/matches?filter=' . $k)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<?php if (empty($matches)): ?>
    <?php $title = 'Aucun match'; $message = 'Aucun match ne correspond à ce filtre.'; require dirname(__DIR__) . '/components/empty-state.php'; ?>
<?php else: ?>
<div class="grid">
    <?php foreach ($matches as $m): ?>
        <?php require dirname(__DIR__) . '/components/match-card.php'; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>