<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Competition;
use App\Models\MatchModel;

final class AdminMatchController
{
    public function index(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN, Auth::ROLE_OPERATOR]);
        $filter = $_GET['filter'] ?? 'all';
        $allowed = ['all', 'upcoming', 'live', 'finished', 'postponed'];
        if (!in_array($filter, $allowed, true)) $filter = 'all';

        $matches = MatchModel::allForAdmin($filter);
        $title = 'Matchs';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/matches/index.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $competitions = Competition::all();
        $teams = [];
        $competitionId = (int) ($_GET['competition_id'] ?? 0);
        if ($competitionId > 0) {
            $teams = MatchModel::teamsForSelect($competitionId);
        }
        $match = null;
        $errors = [];
        $title = 'Nouveau match';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/matches/form.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->csrf();
        $data = $this->extract();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            Session::set('form_errors', $errors);
            Session::set('form_old', $data);
            Response::redirect(url('/admin/matches/create'));
        }

        $data['created_by'] = Auth::id();
        MatchModel::create($data);
        Session::set('flash_success', 'Match créé avec succès.');
        Response::redirect(url('/admin/matches'));
    }

    public function edit(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $match = MatchModel::findById($id);
        if ($match === null) { Response::redirect(url('/admin/matches')); }

        // Interdire l'édition des matchs en cours / terminés
        if (in_array($match['status'], ['live', 'half_time', 'suspended', 'finished'], true)) {
            Session::set('flash_error', 'Un match en direct ou terminé ne peut plus être modifié.');
            Response::redirect(url('/admin/matches'));
        }

        $competitions = Competition::all();
        $teams = MatchModel::teamsForSelect((int) $match['competition_id']);
        $errors = [];
        $title = 'Modifier le match';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/matches/form.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function update(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->csrf();
        $match = MatchModel::findById($id);
        if ($match === null) { Response::redirect(url('/admin/matches')); }

        if (in_array($match['status'], ['live', 'half_time', 'suspended', 'finished'], true)) {
            Session::set('flash_error', 'Match non modifiable.');
            Response::redirect(url('/admin/matches'));
        }

        $data = $this->extract();
        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            Session::set('form_errors', $errors);
            Session::set('form_old', $data);
            Response::redirect(url('/admin/matches/' . $id . '/edit'));
        }

        MatchModel::update($id, $data);
        Session::set('flash_success', 'Match modifié.');
        Response::redirect(url('/admin/matches'));
    }

    public function delete(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->csrf();
        $deps = MatchModel::countDependencies($id);
        if ($deps > 0) {
            Session::set('flash_error', "Impossible de supprimer : {$deps} événement(s) lié(s). Annulez-le plutôt.");
            Response::redirect(url('/admin/matches'));
        }
        MatchModel::delete($id);
        Session::set('flash_success', 'Match supprimé.');
        Response::redirect(url('/admin/matches'));
    }

    /* ---------- Helpers ---------- */

    private function extract(): array
    {
        return [
            'competition_id' => (int) ($_POST['competition_id'] ?? 0),
            'home_team_id'   => (int) ($_POST['home_team_id'] ?? 0),
            'away_team_id'   => (int) ($_POST['away_team_id'] ?? 0),
            'matchday'       => trim((string) ($_POST['matchday'] ?? '')),
            'phase'          => trim((string) ($_POST['phase'] ?? '')),
            'venue'          => trim((string) ($_POST['venue'] ?? '')),
            'scheduled_at'   => trim((string) ($_POST['scheduled_at'] ?? '')),
            'call_time'      => trim((string) ($_POST['call_time'] ?? '')),
            'status'         => (string) ($_POST['status'] ?? 'scheduled'),
            'zone_id'        => (int) ($_POST['zone_id'] ?? 0),
            'pool_id'        => (int) ($_POST['pool_id'] ?? 0),
        ];
    }

    private function validate(array $data, ?int $exceptId = null): array
    {
        $errors = [];

        if ($data['competition_id'] <= 0) {
            $errors['competition_id'] = 'Sélectionnez une compétition.';
        }
        if ($data['home_team_id'] <= 0) {
            $errors['home_team_id'] = 'Sélectionnez l\'équipe à domicile.';
        }
        if ($data['away_team_id'] <= 0) {
            $errors['away_team_id'] = 'Sélectionnez l\'équipe extérieure.';
        }
        if ($data['home_team_id'] > 0 && $data['home_team_id'] === $data['away_team_id']) {
            $errors['away_team_id'] = 'Une équipe ne peut pas jouer contre elle-même.';
        }
        if ($data['scheduled_at'] === '') {
            $errors['scheduled_at'] = 'La date et l\'heure sont obligatoires.';
        } elseif (strtotime($data['scheduled_at']) === false) {
            $errors['scheduled_at'] = 'Date invalide.';
        }
        if (!in_array($data['status'], ['scheduled','postponed','cancelled'], true)) {
            $errors['status'] = 'Statut invalide à la programmation.';
        }
        if ($data['matchday'] !== '' && (!ctype_digit($data['matchday']) || (int) $data['matchday'] < 1)) {
            $errors['matchday'] = 'Journée invalide.';
        }

        // Validations croisées uniquement si les IDs sont fournis
        if (empty($errors) || !isset($errors['competition_id'])) {
            if ($data['home_team_id'] > 0 && !MatchModel::isTeamInCompetition($data['home_team_id'], $data['competition_id'])) {
                $errors['home_team_id'] = 'Cette équipe n\'est pas inscrite à la compétition.';
            }
            if ($data['away_team_id'] > 0 && !MatchModel::isTeamInCompetition($data['away_team_id'], $data['competition_id'])) {
                $errors['away_team_id'] = 'Cette équipe n\'est pas inscrite à la compétition.';
            }
        }

        if (empty($errors['scheduled_at']) && $data['home_team_id'] > 0 && $data['away_team_id'] > 0) {
            $conflict = MatchModel::findScheduleConflict(
                $data['home_team_id'], $data['away_team_id'], $data['scheduled_at'], $exceptId
            );
            if ($conflict !== null) {
                $errors['scheduled_at'] = $conflict;
            } elseif (MatchModel::findDuplicate(
                $data['competition_id'], $data['home_team_id'], $data['away_team_id'], $data['scheduled_at'], $exceptId
            )) {
                $errors['scheduled_at'] = 'Ce match existe déjà (même affiche, même jour).';
            }
        }

        if ($data['status'] === 'scheduled' && $data['scheduled_at'] !== '' && strtotime($data['scheduled_at']) < time()) {
            $errors['scheduled_at'] = 'Un match programmé doit être dans le futur.';
        }

        return $errors;
    }

    private function csrf(): void
    {
        if (!Csrf::validate(Csrf::fromPost())) {
            Session::set('flash_error', 'Session expirée.');
            Response::redirect(url('/admin/matches'));
        }
    }
}