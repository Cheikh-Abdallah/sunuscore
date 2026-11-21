<?php
/** @var array $matches */
use App\Core\Csrf;
$appUrl = rtrim((string) config('app.url', ''), '/');
$csrf = Csrf::field();
$filter = $_GET['filter'] ?? 'all';
$filters = ['all'=>'Tous','upcoming'=>'À venir','live'=>'En direct','finished'=>'Terminés','postponed'=>'Reportés'];
?>
<div class="admin-toolbar">
    <a href="<?= e($appUrl) ?>/admin/matches/create" class="btn-primary">+ Nouveau match</a>
    <div style="margin-left:auto;display:flex;gap:6px;">
        <?php foreach ($filters as $k => $lbl): ?>
            <a class="btn-sm <?= $filter === $k ? 'btn-primary' : '' ?>"
               href="<?= e($appUrl) ?>/admin/matches?filter=<?= e($k) ?>"><?= e($lbl) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php require dirname(__DIR__, 2) . '/components/admin-flash.php'; ?>

<?php if (empty($matches)): ?>
    <div class="admin-empty">Aucun match.</div>
<?php else: ?>
<table class="admin-table">
    <thead>
        <tr>
            <th>#</th><th>Compétition</th><th>Domicile</th><th>Extérieur</th>
            <th>Date</th><th>Terrain</th><th>Statut</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($matches as $m): ?>
        <tr>
            <td><?= (int) $m['id'] ?></td>
            <td><small><?= e($m['competition_name']) ?></small></td>
            <td><strong><?= e($m['home_team_name']) ?></strong></td>
            <td><strong><?= e($m['away_team_name']) ?></strong></td>
            <td><?= e(date('d/m/Y H\hi', strtotime($m['scheduled_at']))) ?></td>
            <td><?= e($m['venue'] ?? '—') ?></td>
            <td>
                <span class="badge badge-<?= e($m['status']) ?>"><?= e(strtoupper($m['status'])) ?></span>
                <?php if (in_array($m['status'], ['finished','live'], true)): ?>
                    <br><small><?= (int) $m['home_score'] ?> - <?= (int) $m['away_score'] ?></small>
                <?php endif; ?>
            </td>
            <td class="actions">
                <?php if (!in_array($m['status'], ['live','half_time','suspended','finished'], true)): ?>
                    <a class="btn-sm" href="<?= e($appUrl) ?>/admin/matches/<?= (int) $m['id'] ?>/edit">Modifier</a>
                    <form method="POST" action="<?= e($appUrl) ?>/admin/matches/<?= (int) $m['id'] ?>/delete"
                          onsubmit="return confirm('Supprimer ce match ?')" style="display:inline">
                        <?= $csrf ?>
                        <button type="submit" class="btn-sm btn-danger">Supprimer</button>
                    </form>
                <?php else: ?>
                    <span class="text-muted text-small">Verrouillé</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>