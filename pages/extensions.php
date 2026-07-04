<?php
require_once __DIR__ . '/../inc/blf_sync.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/asterisk_notify.php';
require_once __DIR__ . '/../inc/outbound_routes.php';

spbx_require_admin();

$db = spbx_db();

function spbx_ext_post($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function spbx_ext_digits($value)
{
    return preg_replace('/[^0-9]/', '', (string)$value);
}

function spbx_ext_hex($value)
{
    return strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', (string)$value));
}

function spbx_ext_random_password($length = 16)
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $out;
}


function spbx_ext_limit_chars($value, $max)
{
    $value = trim((string)$value);

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }

    return substr($value, 0, $max);
}

function spbx_ext_callerid_from_display_name($displayName)
{
    $displayName = trim(preg_replace('/\s+/', ' ', (string)$displayName));

    if ($displayName === '') {
        return '';
    }

    $parts = explode(' ', $displayName, 2);
    $first = spbx_ext_limit_chars($parts[0] ?? '', 12);
    $last = spbx_ext_limit_chars($parts[1] ?? '', 12);

    return trim($first . ($last !== '' ? ' ' . $last : ''));
}


function spbx_ext_model_from_form($category, $model)
{
    $category = trim((string)$category);
    $model = trim((string)$model);

    if ($category === 'deskphone') {
        if (in_array($model, ['snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x'], true)) {
            return $model;
        }
        return 'snomD815';
    }

    if ($category === 'dect') {
        return 'snom_dect_handset';
    }

    if ($category === 'siponly') {
        return 'sip_user';
    }

    return 'snomD815';
}

function spbx_ext_category_from_model($model)
{
    if ($model === 'sip_user') return 'siponly';
    if ($model === 'snom_dect_handset') return 'dect';
    return 'deskphone';
}

function spbx_ext_model_label($model)
{
    return [
        'snomD815' => 'snom D815',
        'snomD810' => 'snom D810',
        'snomD812' => 'snom D812',
        'snomD862' => 'snom D862',
        'snomD865' => 'snom D865',
        'snomD892' => 'snom D892M',
        'snomD895' => 'snom D895M',
        'GigasetP810' => 'Gigaset P810',
        'GigasetP82x' => 'Gigaset P82x',
        'GigasetP85x' => 'Gigaset P85x',
        'snom_dect_handset' => 'SNOM DECT Handset',
        'sip_user' => 'SIP User',
        'unknown' => 'Unbekannt',
    ][$model] ?? 'Unbekannt';
}

function spbx_ext_device_icon($model)
{
    return [
        'snomD815' => '☎️',
        'snomD810' => '☎️',
        'snomD812' => '☎️',
        'snomD862' => '☎️',
        'snomD865' => '☎️',
        'snomD892' => '☎️',
        'snomD895' => '☎️',
        'GigasetP810' => '☎️',
        'GigasetP82x' => '☎️',
        'GigasetP85x' => '☎️',
        'snom_dect_handset' => '📡',
        'sip_user' => '🔌',
        'unknown' => '❔',
    ][$model] ?? '❔';
}


function spbx_ext_device_svg($model)
{
    $model = (string)$model;

    if ($model === 'snom_dect_handset') {
        return '<span class="spbx-device-svg spbx-device-svg-dect"><svg viewBox="0 0 24 24"><rect x="7" y="3" width="7" height="18" rx="2"></rect><path d="M10 7h1"></path><path d="M10 11h1"></path><path d="M10 15h1"></path><path d="M17 6c1.8 1.1 3 3 3 5"></path><path d="M17 10c.6.4 1 1 1 1.8"></path></svg></span>';
    }

    return '<span class="spbx-device-svg"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="6" height="16" rx="1.5"></rect><rect x="11" y="5" width="10" height="14" rx="1.5"></rect><path d="M14 8h4"></path><path d="M14 12h.01"></path><path d="M18 12h.01"></path><path d="M14 16h.01"></path><path d="M18 16h.01"></path></svg></span>';
}

function spbx_ext_model_label_ui($model)
{
    $label = spbx_ext_model_label($model);

    if (stripos($label, 'snom') === 0) {
        return 'SNOM' . substr($label, 4);
    }

    return $label;
}

function spbx_ext_action_icon($name)
{
    $icons = [
        'keys' => '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M7 9h.01"></path><path d="M11 9h.01"></path><path d="M15 9h.01"></path><path d="M7 13h10"></path></svg>',
        'notify' => '<svg viewBox="0 0 24 24"><path d="M10 21h4"></path><path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path></svg>',
        'reboot' => '<svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-2.64-6.36"></path><path d="M21 3v6h-6"></path></svg>',
        'edit' => '<svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>',
        'delete' => '<svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>',
    ];

    return '<span class="spbx-action-svg">' . ($icons[$name] ?? '') . '</span>';
}



function spbx_ext_identifier_ui($row, $model)
{
    $model = (string)$model;
    $extension = (string)($row['extension'] ?? '');
    $mac = trim((string)($row['mac'] ?? ''));
    $ipei = trim((string)($row['ipei'] ?? ''));
    $serial = trim((string)($row['serial_number'] ?? ''));

    if ($model === 'snom_dect_handset') {
        // Niemals die Durchwahl als IPEI anzeigen.
        if ($ipei !== '' && $ipei !== $extension) {
            return $ipei;
        }
        if ($serial !== '' && $serial !== $extension) {
            return $serial;
        }
        return '';
    }

    return $mac;
}


function spbx_ext_device_type($model)
{
    return $model === 'sip_user' ? 'sip_user' : 'phone';
}

function spbx_ext_provision_file($model)
{
    if ($model === 'snomD815') return 'snomD815.php';
    if ($model === 'snomD810') return 'snomD810.php';
    if ($model === 'snomD812') return 'snomD812.php';
    if ($model === 'snomD862') return 'snomD862.php';
    if ($model === 'snomD865') return 'snomD865.php';
    if ($model === 'snomD892') return 'snomD892.php';
    if ($model === 'snomD895') return 'snomD895.php';
    if ($model === 'GigasetP810') return 'GigasetP810.php';
    if ($model === 'GigasetP82x') return 'GigasetP82x.php';
    if ($model === 'GigasetP85x') return 'GigasetP85x.php';
    return null;
}

function spbx_ext_provision_url($model, $mac)
{
    $model = trim((string)$model);
    $mac = spbx_ext_hex($mac);

    $file = spbx_ext_provision_file($model);
    if ($file === null || $mac === '') {
        return '';
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'pbx.local');

    return $scheme . '://' . $host . '/provision/' . $file . '?mac=' . rawurlencode($mac);
}






function spbx_ext_mac_exists_elsewhere($mac, $endpointId)
{
    $db = spbx_db();
    if ($mac === null) {
        return false;
    }

    $mac = spbx_ext_hex($mac);
    $endpointId = trim((string)$endpointId);

    if ($mac === '') {
        return false;
    }

    $stmt = $db->prepare("
        SELECT id, sipuser
        FROM deskphones
        WHERE mac=?
          AND sipuser<>?
        LIMIT 1
    ");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $mac, $endpointId);
    $stmt->execute();

    return (bool)$stmt->get_result()->fetch_assoc();
}

function spbx_ext_upsert_device($endpointId, $model, $extension, $displayName, $mac, $ipei, $serial, $provisionFile, $active)
{
    $db = spbx_db();

    $deskphoneModels = ['snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x'];
    if (in_array($model, $deskphoneModels, true)) {
        $stmt = $db->prepare("SELECT id FROM deskphones WHERE sipuser=? LIMIT 1");
        $stmt->bind_param('s', $endpointId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        if ($existing) {
            $deviceId = (int)$existing['id'];
            $stmt = $db->prepare("UPDATE deskphones SET mac=?, phone_type=?, sipuser=?, eth_pc='off', language='Deutsch' WHERE id=? LIMIT 1");
            $stmt->bind_param('sssi', $mac, $model, $endpointId, $deviceId);
            $stmt->execute();
            return $deviceId;
        }

        $stmt = $db->prepare("INSERT INTO deskphones (mac, phone_type, sipuser, eth_pc, language) VALUES (?, ?, ?, 'off', 'Deutsch')");
        $stmt->bind_param('sss', $mac, $model, $endpointId);
        $stmt->execute();
        return (int)$stmt->insert_id;
    }

    $stmt = $db->prepare("SELECT id FROM spbx_devices WHERE endpoint_id=? LIMIT 1");
    $stmt->bind_param('s', $endpointId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        $deviceId = (int)$existing['id'];
        $stmt = $db->prepare("
            UPDATE spbx_devices
            SET device_model=?, extension=?, display_name=?, mac=?, ipei=?, serial_number=?, provisioning_enabled=1, provision_file=?, active=?
            WHERE id=? LIMIT 1
        ");
        $stmt->bind_param('sssssssii', $model, $extension, $displayName, $mac, $ipei, $serial, $provisionFile, $active, $deviceId);
        $stmt->execute();
        return $deviceId;
    }

    $stmt = $db->prepare("
        INSERT INTO spbx_devices
        (endpoint_id, device_model, extension, display_name, mac, ipei, serial_number, provisioning_enabled, provision_file, active)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
    ");
    $stmt->bind_param('ssssssssi', $endpointId, $model, $extension, $displayName, $mac, $ipei, $serial, $provisionFile, $active);
    $stmt->execute();
    return (int)$stmt->insert_id;
}


function spbx_ext_dect_bases()
{
    $db = spbx_db();
    $rows = [];

    $res = $db->query("
        SELECT id, base_name, base_type, mac_address
        FROM spbx_dect_bases
        WHERE active=1
        ORDER BY base_name
    ");

    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
    }

    return $rows;
}

function spbx_ext_dect_base_limit($baseType)
{
    return $baseType === 'snomM900' ? 1000 : 10;
}

function spbx_ext_dect_next_idx($baseId, $baseType)
{
    $db = spbx_db();
    $limit = spbx_ext_dect_base_limit($baseType);

    $stmt = $db->prepare("SELECT idx_number FROM spbx_dect_base_handsets WHERE base_id=? ORDER BY idx_number ASC");
    if (!$stmt) return null;

    $stmt->bind_param('i', $baseId);
    $stmt->execute();

    $used = [];
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $used[(int)$r['idx_number']] = true;
    }

    for ($i = 1; $i <= $limit; $i++) {
        if (empty($used[$i])) return $i;
    }

    return null;
}

function spbx_ext_dect_current_base($endpointId)
{
    $db = spbx_db();

    $stmt = $db->prepare("
        SELECT h.base_id, h.idx_number, b.base_name, b.base_type
        FROM spbx_dect_base_handsets h
        JOIN spbx_dect_bases b ON b.id=h.base_id
        WHERE h.endpoint_id=?
        LIMIT 1
    ");
    if (!$stmt) return null;

    $stmt->bind_param('s', $endpointId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function spbx_ext_dect_assign($baseId, $endpointId, $extension, $displayName)
{
    $db = spbx_db();

    $baseId = (int)$baseId;
    $endpointId = trim((string)$endpointId);

    if ($endpointId === '') return;

    $stmt = $db->prepare("DELETE FROM spbx_dect_base_handsets WHERE endpoint_id=?");
    $stmt->bind_param('s', $endpointId);
    $stmt->execute();

    if ($baseId <= 0) return;

    $stmt = $db->prepare("SELECT id, base_name, base_type FROM spbx_dect_bases WHERE id=? AND active=1 LIMIT 1");
    $stmt->bind_param('i', $baseId);
    $stmt->execute();
    $base = $stmt->get_result()->fetch_assoc();

    if (!$base) {
        throw new RuntimeException('Gewählte DECT-Basis wurde nicht gefunden oder ist inaktiv.');
    }

    $idx = spbx_ext_dect_next_idx($baseId, $base['base_type']);
    if ($idx === null) {
        throw new RuntimeException('Keine freie IDX auf der gewählten DECT-Basis verfügbar.');
    }

    $stmt = $db->prepare("
        INSERT INTO spbx_dect_base_handsets
        (base_id, idx_number, endpoint_id, extension, display_name)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iisss', $baseId, $idx, $endpointId, $extension, $displayName);
    $stmt->execute();
}



function spbx_ext_available_extensions_for_context($context, $currentExtension = '')
{
    $db = spbx_db();
    $context = trim((string)$context);
    $currentExtension = trim((string)$currentExtension);

    $from = null;
    $to = null;

    // Context direkt auswerten:
    // internal_43312423826 -> 43312423826
    $digitsFromContext = '';
    if (preg_match('/^internal_([0-9]+)$/', $context, $m)) {
        $digitsFromContext = $m[1];
    }

    // 1) Exakter Match gegen main_number ohne Sonderzeichen.
    if ($digitsFromContext !== '') {
        $sql = "
            SELECT ext_from, ext_to
            FROM spbx_trunks
            WHERE REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(main_number,''), '+', ''), ' ', ''), '-', ''), '.', '') = ?
            ORDER BY id ASC
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('s', $digitsFromContext);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $from = $row['ext_from'];
                $to = $row['ext_to'];
            }
        }
    }

    // 2) Match über Outbound-Route: internal_x -> outgoing_x -> trunk_endpoint -> spbx_trunks.
    if (($from === null || $to === null) && $digitsFromContext !== '') {
        $outCtx = 'outgoing_' . $digitsFromContext;
        $sql = "
            SELECT t.ext_from, t.ext_to
            FROM spbx_outbound_routes o
            JOIN spbx_trunks t ON t.endpoint_id = o.trunk_endpoint
            WHERE o.outgoing_context = ?
            ORDER BY t.id ASC
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('s', $outCtx);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $from = $row['ext_from'];
                $to = $row['ext_to'];
            }
        }
    }

    // 3) Wenn der aktuelle Context noch "internal" ist:
    // erste aktive Trunk-Range nehmen.
    if ($from === null || $to === null) {
        $res = $db->query("
            SELECT ext_from, ext_to
            FROM spbx_trunks
            WHERE active=1
              AND ext_from IS NOT NULL
              AND ext_to IS NOT NULL
            ORDER BY id ASC
            LIMIT 1
        ");
        if ($res && ($row = $res->fetch_assoc())) {
            $from = $row['ext_from'];
            $to = $row['ext_to'];
        }
    }

    $from = (int)$from;
    $to = (int)$to;

    // Harte letzte Sicherung: wenn noch immer nichts gefunden wurde,
    // Standardbereich 10-99 verwenden, damit das Dropdown nie nur 1 Wert zeigt.
    if ($from <= 0 || $to < $from) {
        $from = 10;
        $to = 99;
    }

    // Schutz vor irrsinnig großen Dropdowns.
    if (($to - $from) > 5000) {
        $to = $from + 5000;
    }

    // Belegte Nebenstellen inklusive Anzeigename laden.
    $used = [];
    $res = $db->query("
        SELECT
            e.extension,
            COALESCE(NULLIF(p.display_name,''), NULLIF(e.display_name,''), e.extension) AS label
        FROM spbx_extensions e
        LEFT JOIN ps_endpoints p ON p.id = e.extension
        ORDER BY CAST(e.extension AS UNSIGNED), e.extension
    ");

    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $used[(string)$r['extension']] = (string)$r['label'];
        }
    }

    $options = [];
    for ($i = $from; $i <= $to; $i++) {
        $e = (string)$i;
        $isCurrent = ($e === $currentExtension);
        $isOccupied = isset($used[$e]) && !$isCurrent;

        if ($isCurrent) {
            $label = $e . ' - aktuelle Nebenstelle';
        } elseif ($isOccupied) {
            $label = $e . ' - belegt: ' . $used[$e];
        } else {
            $label = $e;
        }

        $options[] = [
            'extension' => $e,
            'label' => $label,
            'disabled' => $isOccupied,
            'current' => $isCurrent,
            'occupied' => $isOccupied,
        ];
    }

    return $options;
}


function spbx_ext_context_options()
{
    $db = spbx_db();
    $rows = [];

    if (function_exists('spbx_outbound_install_schema')) {
        spbx_outbound_install_schema();
        spbx_outbound_sync_from_trunks();
    }

    $res = $db->query("
        SELECT route_name, main_number, outgoing_context, active
        FROM spbx_outbound_routes
        WHERE active=1
        ORDER BY id ASC
    ");

    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $ctx = str_replace('outgoing_', 'internal_', (string)$r['outgoing_context']);
            $rows[] = [
                'context' => $ctx,
                'label' => trim((string)$r['route_name'] . ' / ' . (string)$r['main_number'] . ' / ' . $ctx),
            ];
        }
    }

    if (!$rows) {
        $rows[] = ['context' => 'internal', 'label' => 'internal'];
    }

    return $rows;
}

function spbx_ext_valid_context($context)
{
    $context = trim((string)$context);
    foreach (spbx_ext_context_options() as $row) {
        if ($row['context'] === $context) {
            return $context;
        }
    }

    return spbx_ext_context_options()[0]['context'] ?? 'internal';
}


function spbx_ext_load($id)
{
    $db = spbx_db();

    $stmt = $db->prepare("
        SELECT
            e.id,
            p.extension,
            p.display_name,
            p.context AS endpoint_context,
            e.email,
            p.id AS endpoint_id,
            e.voicemail_enabled,
            e.voicemail_pin,
            COALESCE(e.active, p.active) AS active,
            COALESCE(d.id, sd.id) AS device_id,
            p.device_model AS device_model,
            COALESCE(d.mac, sd.mac) AS mac,
            sd.ipei AS ipei,
            sd.serial_number,
            COALESCE(sd.provisioning_enabled, 1) AS provisioning_enabled,
            a.password AS sip_password
        FROM ps_endpoints p
        JOIN spbx_extensions e ON e.endpoint_id=p.id
        LEFT JOIN deskphones d ON d.sipuser=p.id OR d.sipuser=p.extension
        LEFT JOIN spbx_devices sd ON sd.endpoint_id=p.id
        LEFT JOIN ps_auths a ON a.id=p.id
        WHERE e.id=?
        LIMIT 1
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

function spbx_ext_exists($extension, $ignoreId = 0)
{
    $db = spbx_db();

    $stmt = $db->prepare("SELECT id FROM spbx_extensions WHERE extension=? AND id<>? LIMIT 1");
    $stmt->bind_param('si', $extension, $ignoreId);
    $stmt->execute();

    return (bool)$stmt->get_result()->fetch_assoc();
}

function spbx_ext_delete($id)
{
    $db = spbx_db();

    $old = spbx_ext_load($id);
    if (!$old) return;

    $extension = $old['extension'];
    $endpointId = $old['endpoint_id'] ?: $extension;

    $db->begin_transaction();

    try {
        $stmt = $db->prepare("DELETE FROM spbx_dect_base_handsets WHERE endpoint_id=?");
        $stmt->bind_param('s', $endpointId);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM deskphones WHERE sipuser=? OR sipuser=?");
        $stmt->bind_param('ss', $endpointId, $extension);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM spbx_devices WHERE endpoint_id=? OR extension=?");
        $stmt->bind_param('ss', $endpointId, $extension);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM voicemail WHERE mailbox=? AND context='internal'");
        $stmt->bind_param('s', $extension);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM ps_contacts WHERE endpoint=?");
        $stmt->bind_param('s', $endpointId);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM ps_aors WHERE id=?");
        $stmt->bind_param('s', $endpointId);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM ps_auths WHERE id=?");
        $stmt->bind_param('s', $endpointId);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM ps_endpoints WHERE id=?");
        $stmt->bind_param('s', $endpointId);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM spbx_extensions WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $db->commit();

        spbx_audit_log('extension_delete', 'Nebenstelle ' . $extension . ' gelöscht.', 'warning', null, null, 'extensions');
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

function spbx_ext_save($id)
{
    $db = spbx_db();

    $category = spbx_ext_post('device_category', 'deskphone');
    $model = spbx_ext_model_from_form($category, spbx_ext_post('device_model'));

    $extension = spbx_ext_digits(spbx_ext_post('extension'));
    $displayName = spbx_ext_post('display_name');
    $email = spbx_ext_post('email');
    $mac = spbx_ext_hex(spbx_ext_post('mac'));
    $ipei = spbx_ext_hex(spbx_ext_post('ipei'));
    $serial = spbx_ext_post('serial_number');
    $sipPassword = spbx_ext_post('sip_password');
    $voicemail = isset($_POST['voicemail_enabled']) ? 1 : 0;
    $voicemailPin = '';
    $active = isset($_POST['active']) ? 1 : 0;
    $dectBaseId = (int)spbx_ext_post('dect_base_id', '0');
    $selectedContext = spbx_ext_valid_context(spbx_ext_post('endpoint_context', ''));

    if ($extension === '' || strlen($extension) < 2 || strlen($extension) > 4) {
        return 'Bitte eine gültige 2- bis 4-stellige Nebenstelle eingeben.';
    }

    if ($displayName === '') {
        return 'Bitte einen Anzeigenamen eingeben.';
    }

    if (spbx_ext_exists($extension, $id)) {
        return 'Diese Nebenstelle existiert bereits.';
    }

    if (in_array($model, ['snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x'], true) && $mac === '') {
        return 'Für Tischtelefone ist eine MAC-Adresse erforderlich.';
    }

    if ($model === 'snom_dect_handset' && $ipei === '') {
        return 'Für SNOM DECT Handset ist die IPEI erforderlich.';
    }

    if ($sipPassword === '') {
        $sipPassword = spbx_ext_random_password();
    }

    $old = $id > 0 ? spbx_ext_load($id) : null;
    $oldEndpointId = $old['endpoint_id'] ?? null;
    $endpointId = $extension;

    // Nur DECT-Handsets haben eine IPEI als Gerätekennung.
    // Die PJSIP Endpoint-ID ist auch bei DECT die Durchwahl.
    if ($model !== 'snom_dect_handset') {
        $ipei = null;
    }

    // Nur Tischtelefone haben eine MAC-Adresse.
    // DECT-Handsets und SIP-User müssen NULL verwenden, sonst kollidiert UNIQUE uniq_mac auf leerem String.
    if (!in_array($model, ['snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x'], true)) {
        $mac = null;
    }

    $deviceType = spbx_ext_device_type($model);
    $provisionFile = spbx_ext_provision_file($model);
    $mailboxes = $voicemail ? $extension . '@' . $context : null;
    $callerid = spbx_ext_callerid_from_display_name($displayName);
    $endpointContext = $selectedContext;

    $db->begin_transaction();

    try {
        if ($old && $oldEndpointId && $oldEndpointId !== $endpointId) {
            $stmt = $db->prepare("DELETE FROM ps_contacts WHERE endpoint=?");
            $stmt->bind_param('s', $oldEndpointId);
            $stmt->execute();

            $stmt = $db->prepare("DELETE FROM ps_aors WHERE id=?");
            $stmt->bind_param('s', $oldEndpointId);
            $stmt->execute();

            $stmt = $db->prepare("DELETE FROM ps_auths WHERE id=?");
            $stmt->bind_param('s', $oldEndpointId);
            $stmt->execute();

            $stmt = $db->prepare("DELETE FROM ps_endpoints WHERE id=?");
            $stmt->bind_param('s', $oldEndpointId);
            $stmt->execute();

            $stmt = $db->prepare("DELETE FROM spbx_dect_base_handsets WHERE endpoint_id=?");
            $stmt->bind_param('s', $oldEndpointId);
            $stmt->execute();
        }

        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE spbx_extensions
                SET extension=?, display_name=?, email=?, endpoint_id=?, voicemail_enabled=?, voicemail_pin=?, active=?
                WHERE id=?
            ");
            $stmt->bind_param('ssssisii', $extension, $displayName, $email, $endpointId, $voicemail, $voicemailPin, $active, $id);
            $stmt->execute();
        } else {
            $stmt = $db->prepare("
                INSERT INTO spbx_extensions
                (extension, display_name, email, endpoint_id, voicemail_enabled, voicemail_pin, active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ssssisi', $extension, $displayName, $email, $endpointId, $voicemail, $voicemailPin, $active);
            $stmt->execute();
            $id = (int)$stmt->insert_id;
        }
        $deviceId = spbx_ext_upsert_device($endpointId, $model, $extension, $displayName, $mac, $ipei, $serial, $provisionFile, $active);

        $stmt = $db->prepare("
            INSERT INTO ps_endpoints
            (id, extension, display_name, device_type, device_model, transport, aors, auth, context, disallow, allow,
             direct_media, force_rport, rewrite_contact, rtp_symmetric, dtmf_mode, callerid, mailboxes, language, active)
            VALUES (?, ?, ?, ?, ?, 'transport-udp', ?, ?, ?, 'all', 'alaw,ulaw,g722',
                    'no', 'yes', 'yes', 'yes', 'rfc4733', ?, ?, 'de', ?)
            ON DUPLICATE KEY UPDATE
                extension=VALUES(extension),
                display_name=VALUES(display_name),
                device_type=VALUES(device_type),
                device_model=VALUES(device_model),
                transport=VALUES(transport),
                aors=VALUES(aors),
                auth=VALUES(auth),
                context=VALUES(context),
                disallow=VALUES(disallow),
                allow=VALUES(allow),
                direct_media=VALUES(direct_media),
                force_rport=VALUES(force_rport),
                rewrite_contact=VALUES(rewrite_contact),
                rtp_symmetric=VALUES(rtp_symmetric),
                dtmf_mode=VALUES(dtmf_mode),
                callerid=VALUES(callerid),
                mailboxes=VALUES(mailboxes),
                language=VALUES(language),
                active=VALUES(active)
        ");
        $stmt->bind_param('ssssssssssi', $endpointId, $extension, $displayName, $deviceType, $model, $endpointId, $endpointId, $endpointContext, $callerid, $mailboxes, $active);
        $stmt->execute();

        $stmt = $db->prepare("
            INSERT INTO ps_auths
            (id, auth_type, username, password)
            VALUES (?, 'userpass', ?, ?)
            ON DUPLICATE KEY UPDATE
                auth_type=VALUES(auth_type),
                username=VALUES(username),
                password=VALUES(password)
        ");
        $stmt->bind_param('sss', $endpointId, $extension, $sipPassword);
        $stmt->execute();

        $stmt = $db->prepare("
            INSERT INTO ps_aors
            (id, max_contacts, remove_existing, qualify_frequency, authenticate_qualify, maximum_expiration, minimum_expiration, default_expiration)
            VALUES (?, 1, 'yes', 60, 'no', 7200, 60, 3600)
            ON DUPLICATE KEY UPDATE
                max_contacts=VALUES(max_contacts),
                remove_existing=VALUES(remove_existing),
                qualify_frequency=VALUES(qualify_frequency),
                authenticate_qualify=VALUES(authenticate_qualify),
                maximum_expiration=VALUES(maximum_expiration),
                minimum_expiration=VALUES(minimum_expiration),
                default_expiration=VALUES(default_expiration)
        ");
        $stmt->bind_param('s', $endpointId);
        $stmt->execute();

        if ($voicemail) {
            $stmt = $db->prepare("
                INSERT INTO voicemail
                (context, mailbox, password, fullname, email, attach, attachfmt, language, tz, deletevoicemail, active)
                VALUES ('internal', ?, ?, ?, ?, 'yes', 'wav', 'de', 'european', 'no', 1)
                ON DUPLICATE KEY UPDATE
                    password=VALUES(password),
                    fullname=VALUES(fullname),
                    email=VALUES(email),
                    active=1
            ");
            $stmt->bind_param('ssss', $extension, $voicemailPin, $displayName, $email);
            $stmt->execute();
        } else {
            $stmt = $db->prepare("DELETE FROM voicemail WHERE mailbox=? AND context='internal'");
            $stmt->bind_param('s', $extension);
            $stmt->execute();

            if (function_exists('spbx_sync_blf_labels_for_extension')) {
                $blfSync = spbx_sync_blf_labels_for_extension($extension, $callerid, $displayName ?? $extension);
                if (!empty($blfSync['updated_keys'])) {
                    $ok .= ' BLF-Beschriftungen aktualisiert: ' . (int)$blfSync['updated_keys'] . '.';
                }
                if (!empty($blfSync['notified_devices'])) {
                    $ok .= ' Telefone neu provisioniert: ' . (int)$blfSync['notified_devices'] . '.';
                }
            }

        }

        if ($model === 'snom_dect_handset') {
            spbx_ext_dect_assign($dectBaseId, $endpointId, $extension, $displayName);
        } else {
            spbx_ext_dect_assign(0, $endpointId, $extension, $displayName);
        }

        $db->commit();

        if (function_exists('spbx_sync_blf_labels_for_extension')) {
            spbx_sync_blf_labels_for_extension($extension, $callerid, $displayName);
        }

        spbx_notify_snom_check_cfg($endpointId);

        spbx_audit_log($old ? 'extension_update' : 'extension_create', 'Nebenstelle ' . $extension . ' gespeichert mit Gerät ' . $model . '.', 'info', null, null, 'extensions');

        return '';
    } catch (Throwable $e) {
        $db->rollback();
        return 'Speichern fehlgeschlagen: ' . $e->getMessage();
    }
}

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$message = '';
$edit = null;

$context = 'internal';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saveId = (int)($_POST['id'] ?? 0);
    $error = spbx_ext_save($saveId);

    if ($error === '') {
        header('Location: extensions.php?saved=1');
        exit;
    }

    $action = $saveId > 0 ? 'edit' : 'new';
    $edit = [
        'id' => $saveId,
        'extension' => spbx_ext_digits($_POST['extension'] ?? ''),
        'display_name' => trim((string)($_POST['display_name'] ?? '')),
        'endpoint_context' => spbx_ext_valid_context($_POST['endpoint_context'] ?? ''),
        'email' => trim((string)($_POST['email'] ?? '')),
        'device_model' => spbx_ext_model_from_form($_POST['device_category'] ?? '', $_POST['device_model'] ?? ''),
        'device_category' => trim((string)($_POST['device_category'] ?? 'deskphone')),
        'mac' => spbx_ext_hex($_POST['mac'] ?? ''),
        'ipei' => spbx_ext_hex($_POST['ipei'] ?? ''),
        'serial_number' => trim((string)($_POST['serial_number'] ?? '')),
        'sip_password' => trim((string)($_POST['sip_password'] ?? '')),
        'voicemail_enabled' => isset($_POST['voicemail_enabled']) ? 1 : 0,
        'voicemail_pin' => spbx_ext_digits($_POST['voicemail_pin'] ?? '0000') ?: '0000',
        'active' => isset($_POST['active']) ? 1 : 0,
    ];
}


if ($action === 'notify_test' && $id > 0) {
    $target = spbx_ext_load($id);
    if ($target) {
        $notifyDebug = [];
        $ok = spbx_notify_snom_check_cfg($target['endpoint_id'], $notifyDebug);
        $_SESSION['spbx_notify_debug'] = spbx_notify_debug_text($notifyDebug);
        header('Location: extensions.php?' . ($ok ? 'notify_ok=1' : 'notify_failed=1'));
        exit;
    }

    header('Location: extensions.php?notify_failed=1');
    exit;
}

if ($action === 'reboot_phone' && $id > 0) {
    $target = spbx_ext_load($id);
    if ($target) {
        $notifyDebug = [];
        $ok = spbx_notify_snom_reboot($target['endpoint_id'], $notifyDebug);
        $_SESSION['spbx_notify_debug'] = spbx_notify_debug_text($notifyDebug);
        header('Location: extensions.php?' . ($ok ? 'reboot_ok=1' : 'reboot_failed=1'));
        exit;
    }

    header('Location: extensions.php?reboot_failed=1');
    exit;
}

if ($action === 'delete' && $id > 0) {
    spbx_ext_delete($id);
    header('Location: extensions.php?deleted=1');
    exit;
}

if ($action === 'edit' && $id > 0 && !$edit) {
    $edit = spbx_ext_load($id);
    if (!$edit) {
        header('Location: extensions.php');
        exit;
    }
}

if ($action === 'new' && !$edit) {
    $edit = [
        'id' => 0,
        'extension' => '',
        'display_name' => '',
        'endpoint_context' => spbx_ext_context_options()[0]['context'] ?? 'internal',
        'email' => '',
        'device_category' => 'deskphone',
        'device_model' => 'snomD815',
        'mac' => '',
        'ipei' => '',
        'serial_number' => '',
        'sip_password' => '',
        'voicemail_enabled' => 1,
        'voicemail_pin' => '0000',
        'active' => 1,
    ];
}

if ($edit) {
    $edit['device_category'] = $edit['device_category'] ?? spbx_ext_category_from_model($edit['device_model'] ?? '');
}

$dectBases = spbx_ext_dect_bases();
$contextOptions = spbx_ext_context_options();
$selectedFormContext = $edit['endpoint_context'] ?? ($contextOptions[0]['context'] ?? 'internal');
$availableExtensions = spbx_ext_available_extensions_for_context($selectedFormContext, $edit['extension'] ?? '');
$currentDectBase = null;

if ($edit && (($edit['device_model'] ?? '') === 'snom_dect_handset')) {
    $currentEndpointId = !empty($edit['endpoint_id']) ? $edit['endpoint_id'] : ($edit['ipei'] ?? '');
    if ($currentEndpointId) {
        $currentDectBase = spbx_ext_dect_current_base($currentEndpointId);
    }
}

if ($edit && isset($_POST['dect_base_id'])) {
    $currentDectBase = ['base_id' => (int)$_POST['dect_base_id']];
}

if (isset($_GET['saved'])) $message = 'Nebenstelle gespeichert.';
if (isset($_GET['deleted'])) $message = 'Nebenstelle gelöscht.';
if (isset($_GET['reboot_ok'])) $message = 'Reboot-Notify gesendet.';
if (isset($_GET['reboot_failed'])) $error = 'Reboot-Notify fehlgeschlagen.';

$rows = [];
if ($action === 'list') {
    $res = $db->query("
        SELECT
            e.id,
            p.extension,
            p.display_name,
            e.email,
            p.id AS endpoint_id,
            e.voicemail_enabled,
            COALESCE(e.active, p.active) AS active,
            p.device_model AS device_model,
            COALESCE(dp.mac, d.mac, dx.mac) AS mac,
            d.ipei AS ipei,
            c.uri AS contact_uri
        FROM ps_endpoints p
        JOIN spbx_extensions e ON e.endpoint_id=p.id
        LEFT JOIN deskphones dp ON dp.sipuser=p.id OR dp.sipuser=p.extension
        LEFT JOIN spbx_devices d ON d.endpoint_id=p.id
        LEFT JOIN spbx_devices dx ON dx.extension=p.extension
        LEFT JOIN ps_contacts c ON c.endpoint=p.id
        WHERE p.device_model IN ('snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x','snom_dect_handset','sip_user')
        ORDER BY CAST(p.extension AS UNSIGNED), p.extension
    ");

    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Nebenstellen - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('Nebenstellen', 'Nebenstelle und Telefon in einer Maske'); ?>

        <div class="spbx-content spbx-extensions-layout">
            <?php if ($message): ?><div class="spbx-alert success"><?php echo spbx_h($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="spbx-alert error"><?php echo spbx_h($error); ?></div><?php endif; ?>

            <?php if ($action === 'new' || $action === 'edit'): ?>
                <div class="spbx-extensions-card">
                    <div class="spbx-extensions-header">
                        <div>
                            <div class="spbx-extensions-title"><?php echo $action === 'edit' ? 'Nebenstelle bearbeiten' : 'Nebenstelle anlegen'; ?></div>
                            <div class="spbx-extensions-subtitle">
                                Gerätetyp wird eindeutig aus Geräteart und Gerätetyp gespeichert.
                            </div>
                        </div>
                    </div>

                    <form class="spbx-extensions-form" method="post">
                        <input type="hidden" name="id" value="<?php echo (int)($edit['id'] ?? 0); ?>">

                        <div class="spbx-extensions-grid">
                            <div class="spbx-extensions-field">
                                <label>Nebenstelle *</label>
                                <select name="extension" required>
                                    <?php foreach ($availableExtensions as $extOpt): ?>
                                        <option value="<?php echo spbx_h($extOpt['extension']); ?>"
                                            <?php echo !empty($extOpt['disabled']) ? 'disabled' : ''; ?>
                                            <?php echo (($edit['extension'] ?? '') === $extOpt['extension']) ? 'selected' : ''; ?>>
                                            <?php echo spbx_h($extOpt['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="spbx-extensions-help">Freie Durchwahlen sind auswählbar. Belegte Durchwahlen werden angezeigt, aber gesperrt.</div>
                            </div>

                            <div class="spbx-extensions-field">
                                <label>Anzeigename *</label>
                                <input type="text" name="display_name" value="<?php echo spbx_h($edit['display_name'] ?? ''); ?>" required>
                            </div>


                            <div class="spbx-extensions-field">
                                <label>Standort / Context *</label>
                                <select name="endpoint_context" required>
                                    <?php foreach ($contextOptions as $ctxRow): ?>
                                        <option value="<?php echo spbx_h($ctxRow['context']); ?>" <?php echo (($edit['endpoint_context'] ?? '') === $ctxRow['context']) ? 'selected' : ''; ?>>
                                            <?php echo spbx_h($ctxRow['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="spbx-extensions-help">Damit kann ein Telefon von Trunk/Standort A nach B verschoben werden.</div>
                            </div>

                            <div class="spbx-extensions-field">
                                <label>Geräteart *</label>
                                <select name="device_category" id="deviceCategory" required>
                                    <option value="deskphone" <?php echo (($edit['device_category'] ?? '') === 'deskphone') ? 'selected' : ''; ?>>Tischtelefon</option>
                                    <option value="dect" <?php echo (($edit['device_category'] ?? '') === 'dect') ? 'selected' : ''; ?>>DECT-Schnurlos</option>
                                    <option value="siponly" <?php echo (($edit['device_category'] ?? '') === 'siponly') ? 'selected' : ''; ?>>SIP-Gerät</option>
                                </select>
                            </div>

                            <div class="spbx-extensions-field">
                                <label>Gerätetyp *</label>
                                <select name="device_model" id="deviceModel" data-current="<?php echo spbx_h(($edit['device_model'] ?? '') ?: 'snomD815'); ?>" required></select>
                            </div>

                            <div class="spbx-extensions-field" id="macField">
                                <label>MAC-Adresse</label>
                                <input type="text" name="mac" value="<?php echo spbx_h($edit['mac'] ?? ''); ?>" placeholder="z. B. 000413E30A0C">
                                <div class="spbx-extensions-help">Pflicht bei Tischtelefonen.</div>
                            </div>

                            <div class="spbx-extensions-field" id="ipeiField">
                                <label>IPEI</label>
                                <input type="text" name="ipei" value="<?php echo spbx_h($edit['ipei'] ?? ''); ?>" placeholder="für SNOM DECT Handset">
                                <div class="spbx-extensions-help">Pflicht bei SNOM DECT Handset. Die IPEI wird als ps_endpoints.id gespeichert.</div>
                            </div>

                            <div class="spbx-extensions-field" id="dectBaseField">
                                <label>DECT-Basisstation</label>
                                <select name="dect_base_id">
                                    <option value="0">Keine Basis zuordnen</option>
                                    <?php foreach ($dectBases as $baseRow): ?>
                                        <option value="<?php echo (int)$baseRow['id']; ?>" <?php echo ((int)($currentDectBase['base_id'] ?? 0) === (int)$baseRow['id']) ? 'selected' : ''; ?>>
                                            <?php echo spbx_h($baseRow['base_name'] . ' / ' . $baseRow['base_type'] . ' / ' . $baseRow['mac_address']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="spbx-extensions-help">Die niedrigste freie IDX wird automatisch vergeben.</div>
                            </div>

                            <div class="spbx-extensions-field">
                                <label>SIP Passwort</label>
                                <input type="text" name="sip_password" value="<?php echo spbx_h($edit['sip_password'] ?? ''); ?>" placeholder="leer = automatisch erzeugen">
                            </div>

                            <div class="spbx-extensions-field">
                                <label>E-Mail</label>
                                <input type="email" name="email" value="<?php echo spbx_h($edit['email'] ?? ''); ?>" placeholder="optional für Voicemail">
                            </div>

                            <div class="spbx-extensions-field">
                                <label>Seriennummer</label>
                                <input type="text" name="serial_number" value="<?php echo spbx_h($edit['serial_number'] ?? ''); ?>">
                            </div>

                            

                            <div class="spbx-extensions-field">
                                <label>Voicemail</label>
                                <label class="spbx-extensions-check">
                                    <input type="checkbox" name="voicemail_enabled" value="1" <?php echo !empty($edit['voicemail_enabled']) ? 'checked' : ''; ?>>
                                    Voicemail aktiv
                                </label>
                            </div>

                            <div class="spbx-extensions-field">
                                <label>Status</label>
                                <label class="spbx-extensions-check">
                                    <input type="checkbox" name="active" value="1" <?php echo !empty($edit['active']) ? 'checked' : ''; ?>>
                                    Nebenstelle aktiv
                                </label>
                            </div>
                        </div>

                        <div class="spbx-extensions-actions">
                            <a class="spbx-button secondary" href="extensions.php">Abbrechen</a>
                            <button class="spbx-button primary" type="submit">Speichern</button>
                        </div>
                    </form>
                </div>

                <script>
                const spbxDeviceModels = {
                    deskphone: [
                        {value: 'snomD810', label: 'snom D810'},
                        {value: 'snomD812', label: 'snom D812'},
                        {value: 'snomD815', label: 'snom D815'},
                        {value: 'snomD862', label: 'snom D862'},
                        {value: 'snomD865', label: 'snom D865'},
                        {value: 'snomD892', label: 'snom D892M'},
                        {value: 'snomD895', label: 'snom D895M'},
                        {value: 'GigasetP810', label: 'Gigaset P810'},
                        {value: 'GigasetP82x', label: 'Gigaset P82x'},
                        {value: 'GigasetP85x', label: 'Gigaset P85x'}
                    ],
                    dect: [
                        {value: 'snom_dect_handset', label: 'SNOM DECT Handset'}
                    ],
                    siponly: [
                        {value: 'sip_user', label: 'SIP User'}
                    ]
                };

                function rebuildDeviceModel() {
                    const category = document.getElementById('deviceCategory').value;
                    const select = document.getElementById('deviceModel');
                    const current = select.dataset.current || '';
                    select.innerHTML = '';

                    (spbxDeviceModels[category] || []).forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.value;
                        opt.textContent = item.label;
                        if (item.value === current) opt.selected = true;
                        select.appendChild(opt);
                    });

                    if (!select.value && select.options.length) {
                        select.options[0].selected = true;
                    }

                    updateDeviceFields();
                }

                function updateDeviceFields() {
                    const category = document.getElementById('deviceCategory').value;
                    const model = document.getElementById('deviceModel').value;
                    const mac = document.getElementById('macField');
                    const ipei = document.getElementById('ipeiField');
                    const dectBase = document.getElementById('dectBaseField');

                    if (mac) mac.style.display = category === 'deskphone' ? '' : 'none';
                    if (ipei) ipei.style.display = category === 'dect' ? '' : 'none';
                    if (dectBase) dectBase.style.display = category === 'dect' ? '' : 'none';

                    document.getElementById('deviceModel').dataset.current = model;
                }

                document.getElementById('deviceCategory').addEventListener('change', function() {
                    document.getElementById('deviceModel').dataset.current = '';
                    rebuildDeviceModel();
                });

                document.getElementById('deviceModel').addEventListener('change', updateDeviceFields);
                rebuildDeviceModel();
                </script>
            <?php else: ?>
                <div class="spbx-extensions-card">
                    <div class="spbx-extensions-header">
                        <div>
                            <div class="spbx-extensions-title">Nebenstellen</div>
                            <div class="spbx-extensions-subtitle"><?php echo count($rows); ?> Einträge vorhanden.</div>
                        </div>
                        <a class="spbx-button primary" href="extensions.php?action=new">+ Neue Nebenstelle</a>
                    </div>

                    <?php if (!$rows): ?>
                        <div class="spbx-extensions-empty">
                            <strong>Keine Nebenstellen vorhanden</strong>
                            Lege die erste Nebenstelle an.
                        </div>
                    <?php else: ?>
                        <div class="spbx-extensions-table-wrap">
                            <table class="spbx-extensions-table">
                                <thead>
                                    <tr>
                                        <th>Durchwahl</th>
                                        <th>Gerät</th>
                                        <th>MAC/IPEI</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th class="spbx-ext-action-th">Aktion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $r): ?>
                                        <?php
                                            $dm = ($r['device_model'] ?? '') ?: 'unknown';
                                            $isOnline = !empty($r['contact_uri']);
                                            $provUrl = spbx_ext_provision_url($dm, $r['mac'] ?? '');
                                        ?>
                                        <tr>
                                            <td class="spbx-ext-col-extension"><strong><?php echo spbx_h($r['extension']); ?></strong></td>
                                            <td class="spbx-ext-col-device"><?php echo spbx_ext_device_svg($dm); ?><strong><?php echo spbx_h(spbx_ext_model_label_ui($dm)); ?></strong></td>
                                            <td class="spbx-ext-col-mac"><?php echo spbx_h(spbx_ext_identifier_ui($r, $dm)); ?></td>
                                            <td class="spbx-ext-col-name"><?php echo spbx_h($r['display_name']); ?></td>
                                            <td>
                                                <span class="spbx-extensions-badge <?php echo $isOnline ? 'spbx-extensions-badge-online' : 'spbx-extensions-badge-offline'; ?>"><?php echo $isOnline ? 'Online' : 'Offline'; ?></span>
                                                <span class="spbx-extensions-badge"><?php echo !empty($r['active']) ? 'Aktiv' : 'Inaktiv'; ?></span>
                                            </td>
                                            <td class="spbx-ext-action-td">
                                                <div class="spbx-ext-actions spbx-ext-actions-inline">
                                                    <?php if (in_array($dm, ['snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x'], true)): ?>
                                                        <a class="spbx-button small secondary spbx-ext-btn spbx-ext-btn-keys" href="function_keys.php?id=<?php echo (int)$r['id']; ?>"><?php echo spbx_ext_action_icon('keys'); ?>Tasten</a>
                                                        <?php if ($provUrl !== ''): ?>
                                                            <a class="spbx-button small secondary spbx-ext-btn" href="<?php echo spbx_h($provUrl); ?>" target="_blank" rel="noopener">Provisioning</a>
                                                        <?php endif; ?>
                                                        <a class="spbx-button small secondary spbx-ext-btn" href="extensions.php?action=notify_test&id=<?php echo (int)$r['id']; ?>"><?php echo spbx_ext_action_icon('notify'); ?>Notify</a>
                                                        <a class="spbx-button small secondary spbx-ext-btn" href="extensions.php?action=reboot_phone&id=<?php echo (int)$r['id']; ?>" onclick="return confirm('Telefon wirklich neu starten?');"><?php echo spbx_ext_action_icon('reboot'); ?>Reboot</a>
                                                    <?php endif; ?>
                                                    <a class="spbx-button small secondary spbx-ext-btn" href="extensions.php?action=edit&id=<?php echo (int)$r['id']; ?>"><?php echo spbx_ext_action_icon('edit'); ?>Bearbeiten</a>
                                                    <a class="spbx-button small danger spbx-ext-btn" href="extensions.php?action=delete&id=<?php echo (int)$r['id']; ?>" onclick="return confirm('Nebenstelle wirklich löschen?');"><?php echo spbx_ext_action_icon('delete'); ?>Löschen</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
