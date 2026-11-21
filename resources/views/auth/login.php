<?php
/** @var string $csrfField */
/** @var string|null $error */
$appUrl = rtrim((string) config('app.url', ''), '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071A36">
    <title>Connexion — SUNUSCORE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e($appUrl) ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= e($appUrl) ?>/assets/css/components.css">
    <style>
        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: linear-gradient(135deg, #071A36 0%, #0B2551 100%);
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border-radius: 18px;
            padding: 32px 28px;
            box-shadow: 0 24px 60px rgba(0,0,0,.3);
        }
        .login-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .login-brand .badge-s {
            width: 44px; height: 44px;
            background: #155EEF;
            color: #fff;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 22px;
        }
        .login-brand h1 { margin: 0; font-size: 22px; color: #071A36; }
        .login-brand .tag { font-size: 12px; color: #64748B; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #172033;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #E5E9F0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 15px;
            background: #F5F7FB;
            transition: border-color .15s, background .15s;
        }
        .form-input:focus {
            outline: none;
            border-color: #155EEF;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(21,94,239,.15);
        }
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: #155EEF;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: background .15s;
            font-family: inherit;
        }
        .btn-submit:hover { background: #0f4bc4; }
        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 16px;
            border-left: 3px solid #DC2626;
        }
        .login-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748B;
        }
        .login-footer a { color: #155EEF; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <div class="login-brand">
                <div class="badge-s">S</div>
                <div>
                    <h1>SUNUSCORE</h1>
                    <div class="tag">Espace administrateur</div>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= e($appUrl) ?>/login" autocomplete="on">
                <?= $csrfField ?>
                <div class="form-group">
                    <label for="username">Nom d'utilisateur ou email</label>
                    <input type="text" id="username" name="username" class="form-input"
                           required autofocus autocomplete="username" maxlength="60">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-input"
                           required autocomplete="current-password" minlength="6">
                </div>
                <button type="submit" class="btn-submit">Se connecter</button>
            </form>

            <div class="login-footer">
                <a href="<?= e($appUrl) ?>/">← Retour au site public</a>
            </div>
        </div>
    </div>
</body>
</html>