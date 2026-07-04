<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/asterisk.php';

function spbx_ast_config_writer_install_schema()
{
    $db = spbx_db();

    $db->query("CREATE TABLE IF NOT EXISTS ast_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cat_metric INT NOT NULL DEFAULT 0,
        var_metric INT NOT NULL DEFAULT 0,
        commented TINYINT(1) NOT NULL DEFAULT 0,
        filename VARCHAR(128) NOT NULL,
        category VARCHAR(128) NOT NULL,
        var_name VARCHAR(128) NOT NULL,
        var_val VARCHAR(255) NOT NULL,
        KEY idx_file_cat (filename, category),
        KEY idx_file_cat_var (filename, category, var_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}

function spbx_ast_config_writer_clear_dynamic_contexts()
{
    $db = spbx_db();
    spbx_ast_config_writer_install_schema();

    $db->query("
        DELETE FROM ast_config
        WHERE filename='extensions.conf'
          AND (
              category LIKE 'internal\\_%'
              OR category LIKE 'outgoing\\_%'
          )
    ");
}

function spbx_ast_config_writer_add($category, $varName, $varVal, $catMetric, $varMetric)
{
    $db = spbx_db();
    spbx_ast_config_writer_install_schema();

    $stmt = $db->prepare("
        INSERT INTO ast_config
        (cat_metric, var_metric, commented, filename, category, var_name, var_val)
        VALUES (?, ?, 0, 'extensions.conf', ?, ?, ?)
    ");

    if ($stmt) {
        $catMetric = (int)$catMetric;
        $varMetric = (int)$varMetric;
        $category = (string)$category;
        $varName = (string)$varName;
        $varVal = (string)$varVal;
        $stmt->bind_param('iisss', $catMetric, $varMetric, $category, $varName, $varVal);
        $stmt->execute();
    }
}

function spbx_ast_config_writer_internal_context($context, $catMetric = 2000)
{
    // Entspricht der alten funktionierenden Schreibweise:
    // [internal_x]
    // exten => _XX,hint,PJSIP/${EXTEN}
    // exten => _XXX,hint,PJSIP/${EXTEN}
    // exten => _XXXX,hint,PJSIP/${EXTEN}
    // switch => Realtime/@extensions

    spbx_ast_config_writer_add($context, 'exten', '_XX,hint,PJSIP/${EXTEN}', $catMetric, 0);
    spbx_ast_config_writer_add($context, 'exten', '_XXX,hint,PJSIP/${EXTEN}', $catMetric, 1);
    spbx_ast_config_writer_add($context, 'exten', '_XXXX,hint,PJSIP/${EXTEN}', $catMetric, 2);
    spbx_ast_config_writer_add($context, 'switch', 'Realtime/@extensions', $catMetric, 10);
}

function spbx_ast_config_writer_outgoing_context($context, $catMetric = 3000)
{
    spbx_ast_config_writer_add($context, 'switch', 'Realtime/@extensions', $catMetric, 0);
}

function spbx_ast_config_writer_rebuild_from_outbound_routes()
{
    $db = spbx_db();
    spbx_ast_config_writer_install_schema();
    spbx_ast_config_writer_clear_dynamic_contexts();

    $res = $db->query("
        SELECT id, outgoing_context
        FROM spbx_outbound_routes
        WHERE active=1
        ORDER BY id ASC
    ");

    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $id = (int)$r['id'];
            $outCtx = trim((string)$r['outgoing_context']);
            if ($outCtx === '') {
                continue;
            }

            $intCtx = str_replace('outgoing_', 'internal_', $outCtx);

            spbx_ast_config_writer_internal_context($intCtx, 2000 + $id);
            spbx_ast_config_writer_outgoing_context($outCtx, 3000 + $id);
        }
    }
}

function spbx_ast_config_writer_rebuild_and_reload()
{
    spbx_ast_config_writer_rebuild_from_outbound_routes();
    spbx_ast_cli('module reload pbx_config.so');
    spbx_ast_cli('dialplan reload');
}
?>