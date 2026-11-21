<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MatchEvent
{
    /** Événements d'un match (validés + annulés, avec auteur). */
    public static function allByMatch(int $matchId): array
    {
        $sql = "SELECT e.*,
                       p.first_name AS player_first, p.last_name AS player_last,
                       rp.first_name AS related_first, rp.last_name AS related_last,
                       t.name AS team_name,
                       u.username AS created_by_username,
                       vu.username AS voided_by_username
                FROM match_events e
                LEFT JOIN players p ON p.id = e.player_id
                LEFT JOIN players rp ON rp.id = e.related_player_id
                LEFT JOIN teams t ON t.id = e.team_id
                LEFT JOIN users u ON u.id = e.created_by
                LEFT JOIN users vu ON vu.id = e.voided_by
                WHERE e.match_id = :mid
                ORDER BY e.minute ASC, e.extra_minute ASC, e.id ASC";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':mid' => $matchId]);
        return $stmt->fetchAll();
    }

    /** Compteurs validés d'un match pour la timeline. */
    public static function stats(int $matchId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT team_id,
                    SUM(event_type IN ('goal','penalty_goal','own_goal')) AS goals,
                    SUM(event_type = 'yellow_card') AS yellow,
                    SUM(event_type = 'red_card') AS red,
                    SUM(event_type = 'substitution') AS subs
             FROM match_events
             WHERE match_id = :mid AND is_voided = 0
             GROUP BY team_id"
        );
        $stmt->execute([':mid' => $matchId]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int) $row['team_id']] = [
                'goals'  => (int) $row['goals'],
                'yellow' => (int) $row['yellow'],
                'red'    => (int) $row['red'],
                'subs'   => (int) $row['subs'],
            ];
        }
        return $out;
    }
}