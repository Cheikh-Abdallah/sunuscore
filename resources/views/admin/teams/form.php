<?php
/** @var array|null $team */
use App\Core\Csrf;
use App\Core\Session;

$appUrl = rtrim((string) config('app.url', ''), '/');
$isEdit = $team !== null;
$action = $isEdit ? $appUrl . '/admin/teams/' . $team['id'] : $appUrl . '/admin/teams';
$csrf = Csrf::field();
$old = Session::get('form_old'); Session::remove('form_old');
$errs = Session::get('form_errors'); Session::remove('form_errors');
if ($old === null && $isEdit) $old = $team;
if ($old === null) $old = [];
$v = fn(string $k, string $d = '') => e((string) ($old[$k] ?? $d));
$err = fn(string $k) => isset($errs[$k]) ? '<div class="field-error">' . e($errs[$k]) . '</div>' : '';
?>
<form method="POST" action="<?= e($action) ?>" class="admin-form">
    <?= $csrf ?>
    <div class="form-grid-2">
        <div class="form-row">
            <label>Nom <span class="req">*</span></label>
            <input type="text" name="name" value="<?= $v('name') ?>" required maxlength="120"><?= $err('name') ?>
        </div>
        <div class="form-row">
            <label>Slug <span class="req">*</span></label>
            <input type="text" name="slug" value="<?= $v('slug') ?>" required maxlength="140" pattern="[a-z0-9-]+"><?= $err('slug') ?>
        </div>
    </div>
    <div class="form-row">
        <label>Quartier</label>
        <input type="text" name="neighborhood" value="<?= $v('neighborhood') ?>" maxlength="120">
    </div>
    <div class="form-grid-2">
        <div class="form-row">
            <label>Président</label>
            <input type="text" name="president_name" value="<?= $v('president_name') ?>" maxlength="120">
        </div>
        <div class="form-row">
            <label>Entraîneur</label>
            <input type="text" name="coach_name" value="<?= $v('coach_name') ?>" maxlength="120">
        </div>
    </div>
    <div class="form-grid-3">
        <div class="form-row">
            <label>Année de création</label>
            <input type="number" name="founded_year" value="<?= $v('founded_year') ?>" min="1900" max="2100">
        </div>
        <div class="form-row">
            <label>Téléphone</label>
            <input type="text" name="contact_phone" value="<?= $v('contact_phone') ?>" maxlength="30">
        </div>
        <div class="form-row">
            <label>Email</label>
            <input type="email" name="contact_email" value="<?= $v('contact_email') ?>" maxlength="190"><?= $err('contact_email') ?>
        </div>
    </div>
    <div class="form-row">
        <label>Description</label>
        <textarea name="description" rows="3"><?= $v('description') ?></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a href="<?= e($appUrl) ?>/admin/teams" class="btn-secondary">Annuler</a>
    </div>
</form>