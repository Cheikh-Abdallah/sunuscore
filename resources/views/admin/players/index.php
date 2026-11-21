<?php
/** @var array $players */
use App\Core\Csrf;
$appUrl = rtrim((string) config('app.url', ''), '/');
$csrf = Csrf::field();
?>
<div class="admin-toolbar">
    <a href="<?= e($appUrl) ?>/admin/players/create" class="btn-primary">+ Nouveau joueur</a>
</div>
<?php require dirname(__DIR__, 2) . '/components/admin-flash.php'; ?>

<?php if (empty($players)): ?>
    <div class="admin-empty">Aucun joueur.</div>
<?php else: ?>
<table class="admin-table">
    <thead><tr><th>#</th><th>Prénom</th><th>Nom</th><th>Naissance</th><th>Téléphone</th><th>Actions</th></tr></thead>
    <tbody>
        <?php foreach ($players as $p): ?>
        <tr>
            <td><?= (int) $p['id'] ?></td>
            <td><?= e($p['first_name']) ?></td>
            <td><strong><?= e($p['last_name']) ?></strong></td>
            <td><?= $p['birth_date'] ? e(date('d/m/Y', strtotime($p['birth_date']))) : '—' ?></td>
            <td><?= e($p['phone'] ?? '—') ?></td>
            <td class="actions">
                <a class="btn-sm" href="<?= e($appUrl) ?>/admin/players/<?= (int) $p['id'] ?>/edit">Modifier</a>
                <form method="POST" action="<?= e($appUrl) ?>/admin/players/<?= (int) $p['id'] ?>/delete"
                      onsubmit="return confirm('Supprimer ce joueur ?')" style="display:inline">
                    <?= $csrf ?>
                    <button type="submit" class="btn-sm btn-danger">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>