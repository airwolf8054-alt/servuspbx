<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/asterisk.php';
require_once __DIR__ . '/ast_config_writer.php';

function spbx_queues_install_schema()
{
    $db = spbx_db();
    spbx_queue_install_asterisk_realtime_schema();

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

    $db->query("CREATE TABLE IF NOT EXISTS spbx_queue_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        queue_id INT NOT NULL,
        endpoint_id VARCHAR(80) NOT NULL,
        member_type ENUM('fixed','optional') NOT NULL DEFAULT 'fixed',
        penalty INT NOT NULL DEFAULT 0,
        active TINYINT(1) NOT NULL DEFAULT 1,
        UNIQUE KEY uniq_queue_member (queue_id, endpoint_id),
        KEY idx_endpoint (endpoint_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $db->query("CREATE TABLE IF NOT EXISTS spbx_queue_pause_reasons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reason_name VARCHAR(80) NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 0,
        UNIQUE KEY uniq_reason (reason_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $db->query("CREATE TABLE IF NOT EXISTS spbx_queue_agent_status (
        id INT AUTO_INCREMENT PRIMARY KEY,
        endpoint_id VARCHAR(80) NOT NULL,
        queue_id INT NOT NULL,
        logged_in TINYINT(1) NOT NULL DEFAULT 0,
        paused TINYINT(1) NOT NULL DEFAULT 0,
        pause_reason_id INT NULL,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_agent_queue (endpoint_id, queue_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $cols = [
        "queue_announcement_enabled TINYINT(1) NOT NULL DEFAULT 0",
        "queue_announcement_file VARCHAR(255) DEFAULT NULL",
        "queue_tts_text TEXT NULL"
    ];
    foreach ($cols as $def) {
        $name = strtok($def, ' ');
        $res = $db->query("SHOW COLUMNS FROM spbx_queues LIKE '" . $db->real_escape_string($name) . "'");
        if (!$res || $res->num_rows === 0) {
            $db->query("ALTER TABLE spbx_queues ADD COLUMN " . $def);
        }
    }

    foreach (['Arbeitspause','Meeting','Mittagspause'] as $i => $r) {
        $stmt = $db->prepare("INSERT IGNORE INTO spbx_queue_pause_reasons (reason_name, sort_order, active) VALUES (?, ?, 1)");
        if ($stmt) {
            $sort = ($i + 1) * 10;
            $stmt->bind_param('si', $r, $sort);
            $stmt->execute();
        }
    }
}


function spbx_queue_all($activeOnly = true)
{
    spbx_queues_install_schema();
    $db = spbx_db();

    $where = $activeOnly ? "WHERE active=1" : "";
    $res = $db->query("
        SELECT *
        FROM spbx_queues
        {$where}
        ORDER BY CAST(queue_number AS UNSIGNED), queue_number
    ");

    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
    }

    return $rows;
}

function spbx_queue_select_options()
{
    $out = [];
    foreach (spbx_queue_all(true) as $q) {
        $number = (string)($q['queue_number'] ?? '');
        if ($number === '') {
            continue;
        }

        $out[] = [
            'queue_number' => $number,
            'queue_name' => (string)($q['queue_name'] ?? ''),
            'label' => trim($number . ' - ' . (string)($q['queue_name'] ?? '')),
        ];
    }

    return $out;
}

function spbx_queue_get($id)
{
    spbx_queues_install_schema();
    $db = spbx_db();

    $stmt = $db->prepare("SELECT * FROM spbx_queues WHERE id=? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $id = (int)$id;
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

function spbx_queue_members($queueId, $type = null)
{
    spbx_queues_install_schema();
    $db = spbx_db();

    $sql = "
        SELECT m.*, e.extension, e.display_name, p.context
        FROM spbx_queue_members m
        JOIN spbx_extensions e ON e.endpoint_id=m.endpoint_id
        JOIN ps_endpoints p ON p.id=e.endpoint_id
        WHERE m.queue_id=?
          AND m.active=1
    ";

    if ($type !== null) {
        $sql .= " AND m.member_type=? ";
    }

    $sql .= " ORDER BY CAST(e.extension AS UNSIGNED), e.extension";

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $queueId = (int)$queueId;

    if ($type !== null) {
        $type = (string)$type;
        $stmt->bind_param('is', $queueId, $type);
    } else {
        $stmt->bind_param('i', $queueId);
    }

    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }

    return $rows;
}

function spbx_queue_pause_reasons()
{
    spbx_queues_install_schema();
    $db = spbx_db();

    $res = $db->query("SELECT * FROM spbx_queue_pause_reasons WHERE active=1 ORDER BY sort_order,id");
    $rows = [];

    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
    }

    return $rows;
}




function spbx_queue_install_asterisk_realtime_schema()
{
    $db = spbx_db();

    // Asterisk app_queue realtime erwartet standardmäßig eine Tabelle "queues".
    $db->query("CREATE TABLE IF NOT EXISTS queues (
        name VARCHAR(128) NOT NULL PRIMARY KEY,
        musiconhold VARCHAR(128) DEFAULT 'default',
        announce VARCHAR(128) DEFAULT NULL,
        context VARCHAR(128) DEFAULT NULL,
        timeout INT DEFAULT 20,
        ringinuse VARCHAR(8) DEFAULT 'no',
        setinterfacevar VARCHAR(8) DEFAULT 'yes',
        setqueuevar VARCHAR(8) DEFAULT 'yes',
        setqueueentryvar VARCHAR(8) DEFAULT 'yes',
        monitor_format VARCHAR(8) DEFAULT NULL,
        membermacro VARCHAR(512) DEFAULT NULL,
        membergosub VARCHAR(512) DEFAULT NULL,
        queue_youarenext VARCHAR(128) DEFAULT NULL,
        queue_thereare VARCHAR(128) DEFAULT NULL,
        queue_callswaiting VARCHAR(128) DEFAULT NULL,
        queue_quantity1 VARCHAR(128) DEFAULT NULL,
        queue_quantity2 VARCHAR(128) DEFAULT NULL,
        queue_holdtime VARCHAR(128) DEFAULT NULL,
        queue_minutes VARCHAR(128) DEFAULT NULL,
        queue_minute VARCHAR(128) DEFAULT NULL,
        queue_seconds VARCHAR(128) DEFAULT NULL,
        queue_thankyou VARCHAR(128) DEFAULT NULL,
        queue_callerannounce VARCHAR(128) DEFAULT NULL,
        queue_reporthold VARCHAR(128) DEFAULT NULL,
        announce_frequency INT DEFAULT 0,
        announce_to_first_user VARCHAR(8) DEFAULT 'no',
        min_announce_frequency INT DEFAULT 0,
        announce_round_seconds INT DEFAULT 0,
        announce_holdtime VARCHAR(16) DEFAULT 'no',
        retry INT DEFAULT 5,
        wrapuptime INT DEFAULT 0,
        maxlen INT DEFAULT 0,
        servicelevel INT DEFAULT 60,
        strategy VARCHAR(32) DEFAULT 'ringall',
        joinempty VARCHAR(64) DEFAULT 'yes',
        leavewhenempty VARCHAR(64) DEFAULT 'no',
        eventmemberstatus VARCHAR(8) DEFAULT 'yes',
        eventwhencalled VARCHAR(8) DEFAULT 'yes',
        reportholdtime VARCHAR(8) DEFAULT 'no',
        memberdelay INT DEFAULT 0,
        weight INT DEFAULT 0,
        timeoutrestart VARCHAR(8) DEFAULT 'no',
        defaultrule VARCHAR(128) DEFAULT NULL,
        timeoutpriority VARCHAR(32) DEFAULT 'app'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // Asterisk app_queue realtime Members.
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

function spbx_queue_rebuild_asterisk_realtime()
{
    spbx_queue_install_asterisk_realtime_schema();
    $db = spbx_db();

    $db->query("DELETE FROM queue_members WHERE queue_name LIKE 'spbxq_%'");
    $db->query("DELETE FROM queues WHERE name LIKE 'spbxq_%'");

    foreach (spbx_queue_all(true) as $q) {
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
            ON DUPLICATE KEY UPDATE
              musiconhold=VALUES(musiconhold),
              timeout=VALUES(timeout),
              strategy=VALUES(strategy),
              ringinuse='no',
              joinempty='yes',
              leavewhenempty='no'
        ");

        if ($stmt) {
            $stmt->bind_param('ssis', $name, $music, $timeout, $strategy);
            $stmt->execute();
        }

        foreach (spbx_queue_members((int)$q['id'], 'fixed') as $m) {
            $iface = 'Local/' . $m['extension'] . '@' . ($m['context'] ?: 'internal') . '/n';
            $memberName = preg_replace('/[,;]/', ' ', (string)($m['display_name'] ?: $m['extension']));
            $stateInterface = 'PJSIP/' . $m['extension'];
            $penalty = (int)($m['penalty'] ?? 0);

            $stmt = $db->prepare("
                INSERT INTO queue_members
                (queue_name, interface, membername, state_interface, penalty, paused)
                VALUES (?, ?, ?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE
                  membername=VALUES(membername),
                  state_interface=VALUES(state_interface),
                  penalty=VALUES(penalty),
                  paused=0
            ");

            if ($stmt) {
                $stmt->bind_param('ssssi', $name, $iface, $memberName, $stateInterface, $penalty);
                $stmt->execute();
            }
        }
    }
}


function spbx_queue_ensure_queue_services_ast_config()
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


function spbx_queue_destination($type, $ctx, $exten)
{
    $type = trim((string)$type);
    $ctx = trim((string)$ctx);
    $exten = trim((string)$exten);

    if ($type === 'extension' && $exten !== '') return ['Goto', ($ctx ?: 'internal') . ',' . $exten . ',1'];
    if ($type === 'ringgroup' && $exten !== '') return ['Goto', 'ringgroups,' . $exten . ',1'];
    if ($type === 'external' && $exten !== '') return ['Dial', 'Local/' . $exten . '@internal'];
    if ($type === 'voicemail' && $exten !== '') return ['VoiceMail', $exten . '@' . ($ctx ?: 'internal') . ',u'];
    if ($type === 'custom' && $ctx !== '' && $exten !== '') return ['Goto', $ctx . ',' . $exten . ',1'];

    return ['Hangup', ''];
}

function spbx_queue_internal_contexts()
{
    $db = spbx_db();
    $out = [];
    $res = $db->query("SELECT DISTINCT outgoing_context FROM spbx_outbound_routes WHERE active=1 ORDER BY id");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $ctx = str_replace('outgoing_', 'internal_', (string)$r['outgoing_context']);
            if ($ctx !== '') $out[$ctx] = true;
        }
    }
    if (!$out) $out['internal'] = true;
    return array_keys($out);
}

function spbx_queue_ast_insert($filename, $category, $varName, $varVal, $catMetric, $varMetric)
{
    $db = spbx_db();
    spbx_ast_config_writer_install_schema();

    $stmt = $db->prepare("
        INSERT INTO ast_config
        (cat_metric, var_metric, commented, filename, category, var_name, var_val)
        VALUES (?, ?, 0, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE var_val=VALUES(var_val), commented=0
    ");
    if ($stmt) {
        $catMetric = (int)$catMetric;
        $varMetric = (int)$varMetric;
        $stmt->bind_param('iissss', $catMetric, $varMetric, $filename, $category, $varName, $varVal);
        $stmt->execute();
    }
}

function spbx_queues_rebuild()
{
    spbx_queues_install_schema();
    $db = spbx_db();

    $db->query("DELETE FROM ast_config WHERE filename='queues.conf' AND category LIKE 'spbxq_%'");
    $db->query("DELETE FROM ast_config WHERE filename='extensions.conf' AND category IN ('queue-services')");
    $db->query("DELETE FROM extensions WHERE context IN ('queue-services')");
    spbx_queue_ensure_queue_services_ast_config();
    foreach (spbx_queue_internal_contexts() as $ictxClean) {
        $db->query("DELETE FROM extensions WHERE context='" . $db->real_escape_string($ictxClean) . "' AND exten IN ('*45','*46')");
    }

    $queues = spbx_queue_all(true);
    $catMetric = 8000;

    foreach ($queues as $q) {
            $qn = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$q['queue_number']);
            if ($qn === '') continue;

            $cat = 'spbxq_' . $qn;
            spbx_queue_ast_insert('queues.conf', $cat, 'strategy', (string)$q['strategy'], $catMetric, 0);
            spbx_queue_ast_insert('queues.conf', $cat, 'timeout', (string)(int)$q['timeout_seconds'], $catMetric, 1);
            spbx_queue_ast_insert('queues.conf', $cat, 'musicclass', (string)$q['musicclass'], $catMetric, 2);
            spbx_queue_ast_insert('queues.conf', $cat, 'joinempty', 'yes', $catMetric, 3);
            spbx_queue_ast_insert('queues.conf', $cat, 'leavewhenempty', 'no', $catMetric, 4);
            spbx_queue_ast_insert('queues.conf', $cat, 'ringinuse', 'no', $catMetric, 5);

            $vm = 10;
            foreach (spbx_queue_members((int)$q['id'], 'fixed') as $m) {
                $iface = 'Local/' . $m['extension'] . '@' . ($m['context'] ?: 'internal') . '/n';
                $label = preg_replace('/[,;]/', ' ', (string)($m['display_name'] ?: $m['extension']));
                spbx_queue_ast_insert('queues.conf', $cat, 'member', $iface . ',' . (int)$m['penalty'] . ',' . $label, $catMetric, $vm++);
            }

            $catMetric++;
    }

    // Dialplan: Queue entry points.
    foreach (spbx_queue_all(true) as $q) {
            $qn = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$q['queue_number']);
            if ($qn === '') continue;

            $ctx = 'queue-services';
            $p = 1;
            spbx_queue_ext_insert($ctx, $qn, $p++, 'NoOp', 'ServusPBX Queue ' . (string)$q['queue_name']);
            $answered = false;
            if ((int)($q['queue_announcement_enabled'] ?? 0) === 1 && trim((string)($q['queue_announcement_file'] ?? '')) !== '') {
                spbx_queue_ext_insert($ctx, $qn, $p++, 'Answer', '');
                $answered = true;
                spbx_queue_ext_insert($ctx, $qn, $p++, 'MP3Player', (string)$q['queue_announcement_file']);
            }
            if ((int)$q['emergency_announcement_enabled'] === 1 && trim((string)$q['emergency_announcement_file']) !== '') {
                if (!$answered) {
                    spbx_queue_ext_insert($ctx, $qn, $p++, 'Answer', '');
                    $answered = true;
                }
                spbx_queue_ext_insert($ctx, $qn, $p++, 'MP3Player', (string)$q['emergency_announcement_file']);
            }
            spbx_queue_ext_insert($ctx, $qn, $p++, 'Queue', 'spbxq_' . $qn . ',t,,,,' . (int)$q['max_wait_seconds']);
            $dest = spbx_queue_destination($q['timeout_destination_type'], $q['timeout_destination_context'], $q['timeout_destination_exten']);
            spbx_queue_ext_insert($ctx, $qn, $p++, $dest[0], $dest[1]);
            spbx_queue_ext_insert($ctx, $qn, $p++, 'Hangup', '');
    }

    // Internal shortcuts *45 / *46 and include to queue-services.
    foreach (spbx_queue_internal_contexts() as $ictx) {
        spbx_queue_ext_insert($ictx, '*45', 1, 'NoOp', 'ServusPBX Queue Agent Login Toggle ${CALLERID(num)}');
        spbx_queue_ext_insert($ictx, '*45', 2, 'Answer', '');
        spbx_queue_ext_insert($ictx, '*45', 3, 'System', '/usr/bin/php /var/www/html/scripts/queue_agent_toggle.php ${CALLERID(num)}');
        spbx_queue_ext_insert($ictx, '*45', 4, 'Playback', 'beep');
        spbx_queue_ext_insert($ictx, '*45', 5, 'Hangup', '');

        spbx_queue_ext_insert($ictx, '*46', 1, 'NoOp', 'ServusPBX Queue Agent Pause Toggle ${CALLERID(num)}');
        spbx_queue_ext_insert($ictx, '*46', 2, 'Answer', '');
        spbx_queue_ext_insert($ictx, '*46', 3, 'System', '/usr/bin/php /var/www/html/scripts/queue_agent_pause.php ${CALLERID(num)}');
        spbx_queue_ext_insert($ictx, '*46', 4, 'Playback', 'beep');
        spbx_queue_ext_insert($ictx, '*46', 5, 'Hangup', '');

        spbx_queue_ast_insert('extensions.conf', $ictx, 'include', 'queue-services', 9000, 200);
    }

    spbx_queue_rebuild_asterisk_realtime();

    spbx_ast_cli('module reload app_queue.so');
    spbx_ast_cli('module reload pbx_config.so');
    spbx_ast_cli('dialplan reload');

    return true;
}

function spbx_queue_ext_insert($context, $exten, $priority, $app, $appdata = '')
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

function spbx_queue_agent_allowed_queues($endpointId)
{
    spbx_queues_install_schema();
    $db = spbx_db();
    $stmt = $db->prepare("
        SELECT q.*, m.member_type, e.extension, p.context
        FROM spbx_queue_members m
        JOIN spbx_queues q ON q.id=m.queue_id
        JOIN spbx_extensions e ON e.endpoint_id=m.endpoint_id
        JOIN ps_endpoints p ON p.id=e.endpoint_id
        WHERE m.endpoint_id=? AND m.active=1 AND q.active=1 AND m.member_type='optional'
        ORDER BY q.id ASC
    ");
    $stmt->bind_param('s', $endpointId);
    $stmt->execute();
    return $stmt->get_result();
}
?>
