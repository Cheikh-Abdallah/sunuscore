<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Team;

final class AdminTeamController
{
    public function index(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $teams = Team::all();
        $title = 'Équipes';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/teams/index.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $team = null; $errors = []; $title = 'Nouvelle équipe';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/teams/form.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->csrf();
        $data = $this->extract();
        $errors = $this->validate($data);
        if (!empty($errors)) {
            Session::set('form_errors', $errors); Session::set('form_old', $data);
            Response::redirect(url('/admin/teams/create'));
        }
        $data['created_by'] = Auth::id();
        Team::create($data);
        Session::set('flash_success', 'Équipe créée.');
        Response::redirect(url('/admin/teams'));
    }

    public function edit(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $team = Team::findById($id);
        if ($team === null) Response::redirect(url('/admin/teams'));
        $errors = []; $title = 'Modifier l\'équipe';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/teams/form.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function update(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->csrf();
        $team = Team::findById($id);
        if ($team === null) Response::redirect(url('/admin/teams'));
        $data = $this->extract();
        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            Session::set('form_errors', $errors); Session::set('form_old', $data);
            Response::redirect(url('/admin/teams/' . $id . '/edit'));
        }
        Team::update($id, $data);
        Session::set('flash_success', 'Équipe modifiée.');
        Response::redirect(url('/admin/teams'));
    }

    public function delete(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->csrf();
        $deps = Team::countDependencies($id);
        if ($deps > 0) {
            Session::set('flash_error', "Impossible de supprimer : {$deps} dépendance(s) (matchs ou inscriptions).");
            Response::redirect(url('/admin/teams'));
        }
        Team::delete($id);
        Session::set('flash_success', 'Équipe supprimée.');
        Response::redirect(url('/admin/teams'));
    }

    private function extract(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'neighborhood' => trim((string) ($_POST['neighborhood'] ?? '')),
            'president_name' => trim((string) ($_POST['president_name'] ?? '')),
            'coach_name' => trim((string) ($_POST['coach_name'] ?? '')),
            'founded_year' => trim((string) ($_POST['founded_year'] ?? '')),
            'contact_phone' => trim((string) ($_POST['contact_phone'] ?? '')),
            'contact_email' => trim((string) ($_POST['contact_email'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
        ];
    }

    private function validate(array $data, ?int $id = null): array
    {
        $errors = [];
        if ($data['name'] === '') $errors['name'] = 'Le nom est obligatoire.';
        if ($data['slug'] === '') $errors['slug'] = 'Le slug est obligatoire.';
        if ($data['slug'] !== '' && Team::slugExists($data['slug'], $id)) {
            $errors['slug'] = 'Ce slug est déjà utilisé.';
        }
        if ($data['contact_email'] !== '' && !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = 'Email invalide.';
        }
        return $errors;
    }

    private function csrf(): void
    {
        if (!Csrf::validate(Csrf::fromPost())) {
            Session::set('flash_error', 'Session expirée.');
            Response::redirect(url('/admin/teams'));
        }
    }
}