<?php
/** @var array $match */
/** @var array $homeRoster */
/** @var array $awayRoster */
/** @var array $events */
/** @var array|null $award */
use App\Core\Csrf;

$appUrl = rtrim((string) config('app.url', ''), '/');
$csrf = Csrf::field();
$isLive = in_array($match['status'], ['live','half_time','suspended'], true);
?>
<?php require dirname(__DIR__, 2) . '/components/admin-flash.php'; ?>

<?php require __DIR__ . '/partials/scoreboard.php'; ?>

<?php if (!$isLive && !in_array($match['status'], ['finished'], true)): ?>
    <div class="live-actions mb-3">
        <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/start" style="display:inline">
            <?= $csrf ?>
            <button class="btn-primary btn-big">▶ Démarrer le match</button>
        </form>
    </div>
<?php endif; ?>

<?php if ($isLive): ?>
<div class="live-grid">
    <div class="live-panel">
        <h3>But</h3>
        <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/goal" class="live-form">
            <?= $csrf ?>
            <label>Équipe
                <select name="team_id" required>
                    <option value="<?= (int) $match['home_team_id'] ?>"><?= e($match['home_team_name']) ?></option>
                    <option value="<?= (int) $match['away_team_id'] ?>"><?= e($match['away_team_name']) ?></option>
                </select>
            </label>
            <label>Type
                <select name="event_type">
                    <option value="goal">But normal</option>
                    <option value="penalty_goal">Penalty</option>
                    <option value="own_goal">Contre son camp</option>
                </select>
            </label>
            <label>Buteur
                <select name="player_id">
                    <option value="">— Sans joueur —</option>
                    <?php foreach ($homeRoster as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?> (<?= e($match['home_team_name']) ?>)</option>
                    <?php endforeach; ?>
                    <?php foreach ($awayRoster as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?> (<?= e($match['away_team_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Passeur
                <select name="assist_player_id">
                    <option value="">—</option>
                    <?php foreach (array_merge($homeRoster, $awayRoster) as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="grid-2-inline">
                <label>Minute <input type="number" name="minute" min="1" max="130" value="<?= (int) $match['current_minute'] ?>" required></label>
                <label>+ <input type="number" name="extra" min="0" max="20" value="0"></label>
            </div>
            <button class="btn-primary">+ Enregistrer le but</button>
        </form>
    </div>

    <div class="live-panel">
        <h3>Carton</h3>
        <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/card" class="live-form">
            <?= $csrf ?>
            <label>Équipe
                <select name="team_id" required>
                    <option value="<?= (int) $match['home_team_id'] ?>"><?= e($match['home_team_name']) ?></option>
                    <option value="<?= (int) $match['away_team_id'] ?>"><?= e($match['away_team_name']) ?></option>
                </select>
            </label>
            <label>Type
                <select name="event_type">
                    <option value="yellow_card">Jaune</option>
                    <option value="red_card">Rouge</option>
                </select>
            </label>
            <label>Joueur
                <select name="player_id">
                    <option value="">— Sans joueur —</option>
                    <?php foreach (array_merge($homeRoster, $awayRoster) as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Motif <input type="text" name="reason" maxlength="200"></label>
            <div class="grid-2-inline">
                <label>Minute <input type="number" name="minute" min="1" max="130" value="<?= (int) $match['current_minute'] ?>" required></label>
                <label>+ <input type="number" name="extra" min="0" max="20" value="0"></label>
            </div>
            <button class="btn-primary">+ Enregistrer</button>
        </form>
    </div>

    <div class="live-panel">
        <h3>Remplacement</h3>
        <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/substitution" class="live-form">
            <?= $csrf ?>
            <label>Équipe
                <select name="team_id" required>
                    <option value="<?= (int) $match['home_team_id'] ?>"><?= e($match['home_team_name']) ?></option>
                    <option value="<?= (int) $match['away_team_id'] ?>"><?= e($match['away_team_name']) ?></option>
                </select>
            </label>
            <label>Sortant
                <select name="player_out_id" required>
                    <?php foreach (array_merge($homeRoster, $awayRoster) as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Entrant
                <select name="player_in_id" required>
                    <?php foreach (array_merge($homeRoster, $awayRoster) as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="grid-2-inline">
                <label>Minute <input type="number" name="minute" min="1" max="130" value="<?= (int) $match['current_minute'] ?>" required></label>
                <label>+ <input type="number" name="extra" min="0" max="20" value="0"></label>
            </div>
            <button class="btn-primary">+ Enregistrer</button>
        </form>
    </div>

    <div class="live-panel">
        <h3>Contrôle</h3>
        <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/minute" class="live-form">
            <?= $csrf ?>
            <label>Minute courante
                <input type="number" name="minute" min="0" max="130" value="<?= (int) $match['current_minute'] ?>">
            </label>
            <button class="btn-secondary">Mettre à jour</button>
        </form>
        <hr>
        <?php if ($match['status'] === 'live'): ?>
            <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/half-time" style="display:inline">
                <?= $csrf ?><button class="btn-secondary">Mi-temps</button>
            </form>
            <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/suspend" style="display:inline">
                <?= $csrf ?><button class="btn-secondary">Suspendre</button>
            </form>
        <?php elseif (in_array($match['status'], ['half_time','suspended'], true)): ?>
            <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/resume" style="display:inline">
                <?= $csrf ?><button class="btn-secondary">Reprendre</button>
            </form>
        <?php endif; ?>
        <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/finish"
              onsubmit="return confirm('Terminer définitivement ce match ?')" style="display:inline">
            <?= $csrf ?><button class="btn-danger btn-sm">Terminer</button>
        </form>
    </div>
</div>
<?php endif; ?>

<section class="admin-card mt-3">
    <div class="admin-card-head"><h2>Homme du match</h2></div>
    <?php if ($award): ?>
        <div class="text-small text-muted mb-2">
            Actuellement : <strong><?= e($award['first_name'] . ' ' . $award['last_name']) ?></strong>
            (<?= e($award['team_name']) ?>)
        </div>
    <?php endif; ?>
    <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $match['id'] ?>/award" class="live-form">
        <?= $csrf ?>
        <label>Équipe
            <select name="team_id" required>
                <option value="<?= (int) $match['home_team_id'] ?>"><?= e($match['home_team_name']) ?></option>
                <option value="<?= (int) $match['away_team_id'] ?>"><?= e($match['away_team_name']) ?></option>
            </select>
        </label>
        <label>Joueur
            <select name="player_id" required>
                <?php foreach (array_merge($homeRoster, $awayRoster) as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Motif <input type="text" name="reason" maxlength="200"></label>
        <button class="btn-primary">Désigner</button>
    </form>
</section>

<section class="admin-card mt-3">
    <div class="admin-card-head"><h2>Chronologie</h2></div>
    <?php require __DIR__ . '/partials/timeline.php'; ?>
</section>

<?php if (!in_array($match['status'], ['finished'], true)): ?>
<a class="btn-secondary mt-2" href="<?= e($appUrl) ?>/admin/matches">← Retour aux matchs</a>
<?php endif; ?>