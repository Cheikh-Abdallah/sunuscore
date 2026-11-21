<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Calcule le classement d'une compétition à partir des matchs terminés.
 * - Ignore les matchs non `finished`.
 * - Gère le départage : points, goal_diff, goals_for, head_to_head.
 */
final class RankingService
{
    public static function compute(int $competitionId, ?array $tiebreak = null): array
    {
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare(
            "SELECT points_win, points_draw, points_loss, tiebreak_rules
             FROM competitions WHERE id = :id"
        );
        $stmt->execute([':id' => $competitionId]);
        $comp = $stmt->fetch();
        if ($comp === false) return [];

        $pw = (int) $comp['points_win'];
        $pd = (int) $comp['points_draw'];
        $pl = (int) $comp['points_loss'];

        if ($tiebreak === null) {
            if (is_string($comp['tiebreak_rules'])) {
                $parsed = json_decode($comp['tiebreak_rules'], true);
                if (is_array($parsed) && !empty($parsed)) $tiebreak = $parsed;
            }
            if ($tiebreak === null) $tiebreak = ['points', 'goal_diff', 'goals_for'];
        }

        // Inscrits
        $stmt = $pdo->prepare(
            "SELECT t.id, t.name, t.slug, t.logo_path,
                    ct.zone_id, ct.pool_id,
                    z.name AS zone_name, p.name AS pool_name
             FROM teams t
             JOIN competition_teams ct ON ct.team_id = t.id
             LEFT JOIN zones z ON z.id = ct.zone_id
             LEFT JOIN pools p ON p.id = ct.pool_id
             WHERE ct.competition_id = :cid AND ct.status = 'active'"
        );
        $stmt->execute([':cid' => $competitionId]);
        $teams = $stmt->fetchAll();

        $table = [];
        foreach ($teams as $t) {
            $table[(int) $t['id']] = [
                'team_id'    => (int) $t['id'],
                'team_name'  => $t['name'],
                'team_slug'  => $t['slug'],
                'team_logo'  => $t['logo_path'],
                'zone_id'    => $t['zone_id'] !== null ? (int) $t['zone_id'] : null,
                'zone_name'  => $t['zone_name'],
                'pool_id'    => $t['pool_id'] !== null ? (int) $t['pool_id'] : null,
                'pool_name'  => $t['pool_name'],
                'played'     => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                'goals_for'  => 0, 'goals_against' => 0, 'goal_diff' => 0, 'points' => 0,
            ];
        }

        // Matchs terminés
        $stmt = $pdo->prepare(
            "SELECT home_team_id, away_team_id, home_score, away_score
             FROM matches
             WHERE competition_id = :cid AND status = 'finished'"
        );
        $stmt->execute([':cid' => $competitionId]);
        $matches = $stmt->fetchAll();

        // Matrice confrontations directes (head-to-head)
        $h2h = [];

        foreach ($matches as $m) {
            $home = (int) $m['home_team_id'];
            $away = (int) $m['away_team_id'];
            $hs   = (int) $m['home_score'];
            $as   = (int) $m['away_score'];

            if (!isset($table[$home]) || !isset($table[$away])) continue;

            $table[$home]['played']++;
            $table[$away]['played']++;
            $table[$home]['goals_for']     += $hs;
            $table[$home]['goals_against'] += $as;
            $table[$away]['goals_for']     += $as;
            $table[$away]['goals_against'] += $hs;

            if ($hs > $as) {
                $table[$home]['won']++; $table[$away]['lost']++;
                $table[$home]['points'] += $pw; $table[$away]['points'] += $pl;
            } elseif ($hs < $as) {
                $table[$away]['won']++; $table[$home]['lost']++;
                $table[$away]['points'] += $pw; $table[$home]['points'] += $pl;
            } else {
                $table[$home]['drawn']++; $table[$away]['drawn']++;
                $table[$home]['points'] += $pd; $table[$away]['points'] += $pd;
            }

            // Head-to-head
            $h2h[$home][$away] = ($h2h[$home][$away] ?? 0) + ($hs - $as);
            $h2h[$away][$home] = ($h2h[$away][$home] ?? 0) + ($as - $hs);
        }

        foreach ($table as &$row) {
            $row['goal_diff'] = $row['goals_for'] - $row['goals_against'];
        }
        unset($row);

        $table = array_values($table);
        usort($table, function (array $a, array $b) use ($tiebreak, $h2h): int {
            foreach ($tiebreak as $criterion) {
                switch ($criterion) {
                    case 'points':
                        if ($a['points'] !== $b['points']) return $b['points'] <=> $a['points'];
                        break;
                    case 'goal_diff':
                        if ($a['goal_diff'] !== $b['goal_diff']) return $b['goal_diff'] <=> $a['goal_diff'];
                        break;
                    case 'goals_for':
                        if ($a['goals_for'] !== $b['goals_for']) return $b['goals_for'] <=> $a['goals_for'];
                        break;
                    case 'head_to_head':
                        $diff = $h2h[$b['team_id']][$a['team_id']] ?? 0;
                        $diff -= $h2h[$a['team_id']][$b['team_id']] ?? 0;
                        if ($diff !== 0) return $diff <=> 0;
                        break;
                }
            }
            return 0;
        });

        // Regroupement par zone/poule (le rang reste global si pas de zone)
        $byZone = [];
        $grouped = [];
        if (array_filter($teams, fn($t) => $t['zone_id'] !== null)) {
            foreach ($table as $row) {
                $key = $row['zone_id'] !== null ? 'z' . $row['zone_id'] : 'z0';
                $grouped[$key][] = $row;
            }
            $result = [];
            foreach ($grouped as $key => $rows) {
                $rank = 1;
                foreach ($rows as $row) {
                    $row['rank'] = $rank++;
                    $result[] = $row;
                }
            }
            return $result;
        }

        foreach ($table as $i => &$row) {
            $row['rank'] = $i + 1;
        }
        unset($row);
        return $table;
    }
}