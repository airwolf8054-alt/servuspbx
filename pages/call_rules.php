<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/call_rules.php';
require_once __DIR__ . '/../inc/queues.php';
require_once __DIR__ . '/../inc/tts.php';
require_once __DIR__ . '/../inc/ring_groups.php';
require_once __DIR__ . '/../inc/ivr.php';

spbx_require_admin();

$db = spbx_db();
spbx_call_rules_install_schema();
spbx_tts_install_schema();
spbx_ring_groups_install_schema();
spbx_ivr_install_schema();

function cr_h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function cr_tts_voice_select($name, $selected) {
    $voices = spbx_tts_voices();
    $html = '<select class="spbx-select" name="' . cr_h($name) . '">';
    foreach ($voices as $key => $info) {
        $installed = spbx_tts_voice_installed($key);
        $sel = ((string)$selected === (string)$key) ? ' selected' : '';
        $dis = !$installed ? ' disabled' : '';
        $label = $info['label'] . (!$installed ? ' (nicht installiert)' : '');
        $html .= '<option value="' . cr_h($key) . '"' . $sel . $dis . '>' . cr_h($label) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function cr_time_value($value)
{
    $value = trim((string)$value);

    if ($value === '' || $value === 'NULL') {
        return '';
    }

    // MariaDB TIME: 06:00:00
    if (preg_match('/^([0-9]{1,2}):([0-9]{2})(?::[0-9]{2})?$/', $value, $m)) {
        return str_pad($m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
    }

    return '';
}

function cr_normalize_weekday($value)
{
    $value = strtolower(trim((string)$value));

    $map = [
        'mon' => 'mon', 'monday' => 'mon', 'montag' => 'mon', '1' => 'mon',
        'tue' => 'tue', 'tuesday' => 'tue', 'dienstag' => 'tue', '2' => 'tue',
        'wed' => 'wed', 'wednesday' => 'wed', 'mittwoch' => 'wed', '3' => 'wed',
        'thu' => 'thu', 'thursday' => 'thu', 'donnerstag' => 'thu', '4' => 'thu',
        'fri' => 'fri', 'friday' => 'fri', 'freitag' => 'fri', '5' => 'fri',
        'sat' => 'sat', 'saturday' => 'sat', 'samstag' => 'sat', '6' => 'sat',
        'sun' => 'sun', 'sunday' => 'sun', 'sonntag' => 'sun', '7' => 'sun', '0' => 'sun',
    ];

    return $map[$value] ?? $value;
}

function cr_window_at($windows, $day, $idx)
{
    $day = cr_normalize_weekday($day);

    if (!isset($windows[$day]) || !is_array($windows[$day])) {
        return [];
    }

    return $windows[$day][$idx] ?? [];
}


function cr_target_label($r) {
    $type = (string)($r['open_destination_type'] ?? 'custom');
    $ctx = (string)($r['open_destination_context'] ?? '');
    $ext = (string)($r['open_destination_exten'] ?? '');

    if ($type === 'did_extension') {
        return 'Durchwahl aus DID → ' . $ctx . ',' . $ext . ',1';
    }
    if ($type === 'ringgroup') {
        return 'Rufgruppe → ringgroups,' . $ext . ',1';
    }
    if ($type === 'queue') {
        return 'Queue → queue-services,' . $ext . ',1';
    }
    if ($type === 'ivr') {
        return 'Sprachmenü → ivr,' . $ext . ',1';
    }
    if ($type === 'extension') {
        return 'Nebenstelle → ' . $ctx . ',' . $ext . ',1';
    }
    return $ctx . ',' . $ext . ',1';
}

$weekdayLabels = [
    'mon' => 'Montag',
    'tue' => 'Dienstag',
    'wed' => 'Mittwoch',
    'thu' => 'Donnerstag',
    'fri' => 'Freitag',
    'sat' => 'Samstag',
    'sun' => 'Sonntag',
];

$errors = [];
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_rule') {
        $id = (int)($_POST['id'] ?? 0);
        $ruleName = trim((string)($_POST['rule_name'] ?? ''));
        $trunkContext = 'incoming';
        $did = trim((string)($_POST['did'] ?? 's'));
        $holidayEnabled = isset($_POST['holiday_enabled']) ? 1 : 0;
        $manual1 = isset($_POST['manual_announcement_1_enabled']) ? 1 : 0;
        $manual1File = trim((string)($_POST['manual_announcement_1_file'] ?? ''));
        $manual2 = isset($_POST['manual_announcement_2_enabled']) ? 1 : 0;
        $manual2File = trim((string)($_POST['manual_announcement_2_file'] ?? ''));
        $closedAudio = trim((string)($_POST['closed_audio'] ?? '/var/www/html/sounds/closed.mp3'));
        $closedAction = (($_POST['closed_action'] ?? 'hangup') === 'voicemail') ? 'voicemail' : 'hangup';
        $closedDestinationType = trim((string)($_POST['closed_destination_type'] ?? $closedAction));
        $closedDestinationContext = trim((string)($_POST['closed_destination_context'] ?? ''));
        $closedDestinationExten = trim((string)($_POST['closed_destination_exten'] ?? ''));
        $closedTtsText = trim((string)($_POST['closed_tts_text'] ?? ''));
        $closedTtsVoice = spbx_tts_valid_voice($_POST['closed_tts_voice'] ?? spbx_tts_setting('tts_voice', 'de_DE-thorsten-medium'));
        $closedAnnType = (($_POST['closed_announcement_type'] ?? 'tts') === 'mp3') ? 'mp3' : 'tts';
        $closedTtsOptions = spbx_tts_post_options('closed_tts_options');
        $holidayAudio = trim((string)($_POST['holiday_audio'] ?? '/var/www/html/sounds/holiday.mp3'));
        $holidayDestinationType = trim((string)($_POST['holiday_destination_type'] ?? 'hangup'));
        $holidayDestinationContext = trim((string)($_POST['holiday_destination_context'] ?? ''));
        $holidayDestinationExten = trim((string)($_POST['holiday_destination_exten'] ?? ''));
        $holidayTtsText = trim((string)($_POST['holiday_tts_text'] ?? ''));
        $holidayTtsVoice = spbx_tts_valid_voice($_POST['holiday_tts_voice'] ?? spbx_tts_setting('tts_voice', 'de_DE-thorsten-medium'));
        $holidayAnnType = (($_POST['holiday_announcement_type'] ?? 'tts') === 'mp3') ? 'mp3' : 'tts';
        $holidayTtsOptions = spbx_tts_post_options('holiday_tts_options');
        $mainMailbox = trim((string)($_POST['main_mailbox'] ?? '0'));
        $mainMailboxContext = trim((string)($_POST['main_mailbox_context'] ?? 'internal'));
        $openType = trim((string)($_POST['open_destination_type'] ?? 'extension'));
        $openCtx = trim((string)($_POST['open_destination_context'] ?? 'internal'));
        $openExten = trim((string)($_POST['open_destination_exten'] ?? '0'));
        $active = isset($_POST['active']) ? 1 : 0;

        if ($openType === 'extension') {
            $targetExtension = trim((string)($_POST['target_extension'] ?? ''));
            if ($targetExtension !== '') {
                $openCtx = 'auto_internal';
                $openExten = $targetExtension;
            }
        } elseif ($openType === 'did_extension') {
            $targetContext = trim((string)($_POST['target_internal_context'] ?? ''));
            if ($targetContext !== '') {
                $openCtx = $targetContext;
                $openExten = '${EXTEN:-2}';
            }
        } elseif ($openType === 'ringgroup') {
            $targetRinggroup = trim((string)($_POST['target_ringgroup'] ?? ''));
            if ($targetRinggroup !== '') {
                $openCtx = 'ringgroups';
                $openExten = $targetRinggroup;
            }
        } elseif ($openType === 'queue') {
            $targetQueue = trim((string)($_POST['target_queue'] ?? ''));
            if ($targetQueue !== '') {
                $openCtx = 'queue-services';
                $openExten = $targetQueue;
            }
        } elseif ($openType === 'ivr') {
            $targetIvr = trim((string)($_POST['target_ivr'] ?? ''));
            if ($targetIvr !== '') {
                $openCtx = 'ivr';
                $openExten = $targetIvr;
            }
        } elseif ($openType !== 'custom') {
            $openType = 'custom';
        }

        if ($ruleName === '') $errors[] = 'Name fehlt.';
        if ($did === '') $did = 's';
        if ($closedAudio === '') $closedAudio = '/var/www/html/sounds/closed.mp3';
        if ($holidayAudio === '') $holidayAudio = '/var/www/html/sounds/holiday.mp3';
        $allowedClosedDestinations = ['hangup','voicemail','extension','ringgroup','queue','ivr','external','custom'];
        if (!in_array($closedDestinationType, $allowedClosedDestinations, true)) $closedDestinationType = 'hangup';
        if (!in_array($holidayDestinationType, $allowedClosedDestinations, true)) $holidayDestinationType = 'hangup';
        if ($openCtx === '') $errors[] = 'Ziel-Context fehlt.';
        if ($openExten === '') $errors[] = 'Ziel-Extension fehlt.';

        if (!$errors) {
            try {
                $db->begin_transaction();

                if ($id > 0) {
                    $stmt = $db->prepare("UPDATE spbx_call_rules SET rule_name=?, trunk_context=?, did=?, holiday_enabled=?, manual_announcement_1_enabled=?, manual_announcement_1_file=?, manual_announcement_2_enabled=?, manual_announcement_2_file=?, closed_audio=?, closed_action=?, closed_destination_type=?, closed_destination_context=?, closed_destination_exten=?, closed_tts_text=?, holiday_audio=?, holiday_destination_type=?, holiday_destination_context=?, holiday_destination_exten=?, holiday_tts_text=?, main_mailbox=?, main_mailbox_context=?, open_destination_type=?, open_destination_context=?, open_destination_exten=?, active=? WHERE id=?");
                    $stmt->bind_param('sssisisissssssssssssssssii', $ruleName, $trunkContext, $did, $holidayEnabled, $manual1, $manual1File, $manual2, $manual2File, $closedAudio, $closedAction, $closedDestinationType, $closedDestinationContext, $closedDestinationExten, $closedTtsText, $holidayAudio, $holidayDestinationType, $holidayDestinationContext, $holidayDestinationExten, $holidayTtsText, $mainMailbox, $mainMailboxContext, $openType, $openCtx, $openExten, $active, $id);
                    $stmt->execute();
                } else {
                    $stmt = $db->prepare("INSERT INTO spbx_call_rules (rule_name,trunk_context,did,holiday_enabled,manual_announcement_1_enabled,manual_announcement_1_file,manual_announcement_2_enabled,manual_announcement_2_file,closed_audio,closed_action,closed_destination_type,closed_destination_context,closed_destination_exten,closed_tts_text,holiday_audio,holiday_destination_type,holiday_destination_context,holiday_destination_exten,holiday_tts_text,main_mailbox,main_mailbox_context,open_destination_type,open_destination_context,open_destination_exten,active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $stmt->bind_param('sssisisissssssssssssssssi', $ruleName, $trunkContext, $did, $holidayEnabled, $manual1, $manual1File, $manual2, $manual2File, $closedAudio, $closedAction, $closedDestinationType, $closedDestinationContext, $closedDestinationExten, $closedTtsText, $holidayAudio, $holidayDestinationType, $holidayDestinationContext, $holidayDestinationExten, $holidayTtsText, $mainMailbox, $mainMailboxContext, $openType, $openCtx, $openExten, $active);
                    $stmt->execute();
                    $id = (int)$db->insert_id;
                }

                spbx_tts_set_announcement_voice('callrule', $id, 'closed', $closedTtsVoice);
                spbx_tts_set_announcement_voice('callrule', $id, 'holiday', $holidayTtsVoice);
                spbx_tts_set_announcement_type('callrule', $id, 'closed', $closedAnnType);
                spbx_tts_set_announcement_type('callrule', $id, 'holiday', $holidayAnnType);
                spbx_tts_set_announcement_options('callrule', $id, 'closed', $closedTtsOptions);
                spbx_tts_set_announcement_options('callrule', $id, 'holiday', $holidayTtsOptions);

                $db->query("DELETE FROM spbx_call_rule_time_windows WHERE rule_id=" . (int)$id);

                foreach ($weekdayLabels as $day => $label) {
                    for ($i = 1; $i <= 2; $i++) {
                        $start = trim((string)($_POST['time'][$day][$i]['start'] ?? ''));
                        $end = trim((string)($_POST['time'][$day][$i]['end'] ?? ''));
                        if ($start !== '' && $end !== '') {
                            $sort = ($i - 1);
                            $stmt = $db->prepare("INSERT INTO spbx_call_rule_time_windows (rule_id, weekday, start_time, end_time, sort_order, active) VALUES (?, ?, ?, ?, ?, 1)");
                            $stmt->bind_param('isssi', $id, $day, $start, $end, $sort);
                            $stmt->execute();
                        }
                    }
                }

                $ttsWarn = false;
                if ($closedAnnType === 'tts') {
                    if ($closedTtsText !== '') {
                        $ttsErr = '';
                        $generated = spbx_tts_generate_if_text($closedTtsText, 'callrule_' . $id . '_closed', $closedAudio, $ttsErr, $closedTtsVoice, $closedTtsOptions);
                        if ($generated !== $closedAudio) $closedAudio = $generated;
                        if ($ttsErr !== '') $ttsWarn = true;
                    }
                } else {
                    $uploadErr = '';
                    $uploaded = spbx_tts_upload_mp3('closed_mp3_upload', 'callrule_' . $id . '_closed', $uploadErr);
                    if ($uploaded) $closedAudio = $uploaded;
                    if ($uploadErr !== '') $ttsWarn = true;
                }

                if ($holidayAnnType === 'tts') {
                    if ($holidayTtsText !== '') {
                        $ttsErr = '';
                        $generated = spbx_tts_generate_if_text($holidayTtsText, 'callrule_' . $id . '_holiday', $holidayAudio, $ttsErr, $holidayTtsVoice, $holidayTtsOptions);
                        if ($generated !== $holidayAudio) $holidayAudio = $generated;
                        if ($ttsErr !== '') $ttsWarn = true;
                    }
                } else {
                    $uploadErr = '';
                    $uploaded = spbx_tts_upload_mp3('holiday_mp3_upload', 'callrule_' . $id . '_holiday', $uploadErr);
                    if ($uploaded) $holidayAudio = $uploaded;
                    if ($uploadErr !== '') $ttsWarn = true;
                }

                $stmt = $db->prepare("UPDATE spbx_call_rules SET closed_audio=?, holiday_audio=? WHERE id=?");
                if ($stmt) {
                    $stmt->bind_param('ssi', $closedAudio, $holidayAudio, $id);
                    $stmt->execute();
                }

                $db->commit();
                spbx_call_rules_rebuild_dialplan();
                spbx_audit_log('call_rule_save', 'Anrufregel gespeichert.', 'info', null, null, 'call_rules');

                header('Location: call_rules.php?edit=' . (int)$id . '&saved=1' . (!empty($ttsWarn) ? '&tts_warn=1' : ''));
                exit;
            } catch (Throwable $e) {
                $db->rollback();
                $errors[] = 'Speichern fehlgeschlagen: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'delete_rule') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->query("DELETE FROM spbx_call_rule_time_windows WHERE rule_id=" . $id);
            $db->query("DELETE FROM spbx_call_rules WHERE id=" . $id);
            spbx_call_rules_rebuild_dialplan();
            spbx_audit_log('call_rule_delete', 'Anrufregel gelöscht.', 'warning', null, null, 'call_rules');
            header('Location: call_rules.php?deleted=1');
            exit;
        }
    }
}

if (isset($_GET['saved'])) $ok = 'Anrufregel gespeichert. Ansagen wurden automatisch erzeugt bzw. aktualisiert.';
if (isset($_GET['tts_warn'])) $ok = ($ok ?? '') . ' Hinweis: TTS-Datei konnte nicht erzeugt werden. Bitte System → Text-to-Speech prüfen.';
if (isset($_GET['deleted'])) $ok = 'Anrufregel gelöscht.';

$edit = null;
if (isset($_GET['new'])) {
    $edit = [
        'id' => 0,
        'rule_name' => '',
        'trunk_context' => 'incoming',
        'did' => 's',
        'holiday_enabled' => 1,
        'manual_announcement_1_enabled' => 0,
        'manual_announcement_1_file' => '',
        'manual_announcement_2_enabled' => 0,
        'manual_announcement_2_file' => '',
        'closed_audio' => '/var/www/html/sounds/closed.mp3',
        'closed_action' => 'hangup',
        'closed_destination_type' => 'hangup',
        'closed_destination_context' => '',
        'closed_destination_exten' => '',
        'closed_tts_text' => '',
        'holiday_audio' => '/var/www/html/sounds/holiday.mp3',
        'holiday_destination_type' => 'hangup',
        'holiday_destination_context' => '',
        'holiday_destination_exten' => '',
        'holiday_tts_text' => '',
        'main_mailbox' => '0',
        'main_mailbox_context' => 'internal',
        'open_destination_type' => 'extension',
        'open_destination_context' => 'auto_internal',
        'open_destination_exten' => '',
        'active' => 1,
    ];
} elseif (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $db->query("SELECT * FROM spbx_call_rules WHERE id=" . $id . " LIMIT 1");
    $edit = $res ? $res->fetch_assoc() : null;
}

$windows = [];
if ($edit && (int)$edit['id'] > 0) {
    $rid = (int)$edit['id'];

    $res = $db->query("
        SELECT *
        FROM spbx_call_rule_time_windows
        WHERE rule_id=" . $rid . "
          AND COALESCE(active,1)=1
        ORDER BY weekday ASC, sort_order ASC, id ASC
    ");

    if ($res) {
        $nextSlot = [];

        while ($w = $res->fetch_assoc()) {
            $day = cr_normalize_weekday($w['weekday'] ?? '');
            if (!array_key_exists($day, $weekdayLabels)) {
                continue;
            }

            $rawSort = isset($w['sort_order']) ? (int)$w['sort_order'] : -1;

            // Unterstützt alte Speicherung 1/2 und neue Speicherung 0/1.
            if ($rawSort === 0 || $rawSort === 1) {
                $slot = $rawSort;
            } elseif ($rawSort === 2) {
                $slot = 1;
            } else {
                $slot = $nextSlot[$day] ?? 0;
            }

            if ($slot < 0) $slot = 0;
            if ($slot > 1) $slot = 1;

            // Falls wegen Altbestand bereits belegt, nächstes freies Feld nehmen.
            if (isset($windows[$day][$slot])) {
                $slot = isset($windows[$day][0]) ? 1 : 0;
            }

            $windows[$day][$slot] = $w;
            $nextSlot[$day] = min(2, $slot + 1);
        }
    }
}

$rules = $db->query("SELECT * FROM spbx_call_rules ORDER BY id DESC");

$trunks = [];
$resTrunks = $db->query("SELECT id, trunk_name, name, endpoint_id, main_number, provider, active FROM spbx_trunks ORDER BY provider, main_number, trunk_name, name, endpoint_id");
if ($resTrunks) {
    while ($t = $resTrunks->fetch_assoc()) {
        $number = trim((string)($t['main_number'] ?? ''));
        $trunks[] = [
            'label' => 'Zentraler Eingang incoming' . ($number !== '' ? ' · ' . $number : ''),
        ];
    }
}
if (!$trunks) {
    $trunks[] = ['label' => 'Zentraler Eingang incoming'];
}

$extensions = $db->query("SELECT id, extension, display_name FROM ps_endpoints WHERE active=1 AND (device_type IS NULL OR device_type <> 'trunk') ORDER BY CAST(extension AS UNSIGNED), extension");

$internalContexts = [];
if ($resCtx = $db->query("SELECT DISTINCT category FROM ast_config WHERE filename='extensions.conf' AND category LIKE 'internal\\_%' ORDER BY category")) {
    while ($cx = $resCtx->fetch_assoc()) {
        $internalContexts[] = (string)$cx['category'];
    }
}
if (!$internalContexts) {
    $internalContexts[] = 'internal';
}

$ringGroups = $db->query("SELECT id, group_number, name FROM spbx_ring_groups WHERE active=1 ORDER BY CAST(group_number AS UNSIGNED), group_number");

$callRuleQueues = [];
$queueResult = $db->query("SELECT queue_number, queue_name FROM spbx_queues WHERE active=1 ORDER BY CAST(queue_number AS UNSIGNED), queue_number");
if ($queueResult) {
    while ($queueRow = $queueResult->fetch_assoc()) {
        $queueNumber = trim((string)($queueRow['queue_number'] ?? ''));
        if ($queueNumber === '') continue;
        $callRuleQueues[] = [
            'number' => $queueNumber,
            'name' => (string)($queueRow['queue_name'] ?? ''),
            'label' => trim($queueNumber . ' - ' . (string)($queueRow['queue_name'] ?? '')),
        ];
    }
}


$callRuleIvrs = [];
foreach (spbx_ivr_all(true) as $ivrRow) {
    $ivrNumber = trim((string)($ivrRow['ivr_number'] ?? ''));
    if ($ivrNumber === '') continue;
    $callRuleIvrs[] = [
        'number' => $ivrNumber,
        'name' => (string)($ivrRow['name'] ?? ''),
        'label' => trim($ivrNumber . ' - ' . (string)($ivrRow['name'] ?? '')),
    ];
}

$didPatterns = [];
$trunkNumbers = $db->query("SELECT DISTINCT main_number FROM spbx_trunks WHERE COALESCE(main_number,'')<>'' ORDER BY main_number");
if ($trunkNumbers) {
    while ($tn = $trunkNumbers->fetch_assoc()) {
        $n = trim((string)$tn['main_number']);
        if ($n !== '') {
            $didPatterns[] = ['label' => $n . ' + 2-stellige DW', 'value' => '_' . $n . 'XX'];
            $didPatterns[] = ['label' => $n . ' + 3-stellige DW', 'value' => '_' . $n . 'XXX'];
            $didPatterns[] = ['label' => $n . ' + 4-stellige DW', 'value' => '_' . $n . 'XXXX'];
        }
    }
}
$didPatterns[] = ['label' => 'Nur 2-stellige DW', 'value' => '_XX'];
$didPatterns[] = ['label' => 'Nur 3-stellige DW', 'value' => '_XXX'];
$didPatterns[] = ['label' => 'Nur 4-stellige DW', 'value' => '_XXXX'];
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Anrufregeln - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
    <style>
        .callrule-form .spbx-grid-2 { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px 22px; }
        .callrule-form .spbx-field { display:flex; flex-direction:column; gap:7px; }
        .callrule-form input, .callrule-form select { width:100%; min-height:42px; border:1px solid #cbd8ea; border-radius:10px; padding:0 12px; background:#fff; box-sizing:border-box; }
        .callrule-form input[type="checkbox"] { width:18px; min-height:18px; }
        .callrule-time-table { width:100%; border-collapse:collapse; }
        .callrule-time-table th, .callrule-time-table td { padding:10px; border-bottom:1px solid #d6e2f2; }
        .callrule-time-pair { display:grid; grid-template-columns:minmax(0,1fr) 28px minmax(0,1fr); gap:8px; align-items:center; }
        .callrule-time-pair span { text-align:center; font-weight:700; color:#51627a; }
        @media (max-width:900px) { .callrule-form .spbx-grid-2 { grid-template-columns:1fr; } }
        .spbx-ann-widget{border:1px solid #d6e2f2;border-radius:14px;padding:14px;background:rgba(248,250,252,.7);margin-top:8px}.spbx-ann-title{font-weight:800;margin-bottom:10px}.spbx-ann-options-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:10px}.spbx-ann-option{border:1px solid #d6e2f2;border-radius:12px;padding:10px;background:#fff}.spbx-ann-option-head{display:flex;justify-content:space-between;gap:12px;font-size:13px;font-weight:700}.spbx-ann-option input{width:100%;min-height:auto}.spbx-ann-option small{display:block;color:#64748b;margin-top:4px}@media(max-width:900px){.spbx-ann-options-grid{grid-template-columns:1fr}}
    </style>
    <script>
        function spbxCallRuleApplyDidPattern(sel) {
            if (!sel || !sel.value) return;
            const did = document.querySelector('input[name="did"]');
            if (did) did.value = sel.value;
            sel.value = '';
        }

        function spbxCallRuleToggleTarget() {
            const type = document.getElementById('open_destination_type');
            const value = type ? type.value : 'extension';
            const ids = {
                extension: 'target_extension_box',
                did_extension: 'target_did_extension_box',
                ringgroup: 'target_ringgroup_box',
                queue: 'target_queue_box',
                ivr: 'target_ivr_box',
                custom: 'target_custom_box'
            };
            Object.keys(ids).forEach(function(k) {
                const el = document.getElementById(ids[k]);
                if (el) el.style.display = (k === value) ? '' : 'none';
            });
        }


        function spbxAnnToggle(root){
            const sel=root.querySelector('.spbx-ann-type'); if(!sel)return;
            const t=sel.value;
            root.querySelectorAll('.spbx-ann-panel-tts').forEach(e=>e.style.display=(t==='tts'?'':'none'));
            root.querySelectorAll('.spbx-ann-panel-mp3').forEach(e=>e.style.display=(t==='mp3'?'':'none'));
        }
        function spbxAnnRangeUpdate(inp){
            const out=document.querySelector('[data-ann-out="'+inp.name+'"]');
            if(out){let suffix=inp.name.includes('sentence_silence')?' s':(inp.name.includes('volume_db')?' dB':''); out.textContent=inp.value+suffix;}
        }
        function spbxAnnInit(){
            document.querySelectorAll('.spbx-ann-widget').forEach(function(root){
                spbxAnnToggle(root);
                const sel=root.querySelector('.spbx-ann-type');
                if(sel)sel.addEventListener('change',function(){spbxAnnToggle(root);});
            });
            document.querySelectorAll('.spbx-ann-option input[type=range]').forEach(function(inp){
                inp.addEventListener('input',function(){spbxAnnRangeUpdate(inp);});
                spbxAnnRangeUpdate(inp);
            });
            document.querySelectorAll('.spbx-ann-reset-defaults').forEach(function(btn){
                btn.addEventListener('click',function(){
                    const root=btn.closest('.spbx-ann-widget') || document;
                    const voice=root.querySelector('[name="'+btn.dataset.voiceName+'"]');
                    if(voice && btn.dataset.defaultVoice) voice.value=btn.dataset.defaultVoice;
                    const map={length_scale:'lengthScale',noise_scale:'noiseScale',noise_w:'noiseW',sentence_silence:'sentenceSilence',volume_db:'volumeDb'};
                    Object.keys(map).forEach(function(k){
                        const name=btn.dataset.prefix+'_'+k;
                        const inp=root.querySelector('[name="'+name+'"]');
                        const val=btn.dataset[map[k]];
                        if(inp && val!==undefined){inp.value=val; spbxAnnRangeUpdate(inp);}
                    });
                    const note=root.querySelector('.spbx-ann-reset-note');
                    if(note) note.textContent='Standardwerte wurden wiederhergestellt. Bitte speichern, damit die MP3 aktualisiert wird.';
                });
            });
        }
        document.addEventListener('DOMContentLoaded', function(){ spbxCallRuleToggleTarget(); spbxAnnInit(); });
    </script>
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>
    <main class="spbx-main">
        <?php spbx_page_header('Anrufregeln', 'Eingehende und ausgehende Callflows'); ?>
        <div class="spbx-rule-tabs">
            <a class="active" href="call_rules.php">Eingehend</a>
            <a href="outbound_routes.php">Ausgehend</a>
        </div>
        <div class="spbx-content">
            <div class="spbx-card">
                <div class="spbx-card-head">
                    <div>
                        <div class="spbx-card-title"><?php echo $edit ? ((int)$edit['id'] > 0 ? 'Eingehende Regel bearbeiten' : 'Eingehende Regel anlegen') : 'Eingehende Regeln'; ?></div>
                        <?php if (!$edit): ?><div class="spbx-card-muted"><?php echo $rules ? (int)$rules->num_rows : 0; ?> Einträge vorhanden.</div><?php endif; ?>
                    </div>
                    <?php if (!$edit): ?><a class="spbx-button primary" href="call_rules.php?new=1">+ Neue Anrufregel</a><?php endif; ?>
                </div>

                <?php foreach ($errors as $e): ?><div class="spbx-alert error"><?php echo cr_h($e); ?></div><?php endforeach; ?>
                <?php if ($ok): ?><div class="spbx-alert success"><?php echo cr_h($ok); ?></div><?php endif; ?>

                <?php if ($edit): ?>
                    <form method="post" enctype="multipart/form-data" class="spbx-form callrule-form">
                        <input type="hidden" name="action" value="save_rule">
                        <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">

                        <div class="spbx-grid-2">
                            <div class="spbx-field">
                                <label>Name</label>
                                <input name="rule_name" required value="<?php echo cr_h($edit['rule_name']); ?>" placeholder="Ordination Hauptnummer">
                            </div>
                            <div class="spbx-field">
                                <label>SIP-Trunk</label>
                                <select name="trunk_context" required>
                                    <?php foreach ($trunks as $t): ?>
                                        <option value="incoming" selected><?php echo cr_h($t['label'] . ' → incoming'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="spbx-card-muted">Alle eingehenden Trunks verwenden den zentralen Context <strong>incoming</strong>.</div>
                            </div>
                            <div class="spbx-field">
                                <label>Rufnummer / DID</label>
                                <input name="did" value="<?php echo cr_h($edit['did']); ?>" placeholder="s, +433..., _XX oder _+433...XX">
                                <select onchange="spbxCallRuleApplyDidPattern(this)">
                                    <option value="">Pattern auswählen...</option>
                                    <?php foreach ($didPatterns as $dp): ?>
                                        <option value="<?php echo cr_h($dp['value']); ?>"><?php echo cr_h($dp['label'] . ' → ' . $dp['value']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="spbx-card-muted">Für Durchwahlen z. B. <strong>_XX</strong> oder <strong>_+43312423826XX</strong>.</div>
                            </div>
                            <div class="spbx-field">
                                <label>Aktiv</label>
                                <label><input type="checkbox" name="active" <?php echo (int)$edit['active'] === 1 ? 'checked' : ''; ?>> Anrufregel aktiv</label>
                            </div>
                        </div>

                        <hr>
                        <h3>Offen-Ziel</h3>
                        <div class="spbx-grid-2">
                            <div class="spbx-field">
                                <label>Zieltyp</label>
                                <select id="open_destination_type" name="open_destination_type" onchange="spbxCallRuleToggleTarget()">
                                    <option value="extension" <?php echo $edit['open_destination_type']==='extension'?'selected':''; ?>>Nebenstelle</option>
                                    <option value="did_extension" <?php echo $edit['open_destination_type']==='did_extension'?'selected':''; ?>>Durchwahl aus DID</option>
                                    <option value="ringgroup" <?php echo $edit['open_destination_type']==='ringgroup'?'selected':''; ?>>Rufgruppe</option>
                                    <option value="queue" <?php echo $edit['open_destination_type']==='queue'?'selected':''; ?>>Queue</option>
                                    <option value="ivr" <?php echo $edit['open_destination_type']==='ivr'?'selected':''; ?>>Sprachmenü (IVR)</option>
                                    <option value="custom" <?php echo $edit['open_destination_type']==='custom'?'selected':''; ?>>Benutzerdefiniert</option>
                                </select>
                            </div>

                            <div class="spbx-field" id="target_extension_box">
                                <label>Nebenstelle</label>
                                <select name="target_extension" onchange="document.getElementById('open_destination_context').value='auto_internal';document.getElementById('open_destination_exten').value=this.value;">
                                    <option value="">Bitte wählen</option>
                                    <?php if ($extensions): while ($e = $extensions->fetch_assoc()):
                                        $ext = (string)($e['extension'] ?: $e['id']);
                                        $label = trim($ext . ' - ' . ($e['display_name'] ?? ''));
                                    ?>
                                        <option value="<?php echo cr_h($ext); ?>" <?php echo ($edit['open_destination_type']==='extension' && $edit['open_destination_exten']===$ext) ? 'selected' : ''; ?>>
                                            <?php echo cr_h($label); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>

                            <div class="spbx-field" id="target_did_extension_box">
                                <label>Standort / Context</label>
                                <select name="target_internal_context" onchange="document.getElementById('open_destination_context').value=this.value;document.getElementById('open_destination_exten').value='${EXTEN:-2}';">
                                    <option value="">Bitte wählen</option>
                                    <?php foreach ($internalContexts as $cx): ?>
                                        <option value="<?php echo cr_h($cx); ?>" <?php echo ($edit['open_destination_type']==='did_extension' && $edit['open_destination_context']===$cx) ? 'selected' : ''; ?>>
                                            <?php echo cr_h($cx); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="spbx-field" id="target_ringgroup_box">
                                <label>Rufgruppe</label>
                                <select name="target_ringgroup" onchange="document.getElementById('open_destination_context').value='ringgroups';document.getElementById('open_destination_exten').value=this.value;">
                                    <option value="">Bitte wählen</option>
                                    <?php if ($ringGroups): while ($rg = $ringGroups->fetch_assoc()):
                                        $rgNo = (string)$rg['group_number'];
                                        $rgLabel = trim($rgNo . ' - ' . ($rg['name'] ?? ''));
                                    ?>
                                        <option value="<?php echo cr_h($rgNo); ?>" <?php echo ($edit['open_destination_type']==='ringgroup' && $edit['open_destination_exten']===$rgNo) ? 'selected' : ''; ?>>
                                            <?php echo cr_h($rgLabel); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>

                            <div class="spbx-field" id="target_queue_box">
                                <label>Queue</label>
                                <!-- queue_count=<?php echo count($callRuleQueues); ?> -->
                                <select name="target_queue" onchange="document.getElementById('open_destination_context').value='queue-services';document.getElementById('open_destination_exten').value=this.value;">
                                    <option value="">Bitte wählen</option>
                                    <?php foreach ($callRuleQueues as $queueItem): ?>
                                        <option value="<?php echo cr_h($queueItem['number']); ?>" <?php echo ($edit['open_destination_type']==='queue' && $edit['open_destination_exten']===$queueItem['number']) ? 'selected' : ''; ?>>
                                            <?php echo cr_h($queueItem['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$callRuleQueues): ?>
                                    <div class="spbx-card-muted">Keine aktive Queue gefunden.</div>
                                <?php endif; ?>
                            </div>

                            <div class="spbx-field" id="target_ivr_box">
                                <label>Sprachmenü (IVR)</label>
                                <select name="target_ivr" onchange="document.getElementById('open_destination_context').value='ivr';document.getElementById('open_destination_exten').value=this.value;">
                                    <option value="">Bitte wählen</option>
                                    <?php foreach ($callRuleIvrs as $ivrItem): ?>
                                        <option value="<?php echo cr_h($ivrItem['number']); ?>" <?php echo ($edit['open_destination_type']==='ivr' && $edit['open_destination_exten']===$ivrItem['number']) ? 'selected' : ''; ?>>
                                            <?php echo cr_h($ivrItem['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$callRuleIvrs): ?>
                                    <div class="spbx-card-muted">Noch kein aktives Sprachmenü vorhanden.</div>
                                <?php endif; ?>
                            </div>

                            <div class="spbx-field" id="target_custom_box">
                                <label>Benutzerdefiniertes Ziel</label>
                                <input id="open_destination_context" name="open_destination_context" value="<?php echo cr_h($edit['open_destination_context']); ?>" placeholder="Context">
                                <input id="open_destination_exten" name="open_destination_exten" value="<?php echo cr_h($edit['open_destination_exten']); ?>" placeholder="Extension">
                            </div>
                        </div>

                        <hr>
                        <h3>Geschlossen-Bereich</h3>
                        <div class="spbx-grid-2">
                            <div class="spbx-field" style="grid-column:1 / -1;">
                                <input type="hidden" name="closed_audio" value="<?php echo cr_h($edit['closed_audio']); ?>">
                                <?php
                                    $closedFallbackType = trim((string)($edit['closed_tts_text'] ?? '')) !== '' ? 'tts' : 'mp3';
                                    echo spbx_tts_render_announcement_widget([
                                        'title' => 'Geschlossen-Ansage',
                                        'type_name' => 'closed_announcement_type',
                                        'type_value' => spbx_tts_get_announcement_type('callrule', (int)($edit['id'] ?? 0), 'closed', $closedFallbackType),
                                        'text_name' => 'closed_tts_text',
                                        'text_value' => $edit['closed_tts_text'] ?? '',
                                        'voice_name' => 'closed_tts_voice',
                                        'voice_value' => spbx_tts_get_announcement_voice('callrule', (int)($edit['id'] ?? 0), 'closed'),
                                        'upload_name' => 'closed_mp3_upload',
                                        'file_value' => $edit['closed_audio'] ?? '',
                                        'options_prefix' => 'closed_tts_options',
                                        'options' => spbx_tts_get_announcement_options('callrule', (int)($edit['id'] ?? 0), 'closed'),
                                        'placeholder' => 'Dieser Text wird als MP3 für den Geschlossen-Bereich erzeugt.'
                                    ]);
                                ?>
                            </div>
                            <div class="spbx-field">
                                <label>Ziel bei geschlossen</label>
                                <select name="closed_destination_type">
                                    <?php foreach (['hangup'=>'Auflegen','voicemail'=>'Voicemail','extension'=>'Nebenstelle','ringgroup'=>'Rufgruppe','queue'=>'Queue','ivr'=>'Sprachmenü (IVR)','external'=>'Externe Nummer','custom'=>'Benutzerdefiniert'] as $v=>$l): ?>
                                        <option value="<?php echo cr_h($v); ?>" <?php echo (($edit['closed_destination_type'] ?? $edit['closed_action'] ?? 'hangup')===$v)?'selected':''; ?>><?php echo cr_h($l); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input name="closed_destination_context" value="<?php echo cr_h($edit['closed_destination_context'] ?? ''); ?>" placeholder="Context / Mailbox-Context">
                                <input name="closed_destination_exten" value="<?php echo cr_h($edit['closed_destination_exten'] ?? ''); ?>" placeholder="Nebenstelle / Rufgruppe / Nummer / Mailbox">
                            </div>
                                                    </div>

                        <hr>
                        <h3>Feiertage</h3>
                        <div class="spbx-field">
                            <label><input type="checkbox" name="holiday_enabled" <?php echo (int)$edit['holiday_enabled'] === 1 ? 'checked' : ''; ?>> Feiertagsschaltung aktiv</label>
                        </div>
                        <div class="spbx-grid-2">
                            <div class="spbx-field" style="grid-column:1 / -1;">
                                <input type="hidden" name="holiday_audio" value="<?php echo cr_h($edit['holiday_audio'] ?? ''); ?>">
                                <?php
                                    $holidayFallbackType = trim((string)($edit['holiday_tts_text'] ?? '')) !== '' ? 'tts' : 'mp3';
                                    echo spbx_tts_render_announcement_widget([
                                        'title' => 'Feiertags-Ansage',
                                        'type_name' => 'holiday_announcement_type',
                                        'type_value' => spbx_tts_get_announcement_type('callrule', (int)($edit['id'] ?? 0), 'holiday', $holidayFallbackType),
                                        'text_name' => 'holiday_tts_text',
                                        'text_value' => $edit['holiday_tts_text'] ?? '',
                                        'voice_name' => 'holiday_tts_voice',
                                        'voice_value' => spbx_tts_get_announcement_voice('callrule', (int)($edit['id'] ?? 0), 'holiday'),
                                        'upload_name' => 'holiday_mp3_upload',
                                        'file_value' => $edit['holiday_audio'] ?? '',
                                        'options_prefix' => 'holiday_tts_options',
                                        'options' => spbx_tts_get_announcement_options('callrule', (int)($edit['id'] ?? 0), 'holiday'),
                                        'placeholder' => 'Dieser Text wird als MP3 für Feiertage erzeugt.'
                                    ]);
                                ?>
                            </div>
                            <div class="spbx-field">
                                <label>Ziel bei Feiertag</label>
                                <select name="holiday_destination_type">
                                    <?php foreach (['hangup'=>'Auflegen','voicemail'=>'Voicemail','extension'=>'Nebenstelle','ringgroup'=>'Rufgruppe','queue'=>'Queue','ivr'=>'Sprachmenü (IVR)','external'=>'Externe Nummer','custom'=>'Benutzerdefiniert'] as $v=>$l): ?>
                                        <option value="<?php echo cr_h($v); ?>" <?php echo (($edit['holiday_destination_type'] ?? 'hangup')===$v)?'selected':''; ?>><?php echo cr_h($l); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input name="holiday_destination_context" value="<?php echo cr_h($edit['holiday_destination_context'] ?? ''); ?>" placeholder="Context / Mailbox-Context">
                                <input name="holiday_destination_exten" value="<?php echo cr_h($edit['holiday_destination_exten'] ?? ''); ?>" placeholder="Nebenstelle / Rufgruppe / Nummer / Mailbox">
                            </div>
                                                    </div>

                        <hr>
                        <h3>Manuelle Ansagen</h3>
                        <div class="spbx-grid-2">
                            <div class="spbx-field">
                                <label><input type="checkbox" name="manual_announcement_1_enabled" <?php echo (int)$edit['manual_announcement_1_enabled'] === 1 ? 'checked' : ''; ?>> Ansage 1 aktiv</label>
                                <input name="manual_announcement_1_file" value="<?php echo cr_h($edit['manual_announcement_1_file']); ?>" placeholder="/var/www/html/sounds/AB.mp3">
                            </div>
                            <div class="spbx-field">
                                <label><input type="checkbox" name="manual_announcement_2_enabled" <?php echo (int)$edit['manual_announcement_2_enabled'] === 1 ? 'checked' : ''; ?>> Ansage 2 aktiv</label>
                                <input name="manual_announcement_2_file" value="<?php echo cr_h($edit['manual_announcement_2_file']); ?>" placeholder="/var/www/html/sounds/AB2.mp3">
                            </div>
                        </div>

                        <hr>
                        <h3>Öffnungszeiten</h3>
                        <div class="spbx-card-muted">Standard: Alle Tage geschlossen (00:00–00:00). Öffnungszeiten bitte explizit eintragen. Vorhandene gespeicherte Zeiten bleiben erhalten.</div>
                        <table class="callrule-time-table">
                            <thead><tr><th>Tag</th><th>Zeitraum 1</th><th>Zeitraum 2</th></tr></thead>
                            <tbody>
                            <?php foreach ($weekdayLabels as $day => $label): ?>
                                <tr>
                                    <td><strong><?php echo cr_h($label); ?></strong></td>                                    <?php for ($i=1; $i<=2; $i++):
                                        $w = cr_window_at($windows, $day, $i - 1);
                                        $start = cr_time_value($w['start_time'] ?? '');
                                        $end = cr_time_value($w['end_time'] ?? '');

                                        // Default nur anzeigen, wenn kein Wert aus der DB existiert.
                                        // ServusPBX Medical ist standardmäßig geschlossen.
                                        // Beim Speichern werden diese sichtbaren Werte normal in spbx_call_rule_time_windows geschrieben.
                                        if ($start === '' && $end === '') {
                                            $start = '00:00';
                                            $end = '00:00';
                                        }
                                    ?>
                                        <td>
                                            <div class="callrule-time-pair">
                                                <input type="time" name="time[<?php echo cr_h($day); ?>][<?php echo $i; ?>][start]" value="<?php echo cr_h($start); ?>">
                                                <span>bis</span>
                                                <input type="time" name="time[<?php echo cr_h($day); ?>][<?php echo $i; ?>][end]" value="<?php echo cr_h($end); ?>">
                                            </div>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <hr>
                        <h3>Geschlossen</h3>
                        <div class="spbx-grid-2">
                            <div class="spbx-field">
                                <label>Geschlossen-Ansage</label>
                                <input name="closed_audio" value="<?php echo cr_h($edit['closed_audio']); ?>">
                            </div>
                            <div class="spbx-field">
                                <label>Nach Ansage</label>
                                <select name="closed_action">
                                    <option value="hangup" <?php echo $edit['closed_action']==='hangup'?'selected':''; ?>>Auflegen</option>
                                    <option value="voicemail" <?php echo $edit['closed_action']==='voicemail'?'selected':''; ?>>Mailbox</option>
                                </select>
                            </div>
                            <div class="spbx-field">
                                <label>Mailbox</label>
                                <input name="main_mailbox" value="<?php echo cr_h($edit['main_mailbox']); ?>">
                            </div>
                            <div class="spbx-field">
                                <label>Mailbox Context</label>
                                <input name="main_mailbox_context" value="<?php echo cr_h($edit['main_mailbox_context']); ?>">
                            </div>
                        </div>

                        <div class="spbx-actions" style="margin-top:22px">
                            <a class="spbx-button" href="call_rules.php">Abbrechen</a>
                            <button class="spbx-button primary" type="submit">Speichern</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="spbx-table-wrap">
                        <table class="spbx-table">
                            <thead><tr><th>Name</th><th>Rufnummer</th><th>Context</th><th>Offen-Ziel</th><th>Feiertage</th><th>Geschlossen</th><th>Aktiv</th><th>Aktion</th></tr></thead>
                            <tbody>
                            <?php if ($rules && $rules->num_rows > 0): while ($r = $rules->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo cr_h($r['rule_name']); ?></td>
                                    <td><?php echo cr_h($r['did']); ?></td>
                                    <td><?php echo cr_h($r['trunk_context']); ?></td>
                                    <td><?php echo cr_h(cr_target_label($r)); ?></td>
                                    <td><?php echo (int)$r['holiday_enabled'] === 1 ? 'aktiv' : 'aus'; ?></td>
                                    <td><?php echo $r['closed_action'] === 'voicemail' ? 'Mailbox' : 'Auflegen'; ?></td>
                                    <td><?php echo (int)$r['active'] === 1 ? 'Ja' : 'Nein'; ?></td>
                                    <td>
                                        <a class="spbx-button small" href="call_rules.php?edit=<?php echo (int)$r['id']; ?>">Bearbeiten</a>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Anrufregel wirklich löschen?');">
                                            <input type="hidden" name="action" value="delete_rule">
                                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                            <button class="spbx-button small danger" type="submit">Löschen</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="8">Noch keine eingehenden Regeln vorhanden.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
