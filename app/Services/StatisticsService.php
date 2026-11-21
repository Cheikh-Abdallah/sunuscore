<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class StatisticsService
{
    /** Top buteurs d'une compétition (buts normaux + pénaltys). */
    public static function topScorers(int $competitionId, int $limit = 20): array
    {
        $sql = "SELECT p.id, p.first_name, p.last_name, p.photo_path,
                       t.id AS team_id, t.name AS team_name,
                       SUM(e.event_type = 'goal') AS goals_normal,
                       SUM(e.event_type = 'penalty_goal') AS goals_penalty,
                       SUM(e.event_type IN ('goal','penalty_goal')) AS goals_total
                FROM match_events e
                JOIN players p ON p.id = e.player_id
                JOIN teams t ON t.id = e.team_id
                JOIN matches m ON m.id = e.match_id
                WHERE m.competition_id = :cid
                  AND e.is_voided = 0
                  AND e.event_type IN ('goal','penalty_goal')
                  AND e.player_id IS NOT NULL
                GROUP BY p.id, t.id
                ORDER BY goals_total DESC, goals_normal DESC, p.last_name ASC
                LIMIT :lim";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->bindValue(':cid', $competitionId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Hommes du match d'une compétition. */
    public static function menOfTheMatch(int $competitionId, int $limit = 50): array
    {
        $sql = "SELECT a.*, p.first_name, p.last_name, p.photo_path,
                       t.name AS team_name,
                       m.scheduled_at, m.id AS match_id,
                       th.name AS home_name, ta.name AS away_name
                FROM match_awards a
                JOIN players p ON p.id = a.player_id
                JOIN teams t ON t.id = a.team_id
                JOIN matches m ON m.id = a.match_id
                JOIN teams th ON th.id = m.home_team_id
                JOIN teams ta ON ta.id = m.away_team_id
                WHERE m.competition_id = :cid
                ORDER BY m.scheduled_at DESC
                LIMIT :lim";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->bindValue(':cid', $competitionId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Nombre de distinctions par joueur. */
    public static function topMotm(int $competitionId, int $limit = 10): array
    {
        $sql = "SELECT p.id, p.first_name, p.last_name, t.name AS team_name,
                       COUNT(*) AS awards_count
                FROM match_awards a
                JOIN players p ON p.id = a.player_id
                JOIN teams t ON t.id = a.team_id
                JOIN matches m ON m.id = a.match_id
                WHERE m.competition_id = :cid
                GROUP BY p.id, t.id
                ORDER BY awards_count DESC, p.last_name ASC
                LIMIT :lim";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->bindValue(':cid', $competitionId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Cartons par équipe. */
    public static function cardsByTeam(int $competitionId): array
    {
        $sql = "SELECT t.id, t.name,
                       SUM(e.event_type = 'yellow_card') AS yellow,
                       SUM(e.event_type = 'red_card') AS red
                FROM match_events e
                JOIN matches m ON m.id = e.match_id
                JOIN teams t ON t.id = e.team_id
                WHERE m.competition_id = :cid AND e.is_voided = 0
                  AND e.event_type IN ('yellow_card','red_card')
                GROUP BY t.id
                ORDER BY yellow DESC, red DESC";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':cid' => $competitionId]);
        return $stmt->fetchAll();
    }

    /** Buts par journée. */
    public static function goalsByMatchday(int $competitionId): array
    {
        $sql = "SELECT m.matchday,
                       COUNT(DISTINCT m.id) AS matches_played,
                       COALESCE(SUM(e.goals), 0) AS goals
                FROM matches m
                LEFT JOIN (
                    SELECT match_id, COUNT(*) AS goals
                    FROM match_events
                    WHERE event_type IN ('goal','penalty_goal','own_goal') AND is_voided = 0
                    GROUP BY match_id
                ) e ON e.match_id = m.id
                WHERE m.competition_id = :cid AND m.status = 'finished'
                  AND m.matchday IS NOT NULL
                GROUP BY m.matchday
                ORDER BY m.matchday ASC";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':cid' => $competitionId]);
        return $stmt->fetchAll();
    }

    /** Global : matchs terminés, buts, moyenne. */
    public static function globalStats(int $competitionId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS matches_finished,
                    COALESCE(SUM(home_score + away_score), 0) AS total_goals,
                    COUNT(DISTINCT home_team_id) AS teams_count
             FROM matches
             WHERE competition_id = :cid AND status = 'finished'"
        );
        $stmt->execute([':cid' => $competitionId]);
        $row = $stmt->fetch();
        $finished = (int) ($row['matches_finished'] ?? 0);
        $goals    = (int) ($row['total_goals'] ?? 0);
        return [
            'matches_finished' => $finished,
            'total_goals'      => $goals,
            'average'          => $finished > 0 ? round($goals / $finished, 2) : 0,
        ];
    }

    /** Répartition V/N/D sur tous les matchs terminés. */
    public static function resultsBreakdown(int $competitionId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT
                SUM(home_score > away_score) AS home_wins,
                SUM(home_score < away_score) AS away_wins,
                SUM(home_score = away_score) AS draws
             FROM matches
             WHERE competition_id = :cid AND status = 'finished'"
        );
        $stmt->execute([':cid' => $competitionId]);
        $row = $stmt->fetch();
        return [
            'home_wins' => (int) ($row['home_wins'] ?? 0),
            'away_wins' => (int) ($row['away_wins'] ?? 0),
            'draws'     => (int) ($row['draws'] ?? 0),
        ];
    }
}