<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';

spbx_require_login();

$db = spbx_db();
$user = spbx_current_user();
$userId = (int)$user['id'];

$message = '';
$error = '';

$stmt = $db->prepare("SELECT username, display_name, email, email_2fa_enabled FROM spbx_users WHERE id=? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    http_response_code(404);
    exit('Benutzer nicht gefunden.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $enabled = isset($_POST['email_2fa_enabled']) ? 1 : 0;

    if ($enabled && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Bitte eine gültige E-Mail-Adresse eintragen, bevor 2FA aktiviert wird.';
    } else {
        $stmt = $db->prepare("UPDATE spbx_users SET email=?, email_2fa_enabled=? WHERE id=?");
        $stmt->bind_param('sii', $email, $enabled, $userId);
        $stmt->execute();

        $message = '2FA-Einstellungen gespeichert.';

        $stmt = $db->prepare("SELECT username, display_name, email, email_2fa_enabled FROM spbx_users WHERE id=? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>2-Faktor Sicherheit - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('2-Faktor Anmeldung', 'Login-Code per E-Mail'); ?>

        <div class="spbx-content">
            <?php if ($message): ?><div class="spbx-alert success"><?php echo spbx_h($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="spbx-alert error"><?php echo spbx_h($error); ?></div><?php endif; ?>

            <div class="spbx-card spbx-phonebook-form-card">
                <div class="spbx-card-title">E-Mail 2FA</div>
                <div class="spbx-card-muted">
                    Nach erfolgreichem Passwort-Login wird ein 6-stelliger Code per E-Mail versendet.
                </div>

                <form method="post" class="spbx-2fa-form" style="margin-top:18px;">
                    <div class="spbx-form-grid">
                        <div class="spbx-field">
                            <label>E-Mail-Adresse</label>
                            <input type="email" name="email" value="<?php echo spbx_h($row['email'] ?? ''); ?>" placeholder="name@ordination.at">
                        </div>

                        <div class="spbx-field">
                            <label>Status</label>
                            <label class="spbx-toggle-row">
                                <input type="checkbox" name="email_2fa_enabled" value="1" <?php echo !empty($row['email_2fa_enabled']) ? 'checked' : ''; ?>>
                                <span class="spbx-toggle-text">
                                    <span>2FA per E-Mail aktivieren</span>
                                    <small>Beim Login wird ein 6-stelliger Code per E-Mail gesendet.</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="spbx-form-actions">
                        <button class="spbx-button primary" type="submit">Speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>
