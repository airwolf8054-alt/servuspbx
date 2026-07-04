<?php
require_once __DIR__ . '/db.php';

function spbx_ast_cli($command)
{
    $command = trim((string)$command);
    if ($command === '') {
        return ['ok' => false, 'output' => '', 'error' => 'Leerer Asterisk-Befehl'];
    }

    $full = 'sudo /usr/sbin/asterisk -rx ' . escapeshellarg($command) . ' 2>&1';
    $output = shell_exec($full);
    $output = is_string($output) ? $output : '';

    return [
        'ok' => $output !== '',
        'output' => $output,
        'error' => $output === '' ? 'Keine Ausgabe von Asterisk erhalten' : '',
    ];
}

function spbx_ast_db_put($family, $key, $value)
{
    $family = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$family);
    $key = preg_replace('/[^A-Za-z0-9_\-\.]/', '', (string)$key);
    $value = trim((string)$value);

    if ($family === '' || $key === '') {
        return ['ok' => false, 'output' => '', 'error' => 'Ungültige AstDB-Parameter'];
    }

    return spbx_ast_cli('database put ' . $family . ' ' . $key . ' ' . $value);
}

function spbx_ast_db_del($family, $key)
{
    $family = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$family);
    $key = preg_replace('/[^A-Za-z0-9_\-\.]/', '', (string)$key);

    if ($family === '' || $key === '') {
        return ['ok' => false, 'output' => '', 'error' => 'Ungültige AstDB-Parameter'];
    }

    return spbx_ast_cli('database del ' . $family . ' ' . $key);
}

function spbx_ast_db_show_family($family)
{
    $family = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$family);
    if ($family === '') {
        return [];
    }

    $res = spbx_ast_cli('database show ' . $family);
    $rows = [];

    foreach (explode("\n", $res['output'] ?? '') as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        if (preg_match('#/' . preg_quote($family, '#') . '/([^ ]+)\s*:\s*(.*)$#', $line, $m)) {
            $rows[$m[1]] = $m[2];
        }
    }

    ksort($rows);
    return $rows;
}
?>