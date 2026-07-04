<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/asterisk.php';
require_once __DIR__ . '/ast_config_writer.php';

function spbx_ivr_table_exists($table)
{
    $db = spbx_db();
    $stmt = $db->prepare("SELECT COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?");
    if (!$stmt) return false;
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return ((int)($row['c'] ?? 0)) > 0;
}

function spbx_ivr_has_column($table, $column)
{
    $db = spbx_db();
    $stmt = $db->prepare("SELECT COUNT(*) c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
    if (!$stmt) return false;
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return ((int)($row['c'] ?? 0)) > 0;
}

function spbx_ivr_install_schema()
{
    $db = spbx_db();

    $db->query("CREATE TABLE IF NOT EXISTS spbx_ivrs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ivr_number VARCHAR(20) NOT NULL,
        name VARCHAR(120) NOT NULL,
        description TEXT NULL,
        prompt_file VARCHAR(255) DEFAULT NULL,
        prompt_tts_text TEXT NULL,
        timeout_seconds INT NOT NULL DEFAULT 10,
        max_attempts INT NOT NULL DEFAULT 3,
        timeout_target_type VARCHAR(40) NOT NULL DEFAULT 'hangup',
        timeout_target_context VARCHAR(80) NOT NULL DEFAULT '',
        timeout_target_exten VARCHAR(80) NOT NULL DEFAULT '',
        invalid_target_type VARCHAR(40) NOT NULL DEFAULT 'repeat',
        invalid_target_context VARCHAR(80) NOT NULL DEFAULT '',
        invalid_target_exten VARCHAR(80) NOT NULL DEFAULT '',
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_ivr_number (ivr_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $db->query("CREATE TABLE IF NOT EXISTS spbx_ivr_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ivr_id INT NOT NULL,
        digit VARCHAR(2) NOT NULL,
        target_type VARCHAR(40) NOT NULL DEFAULT 'none',
        target_context VARCHAR(80) NOT NULL DEFAULT '',
        target_exten VARCHAR(80) NOT NULL DEFAULT '',
        sort_order INT NOT NULL DEFAULT 0,
        UNIQUE KEY uniq_ivr_digit (ivr_id, digit),
        KEY idx_ivr_id (ivr_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $cols = [
        'description' => "ALTER TABLE spbx_ivrs ADD COLUMN description TEXT NULL",
        'prompt_file' => "ALTER TABLE spbx_ivrs ADD COLUMN prompt_file VARCHAR(255) DEFAULT NULL",
        'prompt_tts_text' => "ALTER TABLE spbx_ivrs ADD COLUMN prompt_tts_text TEXT NULL",
        'timeout_seconds' => "ALTER TABLE spbx_ivrs ADD COLUMN timeout_seconds INT NOT NULL DEFAULT 10",
        'max_attempts' => "ALTER TABLE spbx_ivrs ADD COLUMN max_attempts INT NOT NULL DEFAULT 3",
        'timeout_target_type' => "ALTER TABLE spbx_ivrs ADD COLUMN timeout_target_type VARCHAR(40) NOT NULL DEFAULT 'hangup'",
        'timeout_target_context' => "ALTER TABLE spbx_ivrs ADD COLUMN timeout_target_context VARCHAR(80) NOT NULL DEFAULT ''",
        'timeout_target_exten' => "ALTER TABLE spbx_ivrs ADD COLUMN timeout_target_exten VARCHAR(80) NOT NULL DEFAULT ''",
        'invalid_target_type' => "ALTER TABLE spbx_ivrs ADD COLUMN invalid_target_type VARCHAR(40) NOT NULL DEFAULT 'repeat'",
        'invalid_target_context' => "ALTER TABLE spbx_ivrs ADD COLUMN invalid_target_context VARCHAR(80) NOT NULL DEFAULT ''",
        'invalid_target_exten' => "ALTER TABLE spbx_ivrs ADD COLUMN invalid_target_exten VARCHAR(80) NOT NULL DEFAULT ''",
        'active' => "ALTER TABLE spbx_ivrs ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1",
    ];
    foreach ($cols as $col => $sql) {
        if (!spbx_ivr_has_column('spbx_ivrs', $col)) {
            @$db->query($sql);
        }
    }
}

function spbx_ivr_context()
{
    return 'ivr';
}

function spbx_ivr_context_for_number($ivrNumber)
{
    $num = preg_replace('/[^0-9]/', '', (string)$ivrNumber);
    return $num !== '' ? 'ivr_' . $num : spbx_ivr_context();
}

function spbx_ivr_digits()
{
    return ['1','2','3','4','5','6','7','8','9','*','0','#'];
}

function spbx_ivr_target_types()
{
    return [
        'none' => 'Nicht belegt',
        'extension' => 'Nebenstelle',
        'queue' => 'Queue',
        'ringgroup' => 'Rufgruppe',
        'ivr' => 'Sprachmenü',
        'hangup' => 'Auflegen',
        'repeat' => 'Ansage wiederholen',
        'custom' => 'Benutzerdefiniert',
    ];
}

function spbx_ivr_all($activeOnly = false)
{
    spbx_ivr_install_schema();
    $db = spbx_db();
    $where = $activeOnly ? "WHERE active=1" : "";
    $res = $db->query("SELECT * FROM spbx_ivrs {$where} ORDER BY CAST(ivr_number AS UNSIGNED), ivr_number");
    $rows = [];
    if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;
    return $rows;
}

function spbx_ivr_get($id)
{
    spbx_ivr_install_schema();
    $db = spbx_db();
    $stmt = $db->prepare("SELECT * FROM spbx_ivrs WHERE id=? LIMIT 1");
    if (!$stmt) return null;
    $id = (int)$id;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $ivr = $stmt->get_result()->fetch_assoc();
    if (!$ivr) return null;
    $ivr['options'] = spbx_ivr_options($id);
    return $ivr;
}

function spbx_ivr_options($ivrId)
{
    spbx_ivr_install_schema();
    $db = spbx_db();
    $options = [];
    foreach (spbx_ivr_digits() as $d) {
        $options[$d] = [
            'digit' => $d,
            'target_type' => 'none',
            'target_context' => '',
            'target_exten' => '',
        ];
    }
    $stmt = $db->prepare("SELECT * FROM spbx_ivr_options WHERE ivr_id=?");
    if ($stmt) {
        $ivrId = (int)$ivrId;
        $stmt->bind_param('i', $ivrId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $digit = (string)$r['digit'];
            if (isset($options[$digit])) $options[$digit] = $r;
        }
    }
    return $options;
}

function spbx_ivr_internal_contexts()
{
    $db = spbx_db();
    $contexts = [];
    if (spbx_ivr_table_exists('ast_config')) {
        $res = $db->query("SELECT DISTINCT category FROM ast_config WHERE filename='extensions.conf' AND category LIKE 'internal\\_%' ORDER BY category");
        if ($res) while ($r = $res->fetch_assoc()) $contexts[] = (string)$r['category'];
    }
    if (!$contexts && spbx_ivr_table_exists('spbx_outbound_routes')) {
        $res = $db->query("SELECT outgoing_context FROM spbx_outbound_routes WHERE active=1 ORDER BY id ASC");
        if ($res) while ($r = $res->fetch_assoc()) {
            $out = (string)$r['outgoing_context'];
            if (strpos($out, 'outgoing_') === 0) $contexts[] = str_replace('outgoing_', 'internal_', $out);
        }
    }
    $contexts[] = 'internal';
    return array_values(array_unique(array_filter($contexts)));
}

function spbx_ivr_target_appdata($type, $context, $exten, $currentIvrNumber = '')
{
    $type = (string)$type;
    $context = trim((string)$context);
    $exten = trim((string)$exten);

    if ($type === 'extension' && $exten !== '') {
        $internalCtxExpr = '${IF($["${SPBX_INTERNAL_CONTEXT}"=""]?internal:${SPBX_INTERNAL_CONTEXT})}';
        return ['Goto', $internalCtxExpr . ',' . $exten . ',1'];
    }
    if ($type === 'queue' && $exten !== '') return ['Goto', 'queue-services,' . $exten . ',1'];
    if ($type === 'ringgroup' && $exten !== '') return ['Goto', 'ringgroups,' . $exten . ',1'];
    if ($type === 'ivr' && $exten !== '') return ['Goto', spbx_ivr_context_for_number($exten) . ',s,1'];
    if ($type === 'repeat' && $currentIvrNumber !== '') return ['Goto', spbx_ivr_context_for_number($currentIvrNumber) . ',s,1'];
    if ($type === 'custom' && $context !== '' && $exten !== '') return ['Goto', $context . ',' . $exten . ',1'];
    if ($type === 'hangup') return ['Hangup', ''];
    return ['Hangup', ''];
}

function spbx_ivr_rebuild_dialplan()
{
    spbx_ivr_install_schema();
    $db = spbx_db();

    if (function_exists('spbx_ast_config_writer_add') && spbx_ivr_table_exists('ast_config')) {
        $db->query("DELETE FROM ast_config WHERE filename='extensions.conf' AND (category='ivr' OR category LIKE 'ivr\\_%')");
    }

    $db->query("DELETE FROM extensions WHERE context='ivr' OR context LIKE 'ivr\\_%'");
    $db->query("DELETE FROM extensions WHERE app='Goto' AND (appdata LIKE 'ivr,%' OR appdata LIKE 'ivr\\_%,%')");

    $insert = function($context, $exten, $priority, $app, $appdata = '') use ($db) {
        $priority = (string)$priority;
        $stmt = $db->prepare("INSERT INTO extensions (context, exten, priority, app, appdata) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('sssss', $context, $exten, $priority, $app, $appdata);
            $stmt->execute();
        }
    };

    $internalContexts = spbx_ivr_internal_contexts();
    foreach (spbx_ivr_all(true) as $ivr) {
        $ivrId = (int)$ivr['id'];
        $num = trim((string)$ivr['ivr_number']);
        if (!preg_match('/^[0-9]{2,6}$/', $num)) continue;
        $ctx = spbx_ivr_context_for_number($num);
        $name = (string)($ivr['name'] ?? 'Sprachmenü');
        $timeout = max(1, min(60, (int)($ivr['timeout_seconds'] ?? 10)));
        $prompt = trim((string)($ivr['prompt_file'] ?? ''));

        if (function_exists('spbx_ast_config_writer_add') && spbx_ivr_table_exists('ast_config')) {
            spbx_ast_config_writer_add($ctx, 'switch', 'Realtime/@extensions', 4100 + $ivrId, 0);
        }

        $prio = 1;
        $insert($ctx, 's', $prio++, 'NoOp', 'ServusPBX Sprachmenue ' . $name . ' (' . $num . ')');
        $insert($ctx, 's', $prio++, 'Answer', '');
        if ($prompt !== '') {
            $insert($ctx, 's', $prio++, 'MP3Player', $prompt);
        }
        $insert($ctx, 's', $prio++, 'WaitExten', (string)$timeout);
        $insert($ctx, 's', $prio++, 'Goto', $ctx . ',t,1');

        $options = spbx_ivr_options($ivrId);
        foreach ($options as $digit => $o) {
            if (($o['target_type'] ?? 'none') === 'none') continue;
            [$app, $appdata] = spbx_ivr_target_appdata($o['target_type'], $o['target_context'], $o['target_exten'], $num);
            $insert($ctx, $digit, 1, 'NoOp', 'IVR ' . $num . ' Taste ' . $digit);
            $insert($ctx, $digit, 2, $app, $appdata);
        }

        [$tApp, $tData] = spbx_ivr_target_appdata($ivr['timeout_target_type'] ?? 'hangup', $ivr['timeout_target_context'] ?? '', $ivr['timeout_target_exten'] ?? '', $num);
        $insert($ctx, 't', 1, 'NoOp', 'IVR Timeout');
        $insert($ctx, 't', 2, $tApp, $tData);

        [$iApp, $iData] = spbx_ivr_target_appdata($ivr['invalid_target_type'] ?? 'repeat', $ivr['invalid_target_context'] ?? '', $ivr['invalid_target_exten'] ?? '', $num);
        $insert($ctx, 'i', 1, 'NoOp', 'IVR ungueltige Eingabe');
        $insert($ctx, 'i', 2, $iApp, $iData);

        foreach ($internalContexts as $intCtx) {
            $insert($intCtx, $num, 1, 'Set', 'SPBX_INTERNAL_CONTEXT=' . $intCtx);
            $insert($intCtx, $num, 2, 'Goto', $ctx . ',s,1');
        }
    }

    spbx_ast_cli('dialplan reload');
}
?>
