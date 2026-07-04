<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/asterisk_ami.php';

spbx_require_login();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    $calls = spbx_ami_active_calls();
    echo json_encode(['ok'=>true, 'count'=>count($calls), 'calls'=>$calls, 'ts'=>time()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage(), 'count'=>0, 'calls'=>[], 'ts'=>time()], JSON_UNESCAPED_UNICODE);
}
?>