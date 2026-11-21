<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Competition;
use App\Models\MatchAward;
use App\Models\MatchEvent;
use App\Models\MatchModel;

final class MatchController
{
    public function index(): void
    {
        $competition = Competition::findFirstActive();
        if ($competition === null) Response::redirect('/competitions');
        $filter  = $_GET['filter'] ?? 'all';
        $allowed = ['all', 'live', 'upcoming', 'finished', 'postponed'];
        if (!in_array($filter, $allowed, true)) $filter = 'all';
        $matches = MatchModel::byCompetition((int) $competition['id'], $filter);
        $view = dirname(__DIR__, 2) . '/resources/views/public/matches.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }

    public function show(int $id): void
    {
        $match = MatchModel::findById($id);
        if ($match === null) {
            http_response_code(404);
            $view = dirname(__DIR__, 2) . '/resources/views/errors/404.php';
            require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
            return;
        }
        $events = MatchEvent::allByMatch($id);
        $award  = MatchAward::manOfTheMatch($id);
        $view = dirname(__DIR__, 2) . '/resources/views/public/match-detail.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }
}