<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Competition;
use App\Models\Team;
use App\Services\RankingService;

final class TeamController
{
    public function index(): void
    {
        $competition = Competition::findFirstActive();
        if ($competition === null) Response::redirect('/competitions');

        $teams   = Team::allByCompetition((int) $competition['id']);
        $ranking = RankingService::compute((int) $competition['id']);
        $rankMap = [];
        foreach ($ranking as $r) $rankMap[$r['team_id']] = $r;

        $view = dirname(__DIR__, 2) . '/resources/views/public/teams.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }

    public function show(int $id): void
    {
        $team = Team::findById($id);
        if ($team === null) {
            http_response_code(404);
            $view = dirname(__DIR__, 2) . '/resources/views/errors/404.php';
            require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
            return;
        }
        $competition = Competition::findFirstActive();
        $roster  = $competition ? Team::roster($id, (int) $competition['id']) : [];
        $ranking = $competition ? RankingService::compute((int) $competition['id']) : [];
        $teamRank = null;
        foreach ($ranking as $r) if ($r['team_id'] === $id) { $teamRank = $r; break; }

        $view = dirname(__DIR__, 2) . '/resources/views/public/team-detail.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }
}