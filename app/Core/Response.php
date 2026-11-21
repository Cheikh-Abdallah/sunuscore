<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Réponse HTTP standardisée (JSON pour l'API, HTML pour le web).
 */
final class Response
{
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => [],
        ], $status);
    }

    public static function error(string $message, array $errors = [], int $status = 400): never
    {
        self::json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $status);
    }

    public static function redirect(string $url): never
    {
        header('Location: ' . $url, true, 302);
        exit;
    }
}