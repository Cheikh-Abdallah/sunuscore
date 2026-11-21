<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Middleware\AuthMiddleware;

final class AdminUserController
{
    public function index(): void
    {
        AuthMiddleware::handle([Auth::ROLE_SUPER_ADMIN]);

        $users = Database::getInstance()->query(
            "SELECT u.id, u.username, u.email, u.is_active, u.last_login_at,
                    r.code AS role_code, r.label AS role_label
             FROM users u JOIN roles r ON r.id = u.role_id
             ORDER BY u.id ASC"
        )->fetchAll();

        $title = 'Utilisateurs';
        $view = dirname(__DIR__, 2) . '/resources/views/admin/users/index.php';
        require dirname(__DIR__, 2) . '/resources/views/layouts/admin.php';
    }
}