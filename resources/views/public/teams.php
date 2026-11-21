<?php
/** @var array $teams */
/** @var array $rankMap */
?>
<h1 class="mb-2">Équipes</h1>

<?php if (empty($teams)): ?>
    <?php $title = 'Aucune équipe'; $message = 'Aucune équipe inscrite pour le moment.'; require dirname(__DIR__) . '/components/empty-state.php'; ?>
<?php else: ?>
<div class="grid grid-2">
    <?php foreach ($teams as $t): ?>
        <?php $rank = $rankMap[$t['id']] ?? null; ?>
        <a href="<?= e(url('/teams/' . (int) $t['id'])) ?>" class="card card-lg card-link">
            <div style="display:flex;gap:12px;align-items:center;">
                <div class="team-mini-logo" style="width:48px;height:48px;font-size:16px;">
                    <?= e(strtoupper(mb_substr($t['name'], 0, 2))) ?>
                </div>
                <div style="min-width:0;">
                    <h3 style="margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($t['name']) ?></h3>
                    <div class="text-muted text-small">
                        <?= e($t['neighborhood'] ?? 'Quartier non renseigné') ?>
                    </div>
                </div>
            </div>
            <?php if ($rank): ?>
                <div class="text-small text-muted mt-2" style="display:flex;justify-content:space-between;">
                    <span><?= (int) $rank['played'] ?> matchs joués</span>
                    <span><strong><?= (int) $rank['points'] ?> pts</strong> · <?= (int) $rank['rank'] ?>e</span>
                </div>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>