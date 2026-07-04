<?php
define('SPBX_DB_HOST', 'localhost');
define('SPBX_DB_NAME', 'general');
define('SPBX_DB_USER', 'servuspbx');
define('SPBX_DB_PASS', 'Freecom1212!');

define('SPBX_APP_NAME', 'ServusPBX Medical');

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
}
?>