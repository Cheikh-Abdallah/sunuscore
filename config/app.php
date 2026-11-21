<?php
/**
 * SUNUSCORE - Configuration applicative
 * Charge les variables d'environnement depuis .env (via parser maison léger)
 * et expose la configuration sous forme de tableau.
 */
declare(strict_types=1);

// Chargement minimaliste du .env (aucune dépendance externe)
$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Retirer les guillemets
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

$timezone = getenv('APP_TIMEZONE') ?: 'Africa/Dakar';
date_default_timezone_set($timezone);

return [
    'name'      => getenv('APP_NAME') ?: 'SUNUSCORE',
    'env'       => getenv('APP_ENV') ?: 'local',
    'debug'     => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOL),
    'url'       => rtrim(getenv('APP_URL') ?: 'http://localhost/sunuscore/public', '/'),
    'timezone'  => $timezone,
    'session'   => [
        'name'     => getenv('SESSION_NAME') ?: 'SUNUSCORE_SESSID',
        'lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 7200),
    ],
    'csrf'      => [
        'token_name' => getenv('CSRF_TOKEN_NAME') ?: '_csrf_token',
    ],
];