<?php
require_once __DIR__ . '/inc/auth.php';
spbx_logout();
header('Location: login.php');
exit;
?>