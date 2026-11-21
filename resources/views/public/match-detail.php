<?php
/** @var array $match */
/** @var array $events */
/** @var array|null $award */
$appUrl = rtrim((string) config('app.url', ''), '/');
$isLive = in_array($match['status'], ['live','half_time','suspended'], true);
$isFinished = $match['status'] === 'finished';
$home = $match['home_team_name'];
$away = $match['away_team_name'];
?>
<div id="live-root"
     data-match-id="<?= (int) $match['id'] ?>"
     data-live="<?= $isLive ? '1' : '0' ?>"
     data-api="<?= e($appUrl) ?>/matches/<?= (int) $match['id'] ?>/live">

    <h1 class="mb-1"><?= e($home) ?> <span style="color:var(--c-muted);font-weight:400">vs</span> <?= e($away) ?></h1>
    <p class="text-muted text-small mb-2">
        <?= e($match['competition_name']) ?>
        · <?= e(date('d/m/Y H\hi', strtotime($match['scheduled_at']))) ?>
        <?php if ($match['venue']): ?> · <?= e($match['venue']) ?><?php endif; ?>
    </p>

    <div class="hero-live mb-3">
        <div class="hero-teams">
            <div class="hero-team">
                <div class="logo"><?= e(strtoupper(mb_substr($home, 0, 2))) ?></div>
                <div class="name"><?= e($home) ?></div>
            </div>
            <div style="text-align:center;">
                <div class="hero-score" id="live-score"><?= (int) $match['home_score'] ?> - <?= (int) $match['away_score'] ?></div>
                <div class="hero-meta" id="live-status">
                    <?php if ($isLive): ?>
                        ● <?= (int) $match['current_minute'] ?>' EN DIRECT
                    <?php elseif ($isFinished): ?>
                        TERMINÉ
                    <?php else: ?>
                        À VENIR
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-team">
                <div class="logo"><?= e(strtoupper(mb_substr($away, 0, 2))) ?></div>
                <div class="name"><?= e($away) ?></div>
            </div>
        </div>
    </div>

    <?php if ($award): ?>
    <div class="card mb-3">
        <div class="text-small text-muted">🏅 Homme du match</div>
        <div style="font-weight:700;font-size:16px;">
            <?= e($award['first_name'] . ' ' . $award['last_name']) ?>
            <span class="text-muted text-small">— <?= e($award['team_name']) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <section class="section">
        <h2>Événements</h2>
        <div id="live-events">
            <?php if (empty($events)): ?>
                <div class="empty-state">
                    <h3>Aucun événement</h3>
                    <p>Les événements de ce match seront affichés dès leur saisie.</p>
                </div>
            <?php else: ?>
                <?php
                $filtered = array_filter($events, fn($e) => (int) $e['is_voided'] === 0);
                if (empty($filtered)): ?>
                    <div class="empty-state"><h3>Aucun événement</h3></div>
                <?php else: ?>
                    <div class="card">
                        <?php foreach ($filtered as $ev): ?>
                            <div class="timeline-row">
                                <div class="tr-minute"><?= (int) $ev['minute'] ?>'</div>
                                <div class="tr-body">
                                    <div class="tr-title">
                                        <?php
                                        $labels = ['goal'=>'⚽ But','penalty_goal'=>'⚽ Penalty','own_goal'=>'⚽ CSC',
                                                   'yellow_card'=>'🟨 Jaune','red_card'=>'🟥 Rouge','substitution'=>'🔄 Remplacement'];
                                        echo $labels[$ev['event_type']] ?? e($ev['event_type']);
                                        ?>
                                        <span class="text-muted">— <?= e($ev['team_name']) ?></span>
                                    </div>
                                    <?php if ($ev['player_first']): ?>
                                        <div class="text-small text-muted"><?= e($ev['player_first'] . ' ' . $ev['player_last']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($isLive): ?>
    <?php
/** @var array $match */
/** @var array $events */
/** @var array|null $award */
$appUrl = rtrim((string) config('app.url', ''), '/');
$isLive = in_array($match['status'], ['live','half_time','suspended'], true);
$isFinished = $match['status'] === 'finished';
$home = $match['home_team_name'];
$away = $match['away_team_name'];
?>
<div id="live-root"
     data-match-id="<?= (int) $match['id'] ?>"
     data-live="<?= $isLive ? '1' : '0' ?>"
     data-api="<?= e($appUrl) ?>/api/matches/<?= (int) $match['id'] ?>/live">

    <h1 class="mb-1"><?= e($home) ?> <span style="color:var(--c-muted);font-weight:400">vs</span> <?= e($away) ?></h1>
    <p class="text-muted text-small mb-2">
        <?= e($match['competition_name']) ?>
        · <?= e(date('d/m/Y H\hi', strtotime($match['scheduled_at']))) ?>
        <?php if ($match['venue']): ?> · <?= e($match['venue']) ?><?php endif; ?>
    </p>

    <div class="hero-live mb-3">
        <div class="hero-teams">
            <div class="hero-team">
                <div class="logo"><?= e(strtoupper(mb_substr($home, 0, 2))) ?></div>
                <div class="name"><?= e($home) ?></div>
            </div>
            <div style="text-align:center;">
                <div class="hero-score" id="live-score"><?= (int) $match['home_score'] ?> - <?= (int) $match['away_score'] ?></div>
                <div class="hero-meta" id="live-status">
                    <?php if ($isLive): ?>
                        ● <?= (int) $match['current_minute'] ?>' EN DIRECT
                    <?php elseif ($isFinished): ?>
                        TERMINÉ
                    <?php else: ?>
                        À VENIR
                    <?php endif; ?>
                </div>
                <div class="hero-meta" id="live-freshness" style="font-size:11px;opacity:.6;"></div>
            </div>
            <div class="hero-team">
                <div class="logo"><?= e(strtoupper(mb_substr($away, 0, 2))) ?></div>
                <div class="name"><?= e($away) ?></div>
            </div>
        </div>
    </div>

    <?php if ($award): ?>
    <div class="card mb-3">
        <div class="text-small text-muted">🏅 Homme du match</div>
        <div style="font-weight:700;font-size:16px;">
            <?= e($award['first_name'] . ' ' . $award['last_name']) ?>
            <span class="text-muted text-small">— <?= e($award['team_name']) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <section class="section">
        <h2>Événements</h2>
        <div id="live-events">
            <?php if (empty($events)): ?>
                <div class="empty-state">
                    <h3>Aucun événement</h3>
                    <p>Les événements de ce match seront affichés dès leur saisie.</p>
                </div>
            <?php else: ?>
                <?php
                $filtered = array_filter($events, fn($e) => (int) $e['is_voided'] === 0);
                if (empty($filtered)): ?>
                    <div class="empty-state"><h3>Aucun événement</h3></div>
                <?php else: ?>
                    <div class="card">
                        <?php foreach ($filtered as $ev): ?>
                            <div class="timeline-row">
                                <div class="tr-minute"><?= (int) $ev['minute'] ?>'</div>
                                <div class="tr-body">
                                    <div class="tr-title">
                                        <?php
                                        $labels = ['goal'=>'⚽ But','penalty_goal'=>'⚽ Penalty','own_goal'=>'⚽ CSC',
                                                   'yellow_card'=>'🟨 Jaune','red_card'=>'🟥 Rouge','substitution'=>'🔄 Remplacement'];
                                        echo $labels[$ev['event_type']] ?? e($ev['event_type']);
                                        ?>
                                        <span class="text-muted">— <?= e($ev['team_name']) ?></span>
                                    </div>
                                    <?php if ($ev['player_first']): ?>
                                        <div class="text-small text-muted"><?= e($ev['player_first'] . ' ' . $ev['player_last']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<script src="<?= e(asset('js/live-score.js')) ?>" defer></script>
    <?php endif; ?>
</div>