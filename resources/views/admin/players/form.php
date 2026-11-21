<?php
/** @var array|null $player */
use App\Core\Csrf;
use App\Core\Session;

$appUrl = rtrim((string) config('app.url', ''), '/');
$isEdit = $player !== null;
$action = $isEdit ? $appUrl . '/admin/players/' . $player['id'] : $appUrl . '/admin/players';
$csrf = Csrf::field();
$old = Session::get('form_old'); Session::remove('form_old');
$errs = Session::get('form_errors'); Session::remove('form_errors');
if ($old === null && $isEdit) $old = $player;
if ($old === null) $old = [];
$v = fn(string $k, string $d = '') => e((string) ($old[$k] ?? $d));
$err = fn(string $k) => isset($errs[$k]) ? '<div class="field-error">' . e($errs[$k]) . '</div>' : '';
?>
<form method="POST" action="<?= e($action) ?>" class="admin-form">
    <?= $csrf ?>
    <div class="form-grid-2">
        <div class="form-row">
            <label>Prénom <span class="req">*</span></label>
            <input type="text" name="first_name" value="<?= $v('first_name') ?>" required maxlength="80"><?= $err('first_name') ?>
        </div>
        <div class="form-row">
            <label>Nom <span class="req">*</span></label>
            <input type="text" name="last_name" value="<?= $v('last_name') ?>" required maxlength="80"><?= $err('last_name') ?>
        </div>
    </div>
    <div class="form-grid-2">
        <div class="form-row">
            <label>Date de naissance</label>
            <input type="date" name="birth_date" value="<?= $v('birth_date') ?>">
        </div>
        <div class="form-row">
            <label>Téléphone</label>
            <input type="text" name="phone" value="<?= $v('phone') ?>" maxlength="30">
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a href="<?= e($appUrl) ?>/admin/players" class="btn-secondary">Annuler</a>
    </div>
</form>