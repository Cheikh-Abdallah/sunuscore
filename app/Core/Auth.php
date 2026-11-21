<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Authentification - squelette pour la Phase 3.
 * Expose les helpers de session utilisateur et de contrôle de rôle.
 */
final class Auth
{
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN       = 'admin';
    public const ROLE_OPERATOR    = 'operator';

    public static function user(): ?array
    {
        Session::start();
        $user = Session::get('auth_user');
        return is_array($user) ? $user : null;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u !== null ? (int) $u['id'] : null;
    }

    public static function role(): ?string
    {
        $u = self::user();
        return $u['role'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $role = self::role();
        return $role !== null && in_array($role, $roles, true);
    }

    public static function login(array $user): void
    {
        Session::start();
        Session::regenerate();
        Session::set('auth_user', [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'] ?? null,
            'role'     => $user['role'],
        ]);
    }

    public static function logout(): void
    {
        Session::start();
        Session::destroy();
    }
}