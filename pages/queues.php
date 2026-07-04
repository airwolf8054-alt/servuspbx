<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/queues.php';
require_once __DIR__ . '/../inc/tts.php';

spbx_require_admin();
spbx_queues_install_schema();
spbx_tts_install_schema();
$db = spbx_db();

function qh($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function q_tts_voice_select($name, $selected) {
    $voices = spbx_tts_voices();
    $html = '<select class="spbx-select" name="' . qh($name) . '">';
    foreach ($voices as $key => $info) {
        $installed = spbx_tts_voice_installed($key);
        $sel = ((string)$selected === (string)$key) ? ' selected' : '';
        $dis = !$installed ? ' disabled' : '';
        $label = $info['label'] . (!$installed ? ' (nicht installiert)' : '');
        $html .= '<option value="' . qh($key) . '"' . $sel . $dis . '>' . qh($label) . '</option>';
    }
    $html .= '</select>';
    return $html;
}
function qp($k, $d='') { return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; }
function queue_member_count($queueId) { return count(spbx_queue_members((int)$queueId)); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';
$error = '';
$message = '';

if (isset($_GET['saved'])) $message = 'Queue gespeichert. Ansagen wurden automatisch erzeugt bzw. aktualisiert.';
if (isset($_GET['deleted'])) $message = 'Queue gelöscht.';
if (isset($_GET['rebuilt'])) $message = 'Queues neu erzeugt und Asterisk neu geladen.';
if (isset($_GET['tts_warn'])) $message .= ($message ? ' ' : '') . 'Hinweis: TTS-Datei konnte nicht erzeugt werden. Bitte System → Text-to-Speech prüfen.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['form_action'] ?? '') === 'save_queue') {
        $id = (int)($_POST['id'] ?? 0);
        $num = preg_replace('/[^0-9]/', '', qp('queue_number'));
        $name = qp('queue_name');
        $strategy = qp('strategy', 'ringall');
        $timeout = max(5, (int)qp('timeout_seconds', '20'));
        $maxWait = max(30, (int)qp('max_wait_seconds', '300'));
        $music = qp('musicclass', 'default');
        $queueAnnEnabled = isset($_POST['queue_announcement_enabled']) ? 1 : 0;
        $queueAnnFile = qp('queue_announcement_file');
        $queueTts = qp('queue_tts_text');
        $queueTtsVoice = spbx_tts_valid_voice($_POST['queue_tts_voice'] ?? spbx_tts_setting('tts_voice', 'de_DE-thorsten-medium'));
        $queueAnnType = (($_POST['queue_announcement_type'] ?? 'tts') === 'mp3') ? 'mp3' : 'tts';
        $queueTtsOptions = spbx_tts_post_options('queue_tts_options');
        $emEnabled = isset($_POST['emergency_announcement_enabled']) ? 1 : 0;
        $emFile = qp('emergency_announcement_file');
        $emTts = qp('emergency_tts_text');
        $emTtsVoice = spbx_tts_valid_voice($_POST['emergency_tts_voice'] ?? spbx_tts_setting('tts_voice', 'de_DE-thorsten-medium'));
        $emAnnType = (($_POST['emergency_announcement_type'] ?? 'tts') === 'mp3') ? 'mp3' : 'tts';
        $emTtsOptions = spbx_tts_post_options('emergency_tts_options');
        $dstType = qp('timeout_destination_type', 'hangup');
        $dstCtx = qp('timeout_destination_context');
        $dstExt = qp('timeout_destination_exten');
        $callback = isset($_POST['callback_login_enabled']) ? 1 : 0;
        $active = isset($_POST['active']) ? 1 : 0;

        if ($num === '') $error = 'Bitte eine Queue-Nummer eingeben.';
        if ($name === '') $error = 'Bitte einen Namen eingeben.';

        $allowedStrategies = ['ringall','leastrecent','fewestcalls','random','rrmemory','linear'];
        if (!in_array($strategy, $allowedStrategies, true)) $strategy = 'ringall';

        if ($error === '') {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE spbx_queues SET queue_number=?, queue_name=?, strategy=?, timeout_seconds=?, max_wait_seconds=?, musicclass=?, queue_announcement_enabled=?, queue_announcement_file=?, queue_tts_text=?, emergency_announcement_enabled=?, emergency_announcement_file=?, emergency_tts_text=?, timeout_destination_type=?, timeout_destination_context=?, timeout_destination_exten=?, callback_login_enabled=?, active=? WHERE id=?");
                $stmt->bind_param('sssiisississsssiii', $num, $name, $strategy, $timeout, $maxWait, $music, $queueAnnEnabled, $queueAnnFile, $queueTts, $emEnabled, $emFile, $emTts, $dstType, $dstCtx, $dstExt, $callback, $active, $id);
                $stmt->execute();
            } else {
                $stmt = $db->prepare("INSERT INTO spbx_queues (queue_number, queue_name, strategy, timeout_seconds, max_wait_seconds, musicclass, queue_announcement_enabled, queue_announcement_file, queue_tts_text, emergency_announcement_enabled, emergency_announcement_file, emergency_tts_text, timeout_destination_type, timeout_destination_context, timeout_destination_exten, callback_login_enabled, active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param('sssiisississsssii', $num, $name, $strategy, $timeout, $maxWait, $music, $queueAnnEnabled, $queueAnnFile, $queueTts, $emEnabled, $emFile, $emTts, $dstType, $dstCtx, $dstExt, $callback, $active);
                $stmt->execute();
                $id = (int)$db->insert_id;
            }

            spbx_tts_set_announcement_voice('queue', $id, 'welcome', $queueTtsVoice);
            spbx_tts_set_announcement_voice('queue', $id, 'emergency', $emTtsVoice);
            spbx_tts_set_announcement_type('queue', $id, 'welcome', $queueAnnType);
            spbx_tts_set_announcement_type('queue', $id, 'emergency', $emAnnType);
            spbx_tts_set_announcement_options('queue', $id, 'welcome', $queueTtsOptions);
            spbx_tts_set_announcement_options('queue', $id, 'emergency', $emTtsOptions);

            $db->query("DELETE FROM spbx_queue_members WHERE queue_id=" . (int)$id);

            // Doppelauswahl verhindern:
            // Wenn eine Nebenstelle fix UND optional markiert ist, gewinnt "fix".
            $memberMap = [];

            foreach (['optional', 'fixed'] as $type) {
                $list = $_POST[$type . '_agents'] ?? [];
                if (!is_array($list)) $list = [];

                foreach ($list as $endpointId) {
                    $endpointId = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$endpointId);
                    if ($endpointId === '') continue;

                    $memberMap[$endpointId] = $type;
                }
            }

            foreach ($memberMap as $endpointId => $type) {
                $stmt = $db->prepare("
                    INSERT INTO spbx_queue_members
                    (queue_id, endpoint_id, member_type, active)
                    VALUES (?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE
                      member_type=VALUES(member_type),
                      active=1
                ");

                if ($stmt) {
                    $stmt->bind_param('iss', $id, $endpointId, $type);
                    $stmt->execute();
                }
            }

            $ttsWarn = false;
            if ($queueAnnType === 'tts') {
                if ($queueTts !== '') {
                    $ttsErr = '';
                    $generated = spbx_tts_generate_if_text($queueTts, 'queue_' . $num . '_welcome', $queueAnnFile, $ttsErr, $queueTtsVoice, $queueTtsOptions);
                    if ($generated !== $queueAnnFile) $queueAnnFile = $generated;
                    if ($ttsErr !== '') $ttsWarn = true;
                }
            } else {
                $uploadErr = '';
                $uploaded = spbx_tts_upload_mp3('queue_mp3_upload', 'queue_' . $num . '_welcome', $uploadErr);
                if ($uploaded) $queueAnnFile = $uploaded;
                if ($uploadErr !== '') $ttsWarn = true;
            }
            if ($emAnnType === 'tts') {
                if ($emTts !== '') {
                    $ttsErr = '';
                    $generated = spbx_tts_generate_if_text($emTts, 'queue_' . $num . '_emergency', $emFile, $ttsErr, $emTtsVoice, $emTtsOptions);
                    if ($generated !== $emFile) $emFile = $generated;
                    if ($ttsErr !== '') $ttsWarn = true;
                }
            } else {
                $uploadErr = '';
                $uploaded = spbx_tts_upload_mp3('emergency_mp3_upload', 'queue_' . $num . '_emergency', $uploadErr);
                if ($uploaded) $emFile = $uploaded;
                if ($uploadErr !== '') $ttsWarn = true;
            }

            $stmt = $db->prepare("UPDATE spbx_queues SET queue_announcement_file=?, emergency_announcement_file=? WHERE id=?");
            if ($stmt) {
                $stmt->bind_param('ssi', $queueAnnFile, $emFile, $id);
                $stmt->execute();
            }

            spbx_queues_rebuild();
            header('Location: queues.php?action=edit&id=' . (int)$id . '&saved=1' . ($ttsWarn ? '&tts_warn=1' : ''));
            exit;
        }
        $action = $id > 0 ? 'edit' : 'new';
    }

    if (($_POST['form_action'] ?? '') === 'save_pause_reason') {
        $reason = qp('reason_name');
        if ($reason !== '') {
            $sort = (int)qp('sort_order', '100');
            $stmt = $db->prepare("INSERT INTO spbx_queue_pause_reasons (reason_name, sort_order, active) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE sort_order=VALUES(sort_order), active=1");
            $stmt->bind_param('si', $reason, $sort);
            $stmt->execute();
        }
        header('Location: queues.php?reasons=1');
        exit;
    }
}

if ($action === 'delete' && $id > 0) {
    $db->query("DELETE FROM spbx_queue_members WHERE queue_id=" . (int)$id);
    $db->query("DELETE FROM spbx_queues WHERE id=" . (int)$id);
    spbx_queues_rebuild();
    header('Location: queues.php?deleted=1');
    exit;
}

if ($action === 'rebuild') {
    spbx_queues_rebuild();
    header('Location: queues.php?rebuilt=1');
    exit;
}

$edit = [
    'id'=>0,
    'queue_number'=>'700',
    'queue_name'=>'Support',
    'strategy'=>'ringall',
    'timeout_seconds'=>20,
    'max_wait_seconds'=>300,
    'musicclass'=>'default',
    'queue_announcement_enabled'=>0,
    'queue_announcement_file'=>'',
    'queue_tts_text'=>'',
    'emergency_announcement_enabled'=>0,
    'emergency_announcement_file'=>'',
    'emergency_tts_text'=>'',
    'timeout_destination_type'=>'hangup',
    'timeout_destination_context'=>'',
    'timeout_destination_exten'=>'',
    'callback_login_enabled'=>1,
    'active'=>1,
];

$fixed = [];
$optional = [];

if (($action === 'edit' || $action === 'new') && $id > 0) {
    $res = $db->query("SELECT * FROM spbx_queues WHERE id=" . (int)$id . " LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) $edit = array_merge($edit, $row);

    $res = $db->query("SELECT endpoint_id, member_type FROM spbx_queue_members WHERE queue_id=" . (int)$id);
    if ($res) {
        while ($m = $res->fetch_assoc()) {
            if ($m['member_type'] === 'fixed') $fixed[$m['endpoint_id']] = true;
            if ($m['member_type'] === 'optional') $optional[$m['endpoint_id']] = true;
        }
    }
}

$queues = spbx_queue_all(false);
$extensions = $db->query("SELECT e.endpoint_id, e.extension, e.display_name FROM spbx_extensions e WHERE e.active=1 ORDER BY CAST(e.extension AS UNSIGNED), e.extension");
$pauseReasons = spbx_queue_pause_reasons();
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Queues - ServusPBX Professional</title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="stylesheet" href="../css/servuspbx.css">
<style>.spbx-ann-widget{border:1px solid #d6e2f2;border-radius:14px;padding:14px;background:rgba(248,250,252,.7);margin-top:8px}.spbx-ann-title{font-weight:800;margin-bottom:10px}.spbx-ann-options-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:10px}.spbx-ann-option{border:1px solid #d6e2f2;border-radius:12px;padding:10px;background:#fff}.spbx-ann-option-head{display:flex;justify-content:space-between;gap:12px;font-size:13px;font-weight:700}.spbx-ann-option input{width:100%}.spbx-ann-option small{display:block;color:#64748b;margin-top:4px}@media(max-width:900px){.spbx-ann-options-grid{grid-template-columns:1fr}}</style>
<script>

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
document.addEventListener('DOMContentLoaded',function(){spbxAnnInit();});
</script>
</head>
<body>
<div class="spbx-app">
<?php spbx_sidebar(); ?>
<main class="spbx-main">
<?php spbx_page_header('Queues', 'Support-Warteschlangen mit festen Agenten und berechtigtem Callback-Login'); ?>
<div class="spbx-content">
<?php if ($message): ?><div class="spbx-alert success"><?php echo qh($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="spbx-alert error"><?php echo qh($error); ?></div><?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>
<form method="post" enctype="multipart/form-data" class="spbx-card" style="padding:18px;">
<input type="hidden" name="form_action" value="save_queue">
<input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">
<div class="spbx-card-title"><?php echo $edit['id'] ? 'Queue bearbeiten' : 'Queue anlegen'; ?></div>
<div class="spbx-form-grid" style="margin-top:14px;">
<div class="spbx-field"><label>Queue-Nummer</label><input class="spbx-input" name="queue_number" value="<?php echo qh($edit['queue_number']); ?>" required></div>
<div class="spbx-field"><label>Name</label><input class="spbx-input" name="queue_name" value="<?php echo qh($edit['queue_name']); ?>" required></div>
<div class="spbx-field"><label>Strategie</label><select class="spbx-select" name="strategy"><?php foreach(['ringall'=>'Alle klingeln','leastrecent'=>'Längste frei','fewestcalls'=>'Wenigste Anrufe','random'=>'Zufällig','rrmemory'=>'Round Robin','linear'=>'Linear'] as $v=>$l): ?><option value="<?php echo qh($v); ?>" <?php echo $edit['strategy']===$v?'selected':''; ?>><?php echo qh($l); ?></option><?php endforeach; ?></select></div>
<div class="spbx-field"><label>Agent Timeout Sekunden</label><input class="spbx-input" type="number" name="timeout_seconds" value="<?php echo (int)$edit['timeout_seconds']; ?>"></div>
<div class="spbx-field"><label>Max. Wartezeit Sekunden</label><input class="spbx-input" type="number" name="max_wait_seconds" value="<?php echo (int)$edit['max_wait_seconds']; ?>"></div>
<div class="spbx-field"><label>Wartemusik</label><input class="spbx-input" name="musicclass" value="<?php echo qh($edit['musicclass']); ?>"></div>
</div>

<hr><h3>Ansagen vor Queue</h3>
<div class="spbx-grid-2">
<div class="spbx-field"><label><input type="checkbox" name="queue_announcement_enabled" <?php echo (int)($edit['queue_announcement_enabled'] ?? 0)?'checked':''; ?>> Begrüßungsansage aktiv</label><input type="hidden" name="queue_announcement_file" value="<?php echo qh($edit['queue_announcement_file'] ?? ''); ?>"></div>
<div class="spbx-field" style="grid-column:1 / -1;"><?php $qFallbackType = trim((string)($edit['queue_tts_text'] ?? '')) !== '' ? 'tts' : 'mp3'; echo spbx_tts_render_announcement_widget(['title'=>'Queue-Begrüßungsansage','type_name'=>'queue_announcement_type','type_value'=>spbx_tts_get_announcement_type('queue',(int)($edit['id'] ?? 0),'welcome',$qFallbackType),'text_name'=>'queue_tts_text','text_value'=>$edit['queue_tts_text'] ?? '','voice_name'=>'queue_tts_voice','voice_value'=>spbx_tts_get_announcement_voice('queue',(int)($edit['id'] ?? 0),'welcome'),'upload_name'=>'queue_mp3_upload','file_value'=>$edit['queue_announcement_file'] ?? '','options_prefix'=>'queue_tts_options','options'=>spbx_tts_get_announcement_options('queue',(int)($edit['id'] ?? 0),'welcome'),'placeholder'=>'z.B. Herzlich willkommen beim Servicedesk.']); ?></div>
<div class="spbx-field"><label><input type="checkbox" name="emergency_announcement_enabled" <?php echo (int)$edit['emergency_announcement_enabled']?'checked':''; ?>> Notfall-/Störungsansage aktiv</label><input type="hidden" name="emergency_announcement_file" value="<?php echo qh($edit['emergency_announcement_file']); ?>"></div>
<div class="spbx-field" style="grid-column:1 / -1;"><?php $eFallbackType = trim((string)($edit['emergency_tts_text'] ?? '')) !== '' ? 'tts' : 'mp3'; echo spbx_tts_render_announcement_widget(['title'=>'Queue-Notfall-/Störungsansage','type_name'=>'emergency_announcement_type','type_value'=>spbx_tts_get_announcement_type('queue',(int)($edit['id'] ?? 0),'emergency',$eFallbackType),'text_name'=>'emergency_tts_text','text_value'=>$edit['emergency_tts_text'] ?? '','voice_name'=>'emergency_tts_voice','voice_value'=>spbx_tts_get_announcement_voice('queue',(int)($edit['id'] ?? 0),'emergency'),'upload_name'=>'emergency_mp3_upload','file_value'=>$edit['emergency_announcement_file'] ?? '','options_prefix'=>'emergency_tts_options','options'=>spbx_tts_get_announcement_options('queue',(int)($edit['id'] ?? 0),'emergency'),'placeholder'=>'z.B. Aufgrund einer aktuellen Störung...']); ?></div>
</div>

<hr><h3>Timeout-Ziel</h3>
<div class="spbx-form-grid">
<div class="spbx-field"><label>Zieltyp</label><select class="spbx-select" name="timeout_destination_type"><?php foreach(['hangup'=>'Auflegen','extension'=>'Nebenstelle','ringgroup'=>'Rufgruppe','external'=>'Externe Nummer','voicemail'=>'Voicemail','custom'=>'Benutzerdefiniert'] as $v=>$l): ?><option value="<?php echo qh($v); ?>" <?php echo $edit['timeout_destination_type']===$v?'selected':''; ?>><?php echo qh($l); ?></option><?php endforeach; ?></select></div>
<div class="spbx-field"><label>Context</label><input class="spbx-input" name="timeout_destination_context" value="<?php echo qh($edit['timeout_destination_context']); ?>"></div>
<div class="spbx-field"><label>Extension / Nummer</label><input class="spbx-input" name="timeout_destination_exten" value="<?php echo qh($edit['timeout_destination_exten']); ?>"></div>
</div>

<hr><h3>Agenten</h3>
<label><input type="checkbox" name="callback_login_enabled" <?php echo (int)$edit['callback_login_enabled']?'checked':''; ?>> Optionalen Agent-Login via *45 erlauben</label>
<div class="spbx-grid-2" style="margin-top:14px;">
<div class="spbx-card" style="padding:14px;"><strong>Fixe Agenten</strong><div class="spbx-card-muted">Sind immer Queue-Mitglied.</div>
<?php if ($extensions): mysqli_data_seek($extensions, 0); while($e=$extensions->fetch_assoc()): $eid=$e['endpoint_id']; ?>
<label style="display:block;margin-top:8px;"><input type="checkbox" name="fixed_agents[]" value="<?php echo qh($eid); ?>" <?php echo isset($fixed[$eid])?'checked':''; ?>> <?php echo qh($e['extension'].' - '.$e['display_name']); ?></label>
<?php endwhile; endif; ?>
</div>
<div class="spbx-card" style="padding:14px;"><strong>Berechtigte optionale Agenten</strong><div class="spbx-card-muted">Nur diese dürfen *45 verwenden.</div>
<?php if ($extensions): mysqli_data_seek($extensions, 0); while($e=$extensions->fetch_assoc()): $eid=$e['endpoint_id']; ?>
<label style="display:block;margin-top:8px;"><input type="checkbox" name="optional_agents[]" value="<?php echo qh($eid); ?>" <?php echo isset($optional[$eid])?'checked':''; ?>> <?php echo qh($e['extension'].' - '.$e['display_name']); ?></label>
<?php endwhile; endif; ?>
</div>
</div>

<div style="margin-top:18px;display:flex;gap:10px;">
<button class="spbx-button primary" type="submit">Speichern</button>
<a class="spbx-button secondary" href="queues.php">Abbrechen</a>
<label style="margin-left:auto;"><input type="checkbox" name="active" <?php echo (int)$edit['active']?'checked':''; ?>> Aktiv</label>
</div>
</form>

<?php else: ?>
<div class="spbx-card" style="padding:18px;">
<div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
<div class="spbx-card-title">Queues</div>
<a class="spbx-button primary" href="queues.php?action=new" style="margin-left:auto;">+ Neue Queue</a>
<a class="spbx-button secondary" href="queues.php?action=rebuild">Neu erzeugen</a>
</div>
<table class="spbx-table"><thead><tr><th>Nummer</th><th>Name</th><th>Strategie</th><th>Agenten</th><th>*45</th><th>Status</th><th style="text-align:right;">Aktion</th></tr></thead><tbody>
<?php foreach (($queues ?? []) as $q): ?>
<tr>
<td><strong><?php echo qh($q['queue_number']); ?></strong></td>
<td><?php echo qh($q['queue_name']); ?></td>
<td><?php echo qh($q['strategy']); ?></td>
<td><?php echo queue_member_count((int)$q['id']); ?></td>
<td><?php echo (int)$q['callback_login_enabled'] ? 'Erlaubt' : 'Aus'; ?></td>
<td><?php echo (int)$q['active'] ? 'Aktiv' : 'Inaktiv'; ?></td>
<td style="text-align:right;"><a class="spbx-button small secondary" href="queues.php?action=edit&id=<?php echo (int)$q['id']; ?>">Bearbeiten</a> <a class="spbx-button small danger" href="queues.php?action=delete&id=<?php echo (int)$q['id']; ?>" onclick="return confirm('Queue wirklich löschen?');">Löschen</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>

<div class="spbx-card" style="padding:18px;margin-top:18px;">
<div class="spbx-card-title">Pausengründe</div>
<table class="spbx-table" style="margin-top:10px;"><thead><tr><th>Name</th><th>Sortierung</th><th>Status</th></tr></thead><tbody>
<?php foreach (($pauseReasons ?? []) as $r): ?><tr><td><?php echo qh($r['reason_name']); ?></td><td><?php echo (int)$r['sort_order']; ?></td><td><?php echo (int)$r['active']?'Aktiv':'Inaktiv'; ?></td></tr><?php endforeach; ?>
</tbody></table>
<form method="post" class="spbx-form-grid" style="margin-top:14px;">
<input type="hidden" name="form_action" value="save_pause_reason">
<div class="spbx-field"><label>Neuer Pausengrund</label><input class="spbx-input" name="reason_name" placeholder="z.B. Schulung"></div>
<div class="spbx-field"><label>Sortierung</label><input class="spbx-input" name="sort_order" value="100"></div>
<div class="spbx-field" style="align-self:end;"><button class="spbx-button secondary" type="submit">Hinzufügen</button></div>
</form>
</div>
<?php endif; ?>
</div>
</main>
</div>
</body>
</html>
