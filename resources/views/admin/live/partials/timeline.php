<?php
/** @var array $events */
use App\Core\Csrf;
$appUrl = rtrim((string) config('app.url', ''), '/');
$csrf = Csrf::field();
$canVoid = \App\Core\Auth::hasRole(\App\Core\Auth::ROLE_SUPER_ADMIN, \App\Core\Auth::ROLE_ADMIN);
?>
<?php if (empty($events)): ?>
    <div class="admin-empty">Aucun événement.</div>
<?php else: ?>
    <div class="live-timeline">
    <?php foreach ($events as $e): ?>
        <div class="lt-item <?= (int) $e['is_voided'] === 1 ? 'is-voided' : '' ?>">
            <div class="lt-minute"><?= (int) $e['minute'] ?>'<?= (int) $e['extra_minute'] > 0 ? '+' . (int) $e['extra_minute'] : '' ?></div>
            <div class="lt-body">
                <div class="lt-title">
                    <?php
                    $typeLabels = [
                        'goal' => '⚽ But', 'penalty_goal' => '⚽ Penalty',
                        'own_goal' => '⚽ CSC', 'yellow_card' => '🟨 Jaune',
                        'red_card' => '🟥 Rouge', 'substitution' => '🔄 Remplacement',
                        'match_start' => '▶ Début', 'match_end' => '⏹ Fin',
                        'half_time' => '⏸ Mi-temps', 'resume' => '▶ Reprise',
                        'suspend' => '⏸ Suspension', 'award' => '🏅 Homme du match',
                        'period' => 'Période',
                    ];
                    echo $typeLabels[$e['event_type']] ?? e($e['event_type']);
                    ?>
                    <span class="text-muted">— <?= e($e['team_name']) ?></span>
                </div>
                <?php if ($e['player_first']): ?>
                    <div class="text-small">
                        <?= e($e['player_first'] . ' ' . $e['player_last']) ?>
                        <?php if ($e['related_first']): ?>
                            <span class="text-muted">(avec <?= e($e['related_first'] . ' ' . $e['related_last']) ?>)</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ((int) $e['is_voided'] === 1): ?>
                    <div class="text-small" style="color:var(--c-red)">
                        Annulé par <?= e($e['voided_by_username'] ?? '?') ?> — <?= e($e['voided_reason'] ?? '') ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="lt-action">
                <?php if ((int) $e['is_voided'] === 0 && $canVoid && !in_array($e['event_type'], ['match_start','match_end','half_time','resume','suspend','period'], true)): ?>
                    <form method="POST" action="<?= e($appUrl) ?>/admin/live/<?= (int) $e['match_id'] ?>/event/<?= (int) $e['id'] ?>/void"
                          onsubmit="var r=prompt('Motif d\'annulation ?');if(!r){return false;}this.reason.value=r;return true;" style="display:inline">
                        <?= $csrf ?>
                        <input type="hidden" name="reason">
                        <button class="btn-sm btn-danger">Annuler</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>