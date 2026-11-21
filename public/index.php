<?php
declare(strict_types=1);

/**
 * SUNUSCORE - Point d'entrée unique (front controller).
 */

// Démarrer le buffer pour éviter les headers déjà envoyés
ob_start();

// Chargement de la configuration (définit fuseau, lit .env)
require dirname(__DIR__) . '/config/app.php';

// Autoloading Composer
$vendor = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($vendor)) {
    http_response_code(500);
    echo 'Composer non installé. Exécutez "composer install" à la racine du projet.';
    exit;
}
require $vendor;

use App\Core\Request;
use App\Core\Router;
use App\Core\Response;

// En mode debug, afficher les erreurs (jamais en production)
if (config('app.debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-error.log');
}

// Gestionnaire global d'exceptions
set_exception_handler(function (Throwable $e) {
    error_log('[SUNUSCORE][EXCEPTION] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
    if ($isApi) {
        Response::error('Erreur serveur.', [], 500);
    }
    http_response_code(500);
    echo 'Une erreur interne est survenue.';
    exit;
});

$request = new Request();
$router  = new Router();

require dirname(__DIR__) . '/routes/web.php';
require dirname(__DIR__) . '/routes/api.php';

$router->dispatch($request);

ob_end_flush();