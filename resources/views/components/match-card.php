<?php
/**
 * Composant carte de match.
 * Variables attendues : $m (ligne de la table matches jointe)
 */
$home = $m['home_team_name'] ?? '—';
$away = $m['away_team_name'] ?? '—';
$hs   = (int) ($m['home_score'] ?? 0);
$as   = (int) ($m['away_score'] ?? 0);
$status = $m['status'];
$isLive = in_array($status, ['live','half_time','suspended'], true);
$isFinished = $status === 'finished';

$badgeClass = match($status) {
    'live','half_time' => 'badge-live',
    'scheduled'        => 'badge-scheduled',
    'finished'         => 'badge-finished',
    'suspended'        => 'badge-suspended',
    'postponed'        => 'badge-postponed',
    'cancelled'        => 'badge-cancelled',
    default            => 'badge-draft',
};
$statusLabel = match($status) {
    'live'       => 'EN DIRECT',
    'half_time'  => 'MI-TEMPS',
    'scheduled'  => 'À VENIR',
    'finished'   => 'TERMINÉ',
    'suspended'  => 'SUSPENDU',
    'postponed'  => 'REPORTÉ',
    'cancelled'  => 'ANNULÉ',
    default      => strtoupper($status),
};

$initials = fn(string $s): string => strtoupper(mb_substr(trim($s), 0, 2));

$dateLabel = '';
if (!empty($m['scheduled_at'])) {
    $ts = strtotime($m['scheduled_at']);
    $dateLabel = date('d/m/Y à H\hi', $ts);
}

$link = url('/matches/' . (int) $m['id']);
?>
<a href="<?= e($link) ?>" class="match-card">
    <div class="match-card-head">
        <span><?= e($m['competition_name'] ?? '') ?> · J<?= (int) ($m['matchday'] ?? 0) ?></span>
        <span class="badge <?= e($badgeClass) ?>"><?= e($statusLabel) ?></span>
    </div>
    <div class="match-card-body">
        <div class="team-mini">
            <div class="team-mini-logo">
                <?php if (!empty($m['home_team_logo'])): ?>
                    <img src="<?= e(url($m['home_team_logo'])) ?>" alt="">
                <?php else: ?>
                    <?= e($initials($home)) ?>
                <?php endif; ?>
            </div>
            <div class="team-mini-name"><?= e($home) ?></div>
        </div>
        <div class="score-box <?= $isFinished || $isLive ? '' : 'vs' ?>">
            <?php if ($isFinished || $isLive): ?>
                <span><?= $hs ?></span><span>-</span><span><?= $as ?></span>
            <?php else: ?>
                VS
            <?php endif; ?>
        </div>
        <div class="team-mini away">
            <div class="team-mini-logo">
                <?php if (!empty($m['away_team_logo'])): ?>
                    <img src="<?= e(url($m['away_team_logo'])) ?>" alt="">
                <?php else: ?>
                    <?= e($initials($away)) ?>
                <?php endif; ?>
            </div>
            <div class="team-mini-name"><?= e($away) ?></div>
        </div>
    </div>
    <div class="match-card-head" style="margin-top:10px;margin-bottom:0;">
        <span>📅 <?= e($dateLabel) ?></span>
        <span><?= e($m['venue'] ?? '') ?></span>
    </div>
</a>