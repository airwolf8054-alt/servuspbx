<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/ivr.php';
require_once __DIR__ . '/../inc/tts.php';

spbx_require_admin();
spbx_ivr_install_schema();
spbx_tts_install_schema();
$db = spbx_db();

function ih($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function ipost($k, $d='') { return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; }

function spbx_ivr_extensions_list()
{
    $db = spbx_db();
    $rows = [];
    $res = $db->query("SELECT extension, display_name FROM spbx_extensions WHERE active=1 ORDER BY CAST(extension AS UNSIGNED), extension");
    if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;
    return $rows;
}

function spbx_ivr_queues_list()
{
    $db = spbx_db();
    $rows = [];
    $res = $db->query("SELECT queue_number, queue_name FROM spbx_queues WHERE active=1 ORDER BY CAST(queue_number AS UNSIGNED), queue_number");
    if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;
    return $rows;
}

function spbx_ivr_ringgroups_list()
{
    $db = spbx_db();
    $rows = [];
    $res = $db->query("SELECT group_number, name FROM spbx_ring_groups WHERE active=1 ORDER BY CAST(group_number AS UNSIGNED), group_number");
    if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;
    return $rows;
}

function spbx_ivr_target_select($name, $selected, $allowRepeat = true)
{
    $types = spbx_ivr_target_types();
    if (!$allowRepeat) unset($types['repeat']);
    $html = '<select class="spbx-select" name="' . ih($name) . '">';
    foreach ($types as $k => $label) {
        $html .= '<option value="' . ih($k) . '"' . ((string)$selected === (string)$k ? ' selected' : '') . '>' . ih($label) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function spbx_ivr_target_value($type, $exten = '')
{
    $type = trim((string)$type);
    $exten = trim((string)$exten);
    if ($type === '' || $type === 'none') return 'none|';
    if ($type === 'hangup') return 'hangup|';
    if ($type === 'repeat') return 'repeat|';
    return $type . '|' . $exten;
}

function spbx_ivr_parse_target_value($value)
{
    $parts = explode('|', (string)$value, 2);
    $type = $parts[0] ?? 'none';
    $exten = $parts[1] ?? '';
    if (!isset(spbx_ivr_target_types()[$type])) $type = 'none';
    if ($type === 'none' || $type === 'hangup' || $type === 'repeat') $exten = '';
    return [$type, '', trim((string)$exten)];
}

function spbx_ivr_target_catalog($extensions, $queues, $ringgroups, $ivrs, $currentId = 0, $allowRepeat = true)
{
    $items = [];
    $items[] = ['value'=>'none|', 'label'=>'Nicht belegt', 'short'=>'+', 'type'=>'none'];
    foreach ($queues as $q) {
        $name = trim((string)($q['queue_name'] ?? 'Queue'));
        $num = trim((string)($q['queue_number'] ?? ''));
        if ($num !== '') $items[] = ['value'=>'queue|' . $num, 'label'=>$name . ' (Queue ' . $num . ')', 'short'=>$name, 'type'=>'queue'];
    }
    foreach ($extensions as $e) {
        $num = trim((string)($e['extension'] ?? ''));
        $name = trim((string)($e['display_name'] ?? ''));
        if ($num !== '') $items[] = ['value'=>'extension|' . $num, 'label'=>($name !== '' ? $name : 'Nebenstelle') . ' (' . $num . ')', 'short'=>($name !== '' ? $name : $num), 'type'=>'extension'];
    }
    foreach ($ringgroups as $g) {
        $num = trim((string)($g['group_number'] ?? ''));
        $name = trim((string)($g['name'] ?? 'Rufgruppe'));
        if ($num !== '') $items[] = ['value'=>'ringgroup|' . $num, 'label'=>$name . ' (Rufgruppe ' . $num . ')', 'short'=>$name, 'type'=>'ringgroup'];
    }
    foreach ($ivrs as $i) {
        if ((int)($i['id'] ?? 0) === (int)$currentId) continue;
        $num = trim((string)($i['ivr_number'] ?? ''));
        $name = trim((string)($i['name'] ?? 'Sprachmenü'));
        if ($num !== '') $items[] = ['value'=>'ivr|' . $num, 'label'=>$name . ' (IVR ' . $num . ')', 'short'=>$name, 'type'=>'ivr'];
    }
    if ($allowRepeat) $items[] = ['value'=>'repeat|', 'label'=>'Ansage wiederholen', 'short'=>'Wiederholen', 'type'=>'repeat'];
    $items[] = ['value'=>'hangup|', 'label'=>'Auflegen', 'short'=>'Auflegen', 'type'=>'hangup'];
    return $items;
}

function spbx_ivr_target_select_compact($name, $selectedValue, $items, $attrs = '')
{
    $html = '<select class="spbx-select" name="' . ih($name) . '" ' . $attrs . '>';
    foreach ($items as $it) {
        $html .= '<option value="' . ih($it['value']) . '" data-short="' . ih($it['short']) . '" data-type="' . ih($it['type']) . '"' . ((string)$selectedValue === (string)$it['value'] ? ' selected' : '') . '>' . ih($it['label']) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

$error = '';
$message = '';
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_GET['saved'])) $message = 'Sprachmenü gespeichert. Ansage und Dialplan wurden aktualisiert.';
if (isset($_GET['deleted'])) $message = 'Sprachmenü gelöscht.';
if (isset($_GET['rebuilt'])) $message = 'Sprachmenüs neu erzeugt und Asterisk neu geladen.';
if (isset($_GET['tts_warn'])) $message .= ($message ? ' ' : '') . 'Hinweis: Ansage konnte nicht erzeugt werden. Bitte System → Text-to-Speech prüfen.';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'save_ivr') {
    $id = (int)($_POST['id'] ?? 0);
    $num = preg_replace('/[^0-9]/', '', ipost('ivr_number'));
    $name = ipost('name');
    $description = ipost('description');
    $promptFile = ipost('prompt_file');
    $promptText = ipost('prompt_tts_text');
    $promptType = (($_POST['prompt_announcement_type'] ?? 'tts') === 'mp3') ? 'mp3' : 'tts';
    $promptVoice = spbx_tts_valid_voice($_POST['prompt_tts_voice'] ?? spbx_tts_setting('tts_voice', 'de_DE-thorsten-medium'));
    $promptOptions = spbx_tts_post_options('prompt_tts_options');
    $timeoutSeconds = max(1, min(60, (int)ipost('timeout_seconds', '10')));
    $maxAttempts = max(1, min(9, (int)ipost('max_attempts', '3')));
    [$timeoutType, $timeoutContext, $timeoutExten] = spbx_ivr_parse_target_value(ipost('timeout_target', 'hangup|'));
    [$invalidType, $invalidContext, $invalidExten] = spbx_ivr_parse_target_value(ipost('invalid_target', 'repeat|'));
    $active = isset($_POST['active']) ? 1 : 0;

    if (!preg_match('/^[0-9]{2,6}$/', $num)) $error = 'Bitte eine gültige IVR-Durchwahl eingeben.';
    if ($name === '') $error = 'Bitte einen Namen eingeben.';

    if ($error === '') {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE spbx_ivrs SET ivr_number=?, name=?, description=?, prompt_file=?, prompt_tts_text=?, timeout_seconds=?, max_attempts=?, timeout_target_type=?, timeout_target_context=?, timeout_target_exten=?, invalid_target_type=?, invalid_target_context=?, invalid_target_exten=?, active=? WHERE id=?");
            $stmt->bind_param('sssssisssssssii', $num, $name, $description, $promptFile, $promptText, $timeoutSeconds, $maxAttempts, $timeoutType, $timeoutContext, $timeoutExten, $invalidType, $invalidContext, $invalidExten, $active, $id);
            $stmt->execute();
        } else {
            $stmt = $db->prepare("INSERT INTO spbx_ivrs (ivr_number, name, description, prompt_file, prompt_tts_text, timeout_seconds, max_attempts, timeout_target_type, timeout_target_context, timeout_target_exten, invalid_target_type, invalid_target_context, invalid_target_exten, active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sssssisssssssi', $num, $name, $description, $promptFile, $promptText, $timeoutSeconds, $maxAttempts, $timeoutType, $timeoutContext, $timeoutExten, $invalidType, $invalidContext, $invalidExten, $active);
            $stmt->execute();
            $id = (int)$db->insert_id;
        }

        spbx_tts_set_announcement_type('ivr', $id, 'prompt', $promptType);
        spbx_tts_set_announcement_voice('ivr', $id, 'prompt', $promptVoice);
        spbx_tts_set_announcement_options('ivr', $id, 'prompt', $promptOptions);

        $ttsWarn = false;
        if ($promptType === 'tts') {
            if ($promptText !== '') {
                $ttsErr = '';
                $generated = spbx_tts_generate_if_text($promptText, 'ivr_' . $num . '_prompt', $promptFile, $ttsErr, $promptVoice, $promptOptions);
                if ($generated !== $promptFile) $promptFile = $generated;
                if ($ttsErr !== '') $ttsWarn = true;
            }
        } else {
            $uploadErr = '';
            $uploaded = spbx_tts_upload_mp3('prompt_mp3_upload', 'ivr_' . $num . '_prompt', $uploadErr);
            if ($uploaded) $promptFile = $uploaded;
            if ($uploadErr !== '') $ttsWarn = true;
        }
        $stmt = $db->prepare("UPDATE spbx_ivrs SET prompt_file=? WHERE id=?");
        $stmt->bind_param('si', $promptFile, $id);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM spbx_ivr_options WHERE ivr_id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $pos = 0;
        foreach (spbx_ivr_digits() as $digit) {
            $safe = $digit === '*' ? 'star' : ($digit === '#' ? 'hash' : $digit);
            [$type, $context, $exten] = spbx_ivr_parse_target_value(ipost('digit_' . $safe . '_target', 'none|'));
            $stmt = $db->prepare("INSERT INTO spbx_ivr_options (ivr_id, digit, target_type, target_context, target_exten, sort_order) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('issssi', $id, $digit, $type, $context, $exten, $pos);
            $stmt->execute();
            $pos++;
        }

        spbx_ivr_rebuild_dialplan();
        spbx_audit_log('ivr_save', 'Sprachmenü ' . $num . ' gespeichert.', 'info', null, null, 'ivr');
        header('Location: ivr.php?action=edit&id=' . (int)$id . '&saved=1' . ($ttsWarn ? '&tts_warn=1' : ''));
        exit;
    }
    $action = $id > 0 ? 'edit' : 'new';
}

if ($action === 'delete' && $id > 0) {
    $db->query("DELETE FROM spbx_ivr_options WHERE ivr_id=" . (int)$id);
    $db->query("DELETE FROM spbx_ivrs WHERE id=" . (int)$id);
    spbx_ivr_rebuild_dialplan();
    spbx_audit_log('ivr_delete', 'Sprachmenü gelöscht.', 'warning', null, null, 'ivr');
    header('Location: ivr.php?deleted=1');
    exit;
}

if ($action === 'rebuild') {
    spbx_ivr_rebuild_dialplan();
    header('Location: ivr.php?rebuilt=1');
    exit;
}

$edit = [
    'id'=>0,
    'ivr_number'=>'800',
    'name'=>'Hauptmenü',
    'description'=>'',
    'prompt_file'=>'',
    'prompt_tts_text'=>'',
    'timeout_seconds'=>10,
    'max_attempts'=>3,
    'timeout_target_type'=>'hangup',
    'timeout_target_context'=>'',
    'timeout_target_exten'=>'',
    'invalid_target_type'=>'repeat',
    'invalid_target_context'=>'',
    'invalid_target_exten'=>'',
    'active'=>1,
    'options'=>spbx_ivr_options(0),
];

if (($action === 'edit' || $action === 'new') && $id > 0) {
    $row = spbx_ivr_get($id);
    if ($row) $edit = array_merge($edit, $row);
}

$ivrs = spbx_ivr_all(false);
$extensions = spbx_ivr_extensions_list();
$queues = spbx_ivr_queues_list();
$ringgroups = spbx_ivr_ringgroups_list();
$otherIvrs = spbx_ivr_all(true);
$targetItems = spbx_ivr_target_catalog($extensions, $queues, $ringgroups, $otherIvrs, (int)($edit['id'] ?? 0), true);
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Sprachmenü (IVR) - ServusPBX Professional</title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="stylesheet" href="../css/servuspbx.css">
<style>
.spbx-ann-widget{border:1px solid #d6e2f2;border-radius:14px;padding:14px;background:rgba(248,250,252,.7);margin-top:8px}.spbx-ann-title{font-weight:800;margin-bottom:10px}.spbx-ann-options-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:10px}.spbx-ann-option{border:1px solid #d6e2f2;border-radius:12px;padding:10px;background:#fff}.spbx-ann-option-head{display:flex;justify-content:space-between;gap:12px;font-size:13px;font-weight:700}.spbx-ann-option input{width:100%}.spbx-ann-option small{display:block;color:#64748b;margin-top:4px}
.spbx-ivr-workspace{display:grid;grid-template-columns:minmax(280px,420px) minmax(280px,1fr);gap:18px;align-items:start}.spbx-ivr-phone{border:1px solid #d6e2f2;border-radius:18px;background:linear-gradient(180deg,#fff,#f8fafc);padding:18px}.spbx-ivr-keypad{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.spbx-ivr-padkey{border:1px solid #d6e2f2;border-radius:16px;background:#fff;min-height:86px;padding:10px;text-align:center;cursor:pointer;box-shadow:0 1px 2px rgba(15,23,42,.06);transition:.12s}.spbx-ivr-padkey:hover{transform:translateY(-1px);border-color:#94a3b8}.spbx-ivr-padkey.active{outline:3px solid rgba(37,99,235,.25);border-color:#2563eb}.spbx-ivr-digit{font-size:24px;font-weight:900;line-height:1}.spbx-ivr-label{font-size:12px;margin-top:8px;color:#475569;word-break:break-word;min-height:28px;display:flex;align-items:center;justify-content:center}.spbx-ivr-type-none{background:#fff}.spbx-ivr-type-extension{background:#ecfdf5}.spbx-ivr-type-queue{background:#eff6ff}.spbx-ivr-type-ringgroup{background:#fff7ed}.spbx-ivr-type-ivr{background:#f5f3ff}.spbx-ivr-type-repeat{background:#fefce8}.spbx-ivr-type-hangup{background:#fef2f2}.spbx-ivr-specials{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px}.spbx-ivr-special{border:1px solid #d6e2f2;border-radius:14px;background:#fff;padding:12px;cursor:pointer}.spbx-ivr-special.active{outline:3px solid rgba(37,99,235,.25);border-color:#2563eb}.spbx-ivr-editor{border:1px solid #d6e2f2;border-radius:18px;background:#fff;padding:18px;position:sticky;top:16px}.spbx-ivr-editor-title{font-size:20px;font-weight:900;margin-bottom:10px}.spbx-ivr-flow{border:1px solid #d6e2f2;border-radius:18px;background:#f8fafc;padding:14px;margin-top:18px}.spbx-flow-line{display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px dashed #d6e2f2}.spbx-flow-line:last-child{border-bottom:0}.spbx-flow-badge{min-width:48px;font-weight:900;color:#0f172a}.spbx-flow-target{font-weight:700}.spbx-hidden{display:none!important}@media(max-width:980px){.spbx-ivr-workspace{grid-template-columns:1fr}.spbx-ivr-editor{position:static}.spbx-ann-options-grid{grid-template-columns:1fr}}
</style>
<script>
function spbxAnnToggle(root){const sel=root.querySelector('.spbx-ann-type');if(!sel)return;const t=sel.value;root.querySelectorAll('.spbx-ann-panel-tts').forEach(e=>e.style.display=(t==='tts'?'':'none'));root.querySelectorAll('.spbx-ann-panel-mp3').forEach(e=>e.style.display=(t==='mp3'?'':'none'));}
function spbxAnnRangeUpdate(inp){const out=document.querySelector('[data-ann-out="'+inp.name+'"]');if(out){let suffix=inp.name.includes('sentence_silence')?' s':(inp.name.includes('volume_db')?' dB':'');out.textContent=inp.value+suffix;}}
function spbxAnnInit(){document.querySelectorAll('.spbx-ann-widget').forEach(function(root){spbxAnnToggle(root);const sel=root.querySelector('.spbx-ann-type');if(sel)sel.addEventListener('change',function(){spbxAnnToggle(root);});});document.querySelectorAll('.spbx-ann-option input[type=range]').forEach(function(inp){inp.addEventListener('input',function(){spbxAnnRangeUpdate(inp);});spbxAnnRangeUpdate(inp);});document.querySelectorAll('.spbx-ann-reset-defaults').forEach(function(btn){btn.addEventListener('click',function(){const root=btn.closest('.spbx-ann-widget')||document;const voice=root.querySelector('[name="'+btn.dataset.voiceName+'"]');if(voice&&btn.dataset.defaultVoice)voice.value=btn.dataset.defaultVoice;const map={length_scale:'lengthScale',noise_scale:'noiseScale',noise_w:'noiseW',sentence_silence:'sentenceSilence',volume_db:'volumeDb'};Object.keys(map).forEach(function(k){const name=btn.dataset.prefix+'_'+k;const inp=root.querySelector('[name="'+name+'"]');const val=btn.dataset[map[k]];if(inp&&val!==undefined){inp.value=val;spbxAnnRangeUpdate(inp);}});const note=root.querySelector('.spbx-ann-reset-note');if(note)note.textContent='Standardwerte wurden wiederhergestellt. Bitte speichern, damit die MP3 aktualisiert wird.';});});}
document.addEventListener('DOMContentLoaded',spbxAnnInit);

const spbxIvrTargets = window.spbxIvrTargets || {};
function spbxIvrTargetMeta(value){return spbxIvrTargets[value] || {short:'+', type:'none', label:'Nicht belegt'};}
function spbxIvrShort(value){const m=spbxIvrTargetMeta(value);return m.short || '+';}
function spbxIvrType(value){const m=spbxIvrTargetMeta(value);return m.type || 'none';}
function spbxIvrSetActive(key){
  document.querySelectorAll('[data-ivr-key]').forEach(e=>e.classList.remove('active'));
  const btn=document.querySelector('[data-ivr-key="'+key+'"]'); if(btn)btn.classList.add('active');
  const title=document.getElementById('spbxIvrEditorTitle'); if(title)title.textContent=(key==='timeout'?'Timeout':(key==='invalid'?'Ungültige Eingabe':'Taste '+key));
  const select=document.getElementById('spbxIvrEditorSelect');
  const hidden=document.querySelector('[data-ivr-hidden="'+key+'"]');
  if(select&&hidden) select.value=hidden.value || 'none|';
}
function spbxIvrRefresh(){
  document.querySelectorAll('[data-ivr-key]').forEach(function(btn){
    const key=btn.dataset.ivrKey; const hidden=document.querySelector('[data-ivr-hidden="'+key+'"]'); const value=hidden?hidden.value:'none|';
    btn.className=btn.className.replace(/spbx-ivr-type-\S+/g,'').trim();
    btn.classList.add('spbx-ivr-type-'+spbxIvrType(value));
    const lab=btn.querySelector('.spbx-ivr-label'); if(lab) lab.textContent=spbxIvrShort(value);
    btn.title=spbxIvrTargetMeta(value).label || '';
  });
  const flow=document.getElementById('spbxIvrFlowLines'); if(flow){
    let html=''; document.querySelectorAll('[data-ivr-flow-key]').forEach(function(h){
      const v=h.value || 'none|'; if(spbxIvrType(v)==='none') return;
      const key=h.dataset.ivrFlowKey; const label=(key==='timeout'?'Timeout':(key==='invalid'?'Ungültig':'Taste '+key));
      html += '<div class="spbx-flow-line"><div class="spbx-flow-badge">'+label+'</div><div>→</div><div class="spbx-flow-target">'+spbxIvrShort(v)+'</div></div>';
    });
    flow.innerHTML = html || '<div class="spbx-card-muted">Noch keine Ziele belegt.</div>';
  }
}
function spbxIvrInit(){
  document.querySelectorAll('[data-ivr-key]').forEach(btn=>btn.addEventListener('click',()=>spbxIvrSetActive(btn.dataset.ivrKey)));
  const select=document.getElementById('spbxIvrEditorSelect');
  if(select){select.addEventListener('change',function(){const active=document.querySelector('[data-ivr-key].active'); if(!active)return; const hidden=document.querySelector('[data-ivr-hidden="'+active.dataset.ivrKey+'"]'); if(hidden){hidden.value=select.value; spbxIvrRefresh();}});}
  const remove=document.getElementById('spbxIvrRemoveTarget');
  if(remove){remove.addEventListener('click',function(e){e.preventDefault(); if(select){select.value='none|'; select.dispatchEvent(new Event('change'));}});}
  spbxIvrRefresh(); spbxIvrSetActive('1');
}
document.addEventListener('DOMContentLoaded',spbxIvrInit);
</script>
</head>
<body>
<div class="spbx-app">
<?php spbx_sidebar(); ?>
<main class="spbx-main">
<?php spbx_page_header('Sprachmenü (IVR)', 'Einfache Auswahlmenüs mit TTS oder MP3-Ansage'); ?>
<div class="spbx-content">
<?php if ($message): ?><div class="spbx-alert success"><?php echo ih($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="spbx-alert error"><?php echo ih($error); ?></div><?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>
<form method="post" enctype="multipart/form-data" class="spbx-card" style="padding:18px;">
<input type="hidden" name="form_action" value="save_ivr">
<input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">
<div class="spbx-card-head"><div><div class="spbx-card-title"><?php echo (int)$edit['id'] ? 'Sprachmenü bearbeiten' : 'Sprachmenü anlegen'; ?></div><div class="spbx-card-muted">Die Konfiguration wird ausschließlich in SQL gespeichert. Der Dialplan wird daraus neu erzeugt.</div></div><a class="spbx-button secondary" href="ivr.php">Zur Übersicht</a></div>

<div class="spbx-form-grid" style="margin-top:14px;">
<div class="spbx-field"><label>Durchwahl</label><input class="spbx-input" name="ivr_number" value="<?php echo ih($edit['ivr_number']); ?>" required></div>
<div class="spbx-field"><label>Name</label><input class="spbx-input" name="name" value="<?php echo ih($edit['name']); ?>" required></div>
<div class="spbx-field" style="grid-column:1 / -1;"><label>Beschreibung</label><input class="spbx-input" name="description" value="<?php echo ih($edit['description']); ?>" placeholder="optional"></div>
<div class="spbx-field"><label>Aktiv</label><label><input type="checkbox" name="active" <?php echo (int)$edit['active'] ? 'checked' : ''; ?>> Sprachmenü aktiv</label></div>
</div>

<hr><h3>Ansage</h3>
<input type="hidden" name="prompt_file" value="<?php echo ih($edit['prompt_file']); ?>">
<?php
$promptFallbackType = trim((string)($edit['prompt_tts_text'] ?? '')) !== '' ? 'tts' : 'mp3';
echo spbx_tts_render_announcement_widget([
    'title'=>'IVR-Ansage',
    'type_name'=>'prompt_announcement_type',
    'type_value'=>spbx_tts_get_announcement_type('ivr',(int)($edit['id'] ?? 0),'prompt',$promptFallbackType),
    'text_name'=>'prompt_tts_text',
    'text_value'=>$edit['prompt_tts_text'] ?? '',
    'voice_name'=>'prompt_tts_voice',
    'voice_value'=>spbx_tts_get_announcement_voice('ivr',(int)($edit['id'] ?? 0),'prompt'),
    'upload_name'=>'prompt_mp3_upload',
    'file_value'=>$edit['prompt_file'] ?? '',
    'options_prefix'=>'prompt_tts_options',
    'options'=>spbx_tts_get_announcement_options('ivr',(int)($edit['id'] ?? 0),'prompt'),
    'placeholder'=>'z.B. Willkommen. Für Support drücken Sie 1, für Verkauf drücken Sie 2.'
]);
?>

<hr><h3>Tastenbelegung</h3>
<p class="spbx-card-muted">Klicken Sie auf eine Taste, um das Ziel festzulegen.</p>
<script>
window.spbxIvrTargets = <?php
$jsTargets = [];
foreach ($targetItems as $it) $jsTargets[$it['value']] = ['short'=>$it['short'], 'type'=>$it['type'], 'label'=>$it['label']];
echo json_encode($jsTargets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>;
</script>
<div class="spbx-ivr-workspace">
  <div>
    <div class="spbx-ivr-phone">
      <div class="spbx-ivr-keypad">
      <?php foreach (['1','2','3','4','5','6','7','8','9','*','0','#'] as $digit):
          $safe = $digit === '*' ? 'star' : ($digit === '#' ? 'hash' : $digit);
          $opt = $edit['options'][$digit] ?? ['target_type'=>'none','target_exten'=>''];
          $value = spbx_ivr_target_value($opt['target_type'] ?? 'none', $opt['target_exten'] ?? '');
      ?>
        <button type="button" class="spbx-ivr-padkey" data-ivr-key="<?php echo ih($digit); ?>">
          <div class="spbx-ivr-digit"><?php echo ih($digit); ?></div>
          <div class="spbx-ivr-label">+</div>
        </button>
        <input type="hidden" data-ivr-hidden="<?php echo ih($digit); ?>" data-ivr-flow-key="<?php echo ih($digit); ?>" name="digit_<?php echo ih($safe); ?>_target" value="<?php echo ih($value); ?>">
      <?php endforeach; ?>
      </div>
      <div class="spbx-ivr-specials">
        <?php $timeoutValue = spbx_ivr_target_value($edit['timeout_target_type'] ?? 'hangup', $edit['timeout_target_exten'] ?? ''); ?>
        <?php $invalidValue = spbx_ivr_target_value($edit['invalid_target_type'] ?? 'repeat', $edit['invalid_target_exten'] ?? ''); ?>
        <button type="button" class="spbx-ivr-special" data-ivr-key="timeout"><strong>⏱ Timeout</strong><br><span class="spbx-ivr-label">+</span></button>
        <input type="hidden" data-ivr-hidden="timeout" data-ivr-flow-key="timeout" name="timeout_target" value="<?php echo ih($timeoutValue); ?>">
        <button type="button" class="spbx-ivr-special" data-ivr-key="invalid"><strong>⚠ Ungültig</strong><br><span class="spbx-ivr-label">+</span></button>
        <input type="hidden" data-ivr-hidden="invalid" data-ivr-flow-key="invalid" name="invalid_target" value="<?php echo ih($invalidValue); ?>">
      </div>
    </div>

    <div class="spbx-ivr-flow">
      <strong>Callflow-Vorschau</strong>
      <div class="spbx-card-muted" style="margin:4px 0 8px;">Anruf → <?php echo ih($edit['name'] ?: 'Sprachmenü'); ?></div>
      <div id="spbxIvrFlowLines"></div>
    </div>
  </div>

  <div class="spbx-ivr-editor">
    <div class="spbx-ivr-editor-title" id="spbxIvrEditorTitle">Taste 1</div>
    <div class="spbx-field">
      <label>Ziel</label>
      <?php echo spbx_ivr_target_select_compact('ivr_editor_target', 'none|', $targetItems, 'id="spbxIvrEditorSelect"'); ?>
    </div>
    <button class="spbx-button secondary" id="spbxIvrRemoveTarget">Ziel entfernen</button>
  </div>
</div>

<hr><h3>Timeout</h3>
<div class="spbx-form-grid">
<div class="spbx-field"><label>Timeout Sekunden</label><input class="spbx-input" type="number" min="1" max="60" name="timeout_seconds" value="<?php echo (int)$edit['timeout_seconds']; ?>"></div>
<div class="spbx-field"><label>Maximale Versuche</label><input class="spbx-input" type="number" min="1" max="9" name="max_attempts" value="<?php echo (int)$edit['max_attempts']; ?>"><div class="spbx-card-muted">für spätere Komfortlogik vorbereitet</div></div>
</div>
<div style="margin-top:18px;display:flex;gap:10px;">
<button class="spbx-button primary" type="submit">Speichern &amp; MP3 erzeugen</button>
<a class="spbx-button secondary" href="ivr.php">Abbrechen</a>
</div>
</form>

<?php else: ?>
<div class="spbx-card" style="padding:18px;">
<div class="spbx-card-head"><div><div class="spbx-card-title">Sprachmenüs</div><div class="spbx-card-muted"><?php echo count($ivrs); ?> Einträge vorhanden.</div></div><div style="display:flex;gap:10px;"><a class="spbx-button secondary" href="ivr.php?action=rebuild">Dialplan neu erzeugen</a><a class="spbx-button primary" href="ivr.php?action=new">+ Neues Sprachmenü</a></div></div>
<table class="spbx-table"><thead><tr><th>Durchwahl</th><th>Name</th><th>Ansage</th><th>Timeout</th><th>Status</th><th style="text-align:right;">Aktion</th></tr></thead><tbody>
<?php if (!$ivrs): ?><tr><td colspan="6">Noch keine Sprachmenüs vorhanden.</td></tr><?php endif; ?>
<?php foreach ($ivrs as $i): ?>
<tr>
<td><strong><?php echo ih($i['ivr_number']); ?></strong></td>
<td><?php echo ih($i['name']); ?></td>
<td><?php echo trim((string)$i['prompt_file']) !== '' ? 'MP3 vorhanden' : 'keine Ansage'; ?></td>
<td><?php echo (int)$i['timeout_seconds']; ?> s</td>
<td><?php echo (int)$i['active'] ? 'Aktiv' : 'Inaktiv'; ?></td>
<td style="text-align:right;"><a class="spbx-button small secondary" href="ivr.php?action=edit&id=<?php echo (int)$i['id']; ?>">Bearbeiten</a> <a class="spbx-button small danger" href="ivr.php?action=delete&id=<?php echo (int)$i['id']; ?>" onclick="return confirm('Sprachmenü wirklich löschen?');">Löschen</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<?php endif; ?>
</div>
</main>
</div>
</body>
</html>
