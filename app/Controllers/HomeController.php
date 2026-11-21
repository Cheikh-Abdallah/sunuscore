<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Competition;
use App\Models\MatchModel;
use App\Services\RankingService;
use App\Services\StatisticsService;

final class HomeController
{
    public function index(): void
    {
        $competition = Competition::findFirstActive();
        if ($competition === null) {
            Response::redirect('/competitions');
        }
        $cid = (int) $competition['id'];

        $live       = MatchModel::currentLive();
        $next       = $live === null ? MatchModel::nextUpcoming($cid) : null;
        $recent     = MatchModel::recentFinished($cid, 5);
        $ranking    = RankingService::compute($cid);
        $rankingTop = array_slice($ranking, 0, 5);
        $topScorers = StatisticsService::topScorers($cid, 3);

        $stats = [
            'teams'   => (int) $competition['teams_count'],
            'matches' => count(MatchModel::byCompetition($cid, 'all')),
        ];

        $view = dirname(__DIR__, 2) . '/resources/views/public/home.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }
}