<?php
/** @var array $scorers */
?>
<h1 class="mb-2">Meilleurs buteurs</h1>

<?php if (empty($scorers)): ?>
    <?php $title = 'Aucun buteur'; $message = 'Les buteurs apparaîtront dès les premiers buts enregistrés.'; require dirname(__DIR__) . '/components/empty-state.php'; ?>
<?php else: ?>
<div class="card">
    <div class="table-responsive">
        <table class="data">
            <thead><tr>
                <th>#</th><th>Joueur</th><th>Équipe</th>
                <th class="num">Buts</th><th class="num">Penaltys</th><th class="num">Total</th>
            </tr></thead>
            <tbody>
            <?php foreach ($scorers as $i => $s): ?>
                <tr>
                    <td class="num"><?= $i + 1 ?></td>
                    <td><strong><?= e($s['first_name'] . ' ' . $s['last_name']) ?></strong></td>
                    <td><?= e($s['team_name']) ?></td>
                    <td class="num"><?= (int) $s['goals_normal'] ?></td>
                    <td class="num"><?= (int) $s['goals_penalty'] ?></td>
                    <td class="num" style="font-weight:800"><?= (int) $s['goals_total'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>