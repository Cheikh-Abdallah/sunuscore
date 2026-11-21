<?php
/** @var array $team */
/** @var array $roster */
/** @var array|null $teamRank */
?>
<h1 class="mb-1"><?= e($team['name']) ?></h1>
<p class="text-muted text-small mb-2">
    <?= e($team['neighborhood'] ?? '') ?>
    <?php if ($team['coach_name']): ?> · Entraîneur : <?= e($team['coach_name']) ?><?php endif; ?>
</p>

<?php if ($teamRank): ?>
<div class="card mb-2">
    <div class="grid grid-3">
        <div><div class="text-muted text-small">Classement</div><div style="font-weight:900;font-size:22px;"><?= (int) $teamRank['rank'] ?>e</div></div>
        <div><div class="text-muted text-small">Points</div><div style="font-weight:900;font-size:22px;"><?= (int) $teamRank['points'] ?></div></div>
        <div><div class="text-muted text-small">Matchs</div><div style="font-weight:900;font-size:22px;"><?= (int) $teamRank['played'] ?></div></div>
    </div>
</div>
<?php endif; ?>

<section class="section">
    <h2>Effectif</h2>
    <?php if (empty($roster)): ?>
        <?php $title = 'Effectif non renseigné'; $message = 'Les joueurs seront affichés dès leur inscription.'; require dirname(__DIR__) . '/components/empty-state.php'; ?>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($roster as $p): ?>
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                        <div style="font-weight:700;"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></div>
                        <div class="text-muted text-small">
                            <?= $p['jersey_number'] !== null ? '#' . (int) $p['jersey_number'] : '—' ?>
                            · <?= e($p['position'] ?? '') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>