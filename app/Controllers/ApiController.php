<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Models\Competition;
use App\Models\MatchAward;
use App\Models\MatchEvent;
use App\Models\MatchModel;
use App\Models\Player;
use App\Models\Team;
use App\Services\RankingService;
use App\Services\StatisticsService;

/**
 * API REST publique — lecture seule.
 * Toutes les réponses sont en JSON et suivent le format :
 * { success, message, data, errors }
 */
final class ApiController
{
    /* ---------- HEALTH ---------- */

    public function health(): void
    {
        $db = 'ok';
        try { Database::getInstance()->query('SELECT 1'); } catch (\Throwable $e) { $db = 'down'; }
        Response::success([
            'app'      => 'SUNUSCORE',
            'version'  => '0.8.0',
            'env'      => config('app.env'),
            'timezone' => date_default_timezone_get(),
            'php'      => PHP_VERSION,
            'database' => $db,
            'time'     => now(),
        ], 'API opérationnelle', 200);
    }

    /* ---------- COMPETITIONS ---------- */

    public function competitions(): void
    {
        Response::success(Competition::all(), 'Compétitions', 200);
    }

    public function competition(int $id): void
    {
        $c = Competition::findById($id);
        if ($c === null) Response::error('Compétition introuvable.', [], 404);
        Response::success($c, 'Compétition', 200);
    }

    /* ---------- MATCHES ---------- */

    public function matches(): void
    {
        $cid = (int) ($_GET['competition_id'] ?? 0);
        $filter = $_GET['filter'] ?? 'all';
        if (!in_array($filter, ['all', 'live', 'upcoming', 'finished', 'postponed'], true)) {
            $filter = 'all';
        }

        if ($cid > 0) {
            $list = MatchModel::byCompetition($cid, $filter);
        } else {
            $competition = Competition::findFirstActive();
            if ($competition === null) Response::success([], 'Aucun match', 200);
            $list = MatchModel::byCompetition((int) $competition['id'], $filter);
        }
        Response::success($list, 'Matchs', 200);
    }

    public function match(int $id): void
    {
        $m = MatchModel::findById($id);
        if ($m === null) Response::error('Match introuvable.', [], 404);
        Response::success($m, 'Match', 200);
    }

    public function matchEvents(int $id): void
    {
        $m = MatchModel::findById($id);
        if ($m === null) Response::error('Match introuvable.', [], 404);
        Response::success(MatchEvent::allByMatch($id), 'Événements', 200);
    }

    /** Endpoint live — déjà utilisé par le frontend, on le garde ici. */
    public function matchLive(int $id): void
    {
        $match = MatchModel::findById($id);
        if ($match === null) Response::error('Match introuvable.', [], 404);

        $events = MatchEvent::allByMatch($id);
        $score = $this->computeLiveScore($match, $events);

        Response::success([
            'status'     => $match['status'],
            'period'     => $match['current_period'],
            'minute'     => (int) $match['current_minute'],
            'extra'      => (int) $match['extra_time'],
            'home_score' => $score['home'],
            'away_score' => $score['away'],
            'server_time'=> now(),
            'events'     => array_map(fn($e) => [
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
        ], 'Live', 200);
    }

    /* ---------- TEAMS / PLAYERS ---------- */

    public function teams(): void
    {
        $cid = (int) ($_GET['competition_id'] ?? 0);
        if ($cid > 0) {
            Response::success(Team::allByCompetition($cid), 'Équipes', 200);
        }
        Response::success(Team::all(), 'Équipes', 200);
    }

    public function team(int $id): void
    {
        $t = Team::findById($id);
        if ($t === null) Response::error('Équipe introuvable.', [], 404);
        Response::success($t, 'Équipe', 200);
    }

    public function players(): void
    {
        $cid = (int) ($_GET['competition_id'] ?? 0);
        if ($cid > 0) {
            Response::success(Player::allByCompetition($cid), 'Joueurs', 200);
        }
        Response::success(Player::all(), 'Joueurs', 200);
    }

    /* ---------- RANKINGS ---------- */

    public function rankings(): void
    {
        $cid = (int) ($_GET['competition_id'] ?? 0);
        if ($cid <= 0) {
            $c = Competition::findFirstActive();
            if ($c === null) Response::success([], 'Aucun classement', 200);
            $cid = (int) $c['id'];
        }
        Response::success(RankingService::compute($cid), 'Classement', 200);
    }

    /* ---------- STATISTIQUES ---------- */

    public function topScorers(): void
    {
        $cid = $this->resolveCompetitionId();
        Response::success(StatisticsService::topScorers($cid, 50), 'Meilleurs buteurs', 200);
    }

    public function manOfTheMatch(): void
    {
        $cid = $this->resolveCompetitionId();
        Response::success(StatisticsService::menOfTheMatch($cid, 100), 'Hommes du match', 200);
    }

    public function statistics(): void
    {
        $cid = $this->resolveCompetitionId();
        Response::success([
            'global'          => StatisticsService::globalStats($cid),
            'results'         => StatisticsService::resultsBreakdown($cid),
            'goals_by_matchday'=> StatisticsService::goalsByMatchday($cid),
            'top_scorers'     => StatisticsService::topScorers($cid, 10),
            'cards_by_team'   => StatisticsService::cardsByTeam($cid),
            'top_motm'        => StatisticsService::topMotm($cid, 5),
        ], 'Statistiques', 200);
    }

    /* ---------- Helpers ---------- */

    private function resolveCompetitionId(): int
    {
        $cid = (int) ($_GET['competition_id'] ?? 0);
        if ($cid > 0) return $cid;
        $c = Competition::findFirstActive();
        if ($c === null) Response::error('Aucune compétition active.', [], 404);
        return (int) $c['id'];
    }

    private function computeLiveScore(array $match, array $events): array
    {
        $home = (int) $match['home_team_id'];
        $away = (int) $match['away_team_id'];
        $hg = 0; $ag = 0;
        foreach ($events as $e) {
            if ((int) $e['is_voided'] === 1) continue;
            $tid = (int) $e['team_id'];
            if ($e['event_type'] === 'goal' || $e['event_type'] === 'penalty_goal') {
                if ($tid === $home) $hg++; elseif ($tid === $away) $ag++;
            } elseif ($e['event_type'] === 'own_goal') {
                if ($tid === $home) $ag++; elseif ($tid === $away) $hg++;
            }
        }
        return ['home' => $hg, 'away' => $ag];
    }
}