<?php
/** @var array $competition */
/** @var array|null $live */
/** @var array|null $next */
/** @var array $recent */
/** @var array $rankingTop */
/** @var array $stats */
?>
<section class="section">
    <div class="hero-live">
        <div class="hero-label">
            <?= $live ? '● MATCH EN DIRECT' : 'PROCHAIN MATCH' ?>
        </div>
        <?php $hero = $live ?? $next; ?>
        <?php if ($hero === null): ?>
            <h2>Aucun match programmé pour le moment.</h2>
            <p style="color:rgba(255,255,255,.7);margin:0;">Consultez les compétitions disponibles.</p>
        <?php else: ?>
            <div class="hero-teams">
                <div class="hero-team">
                    <div class="logo"><?= e(strtoupper(mb_substr($hero['home_team_name'], 0, 2))) ?></div>
                    <div class="name"><?= e($hero['home_team_name']) ?></div>
                </div>
                <div style="text-align:center;">
                    <?php if ($live): ?>
                        <div class="hero-score"><?= (int) $hero['home_score'] ?> - <?= (int) $hero['away_score'] ?></div>
                        <div class="hero-meta"><?= (int) $hero['current_minute'] ?>' · <?= e($hero['venue'] ?? '') ?></div>
                    <?php else: ?>
                        <div class="hero-score">VS</div>
                        <div class="hero-meta"><?= e(date('d/m/Y à H\hi', strtotime($hero['scheduled_at']))) ?></div>
                    <?php endif; ?>
                </div>
                <div class="hero-team">
                    <div class="logo"><?= e(strtoupper(mb_substr($hero['away_team_name'], 0, 2))) ?></div>
                    <div class="name"><?= e($hero['away_team_name']) ?></div>
                </div>
            </div>
            <div style="text-align:center;margin-top:8px;">
                <a href="<?= e(url('/matches/' . (int) $hero['id'])) ?>"
                   style="display:inline-block;background:#155EEF;color:#fff;padding:10px 20px;border-radius:10px;font-weight:700;font-size:14px;">
                    Voir le match
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="section-title">
        <h2><?= e($competition['name']) ?></h2>
        <span class="badge badge-<?= e($competition['status']) ?>"><?= e(strtoupper($competition['status'])) ?></span>
    </div>
    <div class="grid grid-2">
        <div class="card">
            <div class="text-muted text-small">Équipes engagées</div>
            <div style="font-size:28px;font-weight:900;color:var(--c-primary)"><?= (int) $stats['teams'] ?></div>
        </div>
        <div class="card">
            <div class="text-muted text-small">Matchs programmés</div>
            <div style="font-size:28px;font-weight:900;color:var(--c-primary)"><?= (int) $stats['matches'] ?></div>
        </div>
    </div>
</section>

<?php if (!empty($recent)): ?>
<section class="section">
    <div class="section-title">
        <h2>Derniers résultats</h2>
        <a class="link" href="<?= e(url('/matches?filter=finished')) ?>">Tout voir →</a>
    </div>
    <div class="grid">
        <?php foreach ($recent as $m): ?>
            <?php require dirname(__DIR__) . '/components/match-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($rankingTop)): ?>
<section class="section">
    <div class="section-title">
        <h2>Classement</h2>
        <a class="link" href="<?= e(url('/rankings')) ?>">Complet →</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="data">
                <thead>
                    <tr>
                        <th>#</th><th>Équipe</th>
                        <th class="num">MJ</th><th class="num">V</th><th class="num">N</th>
                        <th class="num">D</th><th class="num">Diff</th><th class="num">Pts</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rankingTop as $r): ?>
                    <tr>
                        <td class="num"><?= (int) $r['rank'] ?></td>
                        <td><div class="team-cell"><?= e($r['team_name']) ?></div></td>
                        <td class="num"><?= (int) $r['played'] ?></td>
                        <td class="num"><?= (int) $r['won'] ?></td>
                        <td class="num"><?= (int) $r['drawn'] ?></td>
                        <td class="num"><?= (int) $r['lost'] ?></td>
                        <td class="num"><?= ($r['goal_diff'] > 0 ? '+' : '') . (int) $r['goal_diff'] ?></td>
                        <td class="num" style="font-weight:800"><?= (int) $r['points'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>


<?php endif; ?>
<?php if (!empty($topScorers)): ?>
<section class="section">
    <div class="section-title">
        <h2>Meilleurs buteurs</h2>
        <a class="link" href="<?= e(url('/top-scorers')) ?>">Complet →</a>
    </div>
    <div class="grid grid-3">
        <?php foreach ($topScorers as $s): ?>
            <div class="card">
                <div style="font-weight:700;"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
                <div class="text-small text-muted"><?= e($s['team_name']) ?></div>
                <div style="font-size:24px;font-weight:900;color:var(--c-secondary);margin-top:6px;">
                    <?= (int) $s['goals_total'] ?> ⚽
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>