<?php
require_once __DIR__ . '/inc/auth.php';

spbx_session_start();

if (spbx_current_user()) {
    header('Location: dashboard.php');
    exit;
}

$pending = $_SESSION['spbx_pending_2fa'] ?? null;
if (!$pending) {
    header('Location: login.php');
    exit;
}

$error = '';
$email = $pending['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'resend') {
        $result = spbx_resend_2fa_code();
        if (!empty($result['ok'])) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    } else {
        $code = spbx_post('code');

        if (spbx_finish_2fa($code)) {
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Der Login-Code ist ungültig oder abgelaufen.';
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>2-Faktor Anmeldung - ServusPBX Professional</title>
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
            <div class="spbx-login-claim">Sichere Anmeldung</div>
        </section>
        <section class="spbx-login-card spbx-login-card-pro">

        <h1 class="spbx-login-title">Login-Code</h1>
        <div class="spbx-login-subtitle">
            Wir haben einen 6-stelligen Code per E-Mail gesendet<?php echo $email ? ' an ' . spbx_h($email) : ''; ?>.
        </div>

        <?php if (!empty($message)): ?>
            <div class="spbx-alert success"><?php echo spbx_h($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="spbx-alert error"><?php echo spbx_h($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="spbx-field">
                <label>E-Mail-Code</label>
                <input class="spbx-input" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" autofocus>
            </div>

            <button class="spbx-button primary full" type="submit">Bestätigen</button>
        </form>

        <form method="post" style="margin-top:10px;">
            <input type="hidden" name="action" value="resend">
            <button class="spbx-button secondary full" type="submit">Code erneut senden</button>
        </form>

        <a class="spbx-button secondary full" style="margin-top:10px;" href="logout.php">Abbrechen</a>
    </section>
    </main>
</body>
</html>
