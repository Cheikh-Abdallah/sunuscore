<?php
/** @var array $data */
$g = $data['global'];
$res = $data['results'];
$goalsByMatchday = $data['goalsByMatchday'];
$scorers = $data['scorers'];
$cards = $data['cards'];
?>
<h1 class="mb-2">Statistiques</h1>

<div class="grid grid-3 mb-3">
    <div class="card">
        <div class="text-muted text-small">Matchs terminés</div>
        <div style="font-size:28px;font-weight:900;color:var(--c-primary)"><?= (int) $g['matches_finished'] ?></div>
    </div>
    <div class="card">
        <div class="text-muted text-small">Total buts</div>
        <div style="font-size:28px;font-weight:900;color:var(--c-primary)"><?= (int) $g['total_goals'] ?></div>
    </div>
    <div class="card">
        <div class="text-muted text-small">Moyenne buts / match</div>
        <div style="font-size:28px;font-weight:900;color:var(--c-primary)"><?= e((string) $g['average']) ?></div>
    </div>
</div>

<?php if ((int) $g['matches_finished'] === 0): ?>
    <?php $title = 'Pas encore de statistiques'; $message = 'Les graphiques apparaîtront dès que des matchs seront terminés.'; require dirname(__DIR__) . '/components/empty-state.php'; ?>
<?php else: ?>

<section class="section">
    <h2>Répartition des résultats</h2>
    <div class="card">
        <canvas id="chartResults" height="140"></canvas>
    </div>
</section>

<section class="section">
    <h2>Buts par journée</h2>
    <div class="card">
        <canvas id="chartGoals" height="140"></canvas>
    </div>
</section>

<section class="section">
    <h2>Top buteurs</h2>
    <div class="card">
        <canvas id="chartScorers" height="180"></canvas>
    </div>
</section>

<?php if (!empty($cards)): ?>
<section class="section">
    <h2>Cartons par équipe</h2>
    <div class="card">
        <canvas id="chartCards" height="180"></canvas>
    </div>
</section>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
window.SUNU_STATS = {
    results: <?= json_encode([
        'labels' => ['Victoires domicile', 'Victoires extérieur', 'Nuls'],
        'values' => [(int) $res['home_wins'], (int) $res['away_wins'], (int) $res['draws']],
    ]) ?>,
    goals: <?= json_encode([
        'labels' => array_map(fn($r) => 'J' . (int) $r['matchday'], $goalsByMatchday),
        'values' => array_map(fn($r) => (int) $r['goals'], $goalsByMatchday),
    ]) ?>,
    scorers: <?= json_encode([
        'labels' => array_map(fn($s) => $s['first_name'] . ' ' . $s['last_name'], array_slice($scorers, 0, 8)),
        'values' => array_map(fn($s) => (int) $s['goals_total'], array_slice($scorers, 0, 8)),
    ]) ?>,
    cards: <?= json_encode([
        'labels' => array_map(fn($c) => $c['name'], $cards),
        'yellow' => array_map(fn($c) => (int) $c['yellow'], $cards),
        'red'    => array_map(fn($c) => (int) $c['red'], $cards),
    ]) ?>,
};
</script>
<script src="<?= e(asset('js/statistics.js')) ?>"></script>

<?php endif; ?>