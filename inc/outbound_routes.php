<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/asterisk.php';
require_once __DIR__ . '/ast_config_writer.php';


function spbx_a1_guard_inbound_trunks()
{
    $db = spbx_db();

    // A1/registrierende Provider-Trunks dürfen inbound keine Auth erzwingen.
    // Sonst beantwortet Asterisk eingehende INVITEs mit 401 Unauthorized.
    $db->query("
        UPDATE ps_endpoints p
        JOIN spbx_trunks t ON t.endpoint_id = p.id
        SET p.auth = NULL,
            p.context = 'incoming'
        WHERE p.device_type = 'trunk'
          AND UPPER(COALESCE(t.provider,'A1')) = 'A1'
    ");
}



function spbx_outbound_install_schema()
{
    $db = spbx_db();
    $db->query("CREATE TABLE IF NOT EXISTS spbx_outbound_routes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        route_name VARCHAR(120) NOT NULL,
        provider VARCHAR(40) NOT NULL DEFAULT 'A1',
        main_number VARCHAR(40) NOT NULL,
        trunk_endpoint VARCHAR(120) NOT NULL,
        outgoing_context VARCHAR(120) NOT NULL,
        clip_no_screening TINYINT(1) NOT NULL DEFAULT 1,
        callerid_mode ENUM('main','extension') NOT NULL DEFAULT 'extension',
        emergency_profile ENUM('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT',
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_out_ctx (outgoing_context),
        KEY idx_endpoint (trunk_endpoint),
        KEY idx_active (active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $col = $db->query("SHOW COLUMNS FROM spbx_outbound_routes LIKE 'emergency_profile'");
    if (!$col || $col->num_rows === 0) {
        $db->query("ALTER TABLE spbx_outbound_routes ADD COLUMN emergency_profile ENUM('AT','DE','CH','LI','CUSTOM') NOT NULL DEFAULT 'AT' AFTER callerid_mode");
    }

    $db->query("CREATE TABLE IF NOT EXISTS spbx_country_emergency_numbers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        country_code VARCHAR(10) NOT NULL,
        country_name VARCHAR(80) NOT NULL,
        emergency_number VARCHAR(20) NOT NULL,
        description VARCHAR(120) DEFAULT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 100,
        UNIQUE KEY uniq_country_number (country_code, emergency_number),
        KEY idx_country_active (country_code, active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $defaults = [
        ['AT','Österreich','112','Euro-Notruf',10],
        ['AT','Österreich','122','Feuerwehr',20],
        ['AT','Österreich','133','Polizei',30],
        ['AT','Österreich','144','Rettung',40],
        ['AT','Österreich','141','Ärztenotdienst',50],
        ['AT','Österreich','1450','Gesundheitsberatung',60],
        ['DE','Deutschland','110','Polizei',10],
        ['DE','Deutschland','112','Feuerwehr / Rettung',20],
        ['DE','Deutschland','115','Behördennummer',30],
        ['DE','Deutschland','116117','Ärztlicher Bereitschaftsdienst',40],
        ['CH','Schweiz','112','Euro-Notruf',10],
        ['CH','Schweiz','117','Polizei',20],
        ['CH','Schweiz','118','Feuerwehr',30],
        ['CH','Schweiz','144','Sanität',40],
        ['CH','Schweiz','145','Tox Info Suisse',50],
        ['CH','Schweiz','1414','Rega',60],
        ['CH','Schweiz','143','Dargebotene Hand',70],
        ['CH','Schweiz','147','Pro Juventute',80],
        ['LI','Liechtenstein','112','Euro-Notruf',10],
        ['LI','Liechtenstein','117','Polizei',20],
        ['LI','Liechtenstein','118','Feuerwehr',30],
        ['LI','Liechtenstein','144','Rettung',40],
    ];

    $stmt = $db->prepare("INSERT INTO spbx_country_emergency_numbers
        (country_code, country_name, emergency_number, description, sort_order, active)
        VALUES (?, ?, ?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE
            country_name=VALUES(country_name),
            description=VALUES(description),
            sort_order=VALUES(sort_order),
            active=1");
    if ($stmt) {
        foreach ($defaults as $d) {
            $stmt->bind_param('ssssi', $d[0], $d[1], $d[2], $d[3], $d[4]);
            $stmt->execute();
        }
    }
}

function spbx_outbound_normalize_main_number($number)
{
    $number = trim((string)$number);
    if ($number === '') return '';
    if ($number[0] === '+') return '+' . preg_replace('/[^0-9]/', '', $number);
    $digits = preg_replace('/[^0-9]/', '', $number);
    if ($digits === '') return '';
    if (strpos($digits, '43') === 0) return '+' . $digits;
    if (substr($digits, 0, 2) === '00') return '+' . substr($digits, 2);
    if ($digits[0] === '0') return '+43' . substr($digits, 1);
    return '+' . $digits;
}

function spbx_outbound_context_from_number($number)
{
    return 'outgoing_' . preg_replace('/[^0-9]/', '', spbx_outbound_normalize_main_number($number));
}

function spbx_internal_context_from_number($number)
{
    return 'internal_' . preg_replace('/[^0-9]/', '', spbx_outbound_normalize_main_number($number));
}


function spbx_outbound_emergency_profile_from_number($number)
{
    $digits = preg_replace('/[^0-9]/', '', spbx_outbound_normalize_main_number($number));

    if (strpos($digits, '49') === 0) return 'DE';
    if (strpos($digits, '41') === 0) return 'CH';
    if (strpos($digits, '423') === 0) return 'LI';
    return 'AT';
}

function spbx_outbound_emergency_numbers($profile)
{
    $db = spbx_db();
    $profile = strtoupper((string)$profile);
    $numbers = [];

    // Primär aus der Datenbank, damit Länder/Nummern später ohne PHP-Änderung gepflegt werden können.
    $stmt = $db->prepare("
        SELECT emergency_number
        FROM spbx_country_emergency_numbers
        WHERE country_code=?
          AND active=1
        ORDER BY sort_order, emergency_number
    ");
    if ($stmt) {
        $stmt->bind_param('s', $profile);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $numbers[] = (string)$r['emergency_number'];
        }
    }

    if ($numbers) {
        return $numbers;
    }

    // Fallback, falls die Tabelle noch nicht befüllt ist.
    $profiles = [
        'AT' => ['112','122','133','144','141','1450'],
        'DE' => ['110','112','115','116117'],
        'CH' => ['112','117','118','144','145','1414','143','147'],
        'LI' => ['112','117','118','144'],
        'CUSTOM' => [],
    ];

    return $profiles[$profile] ?? $profiles['AT'];
}


function spbx_outbound_sync_from_trunks()
{
    spbx_a1_guard_inbound_trunks();
    $db = spbx_db();
    spbx_outbound_install_schema();

    $res = $db->query("SELECT provider, endpoint_id, main_number, trunk_name, name, active FROM spbx_trunks WHERE COALESCE(endpoint_id,'')<>'' AND COALESCE(main_number,'')<>'' ORDER BY id ASC");
    if (!$res) return;

    while ($t = $res->fetch_assoc()) {
        $main = spbx_outbound_normalize_main_number($t['main_number']);
        if ($main === '') continue;

        $ctx = spbx_outbound_context_from_number($main);
        $provider = strtoupper(trim((string)($t['provider'] ?: 'A1')));
        $endpoint = trim((string)$t['endpoint_id']);
        $routeName = trim($provider . ' ' . (($t['trunk_name'] ?: $t['name']) ?: $main));
        $active = (int)($t['active'] ?? 1);
        $profile = spbx_outbound_emergency_profile_from_number($main);

        $stmt = $db->prepare("INSERT INTO spbx_outbound_routes
            (route_name, provider, main_number, trunk_endpoint, outgoing_context, clip_no_screening, callerid_mode, emergency_profile, active)
            VALUES (?, ?, ?, ?, ?, 1, 'extension', ?, ?)
            ON DUPLICATE KEY UPDATE
                route_name=VALUES(route_name),
                provider=VALUES(provider),
                main_number=VALUES(main_number),
                trunk_endpoint=VALUES(trunk_endpoint),
                active=VALUES(active)");
        if ($stmt) {
            $stmt->bind_param('ssssssi', $routeName, $provider, $main, $endpoint, $ctx, $profile, $active);
            $stmt->execute();
        }
    }
}

function spbx_outbound_first_active_route()
{
    $db = spbx_db();
    spbx_outbound_install_schema();
    spbx_outbound_sync_from_trunks();
    $res = $db->query("SELECT * FROM spbx_outbound_routes WHERE active=1 ORDER BY id ASC LIMIT 1");
    return $res ? $res->fetch_assoc() : null;
}


function spbx_outbound_rebuild_dialplan()
{
    spbx_a1_guard_inbound_trunks();
    $db = spbx_db();
    spbx_outbound_install_schema();
    spbx_outbound_sync_from_trunks();

    $db->query("DELETE FROM extensions WHERE context LIKE 'outgoing\\_%'");
    $db->query("DELETE FROM extensions WHERE context LIKE 'internal\\_%'");

    $hasComment = false;
    $c = $db->query("SHOW COLUMNS FROM extensions LIKE 'comment'");
    if ($c && $c->num_rows > 0) $hasComment = true;

    if ($hasComment) {
        $ins = $db->prepare("INSERT INTO extensions (context, exten, priority, app, appdata, comment) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE app=VALUES(app), appdata=VALUES(appdata), comment=VALUES(comment)");
    } else {
        $ins = $db->prepare("INSERT INTO extensions (context, exten, priority, app, appdata) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE app=VALUES(app), appdata=VALUES(appdata)");
    }
    if (!$ins) return false;

    $insert = function($context, $exten, $priority, $app, $appdata='', $comment='') use ($ins, $hasComment) {
        $context=(string)$context; $exten=(string)$exten; $priority=(string)$priority; $app=(string)$app; $appdata=(string)$appdata; $comment=(string)$comment;
        if ($hasComment) $ins->bind_param('ssssss', $context, $exten, $priority, $app, $appdata, $comment);
        else $ins->bind_param('sssss', $context, $exten, $priority, $app, $appdata);
        $ins->execute();
    };

    $routes = $db->query("SELECT * FROM spbx_outbound_routes WHERE active=1 ORDER BY id ASC");
    $firstContext = '';

    if ($routes) {
        while ($r = $routes->fetch_assoc()) {
            $ctx = trim((string)$r['outgoing_context']);
            $main = spbx_outbound_normalize_main_number($r['main_number']);
            $endpoint = trim((string)$r['trunk_endpoint']);
            if ($ctx === '' || $main === '' || $endpoint === '') continue;
            if ($firstContext === '') $firstContext = $ctx;

            // Ausgehender Context nimmt bewusst alles an.
            // Damit funktionieren auch lokale Nummern ohne Vorwahl.
            $pattern = '_X.';
            $insert($ctx, $pattern, 1, 'NoOp', 'ServusPBX A1 outbound ${EXTEN}');
            $insert($ctx, $pattern, 2, 'Set', 'A1_MAIN=' . $main);
            $insert($ctx, $pattern, 3, 'Set', 'EXTNR=${CALLERID(num)}');
            if ((int)$r['clip_no_screening'] === 1 && $r['callerid_mode'] === 'extension') {
                $insert($ctx, $pattern, 4, 'Set', 'CALLERID(num)=${A1_MAIN}${EXTNR}');
            } else {
                $insert($ctx, $pattern, 4, 'Set', 'CALLERID(num)=${A1_MAIN}');
            }
            $insert($ctx, $pattern, 5, 'Set', 'CALLERID(name)=');
            // Zielnummer in einer einzigen Priority normalisieren:
            // 0043664... -> +43664..., 0664... -> +43664..., +43664... bleibt unverändert.
            $insert($ctx, $pattern, 6, 'Set', 'DST=${IF($["${EXTEN:0:2}"="00"]?+${EXTEN:2}:${IF($["${EXTEN:0:1}"="0"]?+43${EXTEN:1}:${EXTEN})})}');
            $insert($ctx, $pattern, 7, 'NoOp', 'A1 From/CLI: ${CALLERID(num)} Ziel: ${DST}');
            $insert($ctx, $pattern, 8, 'Dial', 'PJSIP/${DST}@' . $endpoint . ',60');
            $insert($ctx, $pattern, 9, 'Hangup', '');

            // Interner Context je Rufnummer.
            // Damit können Mehrnummern-Anlagen sauber getrennte interne/ausgehende Callflows bekommen.
            $intCtx = spbx_internal_context_from_number($main);

            // 2-, 3- und 4-stellige Nebenstellen intern wählen.
            foreach (['_XX', '_XXX', '_XXXX'] as $intPattern) {
                $insert($intCtx, $intPattern, 1, 'NoOp', 'ServusPBX internal call ${EXTEN}');
                $insert($intCtx, $intPattern, 2, 'Dial', 'PJSIP/${EXTEN},30');
                $insert($intCtx, $intPattern, 3, 'Hangup', '');
            }


            // Notrufe/Kurznummern abhängig vom Land/Notrufprofil dieser Route.
            $emergencyProfile = $r['emergency_profile'] ?? spbx_outbound_emergency_profile_from_number($main);
            foreach (spbx_outbound_emergency_numbers($emergencyProfile) as $emergency) {
                $insert($intCtx, $emergency, 1, 'NoOp', 'ServusPBX emergency/service ' . $emergency . ' profile ' . $emergencyProfile);
                $insert($intCtx, $emergency, 2, 'Goto', $ctx . ',' . $emergency . ',1');
            }

            // Alles ab 5 Stellen geht aus internal_<rufnummer> direkt zum passenden outgoing_<rufnummer>.
            $insert($intCtx, '_XXXXX.', 1, 'Goto', $ctx . ',${EXTEN},1');
        }
    }

    // Nebenstellen automatisch auf den ersten aktiven internal_<rufnummer>-Context setzen.
    // Trunks bleiben davon unberührt.
    if ($firstContext !== '') {
        $firstInternal = str_replace('outgoing_', 'internal_', $firstContext);
        $db->query("UPDATE ps_endpoints SET context='" . $db->real_escape_string($firstInternal) . "' WHERE (device_type IS NULL OR device_type <> 'trunk') AND id NOT LIKE 'trunk-%'");
    }

    spbx_ast_config_writer_rebuild_from_outbound_routes();
    spbx_ast_cli('dialplan reload');
    return true;
}
?>