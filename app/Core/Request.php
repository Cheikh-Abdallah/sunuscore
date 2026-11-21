<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Encapsule la requête HTTP entrante.
 *
 * Normalise l'URI en retirant automatiquement le préfixe du dossier
 * public (ex: /sunuscore/public/matches → /matches), afin que le
 * routeur travaille toujours avec des chemins "propres" quel que soit
 * l'emplacement de l'application (racine domaine OU sous-dossier XAMPP).
 */
final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // 1. Retirer la query string
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // 2. Retirer le préfixe du dossier public calculé depuis SCRIPT_NAME
        //    ex: SCRIPT_NAME = /sunuscore/public/index.php
        //        base       = /sunuscore/public
        //        uri        = /sunuscore/public/matches  →  /matches
        $base = $this->basePath();
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        // 3. Retirer /index.php s'il est explicite dans l'URL
        if (str_starts_with($uri, '/index.php')) {
            $uri = substr($uri, strlen('/index.php'));
        }

        // 4. Normaliser : toujours un slash initial, pas de slash final
        $uri = '/' . trim($uri, '/');

        return $uri === '' ? '/' : $uri;
    }

    /**
     * Calcule le chemin de base (dossier public) depuis SCRIPT_NAME.
     * Retourne '' si l'app est à la racine du domaine.
     */
    private function basePath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        // SCRIPT_NAME = /sunuscore/public/index.php
        // dirname      = /sunuscore/public
        $dir = str_replace('\\', '/', dirname($script));
        $dir = rtrim($dir, '/');
        return $dir === '.' || $dir === '/' ? '' : $dir;
    }

    public function isApi(): bool
    {
        return str_starts_with($this->uri(), '/api/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function json(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST, $this->json());
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? null;
    }
}