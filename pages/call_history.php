<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';

spbx_require_login();
$user = spbx_current_user();
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Anrufliste - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('Anrufliste', 'CDR Auswertung'); ?>

        <div class="spbx-content">
            <div class="spbx-card">
                <div class="spbx-card-title">Anrufliste</div>
                <div class="spbx-card-muted">Diese Seite wird als nächster Schritt aufgebaut.</div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
