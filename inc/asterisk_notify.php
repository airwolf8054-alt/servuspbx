<?php
function spbx_notify_snom_check_cfg($endpointId, &$debug = null)
{
    $endpointId = trim((string)$endpointId);
    $debugLines = [];

    if ($endpointId === '') {
        $debugLines[] = 'Endpoint-ID leer.';
        $debug = $debugLines;
        error_log('ServusPBX notify failed: endpoint empty');
        return false;
    }

    if (!preg_match('/^[A-Za-z0-9_.:-]+$/', $endpointId)) {
        $debugLines[] = 'Endpoint-ID enthält unsichere Zeichen: ' . $endpointId;
        $debug = $debugLines;
        error_log('ServusPBX notify rejected unsafe endpoint id: ' . $endpointId);
        return false;
    }

    $notify = 'pjsip send notify snom-check-cfg endpoint ' . $endpointId;

    $commands = [
        ['/usr/local/sbin/spbx-snom-check-cfg', [$endpointId]],
        ['sudo', ['-n', '/usr/local/sbin/spbx-snom-check-cfg', $endpointId]],
        ['sudo', ['-n', '/usr/sbin/asterisk', '-rx', $notify]],
        ['sudo', ['-n', '/usr/bin/asterisk', '-rx', $notify]],
        ['/usr/sbin/asterisk', ['-rx', $notify]],
        ['/usr/bin/asterisk', ['-rx', $notify]],
        ['asterisk', ['-rx', $notify]],
    ];

    foreach ($commands as $entry) {
        $bin = $entry[0];
        $args = $entry[1];

        if ($bin !== 'sudo' && $bin !== 'asterisk' && !is_executable($bin)) {
            $debugLines[] = 'Skip nicht ausführbar: ' . $bin;
            continue;
        }

        $cmd = escapeshellcmd($bin);
        foreach ($args as $arg) {
            $cmd .= ' ' . escapeshellarg($arg);
        }

        $output = [];
        $rc = 127;
        @exec($cmd . ' 2>&1', $output, $rc);

        $joined = trim(implode("\n", $output));
        $debugLines[] = 'CMD: ' . $cmd . ' | RC=' . $rc . ($joined !== '' ? ' | OUT=' . $joined : '');

        if ($rc === 0) {
            error_log('ServusPBX notify OK endpoint=' . $endpointId . ' cmd=' . $cmd . ($joined !== '' ? ' out=' . $joined : ''));
            $debug = $debugLines;
            return true;
        }

        error_log('ServusPBX notify failed endpoint=' . $endpointId . ' rc=' . $rc . ' cmd=' . $cmd . ($joined !== '' ? ' out=' . $joined : ''));
    }

    $debug = $debugLines;
    return false;
}

function spbx_notify_debug_text($debug)
{
    if (!is_array($debug)) {
        return '';
    }

    return implode("\n", $debug);
}


function spbx_notify_all_snom_check_cfg(&$debug = null)
{
    $db = spbx_db();
    $debugLines = [];

    $sql = "
        SELECT DISTINCT
            COALESCE(NULLIF(d.endpoint_id,''), NULLIF(e.endpoint_id,''), NULLIF(d.extension,''), NULLIF(e.extension,'')) AS endpoint_id
        FROM spbx_devices d
        LEFT JOIN spbx_extensions e ON e.extension = d.extension
        WHERE COALESCE(d.active,1)=1
          AND COALESCE(NULLIF(d.mac,''),'') <> ''
        ORDER BY endpoint_id
    ";

    $res = $db->query($sql);
    if (!$res) {
        $debugLines[] = 'Geräteabfrage fehlgeschlagen: ' . $db->error;
        $debug = $debugLines;
        return 0;
    }

    $ok = 0;
    $total = 0;

    while ($r = $res->fetch_assoc()) {
        $endpointId = trim((string)($r['endpoint_id'] ?? ''));
        if ($endpointId === '') {
            continue;
        }

        $total++;
        $oneDebug = [];
        if (spbx_notify_snom_check_cfg($endpointId, $oneDebug)) {
            $ok++;
        }

        $debugLines[] = 'Endpoint ' . $endpointId . ': ' . ($oneDebug ? implode(' | ', $oneDebug) : 'kein Debug');
    }

    $debugLines[] = 'snom-check-cfg gesendet: ' . $ok . '/' . $total;
    $debug = $debugLines;

    return $ok;
}


function spbx_notify_snom_reboot($endpointId, &$debug = null)
{
    $endpointId = preg_replace('/[^A-Za-z0-9_.:-]/', '', (string)$endpointId);
    if ($endpointId === '') {
        if (is_array($debug)) $debug[] = 'Leerer Endpoint für snom-reboot.';
        return false;
    }

    $cmd = 'pjsip send notify snom-reboot endpoint ' . $endpointId;

    if (function_exists('spbx_ast_cli')) {
        $res = spbx_ast_cli($cmd);
        if (is_array($debug)) {
            $debug[] = $cmd;
            $debug[] = $res['output'] ?? '';
        }
        return !empty($res['output']);
    }

    $full = 'sudo /usr/sbin/asterisk -rx ' . escapeshellarg($cmd) . ' 2>&1';
    $out = shell_exec($full);
    if (is_array($debug)) {
        $debug[] = $full;
        $debug[] = (string)$out;
    }

    return is_string($out) && $out !== '';
}

?>