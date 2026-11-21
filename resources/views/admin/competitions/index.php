<?php
/** @var array $competitions */
use App\Core\Csrf;
$appUrl = rtrim((string) config('app.url', ''), '/');
$csrf = Csrf::field();
?>
<div class="admin-toolbar">
    <a href="<?= e($appUrl) ?>/admin/competitions/create" class="btn-primary">+ Nouvelle compétition</a>
</div>

<?php require dirname(__DIR__, 2) . '/components/admin-flash.php'; ?>

<?php if (empty($competitions)): ?>
    <div class="admin-empty">Aucune compétition.</div>
<?php else: ?>
<table class="admin-table">
    <thead>
        <tr>
            <th>#</th><th>Nom</th><th>Slug</th><th>Statut</th>
            <th class="num">Équipes</th><th>Début</th><th>Fin</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($competitions as $c): ?>
        <tr>
            <td><?= (int) $c['id'] ?></td>
            <td><strong><?= e($c['name']) ?></strong></td>
            <td><code><?= e($c['slug']) ?></code></td>
            <td><span class="badge badge-<?= e($c['status']) ?>"><?= e(strtoupper($c['status'])) ?></span></td>
            <td class="num"><?= (int) $c['teams_count'] ?></td>
            <td><?= $c['start_date'] ? e(date('d/m/Y', strtotime($c['start_date']))) : '—' ?></td>
            <td><?= $c['end_date'] ? e(date('d/m/Y', strtotime($c['end_date']))) : '—' ?></td>
            <td class="actions">
                <a class="btn-sm" href="<?= e($appUrl) ?>/admin/competitions/<?= (int) $c['id'] ?>/edit">Modifier</a>
                <form method="POST" action="<?= e($appUrl) ?>/admin/competitions/<?= (int) $c['id'] ?>/delete"
                      onsubmit="return confirm('Supprimer cette compétition ?')" style="display:inline">
                    <?= $csrf ?>
                    <button type="submit" class="btn-sm btn-danger">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>