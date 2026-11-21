<?php
/** @var array $competitions */
$filter = $_GET['status'] ?? 'all';
$filters = [
    'all'      => 'Toutes',
    'ongoing'  => 'En cours',
    'upcoming' => 'À venir',
    'finished' => 'Terminées',
];
?>
<h1 class="mb-2">Compétitions</h1>

<div class="chips mb-2">
    <?php foreach ($filters as $k => $label): ?>
        <a class="chip <?= $filter === $k ? 'active' : '' ?>"
           href="<?= e(url('/competitions?status=' . $k)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<?php if (empty($competitions)): ?>
    <?php $title = 'Aucune compétition'; $message = 'Aucune compétition ne correspond à ce filtre.'; require dirname(__DIR__) . '/components/empty-state.php'; ?>
<?php else: ?>
<div class="grid grid-2">
    <?php foreach ($competitions as $c): ?>
        <a href="<?= e(url('/matches')) ?>" class="card card-lg card-link">
            <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;">
                <div>
                    <h3 style="margin:0"><?= e($c['name']) ?></h3>
                    <div class="text-muted text-small">
                        <?= e($c['location'] ?? '—') ?> · <?= (int) $c['teams_count'] ?> équipes
                    </div>
                </div>
                <span class="badge badge-<?= e($c['status']) ?>"><?= e(strtoupper($c['status'])) ?></span>
            </div>
            <div class="text-small text-muted mt-2">
                <?php if ($c['start_date']): ?>
                    Du <?= e(date('d/m/Y', strtotime($c['start_date']))) ?>
                    <?php if ($c['end_date']): ?> au <?= e(date('d/m/Y', strtotime($c['end_date']))) ?><?php endif; ?>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>