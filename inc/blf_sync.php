<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/asterisk.php';

function spbx_blf_label_from_callerid($callerid, $fallback = '')
{
    $callerid = trim((string)$callerid);

    if ($callerid !== '' && preg_match('/"([^"]+)"/', $callerid, $m)) {
        return trim($m[1]);
    }

    if ($callerid !== '') {
        $clean = trim(preg_replace('/\s*<[^>]+>\s*/', '', str_replace('"', '', $callerid)));
        if ($clean !== '') {
            return $clean;
        }
    }

    return trim((string)$fallback);
}

function spbx_sync_blf_labels_for_extension($extension, $callerid, $fallbackLabel = '')
{
    $db = spbx_db();

    $extension = trim((string)$extension);
    if ($extension === '') {
        return ['updated_keys' => 0, 'notified_devices' => 0];
    }

    $label = spbx_blf_label_from_callerid($callerid, $fallbackLabel);
    if ($label === '') {
        $label = $extension;
    }

    // Bewährte Logik aus der alten Anlage:
    // Nach BLF-/CallerID-Änderungen werden alle aktiven provisionierten Telefone
    // per snom-check-cfg neu angestoßen. Das ist robuster als nur "betroffene"
    // Geräte zu suchen.
    $affected = [];
    $resDevices = $db->query("
        SELECT DISTINCT endpoint_id
        FROM spbx_devices
        WHERE active=1
          AND provisioning_enabled=1
          AND endpoint_id IS NOT NULL
          AND endpoint_id<>''
        ORDER BY endpoint_id
    ");
    if ($resDevices) {
        while ($r = $resDevices->fetch_assoc()) {
            $affected[] = (string)$r['endpoint_id'];
        }
    }

    $updated = 0;
    $stmt = $db->prepare("
        UPDATE spbx_device_keys
        SET key_label=?
        WHERE key_type='blf'
          AND key_value=?
    ");
    if ($stmt) {
        $stmt->bind_param('ss', $label, $extension);
        $stmt->execute();
        $updated = (int)$stmt->affected_rows;
    }

    $notified = 0;
    foreach (array_unique($affected) as $endpointId) {
        $endpointId = preg_replace('/[^A-Za-z0-9_.:-]/', '', $endpointId);
        if ($endpointId === '') {
            continue;
        }

        $res = spbx_ast_cli('pjsip send notify snom-check-cfg endpoint ' . $endpointId);
        if (!empty($res['output'])) {
            $notified++;
        }
    }

    return [
        'updated_keys' => $updated,
        'notified_devices' => $notified,
        'label' => $label,
    ];
}
?>