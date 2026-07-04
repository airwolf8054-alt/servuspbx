<?php
require_once __DIR__ . '/inc/auth.php';

spbx_session_start();

if (spbx_current_user()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>