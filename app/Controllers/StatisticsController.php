<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Competition;
use App\Services\StatisticsService;

final class StatisticsController
{
    public function topScorers(): void
    {
        $competition = Competition::findFirstActive();
        if ($competition === null) Response::redirect('/competitions');
        $scorers = StatisticsService::topScorers((int) $competition['id'], 50);
        $view = dirname(__DIR__, 2) . '/resources/views/public/top-scorers.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }

    public function menOfTheMatch(): void
    {
        $competition = Competition::findFirstActive();
        if ($competition === null) Response::redirect('/competitions');
        $motm = StatisticsService::menOfTheMatch((int) $competition['id'], 100);
        $top  = StatisticsService::topMotm((int) $competition['id'], 10);
        $view = dirname(__DIR__, 2) . '/resources/views/public/man-of-the-match.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }

    public function statistics(): void
    {
        $competition = Competition::findFirstActive();
        if ($competition === null) Response::redirect('/competitions');

        $cid = (int) $competition['id'];

        $data = [
            'competition'    => $competition,
            'global'         => StatisticsService::globalStats($cid),
            'results'        => StatisticsService::resultsBreakdown($cid),
            'goalsByMatchday'=> StatisticsService::goalsByMatchday($cid),
            'scorers'        => StatisticsService::topScorers($cid, 10),
            'cards'          => StatisticsService::cardsByTeam($cid),
            'motm'           => StatisticsService::topMotm($cid, 5),
        ];

        $view = dirname(__DIR__, 2) . '/resources/views/public/statistics.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }
}