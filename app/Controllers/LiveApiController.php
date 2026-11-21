<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Models\MatchEvent;
use App\Models\MatchModel;

final class LiveApiController
{
    public function get(int $id): void
    {
        $match = MatchModel::findById($id);
        if ($match === null) Response::error('Match introuvable.', [], 404);

        $events = MatchEvent::allByMatch($id);

        // Score calculé à la volée (source de vérité = événements)
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT
                SUM(CASE WHEN event_type IN ('goal','penalty_goal') AND team_id = :h1 THEN 1
                         WHEN event_type='own_goal' AND team_id = :a1 THEN 1 ELSE 0 END) AS h,
                SUM(CASE WHEN event_type IN ('goal','penalty_goal') AND team_id = :a2 THEN 1
                         WHEN event_type='own_goal' AND team_id = :h2 THEN 1 ELSE 0 END) AS a
             FROM match_events WHERE match_id=:mid AND is_voided=0"
        );
        $stmt->execute([
            ':h1' => $match['home_team_id'], ':a1' => $match['away_team_id'],
            ':h2' => $match['home_team_id'], ':a2' => $match['away_team_id'],
            ':mid' => $id,
        ]);
        $score = $stmt->fetch();

        Response::success([
            'status'        => $match['status'],
            'period'        => $match['current_period'],
            'minute'        => (int) $match['current_minute'],
            'extra'         => (int) $match['extra_time'],
            'home_score'    => (int) ($score['h'] ?? 0),
            'away_score'    => (int) ($score['a'] ?? 0),
            'events'        => array_map(fn($e) => [
                'id'         => (int) $e['id'],
                'type'       => $e['event_type'],
                'minute'     => (int) $e['minute'],
                'extra'      => (int) $e['extra_minute'],
                'team_id'    => (int) $e['team_id'],
                'team_name'  => $e['team_name'],
                'player'     => trim(($e['player_first'] ?? '') . ' ' . ($e['player_last'] ?? '')),
                'related'    => trim(($e['related_first'] ?? '') . ' ' . ($e['related_last'] ?? '')),
                'voided'     => (int) $e['is_voided'] === 1,
                'created_at' => $e['created_at'],
            ], $events),
        ], 'OK', 200);
    }
}