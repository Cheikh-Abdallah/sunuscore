<?php
/** @var array $stats */
/** @var array $recentMatches */
/** @var array $upcomingMatches */
$appUrl = rtrim((string) config('app.url', ''), '/');
$cards = [
    ['label' => 'Compétitions', 'value' => $stats['competitions'], 'icon' => '🏆', 'color' => 'blue'],
    ['label' => 'Équipes',      'value' => $stats['teams'],         'icon' => '🛡️', 'color' => 'green'],
    ['label' => 'Joueurs',      'value' => $stats['players'],       'icon' => '👤', 'color' => 'yellow'],
    ['label' => 'Matchs',       'value' => $stats['matches'],       'icon' => '⚽', 'color' => 'blue'],
    ['label' => 'En direct',    'value' => $stats['matches_live'],  'icon' => '🔴', 'color' => 'red'],
    ['label' => 'Terminés',     'value' => $stats['matches_finished'], 'icon' => '✅', 'color' => 'green'],
    ['label' => 'Buts',         'value' => $stats['goals'],         'icon' => '🥅', 'color' => 'yellow'],
    ['label' => 'Cartons',      'value' => $stats['cards'],         'icon' => '🟨', 'color' => 'red'],
];
?>
<div class="stat-grid">
    <?php foreach ($cards as $c): ?>
        <div class="stat-card stat-<?= e($c['color']) ?>">
            <div class="stat-icon"><?= $c['icon'] ?></div>
            <div class="stat-value"><?= (int) $c['value'] ?></div>
            <div class="stat-label"><?= e($c['label']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="admin-grid-2">
    <section class="admin-card">
        <div class="admin-card-head">
            <h2>Derniers matchs</h2>
        </div>
        <?php if (empty($recentMatches)): ?>
            <div class="admin-empty">Aucun match enregistré.</div>
        <?php else: ?>
            <div class="admin-list">
                <?php foreach ($recentMatches as $m): ?>
                    <div class="admin-list-item">
                        <div>
                            <div class="admin-list-title">
                                <?= e($m['home_team_name']) ?> <span class="text-muted">vs</span> <?= e($m['away_team_name']) ?>
                            </div>
                            <div class="admin-list-meta">
                                <?= e(date('d/m/Y H\hi', strtotime($m['scheduled_at']))) ?>
                                · <?= e($m['venue'] ?? '—') ?>
                            </div>
                        </div>
                        <span class="badge badge-<?= e($m['status']) ?>"><?= e(strtoupper($m['status'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <h2>Prochains matchs</h2>
        </div>
        <?php if (empty($upcomingMatches)): ?>
            <div class="admin-empty">Aucun match à venir.</div>
        <?php else: ?>
            <div class="admin-list">
                <?php foreach ($upcomingMatches as $m): ?>
                    <div class="admin-list-item">
                        <div>
                            <div class="admin-list-title">
                                <?= e($m['home_team_name']) ?> <span class="text-muted">vs</span> <?= e($m['away_team_name']) ?>
                            </div>
                            <div class="admin-list-meta">
                                <?= e(date('d/m/Y H\hi', strtotime($m['scheduled_at']))) ?>
                            </div>
                        </div>
                        <span class="badge badge-scheduled">À VENIR</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>