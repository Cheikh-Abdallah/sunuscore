<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    /** Retourne l'utilisateur + le code du rôle, ou null. */
    public static function findByUsername(string $username): ?array
    {
        $sql = "SELECT u.*, r.code AS role_code, r.label AS role_label
                FROM users u
                JOIN roles r ON r.id = u.role_id
                WHERE u.username = :u OR u.email = :e
                LIMIT 1";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':u' => $username, ':e' => $username]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findById(int $id): ?array
    {
        $sql = "SELECT u.*, r.code AS role_code, r.label AS role_label
                FROM users u
                JOIN roles r ON r.id = u.role_id
                WHERE u.id = :id LIMIT 1";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function updateLastLogin(int $id, string $ip): void
    {
        $stmt = Database::getInstance()->prepare(
            "UPDATE users SET last_login_at = NOW(), last_login_ip = :ip, failed_attempts = 0, locked_until = NULL
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id, ':ip' => $ip]);
    }

    public static function incrementFailedAttempts(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT failed_attempts FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $current = (int) $stmt->fetchColumn();
        $new = $current + 1;
        $lockedUntil = $new >= 5 ? date('Y-m-d H:i:s', time() + 900) : null; // 15 min

        $upd = $pdo->prepare("UPDATE users SET failed_attempts = :fa, locked_until = :lu WHERE id = :id");
        $upd->execute([':fa' => $new, ':lu' => $lockedUntil, ':id' => $id]);
    }

    public static function countByRole(string $roleCode): int
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.code = :c"
        );
        $stmt->execute([':c' => $roleCode]);
        return (int) $stmt->fetchColumn();
    }
}