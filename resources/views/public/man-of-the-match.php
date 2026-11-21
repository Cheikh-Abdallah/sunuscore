<?php
/** @var array $motm */
/** @var array $top */
?>
<h1 class="mb-2">Hommes du match</h1>

<?php if (!empty($top)): ?>
<section class="section">
    <h2>Meilleures distinctions</h2>
    <div class="grid grid-2">
        <?php foreach ($top as $i => $t): ?>
            <div class="card">
                <div class="text-muted text-small">#<?= $i + 1 ?></div>
                <div style="font-weight:700;font-size:16px;"><?= e($t['first_name'] . ' ' . $t['last_name']) ?></div>
                <div class="text-small text-muted"><?= e($t['team_name']) ?> · <strong><?= (int) $t['awards_count'] ?></strong> distinction(s)</div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <h2>Désignations récentes</h2>
    <?php if (empty($motm)): ?>
        <?php $title = 'Aucune désignation'; $message = 'Les hommes du match apparaîtront dès la première désignation.'; require dirname(__DIR__) . '/components/empty-state.php'; ?>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($motm as $a): ?>
                <div class="card">
                    <div style="font-weight:700;">
                        🏅 <?= e($a['first_name'] . ' ' . $a['last_name']) ?>
                        <span class="text-muted text-small">— <?= e($a['team_name']) ?></span>
                    </div>
                    <div class="text-small text-muted mt-1">
                        <?= e($a['home_name']) ?> vs <?= e($a['away_name']) ?>
                        · <?= e(date('d/m/Y', strtotime($a['scheduled_at']))) ?>
                    </div>
                    <?php if (!empty($a['reason'])): ?>
                        <div class="text-small mt-1">« <?= e($a['reason']) ?> »</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>