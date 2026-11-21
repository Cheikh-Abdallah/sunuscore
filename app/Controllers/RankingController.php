<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Competition;
use App\Services\RankingService;

final class RankingController
{
    public function index(): void
    {
        $competition = Competition::findFirstActive();
        if ($competition === null) Response::redirect('/competitions');
        $ranking = RankingService::compute((int) $competition['id']);
        $view = dirname(__DIR__, 2) . '/resources/views/public/rankings.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }
}