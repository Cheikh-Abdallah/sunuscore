<?php
/** @var array|null $competition */
/** @var array $errors */
use App\Core\Csrf;
use App\Core\Session;

$appUrl = rtrim((string) config('app.url', ''), '/');
$isEdit = $competition !== null;
$action = $isEdit ? $appUrl . '/admin/competitions/' . $competition['id'] : $appUrl . '/admin/competitions';
$csrf = Csrf::field();
$old = Session::get('form_old'); Session::remove('form_old');
$errs = Session::get('form_errors'); Session::remove('form_errors');
if ($old === null && $isEdit) $old = $competition;
if ($old === null) $old = [];
$v = fn(string $k, string $d = '') => e((string) ($old[$k] ?? $d));
$err = fn(string $k) => isset($errs[$k]) ? '<div class="field-error">' . e($errs[$k]) . '</div>' : '';
?>
<form method="POST" action="<?= e($action) ?>" class="admin-form">
    <?= $csrf ?>
    <div class="form-grid-2">
        <div class="form-row">
            <label>Nom <span class="req">*</span></label>
            <input type="text" name="name" value="<?= $v('name') ?>" required maxlength="150">
            <?= $err('name') ?>
        </div>
        <div class="form-row">
            <label>Slug <span class="req">*</span></label>
            <input type="text" name="slug" value="<?= $v('slug') ?>" required maxlength="170" pattern="[a-z0-9-]+">
            <?= $err('slug') ?>
        </div>
    </div>
    <div class="form-row">
        <label>Lieu</label>
        <input type="text" name="location" value="<?= $v('location') ?>" maxlength="150">
    </div>
    <div class="form-grid-2">
        <div class="form-row">
            <label>Date de début</label>
            <input type="date" name="start_date" value="<?= $v('start_date') ?>">
        </div>
        <div class="form-row">
            <label>Date de fin</label>
            <input type="date" name="end_date" value="<?= $v('end_date') ?>">
        </div>
    </div>
    <div class="form-grid-3">
        <div class="form-row">
            <label>Points victoire</label>
            <input type="number" name="points_win" value="<?= $v('points_win', '3') ?>" min="0" max="9">
        </div>
        <div class="form-row">
            <label>Points nul</label>
            <input type="number" name="points_draw" value="<?= $v('points_draw', '1') ?>" min="0" max="9">
        </div>
        <div class="form-row">
            <label>Points défaite</label>
            <input type="number" name="points_loss" value="<?= $v('points_loss', '0') ?>" min="0" max="9">
        </div>
    </div>
    <div class="form-row">
        <label>Statut</label>
        <select name="status">
            <?php foreach (['draft'=>'Brouillon','upcoming'=>'À venir','ongoing'=>'En cours','finished'=>'Terminée','archived'=>'Archivée'] as $k => $lbl): ?>
                <option value="<?= e($k) ?>" <?= $v('status') === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
            <?php endforeach; ?>
        </select>
        <?= $err('status') ?>
    </div>
    <div class="form-row">
        <label>Description</label>
        <textarea name="description" rows="4"><?= $v('description') ?></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a href="<?= e($appUrl) ?>/admin/competitions" class="btn-secondary">Annuler</a>
    </div>
</form>