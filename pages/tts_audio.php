<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/tts.php';

spbx_require_admin();

$file = spbx_tts_safe_basename($_GET['file'] ?? '');
if ($file === '') {
    http_response_code(404);
    exit('Nicht gefunden');
}

$path = spbx_tts_output_path($file);
if (!is_file($path) || !is_readable($path)) {
    http_response_code(404);
    exit('Audiodatei nicht gefunden');
}

header('Content-Type: audio/mpeg');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
readfile($path);
exit;
?>
