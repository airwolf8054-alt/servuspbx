<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/db.php';

spbx_require_admin();

$db = spbx_db();

function spbx_check_h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function spbx_check_table_exists($table)
{
    $db = spbx_db();
    $stmt = $db->prepare("
        SELECT COUNT(*) AS c
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA=DATABASE()
          AND TABLE_NAME=?
    ");
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return ((int)($row['c'] ?? 0)) > 0;
}

function spbx_check_column_exists($table, $column)
{
    $db = spbx_db();
    $stmt = $db->prepare("
        SELECT COUNT(*) AS c
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE()
          AND TABLE_NAME=?
          AND COLUMN_NAME=?
    ");
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return ((int)($row['c'] ?? 0)) > 0;
}

function spbx_check_count($sql)
{
    $db = spbx_db();
    $res = $db->query($sql);
    if (!$res) return 0;
    $row = $res->fetch_assoc();
    return (int)array_values($row)[0];
}

function spbx_check_extconfig_contains($needle)
{
    $file = '/etc/asterisk/extconfig.conf';
    if (!is_readable($file)) {
        return null;
    }
    $txt = file_get_contents($file);
    return strpos($txt, $needle) !== false;
}

function spbx_check_status($ok, $label, $detail = '', $fix = '')
{
    return [
        'ok' => $ok,
        'label' => $label,
        'detail' => $detail,
        'fix' => $fix,
    ];
}

$checks = [];

// extconfig.conf checks
$mapAstConfig = spbx_check_extconfig_contains('extensions.conf => odbc,asterisk,ast_config');
$mapExtensions = spbx_check_extconfig_contains('extensions => odbc,asterisk,extensions');

$checks[] = spbx_check_status(
    $mapAstConfig === true,
    'extensions.conf Realtime-Mapping',
    $mapAstConfig === null ? '/etc/asterisk/extconfig.conf ist für PHP nicht lesbar.' : 'Benötigt: extensions.conf => odbc,asterisk,ast_config',
    'In /etc/asterisk/extconfig.conf ergänzen: extensions.conf => odbc,asterisk,ast_config'
);

$checks[] = spbx_check_status(
    $mapExtensions === true,
    'Dialplan Realtime-Mapping',
    $mapExtensions === null ? '/etc/asterisk/extconfig.conf ist für PHP nicht lesbar.' : 'Benötigt: extensions => odbc,asterisk,extensions',
    'In /etc/asterisk/extconfig.conf ergänzen: extensions => odbc,asterisk,extensions'
);

// DB schema
$checks[] = spbx_check_status(
    spbx_check_table_exists('ast_config'),
    'Tabelle ast_config vorhanden',
    'Wird für dynamische Contexts und BLF-Hints benötigt.',
    'Patch 1.5.0 oder neuer SQL einspielen.'
);

$checks[] = spbx_check_status(
    spbx_check_table_exists('extensions'),
    'Tabelle extensions vorhanden',
    'Realtime-Dialplan.',
    'Realtime-Dialplan Tabelle anlegen.'
);

$checks[] = spbx_check_status(
    spbx_check_table_exists('spbx_outbound_routes'),
    'Tabelle spbx_outbound_routes vorhanden',
    'Basis für internal_<rufnummer> und outgoing_<rufnummer>.',
    'Telephony Base SQL einspielen.'
);

$checks[] = spbx_check_status(
    spbx_check_table_exists('spbx_trunks'),
    'Tabelle spbx_trunks vorhanden',
    'Provider-Trunk-Verwaltung.',
    'Trunk Management SQL einspielen.'
);

// dynamic contexts
$internalCount = spbx_check_table_exists('ast_config') ? spbx_check_count("
    SELECT COUNT(*) FROM ast_config
    WHERE filename='extensions.conf'
      AND category LIKE 'internal\\_%'
      AND var_name='switch'
      AND var_val='Realtime/@extensions'
") : 0;

$outgoingCount = spbx_check_table_exists('ast_config') ? spbx_check_count("
    SELECT COUNT(*) FROM ast_config
    WHERE filename='extensions.conf'
      AND category LIKE 'outgoing\\_%'
      AND var_name='switch'
      AND var_val='Realtime/@extensions'
") : 0;

$hintCount = spbx_check_table_exists('ast_config') ? spbx_check_count("
    SELECT COUNT(*) FROM ast_config
    WHERE filename='extensions.conf'
      AND category LIKE 'internal\\_%'
      AND var_name='exten'
      AND var_val LIKE '%hint,PJSIP/%'
") : 0;

$checks[] = spbx_check_status(
    $internalCount > 0,
    'internal_<rufnummer> Contexts',
    $internalCount . ' interne dynamische Contexts gefunden.',
    'Trunk speichern oder AstConfigWriter SQL ausführen.'
);

$checks[] = spbx_check_status(
    $outgoingCount > 0,
    'outgoing_<rufnummer> Contexts',
    $outgoingCount . ' ausgehende dynamische Contexts gefunden.',
    'Trunk speichern oder AstConfigWriter SQL ausführen.'
);

$checks[] = spbx_check_status(
    $hintCount > 0,
    'BLF Pattern-Hints',
    $hintCount . ' Hint-Einträge in ast_config gefunden.',
    'AstConfigWriter SQL 1.5.0 ausführen.'
);

// A1 safety
$a1AuthBad = spbx_check_table_exists('ps_endpoints') ? spbx_check_count("
    SELECT COUNT(*) FROM ps_endpoints
    WHERE id LIKE 'trunk-a1-%'
      AND auth IS NOT NULL
      AND auth <> ''
") : 0;

$a1ContextBad = spbx_check_table_exists('ps_endpoints') ? spbx_check_count("
    SELECT COUNT(*) FROM ps_endpoints
    WHERE id LIKE 'trunk-a1-%'
      AND context <> 'incoming'
") : 0;

$checks[] = spbx_check_status(
    $a1AuthBad === 0,
    'A1 Inbound ohne Auth',
    $a1AuthBad . ' A1-Trunks mit gesetztem Inbound-auth gefunden.',
    'ps_endpoints.auth für A1-Trunks auf NULL setzen.'
);

$checks[] = spbx_check_status(
    $a1ContextBad === 0,
    'A1 Inbound Context incoming',
    $a1ContextBad . ' A1-Trunks mit falschem Context gefunden.',
    'ps_endpoints.context für A1-Trunks auf incoming setzen.'
);

if (spbx_check_column_exists('ps_endpoints', 'from_user')) {
    $a1FromUserBad = spbx_check_count("
        SELECT COUNT(*) FROM ps_endpoints
        WHERE id LIKE 'trunk-a1-%'
          AND from_user IS NOT NULL
          AND from_user <> ''
    ");
    $checks[] = spbx_check_status(
        $a1FromUserBad === 0,
        'A1 from_user leer',
        $a1FromUserBad . ' A1-Trunks mit gesetztem from_user gefunden.',
        'from_user leer lassen, sonst kann CLIP no Screening blockiert werden.'
    );
}

// BLF self filter
$selfBlf = (spbx_check_table_exists('spbx_device_keys') && spbx_check_table_exists('spbx_devices')) ? spbx_check_count("
    SELECT COUNT(*)
    FROM spbx_device_keys k
    JOIN spbx_devices d ON d.id=k.device_id
    WHERE k.key_type='blf'
      AND k.key_value=d.extension
") : 0;

$checks[] = spbx_check_status(
    $selfBlf === 0,
    'Keine Selbst-BLFs',
    $selfBlf . ' Selbst-BLF-Belegungen gefunden.',
    'Patch 1.5.2 SQL ausführen.'
);

$okCount = 0;
foreach ($checks as $c) {
    if ($c['ok']) $okCount++;
}
$total = count($checks);
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Systemprüfung - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
    <style>
        .spbx-check-summary {
            display: flex;
            gap: 14px;
            align-items: center;
            margin-bottom: 18px;
        }
        .spbx-check-score {
            min-width: 92px;
            min-height: 92px;
            border-radius: 24px;
            display: grid;
            place-items: center;
            background: #eef6ff;
            color: #071f45;
            font-weight: 900;
            font-size: 24px;
        }
        .spbx-check-list {
            display: grid;
            gap: 12px;
        }
        .spbx-check-row {
            display: grid;
            grid-template-columns: 44px 1fr;
            gap: 12px;
            padding: 16px;
            border-radius: 18px;
            border: 1px solid #d7e3f3;
            background: #fff;
        }
        .spbx-check-row.ok {
            border-color: #bfe8cf;
            background: #f4fff8;
        }
        .spbx-check-row.bad {
            border-color: #ffd0d0;
            background: #fff7f7;
        }
        .spbx-check-icon {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            font-weight: 900;
        }
        .spbx-check-row.ok .spbx-check-icon {
            background: #d9f7e4;
            color: #0f7a35;
        }
        .spbx-check-row.bad .spbx-check-icon {
            background: #ffe0e0;
            color: #b00020;
        }
        .spbx-check-title {
            font-weight: 900;
            color: #071f45;
            margin-bottom: 4px;
        }
        .spbx-check-detail {
            color: #5d7190;
            font-weight: 700;
            line-height: 1.35;
        }
        .spbx-check-fix {
            margin-top: 8px;
            padding: 8px 10px;
            border-radius: 12px;
            background: rgba(255,255,255,.75);
            color: #7a3b00;
            font-weight: 800;
        }
    </style>
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>
    <main class="spbx-main">
        <?php spbx_page_header('Systemprüfung', 'Realtime, Contexts, A1-Sicherheit und BLF-Schutz'); ?>
        <div class="spbx-content">
            <div class="spbx-card">
                <div class="spbx-card-head">
                    <div>
                        <div class="spbx-card-title">ServusPBX Sicherheitscheck</div>
                        <div class="spbx-card-muted">Diese Prüfung erkennt typische Fehlkonfigurationen, bevor sie beim Kunden auffallen.</div>
                    </div>
                </div>

                <div class="spbx-check-summary">
                    <div class="spbx-check-score"><?php echo (int)$okCount; ?>/<?php echo (int)$total; ?></div>
                    <div>
                        <div class="spbx-card-title"><?php echo $okCount === $total ? 'Alles sieht gut aus.' : 'Es gibt Punkte zum Prüfen.'; ?></div>
                        <div class="spbx-card-muted">
                            Die Prüfung nimmt keine Änderungen vor. Sie zeigt nur an, was sicherheitshalber kontrolliert werden sollte.
                        </div>
                    </div>
                </div>

                <div class="spbx-check-list">
                    <?php foreach ($checks as $c): ?>
                        <div class="spbx-check-row <?php echo $c['ok'] ? 'ok' : 'bad'; ?>">
                            <div class="spbx-check-icon"><?php echo $c['ok'] ? '✓' : '!'; ?></div>
                            <div>
                                <div class="spbx-check-title"><?php echo spbx_check_h($c['label']); ?></div>
                                <div class="spbx-check-detail"><?php echo spbx_check_h($c['detail']); ?></div>
                                <?php if (!$c['ok'] && $c['fix']): ?>
                                    <div class="spbx-check-fix"><?php echo spbx_check_h($c['fix']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
