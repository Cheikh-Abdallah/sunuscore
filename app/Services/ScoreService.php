<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

/**
 * Service central pour toutes les opérations LIVE.
 * Toutes les méthodes qui modifient l'état du match sont transactionnelles.
 */
final class ScoreService
{
    /**
     * Recalcule home_score / away_score d'un match depuis les événements validés.
     * Retourne ['home' => int, 'away' => int].
     */
    public static function recalculateScore(int $matchId): array
    {
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT home_team_id, away_team_id FROM matches WHERE id = :id");
        $stmt->execute([':id' => $matchId]);
        $match = $stmt->fetch();
        if ($match === false) {
            throw new RuntimeException('Match introuvable.');
        }

        $home = (int) $match['home_team_id'];
        $away = (int) $match['away_team_id'];

        // Buts normaux et penaltys : crédités à l'équipe de l'événement
        // Contre-son-camp : crédité à l'équipe ADVERSE du joueur
        $stmt = $pdo->prepare(
            "SELECT
                SUM(CASE
                    WHEN event_type IN ('goal','penalty_goal') AND team_id = :home1 THEN 1
                    WHEN event_type = 'own_goal' AND team_id = :away1 THEN 1
                    ELSE 0
                END) AS home_goals,
                SUM(CASE
                    WHEN event_type IN ('goal','penalty_goal') AND team_id = :away2 THEN 1
                    WHEN event_type = 'own_goal' AND team_id = :home2 THEN 1
                    ELSE 0
                END) AS away_goals
             FROM match_events
             WHERE match_id = :mid AND is_voided = 0"
        );
        $stmt->execute([
            ':home1' => $home, ':away1' => $away,
            ':home2' => $home, ':away2' => $away,
            ':mid'   => $matchId,
        ]);
        $row = $stmt->fetch();

        $homeGoals = (int) ($row['home_goals'] ?? 0);
        $awayGoals = (int) ($row['away_goals'] ?? 0);

        $upd = $pdo->prepare("UPDATE matches SET home_score = :h, away_score = :a WHERE id = :id");
        $upd->execute([':h' => $homeGoals, ':a' => $awayGoals, ':id' => $matchId]);

        return ['home' => $homeGoals, 'away' => $awayGoals];
    }

    /* ============================================================
       Actions de contrôle
       ============================================================ */

    public static function start(int $matchId, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $m = self::requireMatch($matchId);
            if (!in_array($m['status'], ['scheduled','postponed'], true)) {
                throw new RuntimeException('Ce match ne peut pas être démarré.');
            }

            $upd = $pdo->prepare(
                "UPDATE matches SET status='live', current_period='first',
                        current_minute=1, started_at=NOW()
                 WHERE id=:id"
            );
            $upd->execute([':id' => $matchId]);

            self::logEvent($matchId, (int) $m['home_team_id'], null, 'match_start', 1, 0, null, $userId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateMinute(int $matchId, int $minute, int $extra = 0): void
    {
        if ($minute < 0 || $minute > 130) throw new RuntimeException('Minute invalide.');
        $stmt = Database::getInstance()->prepare(
            "UPDATE matches SET current_minute = :m, extra_time = :e WHERE id = :id"
        );
        $stmt->execute([':m' => $minute, ':e' => $extra, ':id' => $matchId]);
    }

    public static function halfTime(int $matchId, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $m = self::requireMatch($matchId);
            if ($m['status'] !== 'live' || $m['current_period'] !== 'first') {
                throw new RuntimeException('La mi-temps n\'est possible qu\'en première période.');
            }
            $min = (int) $m['current_minute'];
            $pdo->prepare("UPDATE matches SET status='half_time', current_period='half_time', current_minute=45 WHERE id=:id")
                ->execute([':id' => $matchId]);
            self::logEvent($matchId, (int) $m['home_team_id'], null, 'half_time', $min, 0, null, $userId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function resume(int $matchId, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $m = self::requireMatch($matchId);
            if (!in_array($m['status'], ['half_time','suspended'], true)) {
                throw new RuntimeException('Reprise impossible dans cet état.');
            }
            $period = $m['status'] === 'half_time' ? 'second' : $m['current_period'];
            $pdo->prepare("UPDATE matches SET status='live', current_period=:p, current_minute=46 WHERE id=:id")
                ->execute([':p' => $period, ':id' => $matchId]);
            self::logEvent($matchId, (int) $m['home_team_id'], null, 'resume', 46, 0, null, $userId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function suspend(int $matchId, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $m = self::requireMatch($matchId);
            if ($m['status'] !== 'live') throw new RuntimeException('Seul un match en direct peut être suspendu.');
            $pdo->prepare("UPDATE matches SET status='suspended' WHERE id=:id")->execute([':id' => $matchId]);
            self::logEvent($matchId, (int) $m['home_team_id'], null, 'suspend', (int) $m['current_minute'], 0, null, $userId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function finish(int $matchId, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $m = self::requireMatch($matchId);
            if (!in_array($m['status'], ['live','half_time','suspended'], true)) {
                throw new RuntimeException('Ce match ne peut pas être terminé.');
            }
            $pdo->prepare("UPDATE matches SET status='finished', current_period='full_time', finished_at=NOW() WHERE id=:id")
                ->execute([':id' => $matchId]);
            self::logEvent($matchId, (int) $m['home_team_id'], null, 'match_end', (int) $m['current_minute'], 0, null, $userId);
            self::recalculateScore($matchId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /* ============================================================
       Événements
       ============================================================ */

    public static function addGoal(
        int $matchId,
        int $teamId,
        ?int $playerId,
        ?int $assistPlayerId,
        int $minute,
        int $extra,
        string $type,
        int $userId
    ): void {
        self::validateEventType($type, ['goal','penalty_goal','own_goal']);
        self::assertTeamInMatch($matchId, $teamId);

        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            self::logEvent($matchId, $teamId, $playerId, $type, $minute, $extra, null, $userId, $assistPlayerId);
            self::recalculateScore($matchId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function addCard(
        int $matchId,
        int $teamId,
        ?int $playerId,
        int $minute,
        int $extra,
        string $type,
        ?string $reason,
        int $userId
    ): void {
        self::validateEventType($type, ['yellow_card','red_card']);
        self::assertTeamInMatch($matchId, $teamId);

        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            self::logEvent($matchId, $teamId, $playerId, $type, $minute, $extra, $reason, $userId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function addSubstitution(
        int $matchId,
        int $teamId,
        int $playerInId,
        int $playerOutId,
        int $minute,
        int $extra,
        int $userId
    ): void {
        self::assertTeamInMatch($matchId, $teamId);

        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            // On stocke : player_id = sortant, related_player_id = entrant
            self::logEvent($matchId, $teamId, $playerOutId, 'substitution', $minute, $extra, null, $userId, $playerInId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function voidEvent(int $eventId, int $userId, string $reason): void
    {
        if (trim($reason) === '') throw new RuntimeException('Motif obligatoire pour annuler.');

        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT match_id FROM match_events WHERE id = :id AND is_voided = 0");
            $stmt->execute([':id' => $eventId]);
            $matchId = $stmt->fetchColumn();
            if ($matchId === false) throw new RuntimeException('Événement introuvable ou déjà annulé.');

            $upd = $pdo->prepare(
                "UPDATE match_events SET is_voided=1, voided_at=NOW(), voided_by=:u, voided_reason=:r
                 WHERE id=:id"
            );
            $upd->execute([':u' => $userId, ':r' => $reason, ':id' => $eventId]);

            self::recalculateScore((int) $matchId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function setAward(int $matchId, int $playerId, int $teamId, ?string $reason, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            self::assertTeamInMatch($matchId, $teamId);

            $sql = "INSERT INTO match_awards (match_id, player_id, team_id, award_type, reason, awarded_by)
                    VALUES (:mid, :pid, :tid, 'man_of_the_match', :r, :u)
                    ON DUPLICATE KEY UPDATE
                        player_id = VALUES(player_id),
                        team_id   = VALUES(team_id),
                        reason    = VALUES(reason),
                        awarded_by = VALUES(awarded_by),
                        created_at = NOW()";
            $pdo->prepare($sql)->execute([
                ':mid' => $matchId, ':pid' => $playerId, ':tid' => $teamId,
                ':r' => $reason, ':u' => $userId,
            ]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /* ============================================================
       Helpers privés
       ============================================================ */

    private static function requireMatch(int $id): array
    {
        $stmt = Database::getInstance()->prepare("SELECT * FROM matches WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $m = $stmt->fetch();
        if ($m === false) throw new RuntimeException('Match introuvable.');
        return $m;
    }

    private static function assertTeamInMatch(int $matchId, int $teamId): void
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT 1 FROM matches WHERE id = :id AND (home_team_id = :t1 OR away_team_id = :t2)"
        );
        $stmt->execute([':id' => $matchId, ':t1' => $teamId, ':t2' => $teamId]);
        if ($stmt->fetchColumn() === false) {
            throw new RuntimeException('Cette équipe ne participe pas à ce match.');
        }
    }

    private static function validateEventType(string $type, array $allowed): void
    {
        if (!in_array($type, $allowed, true)) {
            throw new RuntimeException('Type d\'événement invalide.');
        }
    }

    private static function logEvent(
        int $matchId,
        int $teamId,
        ?int $playerId,
        string $type,
        int $minute,
        int $extra,
        ?string $detail,
        int $userId,
        ?int $relatedPlayerId = null
    ): void {
        $sql = "INSERT INTO match_events
                (match_id, team_id, player_id, related_player_id, event_type, minute, extra_minute, detail, created_by)
                VALUES (:mid, :tid, :pid, :rpid, :type, :min, :extra, :detail, :uid)";
        Database::getInstance()->prepare($sql)->execute([
            ':mid' => $matchId, ':tid' => $teamId,
            ':pid' => $playerId, ':rpid' => $relatedPlayerId,
            ':type' => $type, ':min' => $minute, ':extra' => $extra,
            ':detail' => $detail, ':uid' => $userId,
        ]);
    }
}