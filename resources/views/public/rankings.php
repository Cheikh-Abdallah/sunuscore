<?php
/** @var array $ranking */
?>
<h1 class="mb-2">Classement</h1>

<?php if (empty($ranking)): ?>
    <?php $title = 'Classement indisponible'; $message = 'Aucun match terminé pour le moment.'; require dirname(__DIR__) . '/components/empty-state.php'; ?>
<?php else: ?>
    <?php
    // Regrouper par zone si des zones sont présentes
    $grouped = [];
    $hasZones = false;
    foreach ($ranking as $r) {
        if ($r['zone_name'] !== null) $hasZones = true;
    }
    if ($hasZones) {
        foreach ($ranking as $r) {
            $key = $r['zone_name'] ?? 'Classement général';
            $grouped[$key][] = $r;
        }
    } else {
        $grouped['Classement'] = $ranking;
    }
    ?>
    <?php foreach ($grouped as $groupName => $rows): ?>
        <h2 class="mb-1" style="margin-top:16px;"><?= e($groupName) ?></h2>
        <div class="card mb-2">
            <div class="table-responsive">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th><th>Équipe</th>
                            <th class="num">MJ</th><th class="num">V</th><th class="num">N</th>
                            <th class="num">D</th><th class="num">BP</th><th class="num">BC</th>
                            <th class="num">Diff</th><th class="num">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="num"><?= (int) $r['rank'] ?></td>
                            <td><div class="team-cell">
                                <a href="<?= e(url('/teams/' . (int) $r['team_id'])) ?>"><?= e($r['team_name']) ?></a>
                            </div></td>
                            <td class="num"><?= (int) $r['played'] ?></td>
                            <td class="num"><?= (int) $r['won'] ?></td>
                            <td class="num"><?= (int) $r['drawn'] ?></td>
                            <td class="num"><?= (int) $r['lost'] ?></td>
                            <td class="num"><?= (int) $r['goals_for'] ?></td>
                            <td class="num"><?= (int) $r['goals_against'] ?></td>
                            <td class="num"><?= ($r['goal_diff'] > 0 ? '+' : '') . (int) $r['goal_diff'] ?></td>
                            <td class="num" style="font-weight:800"><?= (int) $r['points'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>