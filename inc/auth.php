<?php
require_once __DIR__ . '/db.php';

define('SPBX_SESSION_TIMEOUT_SECONDS', 43200);
define('SPBX_LOGIN_MAX_ATTEMPTS', 5);
define('SPBX_LOGIN_LOCK_MINUTES', 15);
define('SPBX_2FA_VALID_SECONDS', 300);
define('SPBX_2FA_RESEND_SECONDS', 60);

function spbx_session_start()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.gc_maxlifetime', (string)SPBX_SESSION_TIMEOUT_SECONDS);
        session_set_cookie_params([
            'lifetime' => SPBX_SESSION_TIMEOUT_SECONDS,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    if (!empty($_SESSION['spbx_user'])) {
        $last = (int)($_SESSION['spbx_last_activity'] ?? 0);

        if ($last > 0 && (time() - $last) > SPBX_SESSION_TIMEOUT_SECONDS) {
            // Kein spbx_logout() hier aufrufen, sonst entsteht eine Rekursion:
            // spbx_session_start() -> spbx_logout() -> spbx_session_start() -> ...
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }

            session_destroy();

            header('Location: ' . spbx_auth_base_path() . 'login.php?timeout=1');
            exit;
        }

        $_SESSION['spbx_last_activity'] = time();
    }
}

function spbx_auth_base_path()
{
    return (strpos($_SERVER['PHP_SELF'] ?? '', '/pages/') !== false) ? '../' : '';
}

function spbx_current_user()
{
    spbx_session_start();
    return $_SESSION['spbx_user'] ?? null;
}

function spbx_is_admin()
{
    $user = spbx_current_user();
    return (($user['role'] ?? '') === 'admin');
}

function spbx_require_login()
{
    if (!spbx_current_user()) {
        header('Location: ' . spbx_auth_base_path() . 'login.php');
        exit;
    }
}

function spbx_require_admin()
{
    spbx_require_login();
    if (!spbx_is_admin()) {
        http_response_code(403);
        exit('Zugriff verweigert.');
    }
}

function spbx_client_ip()
{
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function spbx_user_agent()
{
    return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

function spbx_audit_log($action, $message, $level = 'info', $username = null, $userId = null, $source = 'web')
{
    $db = spbx_db();

    $actor = spbx_current_user();
    if ($actor) {
        $username = $username ?? ($actor['username'] ?? null);
        $userId = $userId ?? ($actor['id'] ?? null);
    }

    $ip = spbx_client_ip();
    $ua = spbx_user_agent();

    $stmt = $db->prepare("
        INSERT INTO spbx_audit_log
        (event_level, source, action, username, user_id, ip_address, user_agent, message)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $uid = $userId !== null ? (int)$userId : null;
        $stmt->bind_param('ssssisss', $level, $source, $action, $username, $uid, $ip, $ua, $message);
        $stmt->execute();
    }
}

function spbx_mail_2fa_code()
{
    return (string)random_int(100000, 999999);
}

function spbx_send_2fa_mail($to, $code)
{
    $subject = 'ServusPBX Professional Login-Code';
    $body =
        "Ihr ServusPBX Professional Login-Code lautet:\n\n" .
        $code . "\n\n" .
        "Der Code ist 5 Minuten gültig.\n\n" .
        "Wenn Sie diesen Login nicht ausgelöst haben, ignorieren Sie diese Nachricht.";

    $headers = "From: ServusPBX Professional <no-reply@localhost>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($to, $subject, $body, $headers);
}

function spbx_issue_2fa_code(array $user)
{
    $db = spbx_db();

    $code = spbx_mail_2fa_code();
    $hash = password_hash($code, PASSWORD_DEFAULT);
    $expires = date('Y-m-d H:i:s', time() + SPBX_2FA_VALID_SECONDS);
    $sentAt = date('Y-m-d H:i:s');
    $id = (int)$user['id'];

    $upd = $db->prepare("
        UPDATE spbx_users
        SET email_2fa_code_hash=?, email_2fa_expires_at=?, email_2fa_last_sent_at=?
        WHERE id=?
    ");
    $upd->bind_param('sssi', $hash, $expires, $sentAt, $id);
    $upd->execute();

    $_SESSION['spbx_pending_2fa'] = [
        'id' => $id,
        'username' => $user['username'],
        'email' => $user['email'],
        'last_sent_at' => time(),
    ];

    $sent = spbx_send_2fa_mail($user['email'], $code);
    spbx_audit_log('2fa_code_sent', $sent ? '2FA-Code per E-Mail gesendet.' : '2FA-Code konnte nicht per E-Mail gesendet werden.', $sent ? 'info' : 'warning', $user['username'], $id, 'auth');

    return $sent;
}

function spbx_resend_2fa_code()
{
    spbx_session_start();
    $pending = $_SESSION['spbx_pending_2fa'] ?? null;

    if (!$pending) {
        return ['ok' => false, 'message' => 'Keine offene 2FA-Anmeldung.'];
    }

    $last = (int)($pending['last_sent_at'] ?? 0);
    if ($last > 0 && (time() - $last) < SPBX_2FA_RESEND_SECONDS) {
        return ['ok' => false, 'message' => 'Bitte kurz warten, bevor ein neuer Code angefordert wird.'];
    }

    $db = spbx_db();
    $id = (int)$pending['id'];

    $stmt = $db->prepare("
        SELECT id, username, email
        FROM spbx_users
        WHERE id=? AND active=1
        LIMIT 1
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || empty($user['email'])) {
        return ['ok' => false, 'message' => 'Keine gültige E-Mail-Adresse vorhanden.'];
    }

    spbx_issue_2fa_code($user);
    return ['ok' => true, 'message' => 'Ein neuer Code wurde versendet.'];
}

function spbx_complete_login(array $user)
{
    spbx_session_start();
    session_regenerate_id(true);

    $_SESSION['spbx_user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'display_name' => $user['display_name'],
        'role' => $user['role'],
        'extension' => $user['extension'] ?? null,
    ];
    $_SESSION['spbx_last_activity'] = time();

    unset($_SESSION['spbx_pending_2fa']);

    $db = spbx_db();
    $upd = $db->prepare("
        UPDATE spbx_users
        SET last_login=NOW(),
            failed_logins=0,
            locked_until=NULL,
            email_2fa_code_hash=NULL,
            email_2fa_expires_at=NULL
        WHERE id=?
    ");
    if ($upd) {
        $id = (int)$user['id'];
        $upd->bind_param('i', $id);
        $upd->execute();
    }

    spbx_audit_log('login_success', 'Login erfolgreich.', 'info', $user['username'], (int)$user['id'], 'auth');
}

function spbx_login($username, $password)
{
    $db = spbx_db();

    $stmt = $db->prepare("
        SELECT id, username, password_hash, display_name, role, extension, active,
               email, COALESCE(email_2fa_enabled,0) AS email_2fa_enabled,
               COALESCE(failed_logins,0) AS failed_logins,
               locked_until
        FROM spbx_users
        WHERE username=?
        LIMIT 1
    ");
    if (!$stmt) {
        return ['status' => 'error'];
    }

    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || (int)$user['active'] !== 1) {
        spbx_audit_log('login_failed', 'Login fehlgeschlagen: unbekannter oder inaktiver Benutzer.', 'warning', $username, null, 'auth');
        return ['status' => 'invalid'];
    }

    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        spbx_audit_log('login_locked', 'Login blockiert: Benutzer ist temporär gesperrt.', 'warning', $user['username'], (int)$user['id'], 'auth');
        return ['status' => 'locked', 'locked_until' => $user['locked_until']];
    }

    if (!password_verify($password, $user['password_hash'])) {
        $failed = (int)$user['failed_logins'] + 1;

        if ($failed >= SPBX_LOGIN_MAX_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', time() + (SPBX_LOGIN_LOCK_MINUTES * 60));
            $upd = $db->prepare("UPDATE spbx_users SET failed_logins=?, locked_until=? WHERE id=?");
            $id = (int)$user['id'];
            $upd->bind_param('isi', $failed, $lockedUntil, $id);
            $upd->execute();

            spbx_audit_log('login_locked', 'Benutzer nach zu vielen Fehlversuchen gesperrt.', 'warning', $user['username'], $id, 'auth');
            return ['status' => 'locked', 'locked_until' => $lockedUntil];
        }

        $upd = $db->prepare("UPDATE spbx_users SET failed_logins=? WHERE id=?");
        $id = (int)$user['id'];
        $upd->bind_param('ii', $failed, $id);
        $upd->execute();

        spbx_audit_log('login_failed', 'Login fehlgeschlagen: falsches Passwort.', 'warning', $user['username'], $id, 'auth');
        return ['status' => 'invalid'];
    }

    spbx_session_start();
    session_regenerate_id(true);

    if ((int)$user['email_2fa_enabled'] === 1) {
        if (empty($user['email'])) {
            spbx_audit_log('login_2fa_missing_email', '2FA aktiv, aber keine E-Mail-Adresse hinterlegt.', 'error', $user['username'], (int)$user['id'], 'auth');
            return ['status' => '2fa_email_missing'];
        }

        spbx_issue_2fa_code($user);
        return ['status' => '2fa_required'];
    }

    spbx_complete_login($user);
    return ['status' => 'ok', 'user' => $_SESSION['spbx_user']];
}

function spbx_finish_2fa($code)
{
    spbx_session_start();

    $pending = $_SESSION['spbx_pending_2fa'] ?? null;
    if (!$pending) {
        return false;
    }

    $db = spbx_db();
    $id = (int)$pending['id'];

    $stmt = $db->prepare("
        SELECT id, username, display_name, role, extension, active,
               email_2fa_code_hash, email_2fa_expires_at
        FROM spbx_users
        WHERE id=?
        LIMIT 1
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || (int)$user['active'] !== 1) {
        spbx_audit_log('2fa_failed', '2FA fehlgeschlagen: Benutzer ungültig.', 'warning', $pending['username'] ?? null, $id, 'auth');
        return false;
    }

    if (empty($user['email_2fa_code_hash']) || empty($user['email_2fa_expires_at'])) {
        spbx_audit_log('2fa_failed', '2FA fehlgeschlagen: kein aktiver Code vorhanden.', 'warning', $user['username'], $id, 'auth');
        return false;
    }

    if (strtotime($user['email_2fa_expires_at']) < time()) {
        spbx_audit_log('2fa_failed', '2FA fehlgeschlagen: Code abgelaufen.', 'warning', $user['username'], $id, 'auth');
        return false;
    }

    $code = preg_replace('/[^0-9]/', '', (string)$code);
    if (!password_verify($code, $user['email_2fa_code_hash'])) {
        spbx_audit_log('2fa_failed', '2FA fehlgeschlagen: falscher Code.', 'warning', $user['username'], $id, 'auth');
        return false;
    }

    spbx_audit_log('2fa_success', '2FA erfolgreich.', 'info', $user['username'], $id, 'auth');
    spbx_complete_login($user);
    return true;
}

function spbx_logout()
{
    $user = spbx_current_user();
    if ($user) {
        spbx_audit_log('logout', 'Benutzer abgemeldet.', 'info', $user['username'] ?? null, $user['id'] ?? null, 'auth');
    }

    spbx_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
?>