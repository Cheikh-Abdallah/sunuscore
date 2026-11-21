<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class AuthController
{
    public function showLogin(): void
    {
        // Déjà connecté → dashboard
        if (Auth::check()) {
            Response::redirect(url('/admin'));
        }

        $error = Session::get('login_error');
        Session::remove('login_error');

        $view = dirname(__DIR__, 2) . '/resources/views/auth/login.php';
        $csrfField = Csrf::field();

        require $view;
    }

    public function login(): void
    {
        // 1. CSRF — nom de champ FIXE, pas de config()
        $token = Csrf::fromPost();
        if (!Csrf::validate($token)) {
            Session::set('login_error', 'Session expirée. Merci de réessayer.');
            Response::redirect(url('/login'));
        }

        // 2. Données
        $data = [
            'username' => trim((string) ($_POST['username'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
        ];

        $v = Validator::make($data)
            ->required('username', "Nom d'utilisateur")
            ->required('password', 'Mot de passe');
        if ($v->fails()) {
            Session::set('login_error', 'Identifiants manquants.');
            Response::redirect(url('/login'));
        }

        // 3. Utilisateur
        $user = User::findByUsername($data['username']);
        if ($user === null) {
            Session::set('login_error', 'Identifiants incorrects.');
            Response::redirect(url('/login'));
        }

        // 4. Compte actif ?
        if ((int) $user['is_active'] !== 1) {
            Session::set('login_error', 'Compte désactivé.');
            Response::redirect(url('/login'));
        }

        // 5. Compte verrouillé ?
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            Session::set('login_error', 'Compte temporairement verrouillé. Réessayez plus tard.');
            Response::redirect(url('/login'));
        }

        // 6. Mot de passe
        if (!password_verify($data['password'], $user['password_hash'])) {
            User::incrementFailedAttempts((int) $user['id']);
            Session::set('login_error', 'Identifiants incorrects.');
            Response::redirect(url('/login'));
        }

        // 7. Succès
        User::updateLastLogin((int) $user['id'], $_SERVER['REMOTE_ADDR'] ?? '');
        Auth::login([
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role_code'],
        ]);
        Csrf::regenerate();

        Response::redirect(url('/admin'));
    }

    public function logout(): void
    {
        // CSRF sur le logout aussi (form POST)
        $token = Csrf::fromPost();
        if (!Csrf::validate($token)) {
            Response::redirect(url('/admin'));
        }
        Auth::logout();
        Response::redirect(url('/login'));
    }
}