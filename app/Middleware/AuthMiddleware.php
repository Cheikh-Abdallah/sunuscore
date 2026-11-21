<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Response;

/**
 * Protège les routes admin.
 * - Redirige vers /login si non authentifié.
 * - Renvoie 403 si le rôle n'est pas autorisé.
 */
final class AuthMiddleware
{
    /**
     * @param string[] $allowedRoles
     */
    public static function handle(array $allowedRoles = []): void
    {
        if (!Auth::check()) {
            Response::redirect(url('/login'));
        }

        if (!empty($allowedRoles) && !Auth::hasRole(...$allowedRoles)) {
            http_response_code(403);
            echo '403 - Accès interdit';
            exit;
        }
    }
}