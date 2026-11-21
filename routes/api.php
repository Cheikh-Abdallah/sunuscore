<?php
declare(strict_types=1);

/**
 * API REST SUNUSCORE (lecture publique).
 */

use App\Core\Router;
use App\Controllers\ApiController;

/** @var Router $router */

/* Health */
$router->get('/api/health', fn() => (new ApiController())->health());

/* Compétitions */
$router->get('/api/competitions',     fn() => (new ApiController())->competitions());
$router->get('/api/competitions/{id}', fn(string $id) => (new ApiController())->competition((int) $id));

/* Matchs */
$router->get('/api/matches',             fn() => (new ApiController())->matches());
$router->get('/api/matches/{id}',        fn(string $id) => (new ApiController())->match((int) $id));
$router->get('/api/matches/{id}/events', fn(string $id) => (new ApiController())->matchEvents((int) $id));
$router->get('/api/matches/{id}/live',   fn(string $id) => (new ApiController())->matchLive((int) $id));

/* Équipes / Joueurs */
$router->get('/api/teams',      fn() => (new ApiController())->teams());
$router->get('/api/teams/{id}', fn(string $id) => (new ApiController())->team((int) $id));
$router->get('/api/players',    fn() => (new ApiController())->players());

/* Classement */
$router->get('/api/rankings', fn() => (new ApiController())->rankings());

/* Statistiques */
$router->get('/api/top-scorers',      fn() => (new ApiController())->topScorers());
$router->get('/api/man-of-the-match', fn() => (new ApiController())->manOfTheMatch());
$router->get('/api/statistics',       fn() => (new ApiController())->statistics());