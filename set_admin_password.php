<?php
require_once __DIR__ . '/inc/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = spbx_post('password');

    if (strlen($password) < 8) {
        $message = 'Passwort muss mindestens 8 Zeichen haben.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db = spbx_db();

        $stmt = $db->prepare("
            INSERT INTO spbx_users (username, password_hash, display_name, role, active)
            VALUES ('admin', ?, 'Administrator', 'admin', 1)
            ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), display_name='Administrator', role='admin', active=1
        ");
        $stmt->bind_param('s', $hash);
        $stmt->execute();

        $message = 'Admin-Passwort wurde gesetzt. Diese Datei danach bitte löschen.';
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Admin Passwort setzen</title>
    <link rel="stylesheet" href="css/servuspbx.css">
</head>
<body class="spbx-login-body">
    <main class="spbx-login-card">
        <h1 class="spbx-login-title">Admin-Passwort setzen</h1>
        <?php if ($message): ?><div class="spbx-alert success"><?php echo spbx_h($message); ?></div><?php endif; ?>
        <form method="post">
            <div class="spbx-field">
                <label>Neues Admin-Passwort</label>
                <input class="spbx-input" type="password" name="password" autofocus>
            </div>
            <button class="spbx-button primary full" type="submit">Speichern</button>
        </form>
    </main>
</body>
</html>
