<?php
require_once __DIR__ . '/../inc/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 'Off');

header('Content-Type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';

function sx($value)
{
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function setting_value($key, $default = '')
{
    $db = spbx_db();
    $stmt = $db->prepare("SELECT setting_value FROM spbx_settings WHERE setting_key=? LIMIT 1");
    if (!$stmt) return $default;
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? (string)$row['setting_value'] : $default;
}

function dect_mac($value)
{
    return strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', (string)$value));
}

$db = spbx_db();
$mac = isset($_GET['mac']) ? dect_mac($_GET['mac']) : '000000000000';
$server = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
$dectIp = $_SERVER['REMOTE_ADDR'] ?? '';

$stmt = $db->prepare("UPDATE spbx_dect_bases SET ip_address=? WHERE mac_address=? AND base_type='snomM400'");
if ($stmt) {
    $stmt->bind_param('ss', $dectIp, $mac);
    $stmt->execute();
}

$base = null;
$stmt = $db->prepare("SELECT * FROM spbx_dect_bases WHERE mac_address=? AND base_type='snomM400' AND active=1 LIMIT 1");
if ($stmt) {
    $stmt->bind_param('s', $mac);
    $stmt->execute();
    $base = $stmt->get_result()->fetch_assoc();
}

$adminPass = setting_value('snom_m400_admin_pass', setting_value('default_phone_admin_password', 'admin'));
$dectFw = setting_value('snom_m400_fw', '0790');
$dectBranch = setting_value('snom_m400_branch', '0200');

$slots = [];
for ($i = 1; $i <= 20; $i++) {
    $slots[$i] = [
        'active' => 'off',
        'user' => '',
        'realname' => '',
        'pass' => '',
        'ipei' => '0xFFFFFFFFFF',
    ];
}

if ($base) {
    $baseId = (int)$base['id'];
    $res = $db->query("
        SELECT
            h.idx_number,
            e.endpoint_id,
            e.extension,
            e.display_name,
            d.ipei,
            p.callerid,
            a.password
        FROM spbx_dect_base_handsets h
        JOIN spbx_extensions e ON e.endpoint_id=h.endpoint_id
        JOIN ps_endpoints p ON p.id=e.endpoint_id
        LEFT JOIN spbx_devices d ON d.endpoint_id=e.endpoint_id
        LEFT JOIN ps_auths a ON a.id=e.endpoint_id
        WHERE h.base_id=" . $baseId . "
          AND h.idx_number BETWEEN 1 AND 20
          AND COALESCE(e.active, p.active)=1
        ORDER BY h.idx_number ASC
    ");

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $idx = (int)$row['idx_number'];
            $ipei = trim((string)($row['ipei'] ?? ''));
            if ($idx < 1 || $idx > 20 || $ipei === '') {
                continue;
            }

            $slots[$idx] = [
                'active' => 'on',
                'user' => (string)$row['extension'],
                'realname' => (string)(($row['callerid'] ?? '') ?: ($row['display_name'] ?? $row['extension'])),
                'pass' => (string)($row['password'] ?? ''),
                'ipei' => $ipei,
            ];
        }
    }
}

echo '<settings>';
echo '<global>';
echo "<user_agent_id perm='!'></user_agent_id>";
echo '<allow_call_groups>off</allow_call_groups>';
echo '<ac_code>0000</ac_code>';
echo '<country_region_id>0</country_region_id>';
echo '<tone_scheme>GER</tone_scheme>';
echo '<dialplan_enabled>off</dialplan_enabled>';
echo '<timezone>AT+1</timezone>';
echo '<language>Deutsch</language>';
echo '<web_language>Deutsch</web_language>';
echo '<number_of_base_stations>3</number_of_base_stations>';
echo '<timezone_by_country_region>on</timezone_by_country_region>';
echo '<auto_dect_register>on</auto_dect_register>';
echo '<auto_resync_days>0</auto_resync_days>';
echo '<auto_resync_max_delay>15</auto_resync_max_delay>';
echo '<auto_resync_period>0</auto_resync_period>';
echo '<auto_resync_polling>disabled</auto_resync_polling>';
echo '<auto_resync_time>0</auto_resync_time>';
echo '<central_dir_lookup_disable>off</central_dir_lookup_disable>';
echo '<tls_server_authentication>off</tls_server_authentication>';
echo '<cloud_service_log_level>1</cloud_service_log_level>';
echo '<custom_location_identifier></custom_location_identifier>';
echo '<disable_http_server>disabled</disable_http_server>';
echo '<eth_driver_initialize>off</eth_driver_initialize>';
echo '<fwu_tftp_server_image_path></fwu_tftp_server_image_path>';
echo '<text_msg_terminal_auto_stop_allow>off</text_msg_terminal_auto_stop_allow>';
echo '<text_msg_terminal_auto_stop_delay>30</text_msg_terminal_auto_stop_delay>';
echo '<text_msg_terminal_keep_alive>0</text_msg_terminal_keep_alive>';
echo '<http_user>admin</http_user>';
echo '<http_pass>' . sx($adminPass) . '</http_pass>';
echo '<http_basic_user>User</http_basic_user>';
echo '<http_basic_pass>' . sx($adminPass) . '</http_basic_pass>';
echo '<http_engineer_user>Engineer</http_engineer_user>';
echo '<http_engineer_pass>' . sx($adminPass) . '</http_engineer_pass>';
echo '<secure_web>on</secure_web>';
echo '<phone_name>dect-' . sx($mac) . '</phone_name>';
echo '<log_last_config>disabled</log_last_config>';
echo '<http_client_password>' . sx($adminPass) . '</http_client_password>';
echo '<management_transfer_protocol>http</management_transfer_protocol>';
echo '<management_upload_script>/CfgUpload</management_upload_script>';
echo '<http_client_user>admin</http_client_user>';
echo '<mdns_support>off</mdns_support>';
echo '<mqtt_broker_address></mqtt_broker_address>';
echo '<mqtt_broker_port>1883</mqtt_broker_port>';
echo '<mqtt_change_settings_via_cloud>1</mqtt_change_settings_via_cloud>';
echo '<mqtt_change_settings_via_cloud_timestamp>0</mqtt_change_settings_via_cloud_timestamp>';
echo '<mqtt_connection_keep_alive>60</mqtt_connection_keep_alive>';
echo '<mqtt_site_id></mqtt_site_id>';
echo '<setting_server>http://' . sx($server) . '/provision/snomM400.php?mac={mac}</setting_server>';
echo '<network_sntp_broadcast_enable>on</network_sntp_broadcast_enable>';
echo '<ntp_server>' . sx($server) . '</ntp_server>';
echo '<ntp_refresh_timer>3600</ntp_refresh_timer>';
echo '<stun_server></stun_server>';
echo '<vlan_id>0</vlan_id>';
echo '<network_vlan_synchronization>on</network_vlan_synchronization>';
echo '<vlan_qos>0</vlan_qos>';
echo '<dhcp_option_pnp>on</dhcp_option_pnp>';
echo '<dhcp>on</dhcp>';
echo '<phonebook_filename>dectdirectory.php</phonebook_filename>';
echo '<phonebook_location>http://' . sx($server) . '/provision</phonebook_location>';
echo '<phonebook_reload_time>6000</phonebook_reload_time>';
echo '<phonebook_server_location>0</phonebook_server_location>';
echo '<repeater_legacy_support>on</repeater_legacy_support>';
echo '<rsx_trace_internal>disabled</rsx_trace_internal>';
echo '<rtp_collision_control>off</rtp_collision_control>';
echo '<sip_check_sync_always_reboot>off</sip_check_sync_always_reboot>';
echo '<sip_conf_key_dtmf_string></sip_conf_key_dtmf_string>';
echo '<network_failover_sip_timer_reconnect>60</network_failover_sip_timer_reconnect>';
echo '<sip_outbound_proxy_mode>always</sip_outbound_proxy_mode>';
echo '<pnp_config>on</pnp_config>';
echo '<enable_rport_rfc3581>on</enable_rport_rfc3581>';
echo '<rtp_port_start>50004</rtp_port_start>';
echo '<rtp_port_end>50257</rtp_port_end>';
echo '<codec_tos>160</codec_tos>';
echo '<sip_r_key_dtmf_string></sip_r_key_dtmf_string>';
echo '<network_id_port>5061</network_id_port>';
echo '<signaling_tos>160</signaling_tos>';
echo '<sip_stun_bindtime_determine>on</sip_stun_bindtime_determine>';
echo '<sip_stun_bindtime_guard>80</sip_stun_bindtime_guard>';
echo '<stun_binding_interval>90</stun_binding_interval>';
echo '<network_failover_sip_timer_b>5</network_failover_sip_timer_b>';
echo '<network_failover_sip_timer_f>5</network_failover_sip_timer_f>';
echo '<sip_use_different_ports>off</sip_use_different_ports>';
echo '<srv_xsi_caller_id_blocking>enabled</srv_xsi_caller_id_blocking>';
echo '<log_level>6</log_level>';
echo '<text_msg_keep_alive>30</text_msg_keep_alive>';
echo '<text_msg_mode>disabled</text_msg_mode>';
echo '<text_msg_port>1900</text_msg_port>';
echo '<text_msg_responce_time>30</text_msg_responce_time>';
echo '<text_msg_server></text_msg_server>';
echo '<text_msg_ttl>0</text_msg_ttl>';
echo '<voip_sip_auto_upload>off</voip_sip_auto_upload>';
echo '<web_inputs_allowed>on</web_inputs_allowed>';
echo '<xml_minibrowser_add_info_to_url>0</xml_minibrowser_add_info_to_url>';
echo '</global>';

echo '<server>';
echo '<srv_att_transfer_2nd_call_on_hold idx="1">on</srv_att_transfer_2nd_call_on_hold>';
echo '<dial_plan_subscription idx="1">1</dial_plan_subscription>';
echo '<srv_dtmf_payload_type idx="1">101</srv_dtmf_payload_type>';
echo '<user_dtmf_info idx="1">sip_info</user_dtmf_info>';
echo '<srv_confirm_on_transfer_enable idx="1">enabled</srv_confirm_on_transfer_enable>';
echo '<srv_failover_sip_deregister_after_failback idx="1">disabled</srv_failover_sip_deregister_after_failback>';
echo '<alert_info_playback idx="1">on</alert_info_playback>';
echo '<user_srtp idx="1">disabled</user_srtp>';
echo '<semi_attend_transfer idx="1">enabled</semi_attend_transfer>';
echo '<srv_sip_cli_mode idx="1">0</srv_sip_cli_mode>';
echo '<srv_sip_enable_blind_transfer idx="1">on</srv_sip_enable_blind_transfer>';
echo '<timer_support idx="1">on</timer_support>';
echo '<user_hold_inactive idx="1">off</user_hold_inactive>';
echo '<keepalive_interval idx="1">on</keepalive_interval>';
echo '<user_moh idx="1"></user_moh>';
echo '<srv_sip_rtp_base_equal idx="1">disabled</srv_sip_rtp_base_equal>';
echo '<codec_size idx="1">20</codec_size>';
echo '<srv_sip_server_alias idx="1">PBX</srv_sip_server_alias>';
echo '<session_timer idx="1">3600</session_timer>';
echo '<srv_sip_show_ext_name_in_hs idx="1">on</srv_sip_show_ext_name_in_hs>';
echo '<srv_sip_signal_tcp_port idx="1">on</srv_sip_signal_tcp_port>';
echo '<srv_sip_transport idx="1">udp</srv_sip_transport>';
echo '<codec_priority_list idx="1">pcmu, pcma, g726, g722, g729</codec_priority_list>';
echo '<conferencing idx="1"></conferencing>';
echo '<user_host idx="1">' . sx($server) . '</user_host>';
echo '<user_outbound idx="1">' . sx($server) . '</user_outbound>';
echo '<user_expiry idx="1">3600</user_expiry>';
echo '<srv_sip_ua_data_server_nat_adaption idx="1">enabled</srv_sip_ua_data_server_nat_adaption>';
echo '<srv_sip_use_one_tcp_conn_per_ext idx="1">off</srv_sip_use_one_tcp_conn_per_ext>';
echo '<user_full_sdp_answer idx="1">off</user_full_sdp_answer>';
echo '<srv_srtp_auth idx="1">on</srv_srtp_auth>';
echo '<user_auth_tag idx="1">on</user_auth_tag>';
echo '<srv_use_sip_for_xsi_login idx="1">off</srv_use_sip_for_xsi_login>';
echo '</server>';

echo '<extension>';
for ($i = 1; $i <= 20; $i++) {
    $s = $slots[$i];
    echo '<user_active idx="' . $i . '">' . sx($s['active']) . '</user_active>';
    echo '<user_name idx="' . $i . '">' . sx($s['user']) . '</user_name>';
    echo '<user_realname idx="' . $i . '">' . sx($s['realname']) . '</user_realname>';
    echo '<user_pname idx="' . $i . '">' . sx($s['user']) . '</user_pname>';
    echo '<user_pass idx="' . $i . '">' . sx($s['pass']) . '</user_pass>';
    echo '<subscr_sip_hs_idx idx="' . $i . '">' . $i . '</subscr_sip_hs_idx>';
    echo '<subscr_dect_ipui idx="' . $i . '">' . sx($s['ipei']) . '</subscr_dect_ipui>';
    echo '<subscr_sip_ua_data_server_id idx="' . $i . '">1</subscr_sip_ua_data_server_id>';
}
echo '</extension>';

echo '<firmware-settings>';
echo '<fp_fwu_sw_version>' . sx($dectFw) . '</fp_fwu_sw_version>';
echo '<fp_fwu_branch_version>' . sx($dectBranch) . '</fp_fwu_branch_version>';
echo '<firmware>https://dect.snom.com</firmware>';
foreach (['M30','M65','M70','M80','M85','M90'] as $type) {
    echo '<pp_fwu_sw_version type="' . $type . '">' . sx($dectFw) . '</pp_fwu_sw_version>';
    echo '<pp_fwu_branch_version type="' . $type . '">' . sx($dectBranch) . '</pp_fwu_branch_version>';
}
echo '</firmware-settings>';
echo '</settings>';
?>