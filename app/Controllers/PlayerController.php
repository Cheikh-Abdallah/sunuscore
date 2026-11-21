<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Competition;
use App\Models\Player;

final class PlayerController
{
    public function index(): void
    {
        $competition = Competition::findFirstActive();
        if ($competition === null) Response::redirect('/competitions');

        $players = Player::allByCompetition((int) $competition['id']);

        $view = dirname(__DIR__, 2) . '/resources/views/public/players.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/public.php';
    }
}