<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/asterisk.php';
require_once __DIR__ . '/ast_config_writer.php';
require_once __DIR__ . '/ring_groups.php';
require_once __DIR__ . '/ivr.php';

function spbx_call_rules_install_schema()
{
    $db = spbx_db();

    $db->query("CREATE TABLE IF NOT EXISTS spbx_call_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rule_name VARCHAR(120) NOT NULL,
        trunk_context VARCHAR(120) NOT NULL,
        did VARCHAR(80) NOT NULL DEFAULT 's',
        holiday_enabled TINYINT(1) NOT NULL DEFAULT 1,
        manual_announcement_1_enabled TINYINT(1) NOT NULL DEFAULT 0,
        manual_announcement_1_file VARCHAR(255) DEFAULT NULL,
        manual_announcement_2_enabled TINYINT(1) NOT NULL DEFAULT 0,
        manual_announcement_2_file VARCHAR(255) DEFAULT NULL,
        closed_audio VARCHAR(255) NOT NULL DEFAULT '/var/www/html/sounds/closed.mp3',
        closed_action ENUM('hangup','voicemail') NOT NULL DEFAULT 'hangup',
        main_mailbox VARCHAR(40) NOT NULL DEFAULT '0',
        main_mailbox_context VARCHAR(80) NOT NULL DEFAULT 'internal',
        open_destination_type VARCHAR(40) NOT NULL DEFAULT 'custom',
        open_destination_context VARCHAR(80) NOT NULL DEFAULT 'internal',
        open_destination_exten VARCHAR(80) NOT NULL DEFAULT '0',
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_trunk_context (trunk_context),
        KEY idx_did (did),
        KEY idx_active (active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");


    $cols = [
        "closed_destination_type VARCHAR(40) NOT NULL DEFAULT 'hangup'",
        "closed_destination_context VARCHAR(80) NOT NULL DEFAULT ''",
        "closed_destination_exten VARCHAR(80) NOT NULL DEFAULT ''",
        "closed_tts_text TEXT NULL",
        "holiday_audio VARCHAR(255) NOT NULL DEFAULT '/var/www/html/sounds/holiday.mp3'",
        "holiday_destination_type VARCHAR(40) NOT NULL DEFAULT 'hangup'",
        "holiday_destination_context VARCHAR(80) NOT NULL DEFAULT ''",
        "holiday_destination_exten VARCHAR(80) NOT NULL DEFAULT ''",
        "holiday_tts_text TEXT NULL"
    ];
    foreach ($cols as $def) {
        $name = strtok($def, ' ');
        $res = $db->query("SHOW COLUMNS FROM spbx_call_rules LIKE '" . $db->real_escape_string($name) . "'");
        if (!$res || $res->num_rows === 0) {
            $db->query("ALTER TABLE spbx_call_rules ADD COLUMN " . $def);
        }
    }

    $db->query("CREATE TABLE IF NOT EXISTS spbx_call_rule_time_windows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rule_id INT NOT NULL,
        weekday VARCHAR(10) NOT NULL,
        start_time TIME DEFAULT NULL,
        end_time TIME DEFAULT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        active TINYINT(1) NOT NULL DEFAULT 1,
        KEY idx_rule (rule_id),
        KEY idx_weekday (weekday)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}

function spbx_call_rules_main_from_did($did)
{
    $did = trim((string)$did);
    $plain = ltrim($did, '_');
    $plain = str_replace(['.', 'X', 'x', 'Z', 'z', 'N', 'n'], '', $plain);
    $digitsPlus = preg_replace('/[^0-9+]/', '', $plain);
    $digits = preg_replace('/[^0-9]/', '', $plain);

    $main = '';
    if (preg_match('/^\+([0-9]{8,15})/', $digitsPlus, $m)) {
        $main = '+' . $m[1];
    }

    // Bei Pattern _+43312423826XX soll main ohne die letzten Pattern-Zeichen erkannt werden.
    if (strpos($did, 'X') !== false || strpos($did, 'x') !== false) {
        $prefix = preg_replace('/[XxZzNn\.].*$/', '', ltrim($did, '_'));
        $prefix = preg_replace('/[^0-9+]/', '', $prefix);
        if ($prefix !== '') {
            $main = $prefix;
        }
    }

    return ['main' => $main, 'digits' => $digits];
}

function spbx_call_rules_internal_context_for_did($did)
{
    $m = spbx_call_rules_main_from_did($did);
    $main = (string)($m['main'] ?? '');

    if ($main !== '') {
        $ctxNo = preg_replace('/[^0-9]/', '', $main);
        if ($ctxNo !== '') {
            return 'internal_' . $ctxNo;
        }
    }

    return 'internal';
}

function spbx_call_rules_did_suffix_expr($did)
{
    $did = trim((string)$did);

    // Für Pattern mit Stammnummer:
    // _+43312423826XX -> ${EXTEN:12}
    // _+43312423826XXX -> ${EXTEN:12}
    // Die Länge der Stammnummer wird fix in den Dialplan geschrieben.
    if (substr($did, 0, 1) === '_') {
        $pattern = substr($did, 1);
        $prefix = preg_replace('/[XxZzNn\.].*$/', '', $pattern);
        $prefix = preg_replace('/[^0-9+]/', '', $prefix);

        if ($prefix !== '') {
            return '${EXTEN:' . strlen($prefix) . '}';
        }

        // Pattern nur _XX/_XXX/_XXXX: EXTEN ist bereits die Durchwahl.
        return '${EXTEN}';
    }

    return '${EXTEN}';
}


function spbx_call_rules_is_did_pattern_target($rule)
{
    $did = trim((string)($rule['did'] ?? ''));
    $ctx = trim((string)($rule['open_destination_context'] ?? ''));
    $ext = trim((string)($rule['open_destination_exten'] ?? ''));
    $type = trim((string)($rule['open_destination_type'] ?? ''));

    if ($type === 'did_extension') {
        return true;
    }

    // Fehlertoleranz für falsch gespeicherte Regeln:
    // DID ist ein Pattern und Ziel-Extension ist XX/XXX/XXXX.
    if (substr($did, 0, 1) === '_' && preg_match('/^[Xx]{1,4}$/', $ext) && strpos($ctx, 'internal_') === 0) {
        return true;
    }

    return false;
}


function spbx_call_rules_target($rule)
{
    $type = (string)($rule['open_destination_type'] ?? 'custom');
    $ctx = (string)($rule['open_destination_context'] ?? 'internal');
    $ext = (string)($rule['open_destination_exten'] ?? '0');
    $did = (string)($rule['did'] ?? '');

    if (spbx_call_rules_is_did_pattern_target($rule)) {
        if ($ctx === '' || $ctx === 'auto_internal') {
            $ctx = spbx_call_rules_internal_context_for_did($did);
        }

        $extExpr = spbx_call_rules_did_suffix_expr($did);
        return 'DID_EXTENSION|' . $ctx . '|' . $extExpr;
    }

    if ($type === 'extension') {
        if ($ctx === '' || $ctx === 'internal' || $ctx === 'auto_internal') {
            $ctx = spbx_call_rules_internal_context_for_did($did);
        }
        return $ctx . ',' . $ext . ',1';
    }

    if ($type === 'ringgroup') {
        return 'ringgroups,' . $ext . ',1';
    }
    if ($type === 'queue') {
        return 'queue-services,' . $ext . ',1';
    }
    if ($type === 'ivr') {
        return spbx_ivr_context_for_number($ext) . ',s,1';
    }

    return $ctx . ',' . $ext . ',1';
}


function spbx_call_rules_build_destination($type, $ctx, $exten, $defaultCtx = '', $defaultExten = '')
{
    $type = trim((string)$type);
    $ctx = trim((string)$ctx);
    $exten = trim((string)$exten);
    if ($type === 'hangup' || $type === '') return ['Hangup', ''];
    if ($type === 'voicemail') {
        if ($exten === '') $exten = $defaultExten !== '' ? $defaultExten : '0';
        if ($ctx === '') $ctx = $defaultCtx !== '' ? $defaultCtx : 'internal';
        return ['VoiceMail', $exten . '@' . $ctx . ',u'];
    }
    if ($type === 'external') {
        if ($exten === '') return ['Hangup', ''];
        return ['Dial', 'Local/' . $exten . '@internal'];
    }
    if ($type === 'ringgroup') return ['Goto', 'ringgroups,' . $exten . ',1'];
    if ($type === 'queue') return ['Goto', 'queue-services,' . $exten . ',1'];
    if ($type === 'ivr') return ['Goto', spbx_ivr_context_for_number($exten) . ',s,1'];
    if ($type === 'extension') {
        if ($ctx === '' || $ctx === 'auto_internal') $ctx = $defaultCtx !== '' ? $defaultCtx : 'internal';
        return ['Goto', $ctx . ',' . $exten . ',1'];
    }
    if ($type === 'custom' && $ctx !== '' && $exten !== '') return ['Goto', $ctx . ',' . $exten . ',1'];
    return ['Hangup', ''];
}

function spbx_call_rules_closed_destination($rule, $kind = 'closed')
{
    $defaultCtx = (string)($rule['main_mailbox_context'] ?? 'internal');
    $defaultExt = (string)($rule['main_mailbox'] ?? '0');
    if ($kind === 'holiday') {
        return spbx_call_rules_build_destination($rule['holiday_destination_type'] ?? 'hangup', $rule['holiday_destination_context'] ?? '', $rule['holiday_destination_exten'] ?? '', $defaultCtx, $defaultExt);
    }
    $type = $rule['closed_destination_type'] ?? '';
    if ($type === '') $type = ((string)($rule['closed_action'] ?? 'hangup') === 'voicemail') ? 'voicemail' : 'hangup';
    return spbx_call_rules_build_destination($type, $rule['closed_destination_context'] ?? '', $rule['closed_destination_exten'] ?? '', $defaultCtx, $defaultExt);
}




function spbx_call_rules_install_queue_realtime_schema()
{
    $db = spbx_db();
    $db->query("CREATE TABLE IF NOT EXISTS queues (
        name VARCHAR(128) NOT NULL PRIMARY KEY,
        musiconhold VARCHAR(128) DEFAULT 'default',
        timeout INT DEFAULT 20,
        ringinuse VARCHAR(8) DEFAULT 'no',
        setinterfacevar VARCHAR(8) DEFAULT 'yes',
        setqueuevar VARCHAR(8) DEFAULT 'yes',
        setqueueentryvar VARCHAR(8) DEFAULT 'yes',
        retry INT DEFAULT 5,
        wrapuptime INT DEFAULT 0,
        maxlen INT DEFAULT 0,
        servicelevel INT DEFAULT 60,
        strategy VARCHAR(32) DEFAULT 'ringall',
        joinempty VARCHAR(64) DEFAULT 'yes',
        leavewhenempty VARCHAR(64) DEFAULT 'no',
        eventmemberstatus VARCHAR(8) DEFAULT 'yes',
        eventwhencalled VARCHAR(8) DEFAULT 'yes',
        reportholdtime VARCHAR(8) DEFAULT 'no'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $db->query("CREATE TABLE IF NOT EXISTS queue_members (
        uniqueid INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        queue_name VARCHAR(128) NOT NULL,
        interface VARCHAR(128) NOT NULL,
        membername VARCHAR(128) DEFAULT NULL,
        state_interface VARCHAR(128) DEFAULT NULL,
        penalty INT DEFAULT 0,
        paused INT DEFAULT 0,
        UNIQUE KEY uniq_queue_interface (queue_name, interface),
        KEY idx_queue_name (queue_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}

function spbx_call_rules_rebuild_queue_realtime()
{
    $db = spbx_db();
    spbx_call_rules_install_queue_realtime_schema();

    $db->query("DELETE FROM queue_members WHERE queue_name LIKE 'spbxq_%'");
    $db->query("DELETE FROM queues WHERE name LIKE 'spbxq_%'");

    $res = $db->query("SELECT * FROM spbx_queues WHERE active=1 ORDER BY CAST(queue_number AS UNSIGNED), queue_number");
    if (!$res) return;

    while ($q = $res->fetch_assoc()) {
        $qn = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)($q['queue_number'] ?? ''));
        if ($qn === '') continue;

        $name = 'spbxq_' . $qn;
        $music = (string)($q['musicclass'] ?? 'default');
        $timeout = max(5, (int)($q['timeout_seconds'] ?? 20));
        $strategy = (string)($q['strategy'] ?? 'ringall');

        $stmt = $db->prepare("
            INSERT INTO queues
            (name, musiconhold, timeout, ringinuse, setinterfacevar, setqueuevar, setqueueentryvar,
             retry, wrapuptime, maxlen, servicelevel, strategy, joinempty, leavewhenempty,
             eventmemberstatus, eventwhencalled, reportholdtime)
            VALUES (?, ?, ?, 'no', 'yes', 'yes', 'yes', 5, 0, 0, 60, ?, 'yes', 'no', 'yes', 'yes', 'no')
            ON DUPLICATE KEY UPDATE musiconhold=VALUES(musiconhold), timeout=VALUES(timeout), strategy=VALUES(strategy)
        ");
        if ($stmt) {
            $stmt->bind_param('ssis', $name, $music, $timeout, $strategy);
            $stmt->execute();
        }

        $members = $db->query("
            SELECT m.*, e.extension, e.display_name, p.context
            FROM spbx_queue_members m
            JOIN spbx_extensions e ON e.endpoint_id=m.endpoint_id
            JOIN ps_endpoints p ON p.id=e.endpoint_id
            WHERE m.queue_id=" . (int)$q['id'] . "
              AND m.active=1
              AND m.member_type='fixed'
        ");

        if ($members) {
            while ($m = $members->fetch_assoc()) {
                $iface = 'Local/' . $m['extension'] . '@' . ($m['context'] ?: 'internal') . '/n';
                $memberName = preg_replace('/[,;]/', ' ', (string)($m['display_name'] ?: $m['extension']));
                $stateInterface = 'PJSIP/' . $m['extension'];
                $penalty = (int)($m['penalty'] ?? 0);

                $stmt = $db->prepare("
                    INSERT INTO queue_members
                    (queue_name, interface, membername, state_interface, penalty, paused)
                    VALUES (?, ?, ?, ?, ?, 0)
                    ON DUPLICATE KEY UPDATE membername=VALUES(membername), state_interface=VALUES(state_interface), penalty=VALUES(penalty), paused=0
                ");
                if ($stmt) {
                    $stmt->bind_param('ssssi', $name, $iface, $memberName, $stateInterface, $penalty);
                    $stmt->execute();
                }
            }
        }
    }
}


function spbx_call_rules_ensure_queue_services_ast_config()
{
    $db = spbx_db();
    spbx_ast_config_writer_install_schema();

    $db->query("DELETE FROM ast_config WHERE filename='extensions.conf' AND category='queue-services'");

    $stmt = $db->prepare("
        INSERT INTO ast_config
        (cat_metric, var_metric, commented, filename, category, var_name, var_val)
        VALUES (4010, 0, 0, 'extensions.conf', 'queue-services', 'switch', 'Realtime/@extensions')
    ");
    if ($stmt) {
        $stmt->execute();
    }
}


function spbx_call_rules_ext_insert($context, $exten, $priority, $app, $appdata = '')
{
    $db = spbx_db();
    $stmt = $db->prepare("
        INSERT INTO extensions (context, exten, priority, app, appdata)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE app=VALUES(app), appdata=VALUES(appdata)
    ");
    if ($stmt) {
        $priority = (int)$priority;
        $stmt->bind_param('ssiss', $context, $exten, $priority, $app, $appdata);
        $stmt->execute();
    }
}


function spbx_queues_install_schema_exists_guard()
{
    $db = spbx_db();
    $db->query("CREATE TABLE IF NOT EXISTS spbx_queues (
        id INT AUTO_INCREMENT PRIMARY KEY,
        queue_number VARCHAR(20) NOT NULL,
        queue_name VARCHAR(120) NOT NULL,
        strategy VARCHAR(40) NOT NULL DEFAULT 'ringall',
        timeout_seconds INT NOT NULL DEFAULT 20,
        max_wait_seconds INT NOT NULL DEFAULT 300,
        musicclass VARCHAR(80) NOT NULL DEFAULT 'default',
        queue_announcement_enabled TINYINT(1) NOT NULL DEFAULT 0,
        queue_announcement_file VARCHAR(255) DEFAULT NULL,
        queue_tts_text TEXT NULL,
        emergency_announcement_enabled TINYINT(1) NOT NULL DEFAULT 0,
        emergency_announcement_file VARCHAR(255) DEFAULT NULL,
        emergency_tts_text TEXT NULL,
        timeout_destination_type VARCHAR(40) NOT NULL DEFAULT 'hangup',
        timeout_destination_context VARCHAR(80) NOT NULL DEFAULT '',
        timeout_destination_exten VARCHAR(80) NOT NULL DEFAULT '',
        callback_login_enabled TINYINT(1) NOT NULL DEFAULT 1,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_queue_number (queue_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}


function spbx_call_rules_rebuild_queue_services_context()
{
    $db = spbx_db();

    // Queue-Service Context ist Realtime und muss existieren, sobald eine Anrufregel auf Queue zeigt.
    $db->query("DELETE FROM extensions WHERE context='queue-services'");
    spbx_call_rules_ensure_queue_services_ast_config();
    spbx_call_rules_ensure_queue_services_ast_config();

    $res = $db->query("SELECT * FROM spbx_queues WHERE active=1 ORDER BY CAST(queue_number AS UNSIGNED), queue_number");
    if (!$res) {
        return;
    }

    while ($q = $res->fetch_assoc()) {
        $qn = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)($q['queue_number'] ?? ''));
        if ($qn === '') continue;

        $p = 1;
        spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'NoOp', 'ServusPBX Queue ' . (string)($q['queue_name'] ?? $qn));

        $answered = false;
        if ((int)($q['queue_announcement_enabled'] ?? 0) === 1 && trim((string)($q['queue_announcement_file'] ?? '')) !== '') {
            spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'Answer', '');
            $answered = true;
            spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'MP3Player', (string)$q['queue_announcement_file']);
        }

        if ((int)($q['emergency_announcement_enabled'] ?? 0) === 1 && trim((string)($q['emergency_announcement_file'] ?? '')) !== '') {
            if (!$answered) {
                spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'Answer', '');
                $answered = true;
            }
            spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'MP3Player', (string)$q['emergency_announcement_file']);
        }

        $maxWait = max(30, (int)($q['max_wait_seconds'] ?? 300));
        spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'Queue', 'spbxq_' . $qn . ',t,,,,' . $maxWait);

        $destType = (string)($q['timeout_destination_type'] ?? 'hangup');
        $destCtx = (string)($q['timeout_destination_context'] ?? '');
        $destExt = (string)($q['timeout_destination_exten'] ?? '');

        if ($destType === 'extension' && $destExt !== '') {
            spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'Goto', ($destCtx ?: 'internal') . ',' . $destExt . ',1');
        } elseif ($destType === 'ringgroup' && $destExt !== '') {
            spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'Goto', 'ringgroups,' . $destExt . ',1');
        } elseif ($destType === 'external' && $destExt !== '') {
            spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'Dial', 'Local/' . $destExt . '@internal');
        } elseif ($destType === 'voicemail' && $destExt !== '') {
            spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'VoiceMail', $destExt . '@' . ($destCtx ?: 'internal') . ',u');
        } elseif ($destType === 'custom' && $destCtx !== '' && $destExt !== '') {
            spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'Goto', $destCtx . ',' . $destExt . ',1');
        }

        spbx_call_rules_ext_insert('queue-services', $qn, $p++, 'Hangup', '');
    }
}


function spbx_call_rules_rebuild_dialplan()
{
    $db = spbx_db();
    spbx_call_rules_install_schema();
    spbx_queues_install_schema_exists_guard();
    spbx_ring_groups_install_schema();

    if (function_exists('spbx_ast_config_writer_add')) {
        $db->query("DELETE FROM ast_config WHERE filename='extensions.conf' AND category='incoming'");
        spbx_ast_config_writer_add('incoming', 'switch', 'Realtime/@extensions', 1000, 0);

        $db->query("DELETE FROM ast_config WHERE filename='extensions.conf' AND category IN ('ringgroups','queue-services')");
        spbx_ast_config_writer_add('ringgroups', 'switch', 'Realtime/@extensions', 4000, 0);
        spbx_call_rules_ensure_queue_services_ast_config();
    }

    $db->query("DELETE FROM extensions WHERE context='incoming' OR context LIKE 'from\\_%'");

    $hasCommentColumn = false;
    $colRes = $db->query("SHOW COLUMNS FROM extensions LIKE 'comment'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasCommentColumn = true;
    }

    if ($hasCommentColumn) {
        $ins = $db->prepare("
            INSERT INTO extensions (context, exten, priority, app, appdata, comment)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE app=VALUES(app), appdata=VALUES(appdata), comment=VALUES(comment)
        ");
    } else {
        $ins = $db->prepare("
            INSERT INTO extensions (context, exten, priority, app, appdata)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE app=VALUES(app), appdata=VALUES(appdata)
        ");
    }

    if (!$ins) {
        return false;
    }

    $insert = function($context, $exten, $priority, $app, $appdata = '', $comment = '') use ($ins, $hasCommentColumn) {
        $priority = (int)$priority;
        if ($hasCommentColumn) {
            $ins->bind_param('ssisss', $context, $exten, $priority, $app, $appdata, $comment);
        } else {
            $ins->bind_param('ssiss', $context, $exten, $priority, $app, $appdata);
        }
        $ins->execute();
    };

    spbx_call_rules_rebuild_queue_realtime();
    spbx_call_rules_rebuild_queue_services_context();

    $rules = $db->query("SELECT * FROM spbx_call_rules WHERE active=1 ORDER BY id ASC");
    if (!$rules) {
        return false;
    }

    while ($r = $rules->fetch_assoc()) {
        $ctx = 'incoming';
        $did = trim((string)$r['did']);
        if ($did === '' || $did === '*') {
            $did = 's';
        }

        $extens = [$did];

        // Nur bei konkreten DIDs zusätzliche Varianten erzeugen.
        // Bei Patterns niemals zusätzliche +/Ziffern-Varianten erzeugen.
        if ($did !== 's' && substr($did, 0, 1) !== '_') {
            $cleanDid = preg_replace('/[^0-9]/', '', $did);
            if ($cleanDid !== '') {
                $extens[] = $cleanDid;
                $extens[] = '+' . $cleanDid;
            }
        }

        $extens = array_values(array_unique($extens));

        foreach ($extens as $exten) {
            $isPattern = substr((string)$exten, 0, 1) === '_';
            $isDidExtension = spbx_call_rules_is_did_pattern_target($r);

            $insert($ctx, $exten, 1, 'NoOp', 'ServusPBX Anrufregel ' . (string)$r['rule_name']);
            $insert($ctx, $exten, 2, 'ExecIf', '$["${CALLERID(num):0:1}"="+"]?Set(CALLERID(num)=00${CALLERID(num):1})');
            $insert($ctx, $exten, 3, 'Ringing', '2');

            if ((int)$r['manual_announcement_1_enabled'] === 1 && trim((string)$r['manual_announcement_1_file']) !== '') {
                $insert($ctx, $exten, 4, 'Answer', '');
                $insert($ctx, $exten, 5, 'MP3Player', (string)$r['manual_announcement_1_file']);
                $insert($ctx, $exten, 6, 'Hangup', '');
            } else {
                $insert($ctx, $exten, 4, 'NoOp', 'manual announcement 1 inactive');
                $insert($ctx, $exten, 5, 'NoOp', '');
                $insert($ctx, $exten, 6, 'NoOp', '');
            }

            if ((int)$r['manual_announcement_2_enabled'] === 1 && trim((string)$r['manual_announcement_2_file']) !== '') {
                $insert($ctx, $exten, 7, 'Answer', '');
                $insert($ctx, $exten, 8, 'MP3Player', (string)$r['manual_announcement_2_file']);
                $insert($ctx, $exten, 9, 'Hangup', '');
            } else {
                $insert($ctx, $exten, 7, 'NoOp', 'manual announcement 2 inactive');
                $insert($ctx, $exten, 8, 'NoOp', '');
                $insert($ctx, $exten, 9, 'Set', 'TODAY=${STRFTIME(${EPOCH},,%Y-%m-%d)}');
            }

            if ((int)$r['holiday_enabled'] === 1) {
                $insert($ctx, $exten, 10, 'Set', 'IS_BANKHOLIDAY=${DB_EXISTS(bankholiday/${TODAY})}');
                // Wichtig: nur Priority-Ziel, kein Context/Exten-Ziel. So bleibt ${EXTEN} die echte gewählte Nummer.
                $insert($ctx, $exten, 11, 'GotoIf', '${IS_BANKHOLIDAY}?60:12');
            } else {
                $insert($ctx, $exten, 10, 'NoOp', 'holiday check disabled');
                $insert($ctx, $exten, 11, 'NoOp', '');
            }

            $winStmt = $db->prepare("SELECT * FROM spbx_call_rule_time_windows WHERE rule_id=? AND active=1 ORDER BY sort_order ASC, id ASC");
            $rid = (int)$r['id'];
            $winStmt->bind_param('i', $rid);
            $winStmt->execute();
            $wins = $winStmt->get_result();

            $hasWindow = false;
            $prio = 12;
            $sourceInternalCtx = spbx_call_rules_internal_context_for_did($did);
            $insert($ctx, $exten, $prio++, 'Set', 'SPBX_INTERNAL_CONTEXT=' . $sourceInternalCtx);

            if ($isDidExtension) {
                // Separater, pattern-sicherer Zweig:
                // GotoIfTime springt nur auf Priority 80. Kein Sprung auf _+...XX.
                while ($w = $wins->fetch_assoc()) {
                    $start = substr((string)$w['start_time'], 0, 5);
                    $end = substr((string)$w['end_time'], 0, 5);
                    $day = (string)$w['weekday'];
                    if ($start !== '' && $end !== '' && $day !== '') {
                        $insert($ctx, $exten, $prio++, 'GotoIfTime', $start . '-' . $end . ',' . $day . ',*,*?80');
                        $hasWindow = true;
                    }
                }

                if ($hasWindow) {
                    $insert($ctx, $exten, $prio++, 'NoOp', 'closed by time rules');
                    $insert($ctx, $exten, $prio++, 'Goto', '26');
                } else {
                    $insert($ctx, $exten, $prio++, 'Goto', '80');
                }

                $targetCtx = (string)($r['open_destination_context'] ?? '');
                if ($targetCtx === '' || $targetCtx === 'auto_internal' || $targetCtx === 'internal') {
                    $targetCtx = spbx_call_rules_internal_context_for_did($did);
                }

                $extExpr = spbx_call_rules_did_suffix_expr($did);

                // Hier wird ${EXTEN} direkt im gematchten Pattern ausgewertet.
                // Bei _+43312423826XX wird daraus ${EXTEN:12} => 10.
                $insert($ctx, $exten, 80, 'Set', 'DURCHWAHL=' . $extExpr);
                $insert($ctx, $exten, 81, 'NoOp', 'Gefundene Durchwahl ist: ${DURCHWAHL}');
                $insert($ctx, $exten, 82, 'Goto', $targetCtx . ',${DURCHWAHL},1');
            } else {
                $target = spbx_call_rules_target($r);

                while ($w = $wins->fetch_assoc()) {
                    $start = substr((string)$w['start_time'], 0, 5);
                    $end = substr((string)$w['end_time'], 0, 5);
                    $day = (string)$w['weekday'];
                    if ($start !== '' && $end !== '' && $day !== '') {
                        $insert($ctx, $exten, $prio++, 'GotoIfTime', $start . '-' . $end . ',' . $day . ',*,*?' . $target);
                        $hasWindow = true;
                    }
                }

                if (!$hasWindow) {
                    $insert($ctx, $exten, $prio++, 'Goto', $target);
                } else {
                    $insert($ctx, $exten, $prio++, 'NoOp', 'closed by time rules');
                    $insert($ctx, $exten, $prio++, 'Goto', '26');
                }
            }

            // Geschlossen-Zweig
            $insert($ctx, $exten, 26, 'Answer', '');
            if (trim((string)($r['closed_audio'] ?? '')) !== '') {
                $insert($ctx, $exten, 27, 'MP3Player', (string)$r['closed_audio']);
            } else {
                $insert($ctx, $exten, 27, 'NoOp', 'closed audio empty');
            }
            $closedDest = spbx_call_rules_closed_destination($r, 'closed');
            $insert($ctx, $exten, 28, $closedDest[0], $closedDest[1]);
            $insert($ctx, $exten, 29, 'Hangup', '');

            // Feiertags-Zweig
            $insert($ctx, $exten, 60, 'Answer', '');
            if (trim((string)($r['holiday_audio'] ?? '')) !== '') {
                $insert($ctx, $exten, 61, 'MP3Player', (string)$r['holiday_audio']);
            } else {
                $insert($ctx, $exten, 61, 'NoOp', 'holiday audio empty');
            }
            $holidayDest = spbx_call_rules_closed_destination($r, 'holiday');
            $insert($ctx, $exten, 62, $holidayDest[0], $holidayDest[1]);
            $insert($ctx, $exten, 63, 'Hangup', '');
        }
    }

    return true;
}
?>