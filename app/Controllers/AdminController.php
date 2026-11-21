<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Middleware\AuthMiddleware;

final class AdminController
{
    public function dashboard(): void
    {
        AuthMiddleware::handle([
            Auth::ROLE_SUPER_ADMIN,
            Auth::ROLE_ADMIN,
            Auth::ROLE_OPERATOR,
        ]);

        $pdo = Database::getInstance();

        $stats = [
            'competitions' => (int) $pdo->query("SELECT COUNT(*) FROM competitions")->fetchColumn(),
            'teams'        => (int) $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn(),
            'players'      => (int) $pdo->query("SELECT COUNT(*) FROM players")->fetchColumn(),
            'matches'      => (int) $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn(),
            'matches_live' => (int) $pdo->query("SELECT COUNT(*) FROM matches WHERE status IN ('live','half_time','suspended')")->fetchColumn(),
            'matches_finished' => (int) $pdo->query("SELECT COUNT(*) FROM matches WHERE status='finished'")->fetchColumn(),
            'goals'        => (int) $pdo->query("SELECT COUNT(*) FROM match_events WHERE event_type IN ('goal','penalty_goal','own_goal') AND is_voided=0")->fetchColumn(),
            'cards'        => (int) $pdo->query("SELECT COUNT(*) FROM match_events WHERE event_type IN ('yellow_card','red_card') AND is_voided=0")->fetchColumn(),
        ];

        // Derniers matchs (5)
        $recentMatches = $pdo->query(
            "SELECT m.*, th.name AS home_team_name, ta.name AS away_team_name
             FROM matches m
             JOIN teams th ON th.id = m.home_team_id
             JOIN teams ta ON ta.id = m.away_team_id
             ORDER BY m.scheduled_at DESC LIMIT 5"
        )->fetchAll();

        // Prochains matchs (5)
        $upcomingMatches = $pdo->query(
            "SELECT m.*, th.name AS home_team_name, ta.name AS away_team_name
             FROM matches m
             JOIN teams th ON th.id = m.home_team_id
             JOIN teams ta ON ta.id = m.away_team_id
             WHERE m.status = 'scheduled' AND m.scheduled_at >= NOW()
             ORDER BY m.scheduled_at ASC LIMIT 5"
        )->fetchAll();

        $view = dirname(__DIR__, 2) . '/resources/views/admin/dashboard.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }
}