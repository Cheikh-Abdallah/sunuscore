<?php
/** @var array $users */
$appUrl = rtrim((string) config('app.url', ''), '/');
?>
<?php require dirname(__DIR__, 2) . '/components/admin-flash.php'; ?>

<table class="admin-table">
    <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Rôle</th><th>Actif</th><th>Dernière connexion</th></tr></thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= (int) $u['id'] ?></td>
            <td><strong><?= e($u['username']) ?></strong></td>
            <td><?= e($u['email']) ?></td>
            <td><span class="badge badge-<?= $u['role_code'] === 'super_admin' ? 'ongoing' : 'draft' ?>"><?= e($u['role_label']) ?></span></td>
            <td><?= (int) $u['is_active'] === 1 ? '✅' : '❌' ?></td>
            <td><?= $u['last_login_at'] ? e(date('d/m/Y H\hi', strtotime($u['last_login_at']))) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>