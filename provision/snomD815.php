<?php
ini_set('display_errors','Off');
echo '<?xml version="1.0" standalone="yes"?>';
header("Content-type: text/xml; charset=utf-8");
require_once __DIR__ . '/../inc/db.php';
$verbindung = spbx_db();
if(!isset($_GET['mac'])) { $mac = '000000000000'; } else { $mac = $_GET['mac']; }
$server = $_SERVER['SERVER_ADDR'];
$ausgabe = "SELECT * FROM deskphones WHERE `mac` = '$mac'";
$ergebnis = mysqli_query($verbindung,$ausgabe);
while($row = mysqli_fetch_array($ergebnis)) {
$sipuser = $row['sipuser'];
$fkey0action = $row['fkey0action'];
$fkey1action = $row['fkey1action'];
$fkey2action = $row['fkey2action'];
$fkey3action = $row['fkey3action'];
$fkey4action = $row['fkey4action'];
$fkey5action = $row['fkey5action'];
$fkey6action = $row['fkey6action'];
$fkey7action = $row['fkey7action'];
$fkey8action = $row['fkey8action'];
$fkey9action = $row['fkey9action'];
$fkey10action = $row['fkey10action'];
$fkey11action = $row['fkey11action'];
$fkey12action = $row['fkey12action'];
$fkey13action = $row['fkey13action'];
$fkey14action = $row['fkey14action'];
$fkey15action = $row['fkey15action'];
$fkey16action = $row['fkey16action'];
$fkey17action = $row['fkey17action'];
$fkey18action = $row['fkey18action'];
$fkey19action = $row['fkey19action'];
$fkey20action = $row['fkey20action'];
$fkey21action = $row['fkey21action'];
$fkey22action = $row['fkey22action'];
$fkey23action = $row['fkey23action'];
$fkey24action = $row['fkey24action'];
$fkey25action = $row['fkey25action'];
$fkey26action = $row['fkey26action'];
$fkey27action = $row['fkey27action'];
$fkey28action = $row['fkey28action'];
$fkey29action = $row['fkey29action'];
$fkey30action = $row['fkey30action'];
$fkey31action = $row['fkey31action'];
$fkey32action = $row['fkey32action'];
$fkey33action = $row['fkey33action'];
$fkey34action = $row['fkey34action'];
$fkey35action = $row['fkey35action'];
$fkey36action = $row['fkey36action'];
$fkey37action = $row['fkey37action'];
$fkey38action = $row['fkey38action'];
$fkey39action = $row['fkey39action'];

$fkey0label = $row['fkey0label'];
$fkey1label = $row['fkey1label'];
$fkey2label = $row['fkey2label'];
$fkey3label = $row['fkey3label'];
$fkey4label = $row['fkey4label'];
$fkey5label = $row['fkey5label'];
$fkey6label = $row['fkey6label'];
$fkey7label = $row['fkey7label'];
$fkey8label = $row['fkey8label'];
$fkey9label = $row['fkey9label'];
$fkey10label = $row['fkey10label'];
$fkey11label = $row['fkey11label'];
$fkey12label = $row['fkey12label'];
$fkey13label = $row['fkey13label'];
$fkey14label = $row['fkey14label'];
$fkey15label = $row['fkey15label'];
$fkey16label = $row['fkey16label'];
$fkey17label = $row['fkey17label'];
$fkey18label = $row['fkey18label'];
$fkey19label = $row['fkey19label'];
$fkey20label = $row['fkey20label'];
$fkey21label = $row['fkey21label'];
$fkey22label = $row['fkey22label'];
$fkey23label = $row['fkey23label'];
$fkey24label = $row['fkey24label'];
$fkey25label = $row['fkey25label'];
$fkey26label = $row['fkey26label'];
$fkey27label = $row['fkey27label'];
$fkey28label = $row['fkey28label'];
$fkey29label = $row['fkey29label'];
$fkey30label = $row['fkey30label'];
$fkey31label = $row['fkey31label'];
$fkey32label = $row['fkey32label'];
$fkey33label = $row['fkey33label'];
$fkey34label = $row['fkey34label'];
$fkey35label = $row['fkey35label'];
$fkey36label = $row['fkey36label'];
$fkey37label = $row['fkey37label'];
$fkey38label = $row['fkey38label'];
$fkey39label = $row['fkey39label'];

$fkey0value = $row['fkey0value'];
$fkey1value = $row['fkey1value'];
$fkey2value = $row['fkey2value'];
$fkey3value = $row['fkey3value'];
$fkey4value = $row['fkey4value'];
$fkey5value = $row['fkey5value'];
$fkey6value = $row['fkey6value'];
$fkey7value = $row['fkey7value'];
$fkey8value = $row['fkey8value'];
$fkey9value = $row['fkey9value'];
$fkey10value = $row['fkey10value'];
$fkey11value = $row['fkey11value'];
$fkey12value = $row['fkey12value'];
$fkey13value = $row['fkey13value'];
$fkey14value = $row['fkey14value'];
$fkey15value = $row['fkey15value'];
$fkey16value = $row['fkey16value'];
$fkey17value = $row['fkey17value'];
$fkey18value = $row['fkey18value'];
$fkey19value = $row['fkey19value'];
$fkey20value = $row['fkey20value'];
$fkey21value = $row['fkey21value'];
$fkey22value = $row['fkey22value'];
$fkey23value = $row['fkey23value'];
$fkey24value = $row['fkey24value'];
$fkey25value = $row['fkey25value'];
$fkey26value = $row['fkey26value'];
$fkey27value = $row['fkey27value'];
$fkey28value = $row['fkey28value'];
$fkey29value = $row['fkey29value'];
$fkey30value = $row['fkey30value'];
$fkey31value = $row['fkey31value'];
$fkey32value = $row['fkey32value'];
$fkey33value = $row['fkey33value'];
$fkey34value = $row['fkey34value'];
$fkey35value = $row['fkey35value'];
$fkey36value = $row['fkey36value'];
$fkey37value = $row['fkey37value'];
$fkey38value = $row['fkey38value'];
$fkey39value = $row['fkey39value'];
}
                $ausgabe = "SELECT * FROM ps_auths WHERE id = '$sipuser'";
                $ergebnis = mysqli_query($verbindung,$ausgabe);
                while($row = mysqli_fetch_array($ergebnis)) {
                $pass = $row['password'];
}
                $ausgabe = "SELECT * FROM ps_endpoints WHERE id = '$sipuser'";
                $ergebnis = mysqli_query($verbindung,$ausgabe);
                while($row = mysqli_fetch_array($ergebnis)) {
                $callerid = $row['callerid'];
}

$admin_pass = 'admin';
$firmware = '';
$uxmfw = '';
echo "<settings>";
echo "<phone-settings e='2'>";
echo "<branding_name perm='R'></branding_name>";
echo "<custom_ca_cert_url perm='R'>https://phonesystem.rvits.at/keys</custom_ca_cert_url>";
echo "<webserver_type>http_https</webserver_type>";
echo "<webserver_admin_name perm='R'>admin</webserver_admin_name>";
echo "<webserver_admin_password perm='R'>".$admin_pass."</webserver_admin_password>";
echo "<http_user perm='R'>admin</http_user>";
echo "<http_pass perm='R'>".$admin_pass."</http_pass>";
echo "<admin_mode_login perm='R'>admin</admin_mode_login>";
echo "<admin_mode_password perm='R'>".$admin_pass."</admin_mode_password>";
echo "<update_policy perm='R'>settings_only</update_policy>";
echo "<timezone perm='R'>AUT+1</timezone>";
echo "<keyboard_lock perm='R'>off</keyboard_lock>";
echo "<enable_keyboard_lock perm='R'>off</enable_keyboard_lock>";
echo "<keyboard_lock_pw perm='R'>9999</keyboard_lock_pw>";
echo "<keyboard_lock_timeout perm='R'>600</keyboard_lock_timeout>";
echo "<eth_pc perm='R'>auto</eth_pc>";
echo "<dhcp perm='R'>on</dhcp>";
echo "<wifi_auth_mode perm='!'>off</wifi_auth_mode>";
echo "<smartlabel_optimize_info_box perm='R'>on</smartlabel_optimize_info_box>";
echo "<web_language perm='!'>Deutsch</web_language>";
echo "<prov_polling_enabled perm='R'>off</prov_polling_enabled>";
echo "<language perm='!'>Deutsch</language>";
echo "<pnp_config perm='!'>off</pnp_config>";
echo "<prov_polling_period perm='R'>0</prov_polling_period>";
echo "<xml_notify perm='R'>on</xml_notify>";
echo "<goto_monitor_state_on_line_activity perm='R'>off</goto_monitor_state_on_line_activity>";
echo "<callpickup_dialoginfo perm='R'>on</callpickup_dialoginfo>";
echo "<goto_virtual_key_state_on_activity perm='R'>off</goto_virtual_key_state_on_activity>";
echo "<pickup_indication perm='R'>off</pickup_indication>";
echo "<pui_icon_override_xml_icon perm='R'>off</pui_icon_override_xml_icon>";
echo "<context_key_text perm='R'>on</context_key_text>";
echo "<text_softkey perm='R'>off</text_softkey>";
echo "<date_us_format perm='R'>off</date_us_format>";
echo "<show_ivr_digits perm='R'>on</show_ivr_digits>";
echo "<ignore_security_warning perm='R'>off</ignore_security_warning>";
echo "<read_status perm='R'>on</read_status>";
echo "<advertisement perm='R'>off</advertisement>";
echo "<time_24_format perm='R'>on</time_24_format>";
echo "<dialnumber_us_format perm='R'>off</dialnumber_us_format>";
echo "<show_clock perm='R'>on</show_clock>";
echo "<ntp_server perm='R'>$server</ntp_server>";
echo "<network_id_port perm='R'></network_id_port>";
echo "<phone_name perm='R'>phone-".$mac."</phone_name>";
echo "<context_key_text perm='R'>on</context_key_text>";
echo "<backlight perm='R'>15</backlight>";
echo "<backlight_idle perm='R'>10</backlight_idle>";
echo "<enable_backlight_dimming perm='R'>on</enable_backlight_dimming>";
echo "<dim_timer perm='R'>20</dim_timer>";
echo "<idle_status_btn_index perm='R'>-1</idle_status_btn_index>";
echo "<setting_server perm='RW'>http://$server/provision/snomD815.php?mac={mac}</setting_server>";
echo "<settings_refresh_timer perm='R'>0</settings_refresh_timer>";
echo "<ethernet_detect perm='R'>on</ethernet_detect>";
echo "<ethernet_replug perm='R'>reregister</ethernet_replug>";
echo "<display_method perm='R'>display_name_number</display_method>";
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
################################### ACCOUNT SETTINGS ##########################################################
echo "<user_active idx='1' perm='R'>on</user_active>";
echo "<user_ringer idx='1' perm='!'>Ringer6</user_ringer>";
echo "<user_outbound idx='1' perm='R'></user_outbound>";
echo "<user_custom idx='1' perm='R'></user_custom>";
echo "<user_mailbox idx='1' perm='R'>".$sipuser."</user_mailbox>";
echo "<user_pname idx='1' perm='R'>".$sipuser."</user_pname>";
echo "<user_pass idx='1' perm='R'>".$pass."</user_pass>";
echo "<user_name idx='1' perm='R'>".$sipuser."</user_name>";
echo "<user_realname idx='1' perm='R'>".$callerid."</user_realname>";
echo "<user_idle_text idx='1' perm='R'>".$callerid."</user_idle_text>";
echo "<user_idle_number idx='1' perm='R'>".$sipuser."</user_idle_number>";
echo "<user_mailbox_retrieve idx='1' perm='R'>".$sipuser."</user_mailbox_retrieve>";
echo "<user_host idx='1' perm='R'>$server</user_host>";
################################################################################################################
echo "<gui_fkey_font_size perm='R'>12</gui_fkey_font_size>";
echo "<extension_monitoring_group idx='1' perm='R'>1</extension_monitoring_group>";
echo "<user_allow_inc_dialog_subscribe idx='1' perm='R'>on</user_allow_inc_dialog_subscribe>";
echo "<monitor_notify_for_subscription_refresh idx='1' perm='R'>on</monitor_notify_for_subscription_refresh>";
echo "<user_send_local_name idx='1' perm='1'>on</user_send_local_name>";
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
########################################################################################
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
############################# Farbgebung ###########################################################
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
##################################################################################################
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
echo "<label_scroll_timeout perm='R'>5</label_scroll_timeout>";
echo "<user_agent_string perm='R'>!!$(::)!!$(firmware_version)</user_agent_string>";
echo "<web_user_agent_string perm='R'> </web_user_agent_string>";
#################################### FKEYS ########################################################
// 2.6.24: D815 Funktionstasten/BLF-Ausgabe wieder aktiviert.
// Schema: Taste 1 Seite 1 = fkey0, Taste 10 Seite 1 = fkey9, Taste 1 Seite 2 = fkey10 usw.
function spbx_d815_xml($value) {
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}
function spbx_d815_blf_label($label) {
    $label = trim(preg_replace('/\s+/', ' ', (string)$label));
    if ($label !== '' && strpos($label, '&#10;') === false && strpos($label, ' ') !== false) {
        $label = str_replace(' ', '&#10;', $label);
    }
    $label = htmlspecialchars($label, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    return str_replace('&amp;#10;', '&#10;', $label);
}
function spbx_d815_clean_callerid_label($callerid, $fallback = '') {
    $callerid = trim((string)$callerid);
    if ($callerid !== '' && preg_match('/"([^"]+)"/', $callerid, $m)) return trim($m[1]);
    if ($callerid !== '') {
        $clean = trim(preg_replace('/\s*<[^>]+>\s*/', '', $callerid));
        if ($clean !== '') return $clean;
    }
    return trim((string)$fallback);
}
function spbx_d815_fkey_action_value($action, $value) {
    $action = trim((string)$action);
    $value = trim((string)$value);
    if ($action === '' || $action === 'none') return 'none';
    if ($action === 'blf') return $value !== '' ? 'blf ' . $value : 'none';
    if ($action === 'dest') return $value !== '' ? 'dest ' . $value : 'none';
    if ($action === 'forward') return 'dest *72';
    return trim($action . ($value !== '' ? ' ' . $value : ''));
}

$fkeyCallerLabels = [];
$fkeyTargets = [];
for ($i = 0; $i < 40; $i++) {
    $actionVar = 'fkey' . $i . 'action';
    $valueVar = 'fkey' . $i . 'value';
    if (isset($$actionVar) && trim((string)$$actionVar) === 'blf' && isset($$valueVar) && trim((string)$$valueVar) !== '') {
        $fkeyTargets[trim((string)$$valueVar)] = true;
    }
}
if (!empty($fkeyTargets)) {
    $targetList = array_map(function($v) use ($verbindung) { return "'" . mysqli_real_escape_string($verbindung, (string)$v) . "'"; }, array_keys($fkeyTargets));
    $targetSql = implode(',', $targetList);
    $resLabels = mysqli_query($verbindung, "SELECT id, extension, display_name, callerid FROM ps_endpoints WHERE id IN ($targetSql) OR extension IN ($targetSql)");
    if ($resLabels) {
        while ($lr = mysqli_fetch_array($resLabels)) {
            $label = spbx_d815_clean_callerid_label($lr['callerid'] ?? '', $lr['display_name'] ?? ($lr['extension'] ?? ($lr['id'] ?? '')));
            if ($label !== '') {
                $fkeyCallerLabels[(string)$lr['id']] = $label;
                $fkeyCallerLabels[(string)$lr['extension']] = $label;
            }
        }
    }
}

for ($i = 0; $i < 40; $i++) {
    echo "<fkey_longpress idx='".$i."' perm='R'>off</fkey_longpress>";
}
for ($i = 0; $i < 40; $i++) {
    $actionVar = 'fkey' . $i . 'action';
    $valueVar = 'fkey' . $i . 'value';
    $labelVar = 'fkey' . $i . 'label';
    $action = isset($$actionVar) ? $$actionVar : 'none';
    $value = isset($$valueVar) ? $$valueVar : '';
    $label = isset($$labelVar) ? $$labelVar : '';
    if (trim((string)$action) === 'blf' && trim((string)$value) !== '' && isset($fkeyCallerLabels[trim((string)$value)])) {
        $label = $fkeyCallerLabels[trim((string)$value)];
    }
    echo "<fkey idx='".$i."' context='active' perm='R'>" . spbx_d815_xml(spbx_d815_fkey_action_value($action, $value)) . "</fkey>";
    echo "<fkey_label idx='".$i."' perm='R'>" . spbx_d815_blf_label($label) . "</fkey_label>";
}
echo "<block_url_dialing perm='R'>on</block_url_dialing>";
echo "<locale perm='R'>de_DE</locale>";
echo "<led_orange perm='R'>PhoneHasCallInStateRinging</led_orange>";
echo "<led_call_indicator_usage perm='R'>PhoneHasCallInStateRinging PhoneHasCallInStateCalling PhoneHasCallInStateRingback PhoneHasCallInStateConnected PhoneHasCallInStateOffhook PhoneHasCallInStateHolding PhoneHasCall DateOngoing DateReminding</led_call_indicator_usage>";
echo "<led_message_usage perm='R'>PhoneHasMissedCalls</led_message_usage>";
echo "<mute_is_dnd_in_idle perm='R'>off</mute_is_dnd_in_idle>";
echo "<user_active idx='2' perm='R'>off</user_active>";
echo "<user_active idx='3' perm='R'>off</user_active>";
echo "<user_active idx='4' perm='R'>off</user_active>";
echo "<user_active idx='5' perm='R'>off</user_active>";
echo "<user_active idx='6' perm='R'>off</user_active>";
echo "<user_active idx='7' perm='R'>off</user_active>";
echo "<user_active idx='8' perm='R'>off</user_active>";
echo "<user_active idx='9' perm='R'>off</user_active>";
echo "<user_active idx='10' perm='R'>off</user_active>";
echo "<user_active idx='11' perm='R'>off</user_active>";
echo "<user_active idx='12' perm='R'>off</user_active>";
echo "</phone-settings>";
echo "<tbook complete='true' e='2'>";
$ausgabe = "SELECT * FROM directory";
$ergebnis = mysqli_query($verbindung,$ausgabe);
while($row = mysqli_fetch_array($ergebnis)) {
$firstname = $row['first_name'];
$lastname = $row['last_name'];
$number = $row['number'];
$type = $row['type'];
echo "<item context='active' type='$type'>";
echo "<first_name>".$firstname."</first_name>";
echo "<last_name>".$lastname."</last_name>";
echo "<number>".$number."</number>";
echo "</item>";
}
echo "</tbook>";
// DEBUG TEST 2.6.9: uploads-Block voruebergehend deaktiviert.
// DEBUG TEST 2.6.9: gui-languages-Block voruebergehend deaktiviert.
echo "</settings>";
?>
