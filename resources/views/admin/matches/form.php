<?php
/** @var array|null $match */
/** @var array $competitions */
/** @var array $teams */
use App\Core\Csrf;
use App\Core\Session;

$appUrl = rtrim((string) config('app.url', ''), '/');
$isEdit = $match !== null;
$action = $isEdit ? $appUrl . '/admin/matches/' . $match['id'] : $appUrl . '/admin/matches';
$csrf = Csrf::field();
$old = Session::get('form_old'); Session::remove('form_old');
$errs = Session::get('form_errors'); Session::remove('form_errors');
if ($old === null && $isEdit) $old = $match;
if ($old === null) $old = [];

$v = fn(string $k, string $d = '') => e((string) ($old[$k] ?? $d));
$err = fn(string $k) => isset($errs[$k]) ? '<div class="field-error">' . e($errs[$k]) . '</div>' : '';

$scheduledAt = $v('scheduled_at');
if ($scheduledAt !== '' && str_contains($scheduledAt, ' ')) {
    $scheduledAt = str_replace(' ', 'T', substr($scheduledAt, 0, 16));
}
$callTime = $v('call_time');
if ($callTime !== '' && str_contains($callTime, ' ')) {
    $callTime = str_replace(' ', 'T', substr($callTime, 0, 16));
}
?>
<form method="POST" action="<?= e($action) ?>" class="admin-form">
    <?= $csrf ?>

    <div class="form-row">
        <label>Compétition <span class="req">*</span></label>
        <select name="competition_id" id="competitionSelect" required>
            <option value="">— Sélectionner —</option>
            <?php foreach ($competitions as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (int) $v('competition_id') === (int) $c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= $err('competition_id') ?>
    </div>

    <div class="form-grid-2">
        <div class="form-row">
            <label>Équipe à domicile <span class="req">*</span></label>
            <select name="home_team_id" required>
                <option value="">— Sélectionner —</option>
                <?php foreach ($teams as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= (int) $v('home_team_id') === (int) $t['id'] ? 'selected' : '' ?>>
                        <?= e($t['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= $err('home_team_id') ?>
        </div>
        <div class="form-row">
            <label>Équipe extérieure <span class="req">*</span></label>
            <select name="away_team_id" required>
                <option value="">— Sélectionner —</option>
                <?php foreach ($teams as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= (int) $v('away_team_id') === (int) $t['id'] ? 'selected' : '' ?>>
                        <?= e($t['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= $err('away_team_id') ?>
        </div>
    </div>

    <div class="form-grid-2">
        <div class="form-row">
            <label>Date & heure du coup d'envoi <span class="req">*</span></label>
            <input type="datetime-local" name="scheduled_at" value="<?= e($scheduledAt) ?>" required>
            <?= $err('scheduled_at') ?>
        </div>
        <div class="form-row">
            <label>Heure de convocation</label>
            <input type="datetime-local" name="call_time" value="<?= e($callTime) ?>">
        </div>
    </div>

    <div class="form-grid-3">
        <div class="form-row">
            <label>Journée</label>
            <input type="number" name="matchday" value="<?= $v('matchday') ?>" min="1" max="99">
            <?= $err('matchday') ?>
        </div>
        <div class="form-row">
            <label>Phase</label>
            <input type="text" name="phase" value="<?= $v('phase') ?>" maxlength="60" placeholder="ex: Poules, Quarts">
        </div>
        <div class="form-row">
            <label>Terrain</label>
            <input type="text" name="venue" value="<?= $v('venue') ?>" maxlength="150">
        </div>
    </div>

    <div class="form-row">
        <label>Statut</label>
        <select name="status">
            <?php foreach (['scheduled'=>'Programmé','postponed'=>'Reporté','cancelled'=>'Annulé'] as $k => $lbl): ?>
                <option value="<?= e($k) ?>" <?= $v('status', 'scheduled') === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
            <?php endforeach; ?>
        </select>
        <?= $err('status') ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a href="<?= e($appUrl) ?>/admin/matches" class="btn-secondary">Annuler</a>
    </div>
</form>

<script>
(function () {
    var sel = document.getElementById('competitionSelect');
    if (!sel) return;
    sel.addEventListener('change', function () {
        var id = sel.value;
        var url = new URL(window.location.href);
        if (id) url.searchParams.set('competition_id', id); else url.searchParams.delete('competition_id');
        window.location.href = url.toString();
    });
})();
</script>