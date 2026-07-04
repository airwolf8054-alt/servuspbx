<?php
require_once __DIR__ . '/config.php';

function spbx_db()
{
    static $db = null;

    if ($db instanceof mysqli) {
        return $db;
    }

    $db = new mysqli(SPBX_DB_HOST, SPBX_DB_USER, SPBX_DB_PASS, SPBX_DB_NAME);

    if ($db->connect_error) {
        http_response_code(500);
        die('Datenbankverbindung fehlgeschlagen.');
    }

    $db->set_charset('utf8mb4');
    return $db;
}

function spbx_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function spbx_post($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}
?>