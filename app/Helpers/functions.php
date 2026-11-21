<?php
declare(strict_types=1);

/**
 * Helpers globaux - chargés automatiquement via composer "files".
 */

if (!function_exists('config')) {
    /** Accès rapide à la config (clé pointée ex: "app.url") */
    function config(string $key, mixed $default = null): mixed
    {
        static $cache = [];
        $parts = explode('.', $key, 2);
        $file  = $parts[0];
        $sub   = $parts[1] ?? null;

        if (!isset($cache[$file])) {
            $path = dirname(__DIR__, 2) . "/config/{$file}.php";
            if (!is_file($path)) {
                return $default;
            }
            $cache[$file] = require $path;
        }

        if ($sub === null) {
            return $cache[$file];
        }
        return $cache[$file][$sub] ?? $default;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

if (!function_exists('e')) {
    /** Échappement HTML (anti-XSS) */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /** Construit une URL absolue à partir de APP_URL */
    function url(string $path = ''): string
    {
        $base = rtrim((string) config('app.url', ''), '/');
        $path = '/' . ltrim($path, '/');
        return $base . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        \App\Core\Response::redirect(url($path));
    }
}

if (!function_exists('now')) {
    function now(string $format = 'Y-m-d H:i:s'): string
    {
        return (new DateTimeImmutable('now'))->format($format);
    }
}

if (!function_exists('json_response')) {
    function json_response(array $payload, int $status = 200): never
    {
        \App\Core\Response::json($payload, $status);
    }
}