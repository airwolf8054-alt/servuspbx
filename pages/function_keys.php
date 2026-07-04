<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/asterisk_notify.php';

spbx_require_login();
$db = spbx_db();

$user = spbx_current_user();
$isAdmin = spbx_is_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$message = '';


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


function spbx_keys_clean_callerid($callerid, $fallback = '')
{
    $callerid = trim((string)$callerid);
    if ($callerid === '') {
        return $fallback;
    }

    if (preg_match('/"([^"]+)"/', $callerid, $m)) {
        return trim($m[1]);
    }

    $callerid = preg_replace('/\s*<[^>]+>\s*/', '', $callerid);
    return trim($callerid) !== '' ? trim($callerid) : $fallback;
}

function spbx_keys_extension_label_map()
{
    $db = spbx_db();
    $map = [];

    $res = $db->query("
        SELECT id, extension, display_name, callerid
        FROM ps_endpoints
        WHERE active=1
    ");

    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $ext = (string)$r['extension'];
            $label = spbx_keys_clean_callerid($r['callerid'] ?? '', $r['display_name'] ?? $ext);
            $map[$ext] = $label !== '' ? $label : $ext;
            $map[(string)$r['id']] = $map[$ext];
        }
    }

    return $map;
}



function spbx_keys_grid_class($model)
{
    if ($model === 'snomD810' || $model === 'GigasetP810') return 'd810';
    if ($model === 'snomD812' || $model === 'snomD862' || $model === 'snomD865' || $model === 'snomD892' || $model === 'GigasetP82x') return 'p82x';
    if ($model === 'GigasetP85x') return 'p85x';
    if ($model === 'snomD895') return 'd895';
    return 'd815';
}


function spbx_keys_visual_positions($model)
{
    if ($model === 'snomD810' || $model === 'GigasetP810') {
        // 4 physische Tasten auf der rechten Geräteseite.
        return [0, 1, 2, 3];
    }

    if ($model === 'snomD812' || $model === 'snomD862' || $model === 'snomD865' || $model === 'snomD892' || $model === 'GigasetP82x') {
        // 8 physische Tasten: 1-4 links, 5-8 rechts.
        return [0, 4, 1, 5, 2, 6, 3, 7];
    }

    if ($model === 'snomD895') {
        // 14 physische Tasten: 1-7 links, 8-14 rechts.
        return [0, 7, 1, 8, 2, 9, 3, 10, 4, 11, 5, 12, 6, 13];
    }

    // 10 physische Tasten: 1-5 links, 6-10 rechts.
    return [0, 5, 1, 6, 2, 7, 3, 8, 4, 9];
}

function spbx_keys_visual_side($model, $physicalIndex)
{
    if ($model === 'snomD895') {
        return $physicalIndex < 7 ? 'Links' : 'Rechts';
    }

    if ($model === 'snomD815' || $model === 'GigasetP85x') {
        return $physicalIndex < 5 ? 'Links' : 'Rechts';
    }

    if ($model === 'snomD812' || $model === 'snomD862' || $model === 'snomD865' || $model === 'snomD892' || $model === 'GigasetP82x') {
        return $physicalIndex < 4 ? 'Links' : 'Rechts';
    }

    if ($model === 'snomD810' || $model === 'GigasetP810') {
        return 'Rechts';
    }

    return '';
}

function spbx_keys_visual_number($model, $physicalIndex)
{
    return $physicalIndex + 1;
}





function spbx_keys_schema_ok()
{
    $db = spbx_db();
    $res = $db->query("SHOW TABLES LIKE 'deskphones'");
    if (!$res || $res->num_rows < 1) return false;
    $res = $db->query("SHOW COLUMNS FROM deskphones LIKE 'fkey41label'");
    return $res && $res->num_rows > 0;
}

function spbx_keys_device($id)
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
}

function spbx_keys_physical_count($model)
{
    if ($model === 'snomD895') return 14;
    if ($model === 'snomD815') return 10;
    if ($model === 'GigasetP85x') return 10;
    if ($model === 'snomD812') return 8;
    if ($model === 'snomD862') return 8;
    if ($model === 'snomD865') return 8;
    if ($model === 'snomD892') return 8;
    if ($model === 'GigasetP82x') return 8;
    if ($model === 'snomD810') return 4;
    if ($model === 'GigasetP810') return 4;
    return 0;
}

function spbx_keys_model_label($model)
{
    if ($model === 'snomD815') return 'snom D815';
    if ($model === 'snomD810') return 'snom D810';
    if ($model === 'snomD812') return 'snom D812';
    if ($model === 'snomD862') return 'snom D862';
    if ($model === 'snomD865') return 'snom D865';
    if ($model === 'snomD892') return 'snom D892M';
    if ($model === 'snomD895') return 'snom D895M';
    if ($model === 'GigasetP810') return 'Gigaset P810';
    if ($model === 'GigasetP82x') return 'Gigaset P82x';
    if ($model === 'GigasetP85x') return 'Gigaset P85x';
    return $model;
}

function spbx_keys_type_label($type)
{
    return [
        'none' => 'Leer',
        'blf' => 'Nebenstelle',
        'dest' => 'Rufnummer',
        'forward' => 'Rufumleitung',
    ][$type] ?? 'Leer';
}

function spbx_keys_type_icon($type)
{
    return [
        'none' => '—',
        'blf' => '👤',
        'dest' => '☎️',
        'forward' => '↪️',
    ][$type] ?? '—';
}

if (!spbx_keys_schema_ok()) {
    http_response_code(500);
    exit('Datenbank-Patch für Funktionstasten fehlt: deskphones mit fkey0..fkey41 ist nicht vorhanden. Bitte patch_servuspbx_professional_2_6_1_legacy_deskphones.sql importieren.');
}

$device = spbx_keys_device($id);

if (!$device) {
    http_response_code(404);
    exit('Nebenstelle oder Gerät nicht gefunden.');
}

if (!$isAdmin && (($user['extension'] ?? '') !== $device['extension'])) {
    http_response_code(403);
    exit('Zugriff verweigert.');
}

$physical = spbx_keys_physical_count($device['device_model']);
if ($physical <= 0) {
    http_response_code(400);
    exit('Dieses Gerät unterstützt keine Funktionstasten in ServusPBX Medical.');
}

$pages = ($device['device_model'] === 'snomD895') ? 3 : 4;
$total = min($physical * $pages, 42);
$extensionLabelMap = spbx_keys_extension_label_map();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['form_action'] ?? '') === 'reset_keys')) {
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
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['form_action'] ?? '') !== 'reset_keys')) {
    $db->begin_transaction();

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

        $db->commit();

        spbx_notify_snom_check_cfg($device['endpoint_id']);

        spbx_audit_log('function_keys_save', 'Tastenbelegung für Nebenstelle ' . $device['extension'] . ' gespeichert.', 'info', null, null, 'keys');

        header('Location: function_keys.php?id=' . $id . '&saved=1');
        exit;
    } catch (Throwable $e) {
        $db->rollback();
        $error = 'Speichern fehlgeschlagen: ' . $e->getMessage();
    }
}

if (isset($_GET['saved'])) {
    $message = 'Tastenbelegung gespeichert.';
}
if (isset($_GET['reset'])) {
    $message = 'Alle Tastenbelegungen wurden zurückgesetzt.';
}

$keys = [];
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

$extensions = [];
$res = $db->query("
    SELECT
        p.id,
        p.extension,
        p.display_name,
        p.callerid
    FROM ps_endpoints p
    JOIN spbx_extensions e ON e.endpoint_id=p.id
    WHERE p.active=1
    ORDER BY CAST(p.extension AS UNSIGNED), p.extension
");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $extensions[] = $r;
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Tastenbelegung - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('Tastenbelegung', $device['extension'] . ' - ' . $device['display_name']); ?>

        <div class="spbx-content spbx-keys-layout">
            <?php if ($message): ?><div class="spbx-alert success"><?php echo spbx_h($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="spbx-alert error"><?php echo spbx_h($error); ?></div><?php endif; ?>

            <div class="spbx-keys-card">
                <div class="spbx-keys-header">
                    <div>
                        <div class="spbx-keys-title">
                            <?php echo spbx_h(spbx_keys_model_label($device['device_model'])); ?>
                        </div>
                        <div class="spbx-keys-subtitle">
                            <?php echo (int)$physical; ?> physische Tasten · <?php echo (int)$pages; ?> Ebenen · <?php echo (int)$total; ?> Einträge · fkey0 bis fkey<?php echo (int)($total - 1); ?>
                        </div>
                    </div>
                    <div class="spbx-keys-top-actions">
                        <form method="post" onsubmit="return confirm('Alle Tastenbelegungen wirklich zurücksetzen?');">
                            <input type="hidden" name="form_action" value="reset_keys">
                            <button class="spbx-button secondary" type="submit">Alle BLF zurücksetzen</button>
                        </form>
                        <button class="spbx-button primary" type="submit" form="spbxKeysForm">Speichern</button>
                        <a class="spbx-button secondary" href="extensions.php">Zurück</a>
                    </div>
                </div>

                <div class="spbx-keys-tabs">
                    <?php for ($page = 1; $page <= $pages; $page++): ?>
                        <button class="spbx-keys-tab <?php echo $page === 1 ? 'active' : ''; ?>" type="button" data-key-page="<?php echo $page; ?>">
                            Ebene <?php echo $page; ?>
                        </button>
                    <?php endfor; ?>
                </div>

                <form method="post" id="spbxKeysForm">
                    <input type="hidden" name="form_action" value="save_keys">
                    <?php for ($page = 1; $page <= $pages; $page++): ?>
                        <div class="spbx-keys-page <?php echo $page === 1 ? 'active' : ''; ?>" id="keyPage<?php echo $page; ?>">
                            <div class="spbx-keys-phone-grid <?php echo spbx_h(spbx_keys_grid_class($device['device_model'])); ?>">
                                <?php foreach (spbx_keys_visual_positions($device['device_model']) as $physicalIndex): ?>
                                    <?php
                                        $idx = (($page - 1) * $physical) + $physicalIndex;
                                        $visualSide = spbx_keys_visual_side($device['device_model'], $physicalIndex);
                                        $visualNumber = spbx_keys_visual_number($device['device_model'], $physicalIndex);
                                        $k = $keys[$idx] ?? ['key_type'=>'none','key_label'=>'','key_value'=>''];
                                        $keyType = $k['key_type'] ?? 'none';
                                        if (!in_array($keyType, ['none','blf','dest','forward'], true)) $keyType = 'none';
                                        $value = $k['key_value'] ?? '';

                                        // Bereits vorhandene Selbst-BLFs in der Oberfläche als leer behandeln.
                                        if ($keyType === 'blf' && (string)$value === (string)$device['extension']) {
                                            $keyType = 'none';
                                            $value = '';
                                        }

                                        $displayLabel = $k['key_label'] ?? '';
                                        if ($keyType === 'blf') {
                                            $displayLabel = $extensionLabelMap[$value] ?? $value;
                                        }
                                        if ($keyType === 'forward' && $displayLabel === '') {
                                            $displayLabel = 'Rufumleitung';
                                        }
                                    ?>
                                    <div class="spbx-key-card <?php echo $visualSide === 'Links' ? 'spbx-key-left' : 'spbx-key-right'; ?>" data-key-card="<?php echo $idx; ?>" data-type="<?php echo spbx_h($keyType); ?>">
                                        <div class="spbx-key-card-head">
                                            <div>
                                                <div class="spbx-key-card-title"><?php echo $visualSide !== '' ? spbx_h($visualSide) . ' ' : ''; ?>Taste <?php echo (int)$visualNumber; ?></div>
                                                <div class="spbx-key-card-sub">Ebene <?php echo $page; ?> · fkey<?php echo $idx; ?></div>
                                            </div>
                                            <div class="spbx-key-kind" data-kind-label="<?php echo $idx; ?>">
                                                <?php echo spbx_h(spbx_keys_type_icon($keyType) . ' ' . spbx_keys_type_label($keyType)); ?>
                                            </div>
                                        </div>

                                        <div class="spbx-key-fields">
                                            <div class="spbx-key-field">
                                                <label>Funktion</label>
                                                <select name="key_type[<?php echo $idx; ?>]" data-key-type="<?php echo $idx; ?>">
                                                    <option value="none" <?php echo $keyType === 'none' ? 'selected' : ''; ?>>— Leer</option>
                                                    <option value="blf" <?php echo $keyType === 'blf' ? 'selected' : ''; ?>>👤 Nebenstelle</option>
                                                    <option value="dest" <?php echo $keyType === 'dest' ? 'selected' : ''; ?>>☎️ Rufnummer</option>
                                                    <option value="forward" <?php echo $keyType === 'forward' ? 'selected' : ''; ?>>↪️ Rufumleitung</option>
                                                </select>
                                            </div>

                                            <div class="spbx-key-field spbx-key-target-extension">
                                                <label>Nebenstelle</label>
                                                <select name="key_extension[<?php echo $idx; ?>]" data-extension-select="<?php echo $idx; ?>">
                                                    <option value="">Bitte wählen...</option>
                                                    <?php foreach ($extensions as $e): ?>
                                                        <?php if ((string)$e['extension'] === (string)$device['extension']) continue; ?>
                                                        <option value="<?php echo spbx_h($e['extension']); ?>"
                                                                data-label="<?php echo spbx_h(spbx_keys_clean_callerid($e['callerid'] ?? '', $e['display_name'] ?? $e['extension'])); ?>"
                                                                <?php echo ($keyType === 'blf' && $value === $e['extension']) ? 'selected' : ''; ?>>
                                                            <?php echo spbx_h($e['extension'] . ' - ' . $e['display_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="spbx-key-field spbx-key-target-number">
                                                <label>Rufnummer</label>
                                                <input type="text" name="key_number[<?php echo $idx; ?>]" value="<?php echo $keyType === 'dest' ? spbx_h($value) : ''; ?>" placeholder="z. B. +43123456789">
                                            </div>

                                            <div class="spbx-key-field spbx-key-label">
                                                <label>Beschriftung</label>
                                                <input type="text" name="key_label[<?php echo $idx; ?>]" value="<?php echo spbx_h($displayLabel); ?>" placeholder="z. B. Anmeldung">
                                            </div>

                                            <div class="spbx-key-field spbx-key-label-forward">
                                                <label>Beschriftung</label>
                                                <input type="text" name="key_label[<?php echo $idx; ?>]" value="<?php echo spbx_h($displayLabel ?: 'Rufumleitung'); ?>" placeholder="Rufumleitung">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endfor; ?>

                    
                </form>
            </div>
        </div>
    </main>
</div>

<script>
const keyTypeLabels = {
    none: '— Leer',
    blf: '👤 Nebenstelle',
    dest: '☎️ Rufnummer',
    forward: '↪️ Rufumleitung'
};

document.querySelectorAll('.spbx-keys-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        const page = btn.dataset.keyPage;

        document.querySelectorAll('.spbx-keys-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.spbx-keys-page').forEach(p => p.classList.remove('active'));

        btn.classList.add('active');
        document.getElementById('keyPage' + page).classList.add('active');
    });
});

document.querySelectorAll('[data-key-type]').forEach(sel => {
    sel.addEventListener('change', () => {
        const idx = sel.dataset.keyType;
        const card = document.querySelector('[data-key-card="' + idx + '"]');
        const label = document.querySelector('[data-kind-label="' + idx + '"]');

        card.dataset.type = sel.value;
        label.textContent = keyTypeLabels[sel.value] || keyTypeLabels.none;

        if (sel.value === 'forward') {
            const input = card.querySelector('.spbx-key-label-forward input');
            if (input && input.value === '') input.value = 'Rufumleitung';
        }
    });
});

document.querySelectorAll('[data-extension-select]').forEach(sel => {
    sel.addEventListener('change', () => {
        const idx = sel.dataset.extensionSelect;
        const card = document.querySelector('[data-key-card="' + idx + '"]');
        const labelInput = card.querySelector('.spbx-key-label input');
        const selected = sel.options[sel.selectedIndex];

        if (labelInput && selected && selected.dataset.label) {
            labelInput.value = selected.dataset.label;
        }
    });
});
</script>
</body>
</html>
