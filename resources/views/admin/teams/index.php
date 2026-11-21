<?php
/** @var array $teams */
use App\Core\Csrf;
$appUrl = rtrim((string) config('app.url', ''), '/');
$csrf = Csrf::field();
?>
<div class="admin-toolbar">
    <a href="<?= e($appUrl) ?>/admin/teams/create" class="btn-primary">+ Nouvelle équipe</a>
</div>
<?php require dirname(__DIR__, 2) . '/components/admin-flash.php'; ?>

<?php if (empty($teams)): ?>
    <div class="admin-empty">Aucune équipe.</div>
<?php else: ?>
<table class="admin-table">
    <thead>
        <tr><th>#</th><th>Nom</th><th>Quartier</th><th>Président</th><th>Compétitions</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($teams as $t): ?>
        <tr>
            <td><?= (int) $t['id'] ?></td>
            <td><strong><?= e($t['name']) ?></strong></td>
            <td><?= e($t['neighborhood'] ?? '—') ?></td>
            <td><?= e($t['president_name'] ?? '—') ?></td>
            <td class="num"><?= (int) $t['competitions_count'] ?></td>
            <td class="actions">
                <a class="btn-sm" href="<?= e($appUrl) ?>/admin/teams/<?= (int) $t['id'] ?>/edit">Modifier</a>
                <form method="POST" action="<?= e($appUrl) ?>/admin/teams/<?= (int) $t['id'] ?>/delete"
                      onsubmit="return confirm('Supprimer cette équipe ?')" style="display:inline">
                    <?= $csrf ?>
                    <button type="submit" class="btn-sm btn-danger">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>