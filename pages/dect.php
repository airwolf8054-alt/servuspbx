<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';

spbx_require_admin();
$db = spbx_db();

function spbx_dect_post($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function spbx_dect_mac($value)
{
    return strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', (string)$value));
}

function spbx_dect_type_label($type)
{
    if ($type === 'snomM900') return 'snom M900';
    if ($type === 'GigasetN610') return 'Gigaset N610 IP PRO';
    return 'snom M400';
}

function spbx_dect_limit($type)
{
    if ($type === 'snomM900') return 1000;
    if ($type === 'GigasetN610') return 8;
    return 20;
}

function spbx_dect_load_base($id)
{
    $db = spbx_db();
    $stmt = $db->prepare("SELECT * FROM spbx_dect_bases WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function spbx_dect_next_idx($baseId, $baseType)
{
    $db = spbx_db();
    $limit = spbx_dect_limit($baseType);

    $stmt = $db->prepare("SELECT idx_number FROM spbx_dect_base_handsets WHERE base_id=? ORDER BY idx_number ASC");
    $stmt->bind_param('i', $baseId);
    $stmt->execute();
    $res = $stmt->get_result();

    $used = [];
    while ($r = $res->fetch_assoc()) {
        $used[(int)$r['idx_number']] = true;
    }

    for ($i = 1; $i <= $limit; $i++) {
        if (empty($used[$i])) {
            return $i;
        }
    }

    return null;
}

function spbx_dect_save_base($id)
{
    $db = spbx_db();

    $baseName = spbx_dect_post('base_name');
    $baseType = spbx_dect_post('base_type');
    $mac = spbx_dect_mac(spbx_dect_post('mac_address'));
    $ip = spbx_dect_post('ip_address');
    $location = spbx_dect_post('location');
    $provisioning = isset($_POST['provisioning_enabled']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;

    if ($baseName === '') {
        return 'Bitte einen Namen für die DECT-Basis eingeben.';
    }

    if (!in_array($baseType, ['snomM400', 'snomM900', 'GigasetN610'], true)) {
        return 'Bitte einen gültigen Basistyp auswählen.';
    }

    if ($mac === '') {
        return 'Bitte die MAC-Adresse der Basisstation eingeben.';
    }

    $prov = $baseType === 'GigasetN610' ? 'GigasetN610.php' : ($baseType === 'snomM900' ? 'snomM900.php' : 'snomM400.php');

    if ($id > 0) {
        $stmt = $db->prepare("
            UPDATE spbx_dect_bases
            SET base_name=?, base_type=?, mac_address=?, ip_address=?, location=?,
                provisioning_enabled=?, provision_file=?, active=?
            WHERE id=?
        ");
        $stmt->bind_param('sssssisis', $baseName, $baseType, $mac, $ip, $location, $provisioning, $prov, $active, $id);
        $stmt->execute();
        spbx_audit_log('dect_base_update', 'DECT-Basis ' . $baseName . ' geändert.', 'info', null, null, 'dect');
    } else {
        $stmt = $db->prepare("
            INSERT INTO spbx_dect_bases
            (base_name, base_type, mac_address, ip_address, location, provisioning_enabled, provision_file, active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sssssisi', $baseName, $baseType, $mac, $ip, $location, $provisioning, $prov, $active);
        $stmt->execute();
        spbx_audit_log('dect_base_create', 'DECT-Basis ' . $baseName . ' angelegt.', 'info', null, null, 'dect');
    }

    return '';
}

function spbx_dect_delete_base($id)
{
    $db = spbx_db();
    $base = spbx_dect_load_base($id);
    if (!$base) return;

    $db->begin_transaction();
    try {
        $stmt = $db->prepare("DELETE FROM spbx_dect_base_handsets WHERE base_id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM spbx_dect_bases WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $db->commit();
        spbx_audit_log('dect_base_delete', 'DECT-Basis ' . $base['base_name'] . ' gelöscht.', 'warning', null, null, 'dect');
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

function spbx_dect_assign_handset($baseId, $endpointId)
{
    $db = spbx_db();
    $base = spbx_dect_load_base($baseId);
    if (!$base) return 'DECT-Basis nicht gefunden.';

    $endpointId = trim((string)$endpointId);
    if ($endpointId === '') return 'Bitte ein Handset auswählen.';

    $stmt = $db->prepare("SELECT id FROM spbx_dect_base_handsets WHERE base_id=? AND endpoint_id=? LIMIT 1");
    $stmt->bind_param('is', $baseId, $endpointId);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        return 'Dieses Handset ist dieser Basis bereits zugeordnet.';
    }

    $nextIdx = spbx_dect_next_idx($baseId, $base['base_type']);
    if ($nextIdx === null) {
        return 'Keine freie IDX mehr verfügbar. Limit für ' . spbx_dect_type_label($base['base_type']) . ' erreicht.';
    }

    $stmt = $db->prepare("SELECT extension, display_name FROM ps_endpoints WHERE id=? AND device_model='snom_dect_handset' LIMIT 1");
    $stmt->bind_param('s', $endpointId);
    $stmt->execute();
    $ep = $stmt->get_result()->fetch_assoc();

    if (!$ep) {
        return 'Handset Endpoint nicht gefunden.';
    }

    $stmt = $db->prepare("
        INSERT INTO spbx_dect_base_handsets
        (base_id, idx_number, endpoint_id, extension, display_name)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iisss', $baseId, $nextIdx, $endpointId, $ep['extension'], $ep['display_name']);
    $stmt->execute();

    spbx_audit_log('dect_handset_assign', 'Handset ' . $endpointId . ' als IDX ' . $nextIdx . ' zugeordnet.', 'info', null, null, 'dect');

    return '';
}


function spbx_dect_m400_sync($id)
{
    $base = spbx_dect_load_base($id);
    if (!$base) return 'DECT-Basis nicht gefunden.';
    if (($base['base_type'] ?? '') !== 'snomM400') return 'Sync ist derzeit nur für snom M400 aktiviert.';

    $ip = trim((string)($base['ip_address'] ?? ''));
    if ($ip === '') return 'Keine IP-Adresse der M400 bekannt. Bitte Basis einmal provisionieren lassen oder IP manuell eintragen.';

    $url = 'http://' . $ip . '/CfgResync';
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 2,
            'ignore_errors' => true,
        ]
    ]);

    $ok = @file_get_contents($url, false, $ctx);
    spbx_audit_log('dect_m400_sync', 'M400 Sync ausgelöst für ' . $base['base_name'] . ' (' . $ip . ').', 'info', null, null, 'dect');

    return '';
}


function spbx_dect_remove_handset($id)
{
    $db = spbx_db();

    $stmt = $db->prepare("SELECT * FROM spbx_dect_base_handsets WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) return;

    $stmt = $db->prepare("DELETE FROM spbx_dect_base_handsets WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    spbx_audit_log('dect_handset_remove', 'Handset ' . $row['endpoint_id'] . ' von IDX ' . $row['idx_number'] . ' entfernt. IDX wird wieder frei.', 'warning', null, null, 'dect');
}

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$message = '';
$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['form_action'] ?? '') === 'save_base') {
        $saveId = (int)($_POST['id'] ?? 0);
        $error = spbx_dect_save_base($saveId);
        if ($error === '') {
            header('Location: dect.php?saved=1');
            exit;
        }
        $action = $saveId > 0 ? 'edit' : 'new';
        $edit = $_POST;
        $edit['id'] = $saveId;
    }

    if (($_POST['form_action'] ?? '') === 'assign_handset') {
        $baseId = (int)($_POST['base_id'] ?? 0);
        $endpointId = spbx_dect_post('endpoint_id');
        $error = spbx_dect_assign_handset($baseId, $endpointId);
        if ($error === '') {
            header('Location: dect.php?action=handsets&id=' . $baseId . '&assigned=1');
            exit;
        }
        $action = 'handsets';
        $id = $baseId;
    }
}

if ($action === 'delete' && $id > 0) {
    spbx_dect_delete_base($id);
    header('Location: dect.php?deleted=1');
    exit;
}


if ($action === 'sync_m400' && $id > 0) {
    $error = spbx_dect_m400_sync($id);
    if ($error === '') {
        header('Location: dect.php?sync=1');
        exit;
    }
    $action = '';
}

if ($action === 'remove_handset' && $id > 0) {
    $baseId = isset($_GET['base_id']) ? (int)$_GET['base_id'] : 0;
    spbx_dect_remove_handset($id);
    header('Location: dect.php?action=handsets&id=' . $baseId . '&removed=1');
    exit;
}

if ($action === 'edit' && $id > 0 && !$edit) {
    $edit = spbx_dect_load_base($id);
    if (!$edit) {
        header('Location: dect.php');
        exit;
    }
}

$editAssignedHandsets = [];
if (($action === 'edit') && !empty($edit['id'])) {
    $stmt = $db->prepare("
        SELECT idx_number, endpoint_id, extension, display_name
        FROM spbx_dect_base_handsets
        WHERE base_id=?
        ORDER BY idx_number ASC
    ");
    if ($stmt) {
        $editBaseId = (int)$edit['id'];
        $stmt->bind_param('i', $editBaseId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $editAssignedHandsets[] = $r;
        }
    }
}

if ($action === 'new' && !$edit) {
    $edit = [
        'id' => 0,
        'base_name' => '',
        'base_type' => 'snomM400',
        'mac_address' => '',
        'ip_address' => '',
        'location' => '',
        'provisioning_enabled' => 1,
        'active' => 1,
    ];
}

if (isset($_GET['saved'])) $message = 'DECT-Basis gespeichert.';
if (isset($_GET['deleted'])) $message = 'DECT-Basis gelöscht.';
if (isset($_GET['sync'])) $message = 'M400 Sync ausgelöst.';
if (isset($_GET['assigned'])) $message = 'Handset zugeordnet.';
if (isset($_GET['removed'])) $message = 'Handset entfernt. Die IDX ist wieder frei.';

$bases = [];
if ($action === 'list') {
    $res = $db->query("
        SELECT b.*,
               COUNT(h.id) AS handset_count
        FROM spbx_dect_bases b
        LEFT JOIN spbx_dect_base_handsets h ON h.base_id=b.id
        GROUP BY b.id
        ORDER BY b.base_name
    ");
    if ($res) {
        while ($r = $res->fetch_assoc()) $bases[] = $r;
    }
}

$base = null;
$assigned = [];
$availableHandsets = [];
$nextIdx = null;

if ($action === 'handsets' && $id > 0) {
    $base = spbx_dect_load_base($id);
    if (!$base) {
        header('Location: dect.php');
        exit;
    }

    $nextIdx = spbx_dect_next_idx($id, $base['base_type']);

    $stmt = $db->prepare("SELECT * FROM spbx_dect_base_handsets WHERE base_id=? ORDER BY idx_number ASC");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $assigned[] = $r;

    $res = $db->query("
        SELECT e.id, e.extension, e.display_name, d.ipei
        FROM ps_endpoints e
        LEFT JOIN spbx_devices d ON d.endpoint_id=e.id
        WHERE e.device_model='snom_dect_handset'
          AND e.id NOT IN (
              SELECT endpoint_id FROM spbx_dect_base_handsets WHERE base_id=" . (int)$id . "
          )
        ORDER BY CAST(e.extension AS UNSIGNED), e.extension
    ");
    if ($res) {
        while ($r = $res->fetch_assoc()) $availableHandsets[] = $r;
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>DECT - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('DECT', 'Basisstationen und Handset-Zuordnung'); ?>

        <div class="spbx-content spbx-dect-layout">
            <?php if ($message): ?><div class="spbx-alert success"><?php echo spbx_h($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="spbx-alert error"><?php echo spbx_h($error); ?></div><?php endif; ?>

            <?php if ($action === 'new' || $action === 'edit'): ?>
                <div class="spbx-dect-card">
                    <div class="spbx-dect-header">
                        <div>
                            <div class="spbx-dect-title"><?php echo $action === 'edit' ? 'DECT-Basis bearbeiten' : 'DECT-Basis anlegen'; ?></div>
                            <div class="spbx-dect-subtitle">M400 unterstützt IDX 1 bis 20. Gigaset N610 unterstützt bis zu 8 Handsets. M900 unterstützt IDX 1 bis 1000.</div>
                        </div>
                    </div>

                    <form method="post">
                        <input type="hidden" name="form_action" value="save_base">
                        <input type="hidden" name="id" value="<?php echo (int)($edit['id'] ?? 0); ?>">

                        <div class="spbx-dect-grid">
                            <div class="spbx-dect-field">
                                <label>Name *</label>
                                <input type="text" name="base_name" value="<?php echo spbx_h($edit['base_name'] ?? ''); ?>" required autofocus>
                            </div>

                            <div class="spbx-dect-field">
                                <label>Basistyp *</label>
                                <select name="base_type" required>
                                    <option value="snomM400" <?php echo (($edit['base_type'] ?? '') === 'snomM400') ? 'selected' : ''; ?>>snom M400</option>
                                    <option value="snomM900" <?php echo (($edit['base_type'] ?? '') === 'snomM900') ? 'selected' : ''; ?>>snom M900</option>
                                    <option value="GigasetN610" <?php echo (($edit['base_type'] ?? '') === 'GigasetN610') ? 'selected' : ''; ?>>Gigaset N610 IP PRO</option>
                                </select>
                            </div>

                            <div class="spbx-dect-field">
                                <label>MAC-Adresse *</label>
                                <input type="text" name="mac_address" value="<?php echo spbx_h($edit['mac_address'] ?? ''); ?>" required>
                            </div>

                            <div class="spbx-dect-field">
                                <label>IP-Adresse</label>
                                <input type="text" name="ip_address" value="<?php echo spbx_h($edit['ip_address'] ?? ''); ?>">
                            </div>

                            <div class="spbx-dect-field">
                                <label>Standort</label>
                                <input type="text" name="location" value="<?php echo spbx_h($edit['location'] ?? ''); ?>">
                            </div>

                            <div class="spbx-dect-field">
                                <label>Status</label>
                                <label class="spbx-extensions-check">
                                    <input type="checkbox" name="active" value="1" <?php echo !empty($edit['active']) ? 'checked' : ''; ?>>
                                    Basis aktiv
                                </label>
                            </div>
                        </div>

                        <div class="spbx-dect-actions">
                            <a class="spbx-button secondary" href="dect.php">Abbrechen</a>
                            <button class="spbx-button primary" type="submit">Speichern</button>
                        </div>
                    </form>

                    <?php if ($action === 'edit'): ?>
                        <div class="spbx-dect-assign-box">
                            <div class="spbx-dect-title">Zugeordnete Handsets</div>
                            <div class="spbx-dect-subtitle">
                                Diese Übersicht zeigt die aktuelle IDX-Zuordnung dieser Basisstation.
                            </div>

                            <?php if (!$editAssignedHandsets): ?>
                                <div class="spbx-dect-empty" style="margin-top:14px;">
                                    <strong>Keine Handsets zugeordnet</strong>
                                    Die Zuordnung erfolgt über die Nebenstellenmaske oder über „Handsets“ in der DECT-Liste.
                                </div>
                            <?php else: ?>
                                <div class="spbx-dect-table-wrap" style="margin-top:14px;">
                                    <table class="spbx-dect-table">
                                        <thead>
                                            <tr>
                                                <th>IDX</th>
                                                <th>Nebenstelle</th>
                                                <th>Name</th>
                                                <th>Endpoint / IPEI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($editAssignedHandsets as $hs): ?>
                                                <tr>
                                                    <td><span class="spbx-dect-badge"><?php echo (int)$hs['idx_number']; ?></span></td>
                                                    <td><?php echo spbx_h($hs['extension']); ?></td>
                                                    <td><?php echo spbx_h($hs['display_name']); ?></td>
                                                    <td><?php echo spbx_h($hs['endpoint_id']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="spbx-dect-actions">
                                    <a class="spbx-button secondary" href="dect.php?action=handsets&id=<?php echo (int)$edit['id']; ?>">Handsets verwalten</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($action === 'handsets'): ?>
                <div class="spbx-dect-card">
                    <div class="spbx-dect-header">
                        <div>
                            <div class="spbx-dect-title">Handsets zuordnen: <?php echo spbx_h($base['base_name']); ?></div>
                            <div class="spbx-dect-subtitle">
                                <?php echo spbx_h(spbx_dect_type_label($base['base_type'])); ?> · Limit: <?php echo (int)spbx_dect_limit($base['base_type']); ?> · nächste freie IDX:
                                <?php echo $nextIdx === null ? 'keine' : (int)$nextIdx; ?>
                            </div>
                        </div>
                        <a class="spbx-button secondary" href="dect.php">Zurück</a>
                    </div>

                    <div class="spbx-dect-table-wrap">
                        <table class="spbx-dect-table">
                            <thead>
                                <tr>
                                    <th>IDX</th>
                                    <th>Nebenstelle</th>
                                    <th>Name</th>
                                    <th>Endpoint / IPEI</th>
                                    <th style="text-align:right;">Aktion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$assigned): ?>
                                    <tr><td colspan="5">Noch keine Handsets zugeordnet.</td></tr>
                                <?php endif; ?>

                                <?php foreach ($assigned as $h): ?>
                                    <tr>
                                        <td><span class="spbx-dect-badge"><?php echo (int)$h['idx_number']; ?></span></td>
                                        <td><?php echo spbx_h($h['extension']); ?></td>
                                        <td><?php echo spbx_h($h['display_name']); ?></td>
                                        <td><?php echo spbx_h($h['endpoint_id']); ?></td>
                                        <td>
                                            <div class="spbx-dect-table-actions">
                                                <a class="spbx-button small danger"
                                                   href="dect.php?action=remove_handset&id=<?php echo (int)$h['id']; ?>&base_id=<?php echo (int)$base['id']; ?>"
                                                   onclick="return confirm('Handset von dieser Basis entfernen? Die IDX wird danach wieder frei.');">Entfernen</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="spbx-dect-assign-box">
                        <div class="spbx-dect-title">Handset hinzufügen</div>
                        <div class="spbx-dect-subtitle">
                            Die IDX wird automatisch vergeben. Freie niedrigste IDX wird zuerst verwendet.
                        </div>

                        <?php if ($nextIdx === null): ?>
                            <div class="spbx-alert error" style="margin-top:12px;">Keine freie IDX mehr verfügbar.</div>
                        <?php else: ?>
                            <form method="post" class="spbx-dect-assign-form">
                                <input type="hidden" name="form_action" value="assign_handset">
                                <input type="hidden" name="base_id" value="<?php echo (int)$base['id']; ?>">

                                <div class="spbx-dect-field">
                                    <label>Handset auswählen</label>
                                    <select name="endpoint_id" required>
                                        <option value="">Bitte wählen...</option>
                                        <?php foreach ($availableHandsets as $hs): ?>
                                            <option value="<?php echo spbx_h($hs['id']); ?>">
                                                <?php echo spbx_h($hs['extension'] . ' - ' . $hs['display_name'] . (($hs['ipei'] ?? '') !== '' ? ' / IPEI ' . $hs['ipei'] : '')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <button class="spbx-button primary" type="submit">Als IDX <?php echo (int)$nextIdx; ?> hinzufügen</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="spbx-dect-card">
                    <div class="spbx-dect-header">
                        <div>
                            <div class="spbx-dect-title">DECT-Basisstationen</div>
                            <div class="spbx-dect-subtitle">M400: IDX 1–20 · Gigaset N610: IDX 1–8 · M900: IDX 1–1000</div>
                        </div>
                        <a class="spbx-button primary" href="dect.php?action=new">+ Neue DECT-Basis</a>
                    </div>

                    <?php if (!$bases): ?>
                        <div class="spbx-dect-empty">
                            <strong>Keine DECT-Basis vorhanden</strong>
                            Lege die erste M400 oder M900 Basis an.
                        </div>
                    <?php else: ?>
                        <div class="spbx-dect-table-wrap">
                            <table class="spbx-dect-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Typ</th>
                                        <th>MAC</th>
                                        <th>IP</th>
                                        <th>Standort</th>
                                        <th>Handsets</th>
                                        <th>Status</th>
                                        <th style="text-align:right;">Aktion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bases as $b): ?>
                                        <tr>
                                            <td><?php echo spbx_h($b['base_name']); ?></td>
                                            <td><?php echo spbx_h(spbx_dect_type_label($b['base_type'])); ?></td>
                                            <td><?php echo spbx_h($b['mac_address']); ?></td>
                                            <td><?php echo spbx_h($b['ip_address']); ?></td>
                                            <td><?php echo spbx_h($b['location']); ?></td>
                                            <td><?php echo (int)$b['handset_count']; ?> / <?php echo (int)spbx_dect_limit($b['base_type']); ?></td>
                                            <td><?php echo !empty($b['active']) ? 'Aktiv' : 'Inaktiv'; ?></td>
                                            <td>
                                                <div class="spbx-dect-table-actions">
                                                    <a class="spbx-button small secondary" href="dect.php?action=handsets&id=<?php echo (int)$b['id']; ?>">Handsets</a>
                                                    <?php if (($b['base_type'] ?? '') === 'snomM400'): ?>
                                                        <a class="spbx-button small secondary" href="dect.php?action=sync_m400&id=<?php echo (int)$b['id']; ?>" onclick="return confirm('M400 Sync jetzt auslösen?');">Sync</a>
                                                    <?php endif; ?>
                                                    <a class="spbx-button small secondary" href="dect.php?action=edit&id=<?php echo (int)$b['id']; ?>">Bearbeiten</a>
                                                    <a class="spbx-button small danger" href="dect.php?action=delete&id=<?php echo (int)$b['id']; ?>" onclick="return confirm('DECT-Basis wirklich löschen?');">Löschen</a>
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
