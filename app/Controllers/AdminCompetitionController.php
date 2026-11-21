<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Middleware\AuthMiddleware;
use App\Models\Competition;

final class AdminCompetitionController
{
    public function index(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $competitions = Competition::all();
        $title = 'Compétitions';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/competitions/index.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $competition = null;
        $errors = [];
        $title = 'Nouvelle compétition';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/competitions/form.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->validateCsrf();
        $data = $this->extract();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            Session::set('form_errors', $errors);
            Session::set('form_old', $data);
            Response::redirect(url('/admin/competitions/create'));
        }

        $data['created_by'] = Auth::id();
        Competition::create($data);
        Session::set('flash_success', 'Compétition créée avec succès.');
        Response::redirect(url('/admin/competitions'));
    }

    public function edit(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $competition = Competition::findById($id);
        if ($competition === null) { Response::redirect(url('/admin/competitions')); }
        $errors = [];
        $title = 'Modifier la compétition';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/competitions/form.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function update(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->validateCsrf();
        $competition = Competition::findById($id);
        if ($competition === null) { Response::redirect(url('/admin/competitions')); }

        $data = $this->extract();
        $errors = $this->validate($data, $id);

        if (!empty($errors)) {
            Session::set('form_errors', $errors);
            Session::set('form_old', $data);
            Response::redirect(url('/admin/competitions/' . $id . '/edit'));
        }

        Competition::update($id, $data);
        Session::set('flash_success', 'Compétition modifiée.');
        Response::redirect(url('/admin/competitions'));
    }

    public function delete(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->validateCsrf();
        $deps = Competition::countDependencies($id);
        if ($deps > 0) {
            Session::set('flash_error', "Impossible de supprimer : {$deps} match(s) lié(s). Archivez-la plutôt.");
            Response::redirect(url('/admin/competitions'));
        }
        Competition::delete($id);
        Session::set('flash_success', 'Compétition supprimée.');
        Response::redirect(url('/admin/competitions'));
    }

    private function extract(): array
    {
        return [
            'name'         => trim((string) ($_POST['name'] ?? '')),
            'slug'         => trim((string) ($_POST['slug'] ?? '')),
            'description'  => trim((string) ($_POST['description'] ?? '')),
            'location'     => trim((string) ($_POST['location'] ?? '')),
            'start_date'   => (string) ($_POST['start_date'] ?? ''),
            'end_date'     => (string) ($_POST['end_date'] ?? ''),
            'points_win'   => (int) ($_POST['points_win'] ?? 3),
            'points_draw'  => (int) ($_POST['points_draw'] ?? 1),
            'points_loss'  => (int) ($_POST['points_loss'] ?? 0),
            'status'       => (string) ($_POST['status'] ?? 'draft'),
        ];
    }

    private function validate(array $data, ?int $id = null): array
    {
        $errors = [];
        if ($data['name'] === '') $errors['name'] = 'Le nom est obligatoire.';
        if ($data['slug'] === '') $errors['slug'] = 'Le slug est obligatoire.';
        if ($data['slug'] !== '' && Competition::slugExists($data['slug'], $id)) {
            $errors['slug'] = 'Ce slug est déjà utilisé.';
        }
        if (!in_array($data['status'], ['draft','upcoming','ongoing','finished','archived'], true)) {
            $errors['status'] = 'Statut invalide.';
        }
        return $errors;
    }

    private function validateCsrf(): void
    {
        if (!Csrf::validate(Csrf::fromPost())) {
            Session::set('flash_error', 'Session expirée.');
            Response::redirect(url('/admin/competitions'));
        }
    }
}