<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Player;

final class AdminPlayerController
{
    public function index(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $players = Player::all();
        $title = 'Joueurs';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/players/index.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $player = null; $errors = []; $title = 'Nouveau joueur';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/players/form.php';
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
            Response::redirect(url('/admin/players/create'));
        }
        $data['created_by'] = Auth::id();
        Player::create($data);
        Session::set('flash_success', 'Joueur créé.');
        Response::redirect(url('/admin/players'));
    }

    public function edit(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $player = Player::findById($id);
        if ($player === null) Response::redirect(url('/admin/players'));
        $errors = []; $title = 'Modifier le joueur';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/players/form.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }

    public function update(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->csrf();
        $player = Player::findById($id);
        if ($player === null) Response::redirect(url('/admin/players'));
        $data = $this->extract();
        $errors = $this->validate($data);
        if (!empty($errors)) {
            Session::set('form_errors', $errors); Session::set('form_old', $data);
            Response::redirect(url('/admin/players/' . $id . '/edit'));
        }
        Player::update($id, $data);
        Session::set('flash_success', 'Joueur modifié.');
        Response::redirect(url('/admin/players'));
    }

    public function delete(int $id): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN, Auth::ROLE_ADMIN]);
        $this->csrf();
        $deps = Player::countDependencies($id);
        if ($deps > 0) {
            Session::set('flash_error', "Impossible de supprimer : {$deps} dépendance(s).");
            Response::redirect(url('/admin/players'));
        }
        Player::delete($id);
        Session::set('flash_success', 'Joueur supprimé.');
        Response::redirect(url('/admin/players'));
    }

    private function extract(): array
    {
        return [
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name'  => trim((string) ($_POST['last_name'] ?? '')),
            'birth_date' => (string) ($_POST['birth_date'] ?? ''),
            'phone'      => trim((string) ($_POST['phone'] ?? '')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['first_name'] === '') $errors['first_name'] = 'Prénom obligatoire.';
        if ($data['last_name'] === '') $errors['last_name'] = 'Nom obligatoire.';
        return $errors;
    }

    private function csrf(): void
    {
        if (!Csrf::validate(Csrf::fromPost())) {
            Session::set('flash_error', 'Session expirée.');
            Response::redirect(url('/admin/players'));
        }
    }
}