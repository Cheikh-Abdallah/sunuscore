<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\CompetitionController;
use App\Controllers\MatchController;
use App\Controllers\RankingController;
use App\Controllers\TeamController;
use App\Controllers\PlayerController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\AdminCompetitionController;
use App\Controllers\AdminTeamController;
use App\Controllers\AdminPlayerController;
use App\Controllers\AdminUserController;
use App\Controllers\AdminMatchController;
use App\Controllers\LiveController;
use App\Controllers\StatisticsController;
use App\Controllers\LiveApiController;

/** @var Router $router */

/* Public */
$router->get('/',             fn() => (new HomeController())->index());
$router->get('/competitions', fn() => (new CompetitionController())->index());
$router->get('/matches',      fn() => (new MatchController())->index());
$router->get('/matches/{id}', fn(string $id) => (new MatchController())->show((int) $id));
$router->get('/matches/{id}/live', fn(string $id) => (new LiveApiController())->get((int) $id));
$router->get('/rankings',     fn() => (new RankingController())->index());
$router->get('/teams',        fn() => (new TeamController())->index());
$router->get('/teams/{id}',   fn(string $id) => (new TeamController())->show((int) $id));
$router->get('/players',      fn() => (new PlayerController())->index());


$router->get('/top-scorers',      fn() => (new StatisticsController())->topScorers());
$router->get('/man-of-the-match', fn() => (new StatisticsController())->menOfTheMatch());
$router->get('/statistics',       fn() => (new StatisticsController())->statistics());

/* Auth */
$router->get('/login',  fn() => (new AuthController())->showLogin());
$router->post('/login', fn() => (new AuthController())->login());
$router->post('/logout', fn() => (new AuthController())->logout());

/* Admin - dashboard */
$router->get('/admin', fn() => (new AdminController())->dashboard());

/* Admin - competitions */
$router->get('/admin/competitions',              fn() => (new AdminCompetitionController())->index());
$router->get('/admin/competitions/create',       fn() => (new AdminCompetitionController())->create());
$router->post('/admin/competitions',             fn() => (new AdminCompetitionController())->store());
$router->get('/admin/competitions/{id}/edit',    fn(string $id) => (new AdminCompetitionController())->edit((int) $id));
$router->post('/admin/competitions/{id}',        fn(string $id) => (new AdminCompetitionController())->update((int) $id));
$router->post('/admin/competitions/{id}/delete', fn(string $id) => (new AdminCompetitionController())->delete((int) $id));

/* Admin - teams */
$router->get('/admin/teams',              fn() => (new AdminTeamController())->index());
$router->get('/admin/teams/create',       fn() => (new AdminTeamController())->create());
$router->post('/admin/teams',             fn() => (new AdminTeamController())->store());
$router->get('/admin/teams/{id}/edit',    fn(string $id) => (new AdminTeamController())->edit((int) $id));
$router->post('/admin/teams/{id}',        fn(string $id) => (new AdminTeamController())->update((int) $id));
$router->post('/admin/teams/{id}/delete', fn(string $id) => (new AdminTeamController())->delete((int) $id));

/* Admin - players */
$router->get('/admin/players',              fn() => (new AdminPlayerController())->index());
$router->get('/admin/players/create',       fn() => (new AdminPlayerController())->create());
$router->post('/admin/players',             fn() => (new AdminPlayerController())->store());
$router->get('/admin/players/{id}/edit',    fn(string $id) => (new AdminPlayerController())->edit((int) $id));
$router->post('/admin/players/{id}',        fn(string $id) => (new AdminPlayerController())->update((int) $id));
$router->post('/admin/players/{id}/delete', fn(string $id) => (new AdminPlayerController())->delete((int) $id));

/* Admin - matches */
$router->get('/admin/matches',              fn() => (new AdminMatchController())->index());
$router->get('/admin/matches/create',       fn() => (new AdminMatchController())->create());
$router->post('/admin/matches',             fn() => (new AdminMatchController())->store());
$router->get('/admin/matches/{id}/edit',    fn(string $id) => (new AdminMatchController())->edit((int) $id));
$router->post('/admin/matches/{id}',        fn(string $id) => (new AdminMatchController())->update((int) $id));
$router->post('/admin/matches/{id}/delete', fn(string $id) => (new AdminMatchController())->delete((int) $id));

/* Admin - LIVE */
$router->get('/admin/live/{id}',               fn(string $id) => (new LiveController())->console((int) $id));
$router->post('/admin/live/{id}/start',        fn(string $id) => (new LiveController())->start((int) $id));
$router->post('/admin/live/{id}/minute',       fn(string $id) => (new LiveController())->minute((int) $id));
$router->post('/admin/live/{id}/half-time',    fn(string $id) => (new LiveController())->halfTime((int) $id));
$router->post('/admin/live/{id}/resume',       fn(string $id) => (new LiveController())->resume((int) $id));
$router->post('/admin/live/{id}/suspend',      fn(string $id) => (new LiveController())->suspend((int) $id));
$router->post('/admin/live/{id}/finish',       fn(string $id) => (new LiveController())->finish((int) $id));
$router->post('/admin/live/{id}/goal',         fn(string $id) => (new LiveController())->addGoal((int) $id));
$router->post('/admin/live/{id}/card',         fn(string $id) => (new LiveController())->addCard((int) $id));
$router->post('/admin/live/{id}/substitution', fn(string $id) => (new LiveController())->addSubstitution((int) $id));
$router->post('/admin/live/{id}/event/{eventId}/void', fn(string $id, string $eventId) => (new LiveController())->voidEvent((int) $id, (int) $eventId));
$router->post('/admin/live/{id}/award',        fn(string $id) => (new LiveController())->setAward((int) $id));

/* Admin - users */
$router->get('/admin/users', fn() => (new AdminUserController())->index());