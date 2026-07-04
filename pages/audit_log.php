<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';

spbx_require_admin();

$db = spbx_db();

$res = $db->query("
    SELECT *
    FROM spbx_audit_log
    ORDER BY created_at DESC
    LIMIT 300
");

$rows = [];
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Audit-Log - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('Audit-Log', 'Sicherheits- und Administratoraktionen'); ?>

        <div class="spbx-content">
            <div class="spbx-card">
                <div class="spbx-card-header-row">
                    <div>
                        <div class="spbx-card-title">Letzte Ereignisse</div>
                        <div class="spbx-card-muted">Maximal 300 Einträge.</div>
                    </div>
                </div>

                <div class="spbx-table-wrap">
                    <table class="spbx-table">
                        <thead>
                            <tr>
                                <th>Zeit</th>
                                <th>Level</th>
                                <th>Quelle</th>
                                <th>Aktion</th>
                                <th>Benutzer</th>
                                <th>IP</th>
                                <th>Nachricht</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$rows): ?>
                                <tr><td colspan="7">Keine Einträge vorhanden.</td></tr>
                            <?php endif; ?>

                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?php echo spbx_h($r['created_at']); ?></td>
                                    <td><?php echo spbx_h($r['event_level']); ?></td>
                                    <td><?php echo spbx_h($r['source']); ?></td>
                                    <td><?php echo spbx_h($r['action']); ?></td>
                                    <td><?php echo spbx_h($r['username']); ?></td>
                                    <td><?php echo spbx_h($r['ip_address']); ?></td>
                                    <td><?php echo spbx_h($r['message']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
