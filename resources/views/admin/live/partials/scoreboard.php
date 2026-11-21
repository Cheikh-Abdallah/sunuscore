<?php
/** @var array $match */
?>
<div class="live-scoreboard">
    <div class="ls-team">
        <div class="ls-logo"><?= e(strtoupper(mb_substr($match['home_team_name'], 0, 2))) ?></div>
        <div class="ls-name"><?= e($match['home_team_name']) ?></div>
    </div>
    <div class="ls-center">
        <div class="ls-score"><?= (int) $match['home_score'] ?> - <?= (int) $match['away_score'] ?></div>
        <div class="ls-status">
            <?php if (in_array($match['status'], ['live','half_time','suspended'], true)): ?>
                <span class="badge badge-live">● <?= (int) $match['current_minute'] ?>'</span>
            <?php else: ?>
                <span class="badge badge-<?= e($match['status']) ?>"><?= e(strtoupper($match['status'])) ?></span>
            <?php endif; ?>
        </div>
        <div class="ls-meta text-small text-muted">
            <?= e(date('d/m/Y H\hi', strtotime($match['scheduled_at']))) ?>
            <?php if ($match['venue']): ?> · <?= e($match['venue']) ?><?php endif; ?>
        </div>
    </div>
    <div class="ls-team">
        <div class="ls-logo"><?= e(strtoupper(mb_substr($match['away_team_name'], 0, 2))) ?></div>
        <div class="ls-name"><?= e($match['away_team_name']) ?></div>
    </div>
</div>