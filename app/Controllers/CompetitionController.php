<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Competition;

final class CompetitionController
{
    public function index(): void
    {
        $status = $_GET['status'] ?? 'all';
        $all    = Competition::all();
        $competitions = array_filter($all, function (array $c) use ($status): bool {
            if ($status === 'all') return true;
            return $c['status'] === $status;
        });

        $view = dirname(__DIR__, 2) . '/resources/views/public/competitions.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }
}