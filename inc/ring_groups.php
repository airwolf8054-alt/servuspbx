<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/asterisk.php';
require_once __DIR__ . '/ast_config_writer.php';

function spbx_rg_table_exists($table)
{
    $db = spbx_db();
    $stmt = $db->prepare("SELECT COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?");
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return ((int)($row['c'] ?? 0)) > 0;
}

function spbx_rg_has_column($table, $column)
{
    $db = spbx_db();
    $stmt = $db->prepare("SELECT COUNT(*) c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return ((int)($row['c'] ?? 0)) > 0;
}

function spbx_ring_groups_install_schema()
{
    $db = spbx_db();

    if (!spbx_rg_table_exists('spbx_ring_groups')) {
        $db->query("CREATE TABLE spbx_ring_groups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_number VARCHAR(20) NOT NULL DEFAULT '',
            name VARCHAR(120) NOT NULL DEFAULT '',
            strategy VARCHAR(20) NOT NULL DEFAULT 'ringall',
            ring_time INT NOT NULL DEFAULT 25,
            timeout_action VARCHAR(20) NOT NULL DEFAULT 'hangup',
            timeout_target VARCHAR(40) DEFAULT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_group_number (group_number),
            KEY idx_active (active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    $cols = [
        'group_number' => "ALTER TABLE spbx_ring_groups ADD COLUMN group_number VARCHAR(20) NOT NULL DEFAULT ''",
        'name' => "ALTER TABLE spbx_ring_groups ADD COLUMN name VARCHAR(120) NOT NULL DEFAULT ''",
        'strategy' => "ALTER TABLE spbx_ring_groups ADD COLUMN strategy VARCHAR(20) NOT NULL DEFAULT 'ringall'",
        'ring_time' => "ALTER TABLE spbx_ring_groups ADD COLUMN ring_time INT NOT NULL DEFAULT 25",
        'timeout_action' => "ALTER TABLE spbx_ring_groups ADD COLUMN timeout_action VARCHAR(20) NOT NULL DEFAULT 'hangup'",
        'timeout_target' => "ALTER TABLE spbx_ring_groups ADD COLUMN timeout_target VARCHAR(40) DEFAULT NULL",
        'active' => "ALTER TABLE spbx_ring_groups ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1",
        'created_at' => "ALTER TABLE spbx_ring_groups ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "ALTER TABLE spbx_ring_groups ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    foreach ($cols as $col => $sql) {
        if (!spbx_rg_has_column('spbx_ring_groups', $col)) @$db->query($sql);
    }

    @$db->query("ALTER TABLE spbx_ring_groups MODIFY strategy VARCHAR(20) NOT NULL DEFAULT 'ringall'");
    @$db->query("ALTER TABLE spbx_ring_groups MODIFY timeout_action VARCHAR(20) NOT NULL DEFAULT 'hangup'");

    if (!spbx_rg_table_exists('spbx_ring_group_members')) {
        $db->query("CREATE TABLE spbx_ring_group_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_id INT NOT NULL DEFAULT 0,
            extension VARCHAR(20) NOT NULL DEFAULT '',
            sort_order INT NOT NULL DEFAULT 0,
            active TINYINT(1) NOT NULL DEFAULT 1,
            KEY idx_group (group_id),
            KEY idx_extension (extension)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    $mcols = [
        'group_id' => "ALTER TABLE spbx_ring_group_members ADD COLUMN group_id INT NOT NULL DEFAULT 0",
        'extension' => "ALTER TABLE spbx_ring_group_members ADD COLUMN extension VARCHAR(20) NOT NULL DEFAULT ''",
        'sort_order' => "ALTER TABLE spbx_ring_group_members ADD COLUMN sort_order INT NOT NULL DEFAULT 0",
        'active' => "ALTER TABLE spbx_ring_group_members ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1"
    ];
    foreach ($mcols as $col => $sql) {
        if (!spbx_rg_has_column('spbx_ring_group_members', $col)) @$db->query($sql);
    }

    $db->query("UPDATE spbx_ring_groups SET group_number=COALESCE(NULLIF(group_number,''), CAST(id AS CHAR)) WHERE COALESCE(group_number,'')=''");
    $db->query("UPDATE spbx_ring_groups SET name=COALESCE(NULLIF(name,''), CONCAT('Rufgruppe ', group_number)) WHERE COALESCE(name,'')=''");
    $db->query("UPDATE spbx_ring_groups SET strategy='ringall' WHERE strategy NOT IN ('ringall','hunt','random') OR strategy IS NULL OR strategy=''");
    $db->query("UPDATE spbx_ring_groups SET ring_time=25 WHERE ring_time IS NULL OR ring_time<5");
    $db->query("UPDATE spbx_ring_groups SET timeout_action='hangup' WHERE timeout_action NOT IN ('hangup','extension') OR timeout_action IS NULL OR timeout_action=''");
}

function spbx_ring_group_context() { return 'ringgroups'; }

function spbx_ring_groups_member_string($members)
{
    $parts = [];
    foreach ($members as $ext) {
        $ext = trim((string)$ext);
        if (preg_match('/^[0-9]{2,4}$/', $ext)) $parts[] = 'PJSIP/' . $ext;
    }
    return implode('&', $parts);
}

function spbx_ring_groups_internal_contexts()
{
    $db = spbx_db();
    $contexts = [];

    if (spbx_rg_table_exists('ast_config')) {
        $res = $db->query("SELECT DISTINCT category FROM ast_config WHERE filename='extensions.conf' AND category LIKE 'internal\\\\_%' ORDER BY category");
        if ($res) while ($r = $res->fetch_assoc()) $contexts[] = (string)$r['category'];
    }

    if (!$contexts && spbx_rg_table_exists('spbx_outbound_routes')) {
        $res = $db->query("SELECT outgoing_context FROM spbx_outbound_routes WHERE active=1 ORDER BY id ASC");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $out = (string)$r['outgoing_context'];
                if (strpos($out, 'outgoing_') === 0) $contexts[] = str_replace('outgoing_', 'internal_', $out);
            }
        }
    }

    $contexts[] = 'internal';
    return array_values(array_unique(array_filter($contexts)));
}

function spbx_ring_groups_timeout_steps($insert, $ctx, $groupNo, $prio, $g)
{
    $action = (string)($g['timeout_action'] ?? 'hangup');
    $target = trim((string)($g['timeout_target'] ?? ''));
    if ($action === 'extension' && preg_match('/^[0-9]{2,4}$/', $target)) {
        $insert($ctx, $groupNo, $prio++, 'NoOp', 'Ringgruppe Timeout zu Nebenstelle ' . $target);
        $insert($ctx, $groupNo, $prio++, 'Goto', 'internal,' . $target . ',1');
        return;
    }
    $insert($ctx, $groupNo, $prio++, 'NoOp', 'Ringgruppe Timeout Hangup');
    $insert($ctx, $groupNo, $prio++, 'Hangup', '');
}

function spbx_ring_groups_rebuild_dialplan()
{
    $db = spbx_db();
    spbx_ring_groups_install_schema();
    $ctx = spbx_ring_group_context();

    if (function_exists('spbx_ast_config_writer_add') && spbx_rg_table_exists('ast_config')) {
        $stmt = $db->prepare("DELETE FROM ast_config WHERE filename='extensions.conf' AND category=?");
        $stmt->bind_param('s', $ctx);
        $stmt->execute();
        spbx_ast_config_writer_add($ctx, 'switch', 'Realtime/@extensions', 4000, 0);
    }

    $stmt = $db->prepare("DELETE FROM extensions WHERE context=?");
    $stmt->bind_param('s', $ctx);
    $stmt->execute();
    $db->query("DELETE FROM extensions WHERE app='Goto' AND appdata LIKE 'ringgroups,%'");

    $insert = function($context, $exten, $priority, $app, $appdata = '') use ($db) {
        $priority = (string)$priority;
        $stmt = $db->prepare("INSERT INTO extensions (context, exten, priority, app, appdata) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $context, $exten, $priority, $app, $appdata);
        $stmt->execute();
    };

    $groups = $db->query("SELECT * FROM spbx_ring_groups WHERE active=1 ORDER BY CAST(group_number AS UNSIGNED), group_number");
    $internalContexts = spbx_ring_groups_internal_contexts();

    if ($groups) {
        while ($g = $groups->fetch_assoc()) {
            $groupId = (int)$g['id'];
            $groupNo = trim((string)$g['group_number']);
            if (!preg_match('/^[0-9]{2,4}$/', $groupNo)) continue;

            $members = [];
            $stmt = $db->prepare("SELECT extension FROM spbx_ring_group_members WHERE group_id=? AND active=1 ORDER BY sort_order ASC, CAST(extension AS UNSIGNED), extension");
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($m = $res->fetch_assoc()) {
                $ext = trim((string)$m['extension']);
                if (preg_match('/^[0-9]{2,4}$/', $ext)) $members[] = $ext;
            }

            $strategy = (string)$g['strategy'];
            $ringTime = max(5, min(120, (int)$g['ring_time']));
            $insert($ctx, $groupNo, 1, 'NoOp', 'ServusPBX Ringgruppe ' . (string)$g['name'] . ' (' . $groupNo . ')');

            if (!$members) {
                $insert($ctx, $groupNo, 2, 'NoOp', 'Keine aktiven Mitglieder');
                $insert($ctx, $groupNo, 3, 'Hangup', '');
            } elseif ($strategy === 'hunt') {
                $prio = 2;
                foreach ($members as $ext) $insert($ctx, $groupNo, $prio++, 'Dial', 'PJSIP/' . $ext . ',' . $ringTime);
                spbx_ring_groups_timeout_steps($insert, $ctx, $groupNo, $prio, $g);
            } else {
                if ($strategy === 'random') shuffle($members);
                $insert($ctx, $groupNo, 2, 'Dial', spbx_ring_groups_member_string($members) . ',' . $ringTime);
                spbx_ring_groups_timeout_steps($insert, $ctx, $groupNo, 3, $g);
            }

            foreach ($internalContexts as $intCtx) {
                $insert($intCtx, $groupNo, 1, 'Goto', $ctx . ',' . $groupNo . ',1');
            }
        }
    }
}
?>