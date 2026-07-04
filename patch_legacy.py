from pathlib import Path
import re, textwrap, shutil, os
root=Path('/mnt/data/build_legacy')

# 1) Patch inc/provisioning_snom.php
p=root/'inc/provisioning_snom.php'
s=p.read_text()
# fix undefined variable in callerid helper
s=s.replace("$callerid = spbx_prov_strict_callerid($phone);", "$callerid = trim((string)$callerid);")
# extend profiles
old="""        'snomD812' => [
            'label' => 'snom D812',
            'max_fkeys' => 32,
            'provision_file' => 'snomD812.php',
        ],"""
new=old+"""
        'snomD862' => [
            'label' => 'snom D862',
            'max_fkeys' => 32,
            'provision_file' => 'snomD862.php',
        ],
        'snomD865' => [
            'label' => 'snom D865',
            'max_fkeys' => 32,
            'provision_file' => 'snomD865.php',
        ],
        'snomD892' => [
            'label' => 'snom D892M',
            'max_fkeys' => 32,
            'provision_file' => 'snomD892.php',
        ],
        'snomD895' => [
            'label' => 'snom D895M',
            'max_fkeys' => 42,
            'provision_file' => 'snomD895.php',
        ],"""
s=s.replace(old,new)
# replace load_phone/default/load_static block
start=s.index('function spbx_prov_load_phone')
end=s.index('function spbx_prov_snom_key_action_value')
replacement=r'''function spbx_prov_load_phone($mac, $expectedModel)
{
    $db = spbx_db();

    // Legacy-Architektur: Tischtelefone werden aus der flachen deskphones-Tabelle provisioniert.
    // ps_endpoints/ps_auths bleiben die Quelle für SIP-Account und CallerID.
    $stmt = $db->prepare("
        SELECT
            p.id AS endpoint_id,
            p.extension,
            p.display_name,
            p.device_model,
            p.context,
            p.transport,
            p.callerid,
            p.mailboxes,
            COALESCE(d.language, p.language, 'de') AS language,
            p.active AS endpoint_active,

            a.username AS auth_username,
            a.password AS auth_password,

            d.id AS device_id,
            d.mac,
            d.phone_type AS deskphone_type,
            d.phone_type AS device_model_legacy,
            d.sipuser,
            d.eth_pc,
            1 AS provisioning_enabled,
            1 AS device_active,

            e.email,
            e.voicemail_enabled,

            d.*
        FROM deskphones d
        JOIN ps_endpoints p ON p.id=d.sipuser OR p.extension=d.sipuser
        LEFT JOIN ps_auths a ON a.id=p.id
        LEFT JOIN spbx_extensions e ON e.endpoint_id=p.id
        WHERE d.mac=?
          AND d.phone_type=?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('ss', $mac, $expectedModel);
    $stmt->execute();

    $phone = $stmt->get_result()->fetch_assoc();
    if ($phone) {
        // Für bestehende Codepfade, die device_model erwarten.
        $phone['device_model'] = $phone['phone_type'] ?? $expectedModel;
        if (empty($phone['endpoint_id'])) {
            $phone['endpoint_id'] = $phone['sipuser'] ?? $phone['extension'] ?? '';
        }
    }

    return $phone;
}

function spbx_prov_default_key($idx, $endpointId)
{
    return [
        'fkey_idx' => $idx,
        'key_type' => 'none',
        'key_label' => '',
        'key_value' => '',
    ];
}

function spbx_prov_legacy_action_to_type($action)
{
    $action = trim((string)$action);
    if ($action === 'blf') return 'blf';
    if ($action === 'dest') return 'dest';
    if ($action === 'none' || $action === '') return 'none';
    // Unbekannte alte Snom-Aktion nicht verlieren; später direkt ausgeben.
    return $action;
}

function spbx_prov_load_static_keys($phone, $maxFkeys)
{
    $keys = [];
    $endpointId = (string)($phone['endpoint_id'] ?? '');

    for ($i = 0; $i < $maxFkeys; $i++) {
        $action = (string)($phone['fkey' . $i . 'action'] ?? 'none');
        $value = (string)($phone['fkey' . $i . 'value'] ?? '');
        $label = (string)($phone['fkey' . $i . 'label'] ?? '');
        $type = spbx_prov_legacy_action_to_type($action);

        $keys[$i] = [
            'fkey_idx' => $i,
            'key_type' => $type,
            'key_label' => $label,
            'key_value' => $value,
            'legacy_action' => trim($action),
        ];
    }

    return $keys;
}

'''
s=s[:start]+replacement+s[end:]
# adjust action function to output legacy unknown directly
s=s.replace("""    if ($type === 'forward') {
        return 'dest *72';
    }

    return '';""", """    if ($type === 'forward') {
        return 'dest *72';
    }

    $legacy = trim((string)($key['legacy_action'] ?? ''));
    if ($legacy !== '' && $legacy !== 'none') {
        return trim($legacy . ' ' . $value);
    }

    return '';""")
# firmware map new models
s=s.replace("""        'snomD812' => 'snomD812',
        'snomD815' => 'snomD815',""", """        'snomD812' => 'snomD812',
        'snomD815' => 'snomD815',
        'snomD862' => 'snomD862',
        'snomD865' => 'snomD865',
        'snomD892' => 'snomD892',
        'snomD895' => 'snomD895',""")
p.write_text(s)

# 2) Patch pages/function_keys.php broadly
p=root/'pages/function_keys.php'
s=p.read_text()
# add helpers after message vars
insert_after="$message = '';\n"
helper=r'''

function spbx_keys_deskphone_columns()
{
    $cols = [];
    for ($i = 0; $i <= 41; $i++) {
        $cols[] = 'fkey' . $i . 'action';
        $cols[] = 'fkey' . $i . 'value';
        $cols[] = 'fkey' . $i . 'label';
    }
    return $cols;
}

function spbx_keys_set_column_sql($cols)
{
    return implode(', ', array_map(function($c) { return "`" . $c . "`=?"; }, $cols));
}

function spbx_keys_default_row_values()
{
    $values = [];
    for ($i = 0; $i <= 41; $i++) {
        $values['fkey' . $i . 'action'] = 'none';
        $values['fkey' . $i . 'value'] = '';
        $values['fkey' . $i . 'label'] = '';
    }
    return $values;
}
'''
s=s.replace(insert_after, insert_after+helper,1)
# model functions additions
for old,new in [
("if ($model === 'snomD812' || $model === 'GigasetP82x') return 'd812';", "if ($model === 'snomD812' || $model === 'snomD862' || $model === 'snomD865' || $model === 'snomD892' || $model === 'GigasetP82x') return 'd812';"),
("if ($model === 'snomD815' || $model === 'GigasetP85x') {", "if ($model === 'snomD815' || $model === 'snomD895' || $model === 'GigasetP85x') {"),
("if ($model === 'snomD812' || $model === 'GigasetP82x') {", "if ($model === 'snomD812' || $model === 'snomD862' || $model === 'snomD865' || $model === 'snomD892' || $model === 'GigasetP82x') {"),
("if ($model === 'snomD815') return 10;", "if ($model === 'snomD895') return 14;\n    if ($model === 'snomD815') return 10;"),
("if ($model === 'snomD812') return 8;", "if ($model === 'snomD812') return 8;\n    if ($model === 'snomD862') return 8;\n    if ($model === 'snomD865') return 8;\n    if ($model === 'snomD892') return 8;"),
("if ($model === 'snomD812') return 'snom D812';", "if ($model === 'snomD812') return 'snom D812';\n    if ($model === 'snomD862') return 'snom D862';\n    if ($model === 'snomD865') return 'snom D865';\n    if ($model === 'snomD892') return 'snom D892M';\n    if ($model === 'snomD895') return 'snom D895M';")
]: s=s.replace(old,new)
# schema ok to deskphones
s=re.sub(r"function spbx_keys_schema_ok\(\)\s*\{.*?\n\}", """function spbx_keys_schema_ok()
{
    $db = spbx_db();
    $res = $db->query("SHOW TABLES LIKE 'deskphones'");
    if (!$res || $res->num_rows < 1) return false;
    $res = $db->query("SHOW COLUMNS FROM deskphones LIKE 'fkey41label'");
    return $res && $res->num_rows > 0;
}""", s, flags=re.S)
# device function
s=re.sub(r"function spbx_keys_device\(\$id\)\s*\{.*?return \$stmt->get_result\(\)->fetch_assoc\(\);\n\}", r'''function spbx_keys_device($id)
{
    $db = spbx_db();

    $stmt = $db->prepare("
        SELECT
            e.id AS extension_row_id,
            e.extension,
            e.display_name,
            e.endpoint_id,
            d.id AS device_id,
            COALESCE(d.phone_type, p.device_model) AS device_model,
            d.mac,
            d.*
        FROM spbx_extensions e
        JOIN ps_endpoints p ON p.id=e.endpoint_id
        JOIN deskphones d ON d.sipuser=p.id OR d.sipuser=p.extension
        WHERE e.id=?
        LIMIT 1
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}''', s, flags=re.S)
# remove old schema message
s=s.replace("Datenbank-Patch für Funktionstasten fehlt: spbx_device_keys.fkey_idx ist nicht vorhanden. Bitte patch_servuspbx_medical_1_0_29_blf_stable_fix.sql importieren.", "Datenbank-Patch für Funktionstasten fehlt: deskphones mit fkey0..fkey41 ist nicht vorhanden. Bitte patch_servuspbx_professional_2_6_1_legacy_deskphones.sql importieren.")
# reset block update
s=re.sub(r"if \(\$_SERVER\['REQUEST_METHOD'\] === 'POST' && \(\(\$_POST\['form_action'\] \?\? ''\) === 'reset_keys'\)\) \{.*?\n\}", r'''if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['form_action'] ?? '') === 'reset_keys')) {
    $cols = spbx_keys_deskphone_columns();
    $values = [];
    foreach ($cols as $c) {
        $values[] = (strpos($c, 'action') !== false) ? 'none' : '';
    }
    $types = str_repeat('s', count($values)) . 'i';
    $values[] = (int)$device['device_id'];

    $sql = "UPDATE deskphones SET " . spbx_keys_set_column_sql($cols) . " WHERE id=? LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();

    spbx_notify_snom_check_cfg($device['endpoint_id']);

    spbx_audit_log('function_keys_reset', 'Alle Tastenbelegungen für Nebenstelle ' . $device['extension'] . ' zurückgesetzt.', 'warning', null, null, 'keys');

    header('Location: function_keys.php?id=' . $id . '&reset=1');
    exit;
}''', s, flags=re.S, count=1)
# replace post save delete/insert try body content between begin and commit-ish? easier replace from $db->begin_transaction() to $db->commit() inside POST block first occurrence after not reset.
old_pat=r"\$db->begin_transaction\(\);\s*\n\s*try \{.*?\n\s*\$db->commit\(\);"
def repl(m):
    return r'''$db->begin_transaction();

    try {
        $cols = spbx_keys_deskphone_columns();
        $save = spbx_keys_default_row_values();

        for ($page = 1; $page <= $pages; $page++) {
            for ($pos = 1; $pos <= $physical; $pos++) {
                $idx = (($page - 1) * $physical) + ($pos - 1);
                if ($idx > 41) {
                    continue;
                }

                $type = trim((string)($_POST['key_type'][$idx] ?? 'none'));
                $label = trim((string)($_POST['key_label'][$idx] ?? ''));
                $value = '';

                if (!in_array($type, ['none','blf','dest','forward'], true)) {
                    $type = 'none';
                }

                if ($type === 'none') {
                    $value = '';
                    $label = '';
                }

                if ($type === 'blf') {
                    $value = trim((string)($_POST['key_extension'][$idx] ?? ''));

                    if ($value === '' || $value === (string)$device['extension']) {
                        $type = 'none';
                        $label = '';
                        $value = '';
                    } else {
                        $label = $extensionLabelMap[$value] ?? $value;
                    }
                }

                if ($type === 'dest') {
                    $value = trim((string)($_POST['key_number'][$idx] ?? ''));
                    if ($value === '') { $type = 'none'; $label = ''; }

                    if ($label === '') {
                        $label = $value;
                    }
                }

                if ($type === 'forward') {
                    $type = 'dest';
                    $value = '*72';
                    if ($label === '') {
                        $label = 'Rufumleitung';
                    }
                }

                $save['fkey' . $idx . 'action'] = $type;
                $save['fkey' . $idx . 'value'] = $value;
                $save['fkey' . $idx . 'label'] = $label;
            }
        }

        $values = [];
        foreach ($cols as $c) {
            $values[] = $save[$c] ?? ((strpos($c, 'action') !== false) ? 'none' : '');
        }
        $types = str_repeat('s', count($values)) . 'i';
        $values[] = (int)$device['device_id'];

        $sql = "UPDATE deskphones SET " . spbx_keys_set_column_sql($cols) . " WHERE id=? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        $db->commit();'''
s=re.sub(old_pat, repl, s, count=1, flags=re.S)
# replace loading keys from spbx_device_keys block
s=re.sub(r"\$keys = \[\];\s*\$stmt = \$db->prepare\(\"SELECT \* FROM spbx_device_keys WHERE device_id=\? ORDER BY fkey_idx ASC\"\);.*?\n\}\n\n\$extensionLabelMap", r'''$keys = [];
for ($i = 0; $i < $total && $i <= 41; $i++) {
    $action = (string)($device['fkey' . $i . 'action'] ?? 'none');
    $value = (string)($device['fkey' . $i . 'value'] ?? '');
    $label = (string)($device['fkey' . $i . 'label'] ?? '');
    $type = $action;
    if ($type === 'dest' && $value === '*72') $type = 'forward';
    if (!in_array($type, ['none','blf','dest','forward'], true)) $type = 'none';
    $keys[$i] = [
        'fkey_idx' => $i,
        'key_type' => $type,
        'key_label' => $label,
        'key_value' => $value,
    ];
}

$extensionLabelMap''', s, flags=re.S)
# pages for snomD895 should be 3 not 4 and cap total 42
s=s.replace("$pages = 4;\n$total = $physical * $pages;", "$pages = ($device['device_model'] === 'snomD895') ? 3 : 4;\n$total = min($physical * $pages, 42);")
p.write_text(s)

# 3) Patch pages/extensions.php
p=root/'pages/extensions.php'
s=p.read_text()
# model additions
s=s.replace("['snomD815','snomD810','snomD812','GigasetP810','GigasetP82x','GigasetP85x']", "['snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x']")
labels={
"'snomD812' => 'snom D812',":"'snomD812' => 'snom D812',\n        'snomD862' => 'snom D862',\n        'snomD865' => 'snom D865',\n        'snomD892' => 'snom D892M',\n        'snomD895' => 'snom D895M',",
"'snomD812' => '☎️',":"'snomD812' => '☎️',\n        'snomD862' => '☎️',\n        'snomD865' => '☎️',\n        'snomD892' => '☎️',\n        'snomD895' => '☎️',",
"if ($model === 'snomD812') return 'snomD812.php';":"if ($model === 'snomD812') return 'snomD812.php';\n    if ($model === 'snomD862') return 'snomD862.php';\n    if ($model === 'snomD865') return 'snomD865.php';\n    if ($model === 'snomD892') return 'snomD892.php';\n    if ($model === 'snomD895') return 'snomD895.php';"
}
for old,new in labels.items(): s=s.replace(old,new)
# mac required all deskphones
s=s.replace("if (in_array($model, ['snomD815', 'snomD810'], true) && $mac === '')", "if (in_array($model, ['snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x'], true) && $mac === '')")
s=s.replace("if (!in_array($model, ['snomD815', 'snomD810', 'snomD812', 'GigasetP810', 'GigasetP82x', 'GigasetP85x'], true))", "if (!in_array($model, ['snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x'], true))")
# mac exists elsewhere deskphones
s=re.sub(r"function spbx_ext_mac_exists_elsewhere\(\$mac, \$endpointId\)\s*\{.*?\n\}", r'''function spbx_ext_mac_exists_elsewhere($mac, $endpointId)
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
}''', s, flags=re.S)
# upsert device -> deskphones for deskphones; keep spbx_devices for DECT
s=re.sub(r"function spbx_ext_upsert_device\(\$endpointId, \$model, \$extension, \$displayName, \$mac, \$ipei, \$serial, \$provisionFile, \$active\)\s*\{.*?\n\}", r'''function spbx_ext_upsert_device($endpointId, $model, $extension, $displayName, $mac, $ipei, $serial, $provisionFile, $active)
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
}''', s, flags=re.S)
# load joins
s=s.replace("d.id AS device_id,\n            p.device_model AS device_model,\n            d.mac,\n            d.ipei AS ipei,\n            d.serial_number,\n            d.provisioning_enabled,", "COALESCE(d.id, sd.id) AS device_id,\n            p.device_model AS device_model,\n            COALESCE(d.mac, sd.mac) AS mac,\n            sd.ipei AS ipei,\n            sd.serial_number,\n            COALESCE(sd.provisioning_enabled, 1) AS provisioning_enabled,")
s=s.replace("LEFT JOIN spbx_devices d ON d.endpoint_id=p.id", "LEFT JOIN deskphones d ON d.sipuser=p.id OR d.sipuser=p.extension\n        LEFT JOIN spbx_devices sd ON sd.endpoint_id=p.id",1)
# delete deskphones and no device keys
s=s.replace("""        $stmt = $db->prepare("DELETE FROM spbx_device_keys WHERE device_id=?");
        $deviceId = (int)($old['device_id'] ?? 0);
        if ($deviceId > 0) {
            $stmt->bind_param('i', $deviceId);
            $stmt->execute();
        }

""", "")
s=s.replace("""        $stmt = $db->prepare("DELETE FROM spbx_devices WHERE endpoint_id=? OR extension=?");
        $stmt->bind_param('ss', $endpointId, $extension);
        $stmt->execute();

""", """        $stmt = $db->prepare("DELETE FROM deskphones WHERE sipuser=? OR sipuser=?");
        $stmt->bind_param('ss', $endpointId, $extension);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM spbx_devices WHERE endpoint_id=? OR extension=?");
        $stmt->bind_param('ss', $endpointId, $extension);
        $stmt->execute();

""")
# list query replace joins
s=s.replace("COALESCE(d.mac, dx.mac) AS mac,\n            d.ipei AS ipei,", "COALESCE(dp.mac, d.mac, dx.mac) AS mac,\n            d.ipei AS ipei,")
s=s.replace("LEFT JOIN spbx_devices d ON d.endpoint_id=p.id\n        LEFT JOIN spbx_devices dx ON dx.extension=p.extension", "LEFT JOIN deskphones dp ON dp.sipuser=p.id OR dp.sipuser=p.extension\n        LEFT JOIN spbx_devices d ON d.endpoint_id=p.id\n        LEFT JOIN spbx_devices dx ON dx.extension=p.extension")
s=s.replace("WHERE p.device_model IN ('snomD815','snomD810','snomD812','GigasetP810','GigasetP82x','GigasetP85x','snom_dect_handset','sip_user')", "WHERE p.device_model IN ('snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x','snom_dect_handset','sip_user')")
p.write_text(s)

# 4 create provision files
for model in ['snomD862','snomD865','snomD892','snomD895']:
    (root/'provision'/f'{model}.php').write_text(f"<?php\nrequire_once __DIR__ . '/../inc/provisioning_snom.php';\nspbx_prov_handle_snom('{model}');\n?>\n")

# 5 SQL patch create deskphones with fkey0..41 and migrate existing
cols=[]
for i in range(42):
    cols.append(f"  `fkey{i}action` varchar(8) NOT NULL DEFAULT 'none',")
for i in range(42):
    cols.append(f"  `fkey{i}value` varchar(20) DEFAULT NULL,")
for i in range(42):
    cols.append(f"  `fkey{i}label` varchar(28) DEFAULT NULL,")
create="""CREATE TABLE IF NOT EXISTS `deskphones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac` varchar(15) DEFAULT NULL,
  `phone_type` varchar(15) DEFAULT NULL,
  `sipuser` varchar(15) DEFAULT NULL,
  `eth_pc` varchar(4) NOT NULL DEFAULT 'off',
  `language` varchar(10) NOT NULL DEFAULT 'Deutsch',
"""+"\n".join(cols)+"""
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_deskphones_mac` (`mac`),
  KEY `idx_deskphones_sipuser` (`sipuser`),
  KEY `idx_deskphones_phone_type` (`phone_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
"""
# migration inserts basic rows only; keys optionally from spbx_device_keys for 0..41
mig="""
INSERT IGNORE INTO `deskphones` (`mac`, `phone_type`, `sipuser`, `eth_pc`, `language`)
SELECT d.mac, p.device_model, p.id, 'off', COALESCE(p.language, 'Deutsch')
FROM spbx_devices d
JOIN ps_endpoints p ON p.id=d.endpoint_id
WHERE p.device_model IN ('snomD815','snomD810','snomD812','snomD862','snomD865','snomD892','snomD895','GigasetP810','GigasetP82x','GigasetP85x')
  AND d.mac IS NOT NULL AND d.mac<>'';

-- Bestehende spbx_device_keys in die flache Legacy-Struktur übernehmen.
-- Hinweis: dynamisches SQL vermeidet Abbrüche, wenn spbx_device_keys auf Altinstallationen fehlt.
DROP PROCEDURE IF EXISTS spbx_migrate_legacy_deskphone_keys;
DELIMITER $$
CREATE PROCEDURE spbx_migrate_legacy_deskphone_keys()
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'spbx_device_keys') THEN
"""
for i in range(42):
    mig += f"    UPDATE deskphones dp JOIN spbx_devices d ON d.mac=dp.mac JOIN spbx_device_keys k ON k.device_id=d.id AND k.fkey_idx={i} SET dp.fkey{i}action=k.key_type, dp.fkey{i}value=k.key_value, dp.fkey{i}label=k.key_label;\n"
mig += """  END IF;
END$$
DELIMITER ;
CALL spbx_migrate_legacy_deskphone_keys();
DROP PROCEDURE IF EXISTS spbx_migrate_legacy_deskphone_keys;
"""
(root/'patch_servuspbx_professional_2_6_1_legacy_deskphones.sql').write_text(create+"\n"+mig)

# README
(root/'README_2_6_1_LEGACY_DESKPHONES.txt').write_text("""ServusPBX Professional 2.6.1 - Legacy Deskphones Provisioning

Ziel dieses Standes:
- Generator-/Abstraktionslogik fuer Tischtelefon-Tasten wird nicht mehr verwendet.
- Tischtelefone verwenden die flache Legacy-Tabelle `deskphones`.
- Feldnamen bleiben bewusst kompatibel zum alten System: fkey0action/fkey0value/fkey0label ... fkey41action/fkey41value/fkey41label.
- Maximum: 42 Tasten (snom D895M: 14 physische Tasten x 3 Ebenen). Alle anderen Modelle bleiben darunter.
- Provisioning liest aus `deskphones` und erzeugt wieder direkt Snom/Gigaset XML.

Vor dem Testen importieren:
  mysql -u root -p general < patch_servuspbx_professional_2_6_1_legacy_deskphones.sql

Neue/ergänzte Modelle:
- snomD862
- snomD865
- snomD892
- snomD895

Hinweis:
spbx_devices bleibt fuer DECT/Sondergeraete im Projekt, Tischtelefone werden aber in `deskphones` gefuehrt.
""")
