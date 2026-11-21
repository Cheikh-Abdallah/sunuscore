<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\MatchAward;
use App\Models\MatchEvent;
use App\Models\MatchModel;
use App\Models\Team;
use App\Services\ScoreService;

final class LiveController
{
    /** Console opérateur. */
    public function console(int $matchId): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $match = MatchModel::findById($matchId);
        if ($match === null) { Response::redirect(url('/admin/matches')); }

        $competitionId = (int) $match['competition_id'];
        $homeRoster = Team::roster((int) $match['home_team_id'], $competitionId);
        $awayRoster = Team::roster((int) $match['away_team_id'], $competitionId);
        $events = MatchEvent::allByMatch($matchId);
        $award  = MatchAward::manOfTheMatch($matchId);

        $title = 'Console LIVE — ' . $match['home_team_name'] . ' vs ' . $match['away_team_name'];
        $view = dirname(__DIR__, 2) . '/resources/views/admin/live/console.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    /* ---------- Actions ---------- */

    public function start(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::start($matchId, (int) Auth::id());
            Session::set('flash_success', 'Match démarré.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function minute(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::updateMinute($matchId, (int) ($_POST['minute'] ?? 0), (int) ($_POST['extra'] ?? 0));
            Session::set('flash_success', 'Minute mise à jour.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function halfTime(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::halfTime($matchId, (int) Auth::id());
            Session::set('flash_success', 'Mi-temps enregistrée.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function resume(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::resume($matchId, (int) Auth::id());
            Session::set('flash_success', 'Match repris.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function suspend(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::suspend($matchId, (int) Auth::id());
            Session::set('flash_success', 'Match suspendu.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function finish(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::finish($matchId, (int) Auth::id());
            Session::set('flash_success', 'Match terminé.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function addGoal(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::addGoal(
                $matchId,
                (int) ($_POST['team_id'] ?? 0),
                !empty($_POST['player_id']) ? (int) $_POST['player_id'] : null,
                !empty($_POST['assist_player_id']) ? (int) $_POST['assist_player_id'] : null,
                (int) ($_POST['minute'] ?? 0),
                (int) ($_POST['extra'] ?? 0),
                (string) ($_POST['event_type'] ?? 'goal'),
                (int) Auth::id()
            );
            Session::set('flash_success', 'But enregistré.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function addCard(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::addCard(
                $matchId,
                (int) ($_POST['team_id'] ?? 0),
                !empty($_POST['player_id']) ? (int) $_POST['player_id'] : null,
                (int) ($_POST['minute'] ?? 0),
                (int) ($_POST['extra'] ?? 0),
                (string) ($_POST['event_type'] ?? 'yellow_card'),
                trim((string) ($_POST['reason'] ?? '')) ?: null,
                (int) Auth::id()
            );
            Session::set('flash_success', 'Carton enregistré.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function addSubstitution(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::addSubstitution(
                $matchId,
                (int) ($_POST['team_id'] ?? 0),
                (int) ($_POST['player_in_id'] ?? 0),
                (int) ($_POST['player_out_id'] ?? 0),
                (int) ($_POST['minute'] ?? 0),
                (int) ($_POST['extra'] ?? 0),
                (int) Auth::id()
            );
            Session::set('flash_success', 'Remplacement enregistré.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function voidEvent(int $matchId, int $eventId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->csrf();
        try {
            ScoreService::voidEvent($eventId, (int) Auth::id(), trim((string) ($_POST['reason'] ?? '')));
            Session::set('flash_success', 'Événement annulé, score recalculé.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    public function setAward(int $matchId): void
    {
        $this->guard([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $this->csrf();
        try {
            ScoreService::setAward(
                $matchId,
                (int) ($_POST['player_id'] ?? 0),
                (int) ($_POST['team_id'] ?? 0),
                trim((string) ($_POST['reason'] ?? '')) ?: null,
                (int) Auth::id()
            );
            Session::set('flash_success', 'Homme du match désigné.');
        } catch (\Throwable $e) {
            Session::set('flash_error', $e->getMessage());
        }
        Response::redirect(url('/admin/live/' . $matchId));
    }

    /* ---------- Helpers ---------- */

    private function guard(array $roles): void
    {
        AuthMiddleware::handle($roles);
    }

    private function csrf(): void
    {
        if (!Csrf::validate(Csrf::fromPost())) {
            Session::set('flash_error', 'Session expirée.');
            Response::redirect(url('/admin/matches'));
        }
    }
}