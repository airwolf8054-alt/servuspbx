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
    $timeoutType = ipost('timeout_target_type', 'hangup');
    $timeoutContext = ipost('timeout_target_context');
    $timeoutExten = ipost('timeout_target_exten');
    $invalidType = ipost('invalid_target_type', 'repeat');
    $invalidContext = ipost('invalid_target_context');
    $invalidExten = ipost('invalid_target_exten');
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
            $type = ipost('digit_' . $safe . '_type', 'none');
            $context = ipost('digit_' . $safe . '_context');
            $exten = ipost('digit_' . $safe . '_exten');
            if (!isset(spbx_ivr_target_types()[$type])) $type = 'none';
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
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Sprachmenü (IVR) - ServusPBX Professional</title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="stylesheet" href="../css/servuspbx.css">
<style>
.spbx-ivr-grid{display:grid;grid-template-columns:repeat(3,110px);gap:10px;align-items:stretch}.spbx-ivr-key{border:1px solid #d6e2f2;border-radius:14px;background:#fff;padding:10px}.spbx-ivr-key strong{display:block;font-size:22px;margin-bottom:6px}.spbx-ivr-key select,.spbx-ivr-key input{width:100%;margin-top:6px}.spbx-ann-widget{border:1px solid #d6e2f2;border-radius:14px;padding:14px;background:rgba(248,250,252,.7);margin-top:8px}.spbx-ann-title{font-weight:800;margin-bottom:10px}.spbx-ann-options-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:10px}.spbx-ann-option{border:1px solid #d6e2f2;border-radius:12px;padding:10px;background:#fff}.spbx-ann-option-head{display:flex;justify-content:space-between;gap:12px;font-size:13px;font-weight:700}.spbx-ann-option input{width:100%}.spbx-ann-option small{display:block;color:#64748b;margin-top:4px}@media(max-width:900px){.spbx-ivr-grid{grid-template-columns:1fr}.spbx-ann-options-grid{grid-template-columns:1fr}}
</style>
<script>
function spbxAnnToggle(root){const sel=root.querySelector('.spbx-ann-type');if(!sel)return;const t=sel.value;root.querySelectorAll('.spbx-ann-panel-tts').forEach(e=>e.style.display=(t==='tts'?'':'none'));root.querySelectorAll('.spbx-ann-panel-mp3').forEach(e=>e.style.display=(t==='mp3'?'':'none'));}
function spbxAnnRangeUpdate(inp){const out=document.querySelector('[data-ann-out="'+inp.name+'"]');if(out){let suffix=inp.name.includes('sentence_silence')?' s':(inp.name.includes('volume_db')?' dB':'');out.textContent=inp.value+suffix;}}
function spbxAnnInit(){document.querySelectorAll('.spbx-ann-widget').forEach(function(root){spbxAnnToggle(root);const sel=root.querySelector('.spbx-ann-type');if(sel)sel.addEventListener('change',function(){spbxAnnToggle(root);});});document.querySelectorAll('.spbx-ann-option input[type=range]').forEach(function(inp){inp.addEventListener('input',function(){spbxAnnRangeUpdate(inp);});spbxAnnRangeUpdate(inp);});document.querySelectorAll('.spbx-ann-reset-defaults').forEach(function(btn){btn.addEventListener('click',function(){const root=btn.closest('.spbx-ann-widget')||document;const voice=root.querySelector('[name="'+btn.dataset.voiceName+'"]');if(voice&&btn.dataset.defaultVoice)voice.value=btn.dataset.defaultVoice;const map={length_scale:'lengthScale',noise_scale:'noiseScale',noise_w:'noiseW',sentence_silence:'sentenceSilence',volume_db:'volumeDb'};Object.keys(map).forEach(function(k){const name=btn.dataset.prefix+'_'+k;const inp=root.querySelector('[name="'+name+'"]');const val=btn.dataset[map[k]];if(inp&&val!==undefined){inp.value=val;spbxAnnRangeUpdate(inp);}});const note=root.querySelector('.spbx-ann-reset-note');if(note)note.textContent='Standardwerte wurden wiederhergestellt. Bitte speichern, damit die MP3 aktualisiert wird.';});});}
document.addEventListener('DOMContentLoaded',spbxAnnInit);
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
<div class="spbx-ivr-grid">
<?php foreach (spbx_ivr_digits() as $digit): $safe = $digit === '*' ? 'star' : ($digit === '#' ? 'hash' : $digit); $opt = $edit['options'][$digit] ?? ['target_type'=>'none','target_context'=>'','target_exten'=>'']; ?>
<div class="spbx-ivr-key">
<strong><?php echo ih($digit); ?></strong>
<label>Zieltyp</label>
<?php echo spbx_ivr_target_select('digit_' . $safe . '_type', $opt['target_type'] ?? 'none', true); ?>
<label>Context</label><input class="spbx-input" name="digit_<?php echo ih($safe); ?>_context" value="<?php echo ih($opt['target_context'] ?? ''); ?>" placeholder="optional">
<label>Ziel</label><input class="spbx-input" name="digit_<?php echo ih($safe); ?>_exten" value="<?php echo ih($opt['target_exten'] ?? ''); ?>" list="spbx_ivr_targets" placeholder="Nebenstelle / Queue / IVR">
</div>
<?php endforeach; ?>
</div>
<datalist id="spbx_ivr_targets">
<?php foreach ($extensions as $e): ?><option value="<?php echo ih($e['extension']); ?>"><?php echo ih($e['extension'] . ' - ' . $e['display_name']); ?></option><?php endforeach; ?>
<?php foreach ($queues as $q): ?><option value="<?php echo ih($q['queue_number']); ?>"><?php echo ih($q['queue_number'] . ' - Queue ' . $q['queue_name']); ?></option><?php endforeach; ?>
<?php foreach ($ringgroups as $g): ?><option value="<?php echo ih($g['group_number']); ?>"><?php echo ih($g['group_number'] . ' - Rufgruppe ' . $g['name']); ?></option><?php endforeach; ?>
<?php foreach ($otherIvrs as $i): if ((int)$i['id'] === (int)$edit['id']) continue; ?><option value="<?php echo ih($i['ivr_number']); ?>"><?php echo ih($i['ivr_number'] . ' - IVR ' . $i['name']); ?></option><?php endforeach; ?>
</datalist>

<hr><h3>Timeout und ungültige Eingabe</h3>
<div class="spbx-form-grid">
<div class="spbx-field"><label>Timeout Sekunden</label><input class="spbx-input" type="number" min="1" max="60" name="timeout_seconds" value="<?php echo (int)$edit['timeout_seconds']; ?>"></div>
<div class="spbx-field"><label>Maximale Versuche</label><input class="spbx-input" type="number" min="1" max="9" name="max_attempts" value="<?php echo (int)$edit['max_attempts']; ?>"><div class="spbx-card-muted">für spätere Komfortlogik vorbereitet</div></div>
<div class="spbx-field"><label>Timeout Zieltyp</label><?php echo spbx_ivr_target_select('timeout_target_type', $edit['timeout_target_type'] ?? 'hangup', true); ?></div>
<div class="spbx-field"><label>Timeout Context</label><input class="spbx-input" name="timeout_target_context" value="<?php echo ih($edit['timeout_target_context']); ?>"></div>
<div class="spbx-field"><label>Timeout Ziel</label><input class="spbx-input" name="timeout_target_exten" value="<?php echo ih($edit['timeout_target_exten']); ?>" list="spbx_ivr_targets"></div>
<div class="spbx-field"><label>Ungültige Eingabe Zieltyp</label><?php echo spbx_ivr_target_select('invalid_target_type', $edit['invalid_target_type'] ?? 'repeat', true); ?></div>
<div class="spbx-field"><label>Ungültig Context</label><input class="spbx-input" name="invalid_target_context" value="<?php echo ih($edit['invalid_target_context']); ?>"></div>
<div class="spbx-field"><label>Ungültig Ziel</label><input class="spbx-input" name="invalid_target_exten" value="<?php echo ih($edit['invalid_target_exten']); ?>" list="spbx_ivr_targets"></div>
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
