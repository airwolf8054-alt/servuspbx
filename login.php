<?php
require_once __DIR__ . '/inc/auth.php';

spbx_session_start();

if (spbx_current_user()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if (isset($_GET['timeout'])) {
    $error = 'Die Sitzung wurde wegen Inaktivität beendet.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = spbx_post('username');
    $password = spbx_post('password');

    $loginResult = spbx_login($username, $password);

    if (($loginResult['status'] ?? '') === 'ok') {
        header('Location: dashboard.php');
        exit;
    }

    if (($loginResult['status'] ?? '') === '2fa_required') {
        header('Location: 2fa.php');
        exit;
    }

    if (($loginResult['status'] ?? '') === '2fa_email_missing') {
        $error = 'Für diesen Benutzer ist 2FA aktiv, aber keine E-Mail-Adresse hinterlegt.';
    } elseif (($loginResult['status'] ?? '') === 'locked') {
        $error = 'Zu viele Fehlversuche. Der Benutzer ist vorübergehend gesperrt.';
    } else {
        $error = 'Benutzername oder Passwort ist falsch.';
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Login - ServusPBX Professional</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="stylesheet" href="css/servuspbx.css">
</head>
<body class="spbx-login-body spbx-login-professional">
    <main class="spbx-login-shell">
        <section class="spbx-login-brand-panel">
            <div class="spbx-login-wordmark">
                <span class="spbx-login-wordmark-servus">Servus</span><span class="spbx-login-wordmark-pbx">PBX</span><sup>®</sup>
            </div>
            <div class="spbx-login-edition">Professional Edition</div>
            <div class="spbx-login-claim">PBX Management · Provisioning · Routing</div>
        </section>

        <section class="spbx-login-card spbx-login-card-pro">
            <div class="spbx-login-card-head">
                <div class="spbx-login-title">Anmeldung</div>
            </div>

            <?php if ($error): ?>
                <div class="spbx-alert error"><?php echo spbx_h($error); ?></div>
            <?php endif; ?>

            <form method="post" class="spbx-login-form">
                <div class="spbx-field">
                    <label>Benutzername</label>
                    <input class="spbx-input" name="username" autocomplete="username" autofocus>
                </div>

                <div class="spbx-field">
                    <label>Passwort</label>
                    <div class="spbx-password-wrap">
                        <input class="spbx-input" id="spbxLoginPassword" type="password" name="password" autocomplete="current-password">
                        <button type="button" class="spbx-password-toggle" onclick="spbxTogglePassword()">Anzeigen</button>
                    </div>
                </div>

                <button class="spbx-button primary full" type="submit">Anmelden</button>
            </form>

            <div class="spbx-login-footer">
                ServusPBX Professional Edition · Powered by Asterisk®
            </div>
        </section>
    </main>

    <script>
    function spbxTogglePassword() {
        const field = document.getElementById('spbxLoginPassword');
        const btn = document.querySelector('.spbx-password-toggle');
        if (!field || !btn) return;
        const show = field.type === 'password';
        field.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Verbergen' : 'Anzeigen';
        field.focus();
    }
    </script>
</body>
</html>
