<?php
/** @var array $players */
?>
<h1 class="mb-2">Joueurs</h1>

<?php if (empty($players)): ?>
    <?php $title = 'Aucun joueur'; $message = 'Les joueurs inscrits apparaîtront ici.'; require dirname(__DIR__) . '/components/empty-state.php'; ?>
<?php else: ?>
<div class="card">
    <div class="table-responsive">
        <table class="data">
            <thead>
                <tr><th>#</th><th>Joueur</th><th>Équipe</th><th>Poste</th></tr>
            </thead>
            <tbody>
            <?php foreach ($players as $p): ?>
                <tr>
                    <td class="num"><?= $p['jersey_number'] !== null ? (int) $p['jersey_number'] : '—' ?></td>
                    <td><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td>
                    <td><?= e($p['team_name']) ?></td>
                    <td><?= e($p['position'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>