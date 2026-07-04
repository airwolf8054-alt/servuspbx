<?php
require_once __DIR__ . '/db.php';

ini_set('display_errors', 'Off');


function spbx_prov_filter_self_blf($keys, $ownExtension)
{
    $ownExtension = trim((string)$ownExtension);
    if ($ownExtension === '') {
        return $keys;
    }

    foreach ($keys as $idx => $key) {
        if (($key['key_type'] ?? '') === 'blf' && (string)($key['key_value'] ?? '') === $ownExtension) {
            $keys[$idx]['key_type'] = 'none';
            $keys[$idx]['key_label'] = '';
            $keys[$idx]['key_value'] = '';
        }
    }

    return $keys;
}


function spbx_prov_clean_mac($value)
{
    return strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', (string)$value));
}

function spbx_prov_xml($value)
{
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}


function spbx_prov_blf_label($label)
{
    $label = trim(preg_replace('/\s+/', ' ', (string)$label));

    if ($label === '') {
        return '';
    }

    // Bewährte alte ServusPBX/Snom-Logik:
    // Leerzeichen im BLF-Label werden zu &#10;.
    // Wichtig: Diese Entity darf später nicht zu &amp;#10; escaped werden.
    if (strpos($label, '&#10;') === false && strpos($label, ' ') !== false) {
        $label = str_replace(' ', '&#10;', $label);
    }

    return $label;
}

function spbx_prov_xml_blf_label($label)
{
    $label = spbx_prov_blf_label($label);

    // XML sicher machen.
    $label = htmlspecialchars((string)$label, ENT_XML1 | ENT_QUOTES, 'UTF-8');

    // Den absichtlich gesetzten Snom-Zeilenumbruch wieder roh ausgeben.
    return str_replace('&amp;#10;', '&#10;', $label);
}



function spbx_prov_server_addr()
{
    if (!empty($_SERVER['SERVER_ADDR'])) {
        return $_SERVER['SERVER_ADDR'];
    }

    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'pbx.local');
    return preg_replace('/:\d+$/', '', $host);
}

function spbx_prov_base_url()
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? spbx_prov_server_addr());
    return $scheme . '://' . $host;
}

function spbx_prov_setting($key, $default = '')
{
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        $db = spbx_db();

        $res = $db->query("SELECT setting_key, setting_value FROM spbx_settings");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $cache[$r['setting_key']] = $r['setting_value'];
            }
        }
    }

    return $cache[$key] ?? $default;
}


function spbx_prov_clean_callerid_label($callerid, $fallback = '')
{
    $callerid = trim((string)$callerid);

    if ($callerid !== '' && preg_match('/"([^"]+)"/', $callerid, $m)) {
        return trim($m[1]);
    }

    if ($callerid !== '') {
        $clean = trim(preg_replace('/\s*<[^>]+>\s*/', '', $callerid));
        if ($clean !== '') {
            return $clean;
        }
    }

    return trim((string)$fallback);
}

function spbx_prov_key_label($key)
{
    $type = (string)($key['key_type'] ?? 'none');

    if ($type === 'blf') {
        $callerLabel = spbx_prov_clean_callerid_label(
            $key['target_callerid'] ?? '',
            $key['target_display_name'] ?? ($key['key_value'] ?? '')
        );

        return $callerLabel !== '' ? $callerLabel : (string)($key['key_value'] ?? '');
    }

    return (string)($key['key_label'] ?? '');
}


function spbx_prov_model_profile($model)
{
    $profiles = [
        'snomD815' => [
            'label' => 'snom D815',
            'max_fkeys' => 40,
            'provision_file' => 'snomD815.php',
        ],
        'snomD810' => [
            'label' => 'snom D810',
            'max_fkeys' => 16,
            'provision_file' => 'snomD810.php',
        ],
        'snomD812' => [
            'label' => 'snom D812',
            'max_fkeys' => 32,
            'provision_file' => 'snomD812.php',
        ],
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
        ],
        'GigasetP810' => [
            'label' => 'Gigaset P810',
            'max_fkeys' => 16,
            'provision_file' => 'GigasetP810.php',
        ],
        'GigasetP82x' => [
            'label' => 'Gigaset P82x',
            'max_fkeys' => 32,
            'provision_file' => 'GigasetP82x.php',
        ],
        'GigasetP85x' => [
            'label' => 'Gigaset P85x',
            'max_fkeys' => 40,
            'provision_file' => 'GigasetP85x.php',
        ],
    ];

    return $profiles[$model] ?? null;
}

function spbx_prov_xml_header()
{
    header('Content-type: text/xml; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

function spbx_prov_error($message)
{
    spbx_prov_xml_header();

    echo '<?xml version="1.0" standalone="yes"?>';
    echo "<settings>";
    echo "<phone-settings e='2'>";
    echo "<user_idle_text perm='R'>" . spbx_prov_xml('Provisioning Fehler') . "</user_idle_text>";
    echo "<setting_server perm='RW'>" . spbx_prov_xml($message) . "</setting_server>";
    echo "</phone-settings>";
    echo "</settings>";
    exit;
}

function spbx_prov_load_phone($mac, $expectedModel)
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
    $targets = [];

    for ($i = 0; $i < $maxFkeys; $i++) {
        $action = (string)($phone['fkey' . $i . 'action'] ?? 'none');
        $value = trim((string)($phone['fkey' . $i . 'value'] ?? ''));
        $label = (string)($phone['fkey' . $i . 'label'] ?? '');
        $type = spbx_prov_legacy_action_to_type($action);

        if ($type === 'blf' && $value !== '') {
            $targets[$value] = true;
        }

        $keys[$i] = [
            'fkey_idx' => $i,
            'key_type' => $type,
            'key_label' => $label,
            'key_value' => $value,
            'legacy_action' => trim($action),
        ];
    }

    // Alte, bewährte Logik: BLF-Labels kommen beim Provisionieren aus der
    // CallerID des Ziel-Endpunkts, nicht aus dem gespeicherten Label-Feld.
    // Das gespeicherte Label bleibt nur Fallback.
    if (!empty($targets)) {
        $db = spbx_db();
        $targetValues = array_keys($targets);
        $placeholders = implode(',', array_fill(0, count($targetValues), '?'));
        $types = str_repeat('s', count($targetValues) * 2);
        $params = array_merge($targetValues, $targetValues);

        $stmt = $db->prepare("
            SELECT id, extension, display_name, callerid
            FROM ps_endpoints
            WHERE id IN ($placeholders) OR extension IN ($placeholders)
        ");

        $labelMap = [];
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $label = spbx_prov_clean_callerid_label(
                    $r['callerid'] ?? '',
                    $r['display_name'] ?? ($r['extension'] ?? ($r['id'] ?? ''))
                );
                if ($label !== '') {
                    $labelMap[(string)$r['id']] = $label;
                    $labelMap[(string)$r['extension']] = $label;
                }
            }
        }

        foreach ($keys as $idx => $key) {
            if (($key['key_type'] ?? '') === 'blf') {
                $value = (string)($key['key_value'] ?? '');
                if (isset($labelMap[$value])) {
                    $keys[$idx]['key_label'] = $labelMap[$value];
                    $keys[$idx]['target_callerid'] = $labelMap[$value];
                }
            }
        }
    }

    return $keys;
}

function spbx_prov_snom_key_action_value($key, $server)
{
    $type = (string)($key['key_type'] ?? 'none');
    $value = trim((string)($key['key_value'] ?? ''));

    if ($type === 'blf') {
        return $value !== '' ? 'blf ' . $value : '';
    }

    if ($type === 'dest') {
        return $value !== '' ? 'dest ' . $value : '';
    }

    if ($type === 'forward') {
        return 'dest *72';
    }

    $legacy = trim((string)($key['legacy_action'] ?? ''));
    if ($legacy !== '' && $legacy !== 'none') {
        return trim($legacy . ' ' . $value);
    }

    return '';
}









function spbx_prov_limit_chars($value, $max)
{
    $value = trim((string)$value);

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }

    return substr($value, 0, $max);
}


function spbx_prov_primary_display_name($callerid)
{
    $callerid = trim((string)$callerid);

    if ($callerid !== '' && preg_match('/"([^"]+)"/', $callerid, $m)) {
        $callerid = trim($m[1]);
    } elseif (strpos($callerid, '<') !== false) {
        $callerid = trim(str_replace('"', '', strtok($callerid, '<')));
    }

    $callerid = preg_replace('/\s+/', ' ', $callerid);

    if ($callerid === '') {
        return '';
    }

    $parts = explode(' ', $callerid, 2);
    $first = trim($parts[0] ?? '');
    $last = trim($parts[1] ?? '');

    $display = $last !== '' ? $last : $first;

    return spbx_prov_xml(spbx_prov_limit_chars($display, 12));
}


function spbx_prov_twoline_callerid($callerid)
{
    $callerid = trim((string)$callerid);

    if ($callerid !== '' && preg_match('/"([^"]+)"/', $callerid, $m)) {
        $callerid = trim($m[1]);
    } elseif (strpos($callerid, '<') !== false) {
        $callerid = trim(str_replace('"', '', strtok($callerid, '<')));
    }

    $callerid = preg_replace('/\s+/', ' ', $callerid);

    if ($callerid === '') {
        return '';
    }

    $parts = explode(' ', $callerid, 2);
    $first = spbx_prov_limit_chars($parts[0] ?? '', 12);
    $last = spbx_prov_limit_chars($parts[1] ?? '', 12);

    if ($last === '') {
        return spbx_prov_xml($first);
    }

    return spbx_prov_xml($first) . '&#10;' . spbx_prov_xml($last);
}


function spbx_prov_strict_callerid($phone)
{
    return (string)($phone['callerid'] ?? '');
}



function spbx_prov_firmware_model_name($model)
{
    $model = (string)$model;

    $map = [
        'snomD810' => 'snomD810',
        'snomD812' => 'snomD812',
        'snomD815' => 'snomD815',
        'snomD862' => 'snomD862',
        'snomD865' => 'snomD865',
        'snomD892' => 'snomD892',
        'snomD895' => 'snomD895',
        'GigasetP810' => 'gigasetP810',
        'GigasetP82x' => spbx_prov_setting('fw_gigaset_p82x_model', 'gigasetP820'),
        'GigasetP85x' => spbx_prov_setting('fw_gigaset_p85x_model', 'gigasetP850'),
    ];

    return $map[$model] ?? $model;
}

function spbx_prov_firmware_url($model)
{
    $model = (string)$model;
    $fwModel = spbx_prov_firmware_model_name($model);

    if (strpos($model, 'snomD') === 0) {
        $firmware = spbx_prov_setting('fw_snom_desktop_version', '10.1.226.13');
        return 'https://downloads.snom.com/fw/' . rawurlencode($firmware) . '/bin/' . rawurlencode($fwModel . '-' . $firmware . '-SIP-r.swu');
    }

    if (strpos($model, 'GigasetP') === 0) {
        $firmware = spbx_prov_setting('fw_gigaset_desktop_version', '10.1.226.13');
        return 'https://downloads.grape.gigaset.net/fw/' . rawurlencode($firmware) . '/bin/' . rawurlencode($fwModel . '-' . $firmware . '-SIP-r.swu');
    }

    return '';
}

function spbx_prov_emit_deskphone_defaults()
{
    // ServusPBX Deskphone Defaults für SNOM und Gigaset Tischtelefone.
    // Nicht für DECT-Basen wie M400/M900 verwenden.
    echo "<call_screen_fkeys_on_connected perm='R'>F_LEFT F_RIGHT F_HOLD transfer(not:Transfer) F_DUAL_AUDIO(not:Conference) F_CALLRECORD_CONTROL_ON F_LABEL_PAGE_NEXT</call_screen_fkeys_on_connected>";
    echo "<call_screen_fkeys_on_incoming perm='R'>F_LEFT F_RIGHT transfer(not:Transfer) F_OK F_DIAL(Transfer) F_LABEL_PAGE_NEXT</call_screen_fkeys_on_incoming>";
    echo "<executive_associated_call_screen_fkeys_on_connected perm='R'>F_LEFT F_RIGHT F_EXEC_ASSIST_CALL_PUSH transfer(not:Transfer) F_DUAL_AUDIO(not:Conference) F_LABEL_PAGE_NEXT</executive_associated_call_screen_fkeys_on_connected>";
    echo "<call_screen_fkeys_on_holding perm='R'>F_LEFT F_RIGHT F_DIAL(Transfer) F_HOLD transfer(not:Transfer) F_ABS F_LABEL_PAGE_NEXT</call_screen_fkeys_on_holding>";
    echo "<executive_associated_call_screen_fkeys_on_holding perm='R'>F_LEFT F_RIGHT F_CONF_ON(not:Transfer) F_DIAL(Transfer) F_EXEC_ASSIST_CALL_PUSH transfer(not:Transfer) F_ABS F_LABEL_PAGE_NEXT</executive_associated_call_screen_fkeys_on_holding>";
    echo "<fkeys_on_dialing perm='R'>F_DIALMODE(not:have_incoming_call) F_BACK F_DEFLECT(not:edit_for_transfer) F_ACCEPT_CALL(not:edit_for_transfer) F_DENY(have_incoming_call) F_SAFETRANSFER(edit_for_transfer) F_OK F_REDIAL(not:edit_for_transfer) F_ABS F_LABEL_PAGE_NEXT</fkeys_on_dialing>";
    echo "<block_url_dialing perm='R'>on</block_url_dialing>";
    echo "<locale perm='R'>de_DE</locale>";
    echo "<led_orange perm='R'>PhoneHasCallInStateRinging</led_orange>";
    echo "<led_call_indicator_usage perm='R'>PhoneHasCallInStateRinging PhoneHasCallInStateCalling PhoneHasCallInStateRingback PhoneHasCallInStateConnected PhoneHasCallInStateOffhook PhoneHasCallInStateHolding PhoneHasCall DateOngoing DateReminding</led_call_indicator_usage>";
    echo "<led_message_usage perm='R'>PhoneHasMissedCalls</led_message_usage>";
    echo "<mute_is_dnd_in_idle perm='R'>off</mute_is_dnd_in_idle>";
    for ($i = 2; $i <= 12; $i++) {
        echo "<user_active idx='" . $i . "' perm='R'>off</user_active>";
    }
}


function spbx_prov_emit_static_phone_settings($phone, $profile, $mac)
{
    $server = spbx_prov_server_addr();
    $baseUrl = spbx_prov_base_url();

    $sipuser = (string)($phone['endpoint_id'] ?: $phone['extension']);
    $extension = (string)($phone['extension'] ?: $sipuser);
    $pass = (string)($phone['auth_password'] ?? '');
    $callerid = (string)($phone['callerid'] ?? '');
    $adminPass = spbx_prov_setting('phone_admin_password', spbx_prov_setting('admin_pass', 'admin'));
    $settingServer = $baseUrl . '/provision/' . $profile['provision_file'] . '?mac={mac}';
    $phonebookUrl = $baseUrl . '/provision/phonebook.php';

    echo "<phone-settings e='2'>";
    echo "<branding_name perm='R'></branding_name>";
    echo "<custom_ca_cert_url perm='R'>" . spbx_prov_xml(spbx_prov_setting('custom_ca_cert_url', '')) . "</custom_ca_cert_url>";
    echo "<webserver_type>http_https</webserver_type>";
    echo "<webserver_admin_name perm='R'>admin</webserver_admin_name>";
    echo "<webserver_admin_password perm='R'>" . spbx_prov_xml($adminPass) . "</webserver_admin_password>";
    echo "<http_user perm='R'>admin</http_user>";
    echo "<http_pass perm='R'>" . spbx_prov_xml($adminPass) . "</http_pass>";
    echo "<admin_mode_login perm='R'>admin</admin_mode_login>";
    echo "<admin_mode_password perm='R'>" . spbx_prov_xml($adminPass) . "</admin_mode_password>";

    echo "<update_policy perm='R'>auto_update</update_policy>";
    // Legacy-Stand: keine automatisch generierte Firmware-URL senden.
    // Genau diese Generator-/Firmware-Magie war eine Fehlerquelle fuer Reboot-Loops.
    echo "<timezone perm='R'>AUT+1</timezone>";
    echo "<keyboard_lock perm='R'>off</keyboard_lock>";
    echo "<enable_keyboard_lock perm='R'>off</enable_keyboard_lock>";
    echo "<keyboard_lock_pw perm='R'>9999</keyboard_lock_pw>";
    echo "<keyboard_lock_timeout perm='R'>600</keyboard_lock_timeout>";
    echo "<eth_pc perm='R'>auto</eth_pc>";
    echo "<wifi_auth_mode perm='R'>off</wifi_auth_mode>";
    echo "<smartlabel_optimize_info_box perm='R'>on</smartlabel_optimize_info_box>";
    echo "<web_language perm='R'>Deutsch</web_language>";
    echo "<prov_polling_enabled perm='R'>off</prov_polling_enabled>";
    echo "<language perm='R'>Deutsch</language>";
    echo "<pnp_config perm='R'>off</pnp_config>";
    echo "<prov_polling_period perm='R'>0</prov_polling_period>";
    echo "<xml_notify perm='R'>on</xml_notify>";
    echo "<goto_monitor_state_on_line_activity perm='R'>off</goto_monitor_state_on_line_activity>";
    echo "<callpickup_dialoginfo perm='R'>on</callpickup_dialoginfo>";
    echo "<goto_virtual_key_state_on_activity perm='R'>off</goto_virtual_key_state_on_activity>";
    echo "<pickup_indication perm='R'>off</pickup_indication>";
    echo "<pui_icon_override_xml_icon perm='R'>off</pui_icon_override_xml_icon>";
    echo "<context_key_text perm=\"R\">on</context_key_text>";
    echo "<text_softkey perm='R'>on</text_softkey>";
    echo "<date_us_format perm='R'>off</date_us_format>";
    echo "<show_ivr_digits perm='R'>on</show_ivr_digits>";
    echo "<ignore_security_warning perm='R'>off</ignore_security_warning>";
    echo "<read_status perm='R'>on</read_status>";

    // Vorgabe SNOM Support: LED-Zustände für Ruf-/Pickup-/DND-/Statusanzeige.
    echo "<led_blink_fast perm='R'>early RINGING PICKUP call_center_status_exceed PhoneHasCallInStateRinging alerting_local alerting_remote</led_blink_fast>";
    spbx_prov_emit_deskphone_defaults();
    echo "<advertisement perm='R'>off</advertisement>";
    echo "<time_24_format perm='R'>on</time_24_format>";
    echo "<dialnumber_us_format perm='R'>off</dialnumber_us_format>";
    echo "<show_clock perm='R'>on</show_clock>";    echo "<phone_name perm='R'>phone-" . spbx_prov_xml($mac) . "</phone_name>";
    echo "<backlight perm='R'>15</backlight>";
    echo "<backlight_idle perm='R'>10</backlight_idle>";
    echo "<enable_backlight_dimming perm='R'>on</enable_backlight_dimming>";
    echo "<dim_timer perm='R'>20</dim_timer>";
    echo "<idle_status_btn_index perm='R'>-1</idle_status_btn_index>";
    echo "<setting_server perm='RW'>" . spbx_prov_xml($settingServer) . "</setting_server>";
    echo "<settings_refresh_timer perm='R'>0</settings_refresh_timer>";    echo "<display_method perm='R'>display_name_number</display_method>";
    echo "<publish_presence perm='R'>off</publish_presence>";
    echo "<show_call_status perm='R'>off</show_call_status>";
    echo "<tone_scheme perm='R'>AUT</tone_scheme>";
    echo "<headset_available_detect perm='R'>on</headset_available_detect>";

    echo "<dkey_transfer perm='R'>keyevent F_REDIRECT</dkey_transfer>";
    echo "<dkey_hold perm='R'>keyevent F_HOLD</dkey_hold>";
    echo "<dkey_redial perm='R'>keyevent F_REDIAL</dkey_redial>";
    echo "<dkey_conf perm='R'>keyevent F_CONFERENCE</dkey_conf>";
    echo "<dkey_retrieve perm='R'>keyevent F_MISSED_LIST</dkey_retrieve>";
    echo "<dkey_directory perm='R'>keyevent F_ADR_BOOK</dkey_directory>";
    echo "<context_key idx='0' perm='R'>keyevent F_DND</context_key>";
    echo "<context_key idx='1' perm='R'>keyevent F_CALL_LIST</context_key>";
    echo "<context_key idx='2' perm='R'>keyevent F_RINGER_SILENT</context_key>";
    echo "<context_key idx='3' perm='R'>keyevent F_LABEL_PAGE_NEXT</context_key>";
    echo "<idle_ok_key_action perm='R'>keyevent F_SETTINGS</idle_ok_key_action>";
    echo "<idle_up_key_action perm='R'></idle_up_key_action>";
    echo "<idle_left_key_action perm='R'>keyevent F_LABEL_PAGE_PREV</idle_left_key_action>";
    echo "<idle_right_key_action perm='R'>keyevent F_LABEL_PAGE_NEXT</idle_right_key_action>";
    echo "<idle_down_key_action perm='R'></idle_down_key_action>";


    echo "<user_active idx='1' perm='R'>on</user_active>";
    echo "<user_ringer idx='1' perm='!'>Ringer6</user_ringer>";
    echo "<user_outbound idx='1' perm='R'></user_outbound>";
    echo "<user_custom idx='1' perm='R'></user_custom>";
    echo "<user_mailbox idx='1' perm='R'>" . spbx_prov_xml($sipuser) . "</user_mailbox>";
    echo "<user_pname idx='1' perm='R'>" . spbx_prov_xml($sipuser) . "</user_pname>";
    echo "<user_pass idx='1' perm='R'>" . spbx_prov_xml($pass) . "</user_pass>";
    echo "<user_name idx='1' perm='R'>" . spbx_prov_xml($sipuser) . "</user_name>";
    echo "<user_realname idx='1' perm='R'>" . spbx_prov_primary_display_name($callerid) . "</user_realname>";
    echo "<user_idle_text idx='1' perm='R'>" . spbx_prov_primary_display_name($callerid) . "</user_idle_text>";
    echo "<user_idle_number idx='1' perm='R'>" . spbx_prov_xml($sipuser) . "</user_idle_number>";
    echo "<user_mailbox_retrieve idx='1' perm='R'>" . spbx_prov_xml($sipuser) . "</user_mailbox_retrieve>";
    echo "<user_host idx='1' perm='R'>" . spbx_prov_xml($server) . "</user_host>";

    echo "<gui_fkey_font_size perm='R'>12</gui_fkey_font_size>";
    echo "<extension_monitoring_group idx='1' perm='R'>1</extension_monitoring_group>";
    echo "<user_allow_inc_dialog_subscribe idx='1' perm='R'>on</user_allow_inc_dialog_subscribe>";
    echo "<monitor_notify_for_subscription_refresh idx='1' perm='R'>on</monitor_notify_for_subscription_refresh>";
    echo "<user_send_local_name idx='1' perm='R'>on</user_send_local_name>";
    echo "<user_presence_subscription idx='1' perm='R'>on</user_presence_subscription>";
    echo "<sip_force_sendrecv_on_invite_wo_sdp perm='R'>on</sip_force_sendrecv_on_invite_wo_sdp>";
    echo "<accept_event_talk_without_sdp idx='1' perm='R'>on</accept_event_talk_without_sdp>";
    echo "<user_server_type idx='1' perm='R'>Asterisk</user_server_type>";
    echo "<transfer_on_hangup perm='R'>on</transfer_on_hangup>";
    echo "<disconnect_on_onhook perm='R'>on</disconnect_on_onhook>";
    echo "<user_dtmf_info idx='1' perm='R'>sip_info_only</user_dtmf_info>";
    echo "<contact_source_priority perm='R'>Tbook</contact_source_priority>";
    echo "<guess_number perm='R'>on</guess_number>";
    echo "<guess_start_length perm='R'>1</guess_start_length>";
    echo "<eth_net perm='R'>auto</eth_net>";
    echo "<challenge_response perm='R'>on</challenge_response>";
    echo "<show_name_dialog perm='R'>off</show_name_dialog>";
    echo "<prioritise_asserted perm='R'>on</prioritise_asserted>";
    echo "<record_deflected_calls idx='1' perm='R'>off</record_deflected_calls>";
    echo "<disable_deflection perm='R'>off</disable_deflection>";
    echo "<watchdog perm='R'>off</watchdog>";
    echo "<scroll_outgoing perm='R'>on</scroll_outgoing>";
    echo "<global_missed_counter perm='R'>on</global_missed_counter>";
    echo "<logon_wizard perm='R'>off</logon_wizard>";
    echo "<partial_lookup perm='R'>off</partial_lookup>";
    echo "<redirect_ringing perm='R'>on</redirect_ringing>";
    echo "<show_local_line perm='R'>off</show_local_line>";
    echo "<show_redundant_context_keys perm='R'>on</show_redundant_context_keys>";
    echo "<with_flash perm='R'>off</with_flash>";
    echo "<idle_offhook perm='R'>on</idle_offhook>";
    echo "<quick_transfer perm='R'>new_call</quick_transfer>";
    echo "<transfer_dialing_on_other perm='R'>attended</transfer_dialing_on_other>";
    echo "<transfer_dialing_on_transfer perm='R'>attended</transfer_dialing_on_transfer>";
    echo "<smartlabel_idle_default perm='R'>full</smartlabel_idle_default>";
    echo "<smartlabel_call_default perm='R'>full</smartlabel_call_default>";
    echo "<smartlabel_call_expanded perm='R'>full</smartlabel_call_expanded>";
    echo "<smartlabel_dismiss_timeout perm='R'>5</smartlabel_dismiss_timeout>";
    echo "<label_scroll_timeout perm='R'>5</label_scroll_timeout>";

    echo "<call_states_when_knocking perm='R'>connected calling ringback</call_states_when_knocking>";
    echo "<call_join_xfer perm='R'>always</call_join_xfer>";
    echo "<disable_blind_transfer perm='R'>off</disable_blind_transfer>";
    echo "<mailbox_active perm='R'>on</mailbox_active>";
    echo "<cancel_on_hold perm='R'>on</cancel_on_hold>";
    echo "<offhook_dial_prompt perm='R'>on</offhook_dial_prompt>";
    echo "<cancel_desktop perm='R'>off</cancel_desktop>";
    echo "<ringer_headset_device perm='R'>headsetloud</ringer_headset_device>";
    echo "<overlap_dialing perm='R'>off</overlap_dialing>";
    echo "<speaker_dialer perm='R'>off</speaker_dialer>";
    echo "<speaker_receive_call perm='R'>on</speaker_receive_call>";

    echo "<ui_theme perm='R'>dark</ui_theme>";
echo "<custom_bg_image_url perm='R'></custom_bg_image_url>";
echo "<text_color perm='R'>244 244 244 255</text_color>";
echo "<titlebar_text_color perm='R'>244 244 244 255</titlebar_text_color>";
echo "<subtext_color perm='R'>174 183 188 255</subtext_color>";
echo "<extratext_color perm='R'>174 183 188 255</extratext_color>";
echo "<extratext2_color perm='R'>174 183 188 255</extratext2_color>";
echo "<background_color perm='R'>27 27 27 255</background_color>";
echo "<titlebar_background_color perm='R'>255 255 255 51</titlebar_background_color>";
echo "<fkey_background_color perm='R'>255 255 255 51</fkey_background_color>";
echo "<fkey_pressed_background_color perm='R'>255 92 0 255</fkey_pressed_background_color>";
echo "<fkey_separator_color perm='R'>15 22 29 255</fkey_separator_color>";
echo "<fkey_label_color perm='R'>244 244 244 255</fkey_label_color>";
echo "<fkey_pressed_label_color perm='R'>242 242 242 255</fkey_pressed_label_color>";
echo "<line_background_color perm='R'>0 0 0 0</line_background_color>";
echo "<selected_line_background_color perm='R'>0 0 0 0</selected_line_background_color>";
echo "<selected_line_indicator_color perm='R'>255 92 0 255</selected_line_indicator_color>";
echo "<selected_line_text_color perm='R'>255 92 0 255</selected_line_text_color>";
echo "<line_separator_color perm='R'>0 0 0 255</line_separator_color>";
echo "<scrollbar_color perm='R'>255 92 0 255</scrollbar_color>";
echo "<cursor_color perm='R'>255 92 0 255</cursor_color>";
echo "<status_msgs_background_color perm='R'>0 0 0 0</status_msgs_background_color>";
echo "<status_msgs_border_color perm='R'>255 92 0 255</status_msgs_border_color>";
echo "<smartlabel_background_color perm='R'>0 0 0 0</smartlabel_background_color>";
echo "<smartlabel_label_color perm='R'>224 224 224 255</smartlabel_label_color>";
echo "<smartlabel_pressed_background_color perm='R'>50 205 50</smartlabel_pressed_background_color>";
echo "<smartlabel_separator_color perm='R'>30 36 43 0</smartlabel_separator_color>";
echo "<smartlabel_pressed_label_color perm='R'>242 242 242 255</smartlabel_pressed_label_color>";
echo "<expansion_module_background_color perm='R'>30 36 43 255</expansion_module_background_color>";
echo "<expansion_module_pressed_background_color perm='R'>50 205 50</expansion_module_pressed_background_color>";
echo "<expansion_module_separator_color perm='R'>70 70 70</expansion_module_separator_color>";
echo "<expansion_module_maintext_color perm='R'>244 244 244</expansion_module_maintext_color>";
echo "<icon_color perm='R'>31 41 55 255</icon_color>";
    echo "<icon_fg_color perm='R'>31 41 55 255</icon_fg_color>";
    echo "<smartlabel_icon_color perm='R'>31 41 55 255</smartlabel_icon_color>";
    echo "<smartlabel_icon_fg_color perm='R'>31 41 55 255</smartlabel_icon_fg_color>";

    echo "<cancel_missed perm='R'>on</cancel_missed>";
    echo "<label_contrast perm='R'>10</label_contrast>";
    echo "<fkey_more_auto_assign_enable perm='R'>off</fkey_more_auto_assign_enable>";
    echo "<transfer_on_hangup_non_pots perm='R'>on</transfer_on_hangup_non_pots>";
    echo "<transfer_on_hangup_with_starcode perm='R'>on</transfer_on_hangup_with_starcode>";
    echo "<disable_speaker perm='R'>off</disable_speaker>";
    echo "<record_dialed_calls idx='1' perm='R'>on</record_dialed_calls>";
    echo "<record_received_calls idx='1' perm='R'>on</record_received_calls>";
    echo "<record_missed_calls idx='1' perm='R'>on</record_missed_calls>";
    echo "<user_failover_identity idx='1' perm='R'>none</user_failover_identity>";
    echo "<cache_sip_authorization idx='1' perm='R'>off</cache_sip_authorization>";
    echo "<support_rtcp perm='R'>off</support_rtcp>";
    echo "<contact_source_sip_priority idx='1' perm='R'>RPID</contact_source_sip_priority>";
    echo "<user_agent_string perm='R'>!!$(::)!!$(firmware_version)</user_agent_string>";
    echo "<web_user_agent_string perm='R'> </web_user_agent_string>";

    echo "<smartlabel_hide_disabled_page perm='R'>on</smartlabel_hide_disabled_page>";
    echo "<fkey_next_page_dynamic perm='R'>on</fkey_next_page_dynamic>";
}



function spbx_prov_emit_snom_tbook()
{
    $db = spbx_db();

    echo "<tbook complete='true' e='2'>";

    $tableRes = $db->query("SHOW TABLES LIKE 'spbx_phonebook'");
    if (!$tableRes || $tableRes->num_rows < 1) {
        echo "</tbook>";
        return;
    }

    $columns = [];
    $colRes = $db->query("SHOW COLUMNS FROM spbx_phonebook");
    if ($colRes) {
        while ($col = $colRes->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
    }

    $firstCol = in_array('first_name', $columns, true) ? 'first_name' : (in_array('firstname', $columns, true) ? 'firstname' : '');
    $lastCol  = in_array('last_name', $columns, true) ? 'last_name' : (in_array('lastname', $columns, true) ? 'lastname' : '');
    $numCol   = in_array('number', $columns, true) ? 'number' : (in_array('phone_number', $columns, true) ? 'phone_number' : '');
    $typeCol  = in_array('type', $columns, true) ? 'type' : '';
    $activeCol = in_array('active', $columns, true) ? 'active' : '';

    if ($numCol === '') {
        echo "</tbook>";
        return;
    }

    $where = $activeCol !== '' ? " WHERE `$activeCol`=1 " : "";

    $order = [];
    if ($lastCol !== '') {
        $order[] = "`$lastCol`";
    }
    if ($firstCol !== '') {
        $order[] = "`$firstCol`";
    }
    $order[] = "`$numCol`";

    $sql = "SELECT * FROM spbx_phonebook" . $where . " ORDER BY " . implode(", ", $order);
    $res = $db->query($sql);

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $firstname = $firstCol !== '' ? (string)($row[$firstCol] ?? '') : '';
            $lastname  = $lastCol !== '' ? (string)($row[$lastCol] ?? '') : '';
            $number    = (string)($row[$numCol] ?? '');
            $type      = $typeCol !== '' ? (string)($row[$typeCol] ?? 'office') : 'office';

            if (trim($number) === '') {
                continue;
            }

            if (trim($type) === '') {
                $type = 'office';
            }

            echo "<item context='active' type='" . spbx_prov_xml($type) . "'>";
            echo "<first_name>" . spbx_prov_xml($firstname) . "</first_name>";
            echo "<last_name>" . spbx_prov_xml($lastname) . "</last_name>";
            echo "<number>" . spbx_prov_xml($number) . "</number>";
            echo "</item>";
        }
    }

    echo "</tbook>";
}


function spbx_prov_emit_uploads()
{
    $baseUrl = spbx_prov_base_url();

    echo "<uploads>";
    echo "<file url='" . spbx_prov_xml($baseUrl . "/provision/identity.xml") . "' type='gui_xml_state_settings_identity' />";
    echo "<file url='" . spbx_prov_xml($baseUrl . "/provision/information.xml") . "' type='gui_xml_state_settings_information' />";
    echo "<file url='" . spbx_prov_xml($baseUrl . "/provision/maintenance.xml") . "' type='gui_xml_state_settings_maintenance' />";
    echo "<file url='" . spbx_prov_xml($baseUrl . "/provision/preferences.xml") . "' type='gui_xml_state_settings_preferences' />";
    echo "<file url='" . spbx_prov_xml($baseUrl . "/provision/adressbook.xml") . "' type='gui_xml_state_adressbook' />";
    echo "<file url='" . spbx_prov_xml($baseUrl . "/provision/state_settings.xml") . "' type='gui_xml_state_settings' />";
    echo "</uploads>";
}


function spbx_prov_emit_static_fkeys($phone, $profile)
{
    $max = (int)$profile['max_fkeys'];
    $server = spbx_prov_server_addr();
    $keys = spbx_prov_load_static_keys($phone, $max);

    for ($i = 0; $i < $max; $i++) {
        echo "<fkey_longpress idx='" . $i . "' perm='R'>off</fkey_longpress>";
    }

    for ($i = 0; $i < $max; $i++) {
        $actionValue = spbx_prov_snom_key_action_value($keys[$i], $server);
        $label = spbx_prov_key_label($keys[$i]);

        echo "<fkey idx='" . $i . "' context='active' perm='R'>" . spbx_prov_xml($actionValue) . "</fkey>";
        echo "<fkey_label idx='" . $i . "' perm='R'>" . spbx_prov_xml_blf_label($label) . "</fkey_label>";
    }

    echo "</phone-settings>";
}


function spbx_prov_handle_snom($expectedModel)
{
    $profile = spbx_prov_model_profile($expectedModel);
    if (!$profile) {
        spbx_prov_error('Unbekanntes Telefonmodell.');
    }

    $mac = spbx_prov_clean_mac($_GET['mac'] ?? ($_GET['MAC'] ?? '000000000000'));
    $phone = spbx_prov_load_phone($mac, $expectedModel);

    if (!$phone) {
        spbx_prov_error('Kein ' . $profile['label'] . ' mit MAC ' . $mac . ' gefunden.');
    }

    if ((int)($phone['endpoint_active'] ?? 0) !== 1 || (int)($phone['device_active'] ?? 0) !== 1) {
        spbx_prov_error('Gerät oder Endpoint ist deaktiviert.');
    }

    if ((int)($phone['provisioning_enabled'] ?? 0) !== 1) {
        spbx_prov_error('Provisioning ist für dieses Gerät deaktiviert.');
    }

    if (empty($phone['auth_password'])) {
        spbx_prov_error('SIP Passwort fehlt.');
    }

    spbx_prov_xml_header();

    echo '<?xml version="1.0" standalone="yes"?>';
    echo "<settings>";

    spbx_prov_emit_static_phone_settings($phone, $profile, $mac);
    spbx_prov_emit_static_fkeys($phone, $profile);
    spbx_prov_emit_snom_tbook();
    spbx_prov_emit_uploads();
    echo "</settings>";
}
?>
