<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/outbound_routes.php';

spbx_require_admin();
$db = spbx_db();
spbx_outbound_install_schema();
spbx_outbound_sync_from_trunks();

function ob_h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $routeName = trim((string)($_POST['route_name'] ?? ''));
        $provider = strtoupper(trim((string)($_POST['provider'] ?? 'A1')));
        $mainNumber = spbx_outbound_normalize_main_number($_POST['main_number'] ?? '');
        $trunkEndpoint = trim((string)($_POST['trunk_endpoint'] ?? ''));
        $outCtx = spbx_outbound_context_from_number($mainNumber);
        $clip = isset($_POST['clip_no_screening']) ? 1 : 0;
        $mode = ($_POST['callerid_mode'] ?? 'extension') === 'main' ? 'main' : 'extension';
        $allowedProfiles = ['AT','DE','CH','LI','CUSTOM'];
        $emergencyProfile = strtoupper(trim((string)($_POST['emergency_profile'] ?? 'AT')));
        if (!in_array($emergencyProfile, $allowedProfiles, true)) $emergencyProfile = 'AT';
        $active = isset($_POST['active']) ? 1 : 0;

        if ($routeName === '') $routeName = $provider . ' ' . $mainNumber;
        if ($mainNumber === '') $errors[] = 'Hauptnummer fehlt.';
        if ($trunkEndpoint === '') $errors[] = 'Trunk Endpoint fehlt.';

        if (!$errors) {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE spbx_outbound_routes SET route_name=?, provider=?, main_number=?, trunk_endpoint=?, outgoing_context=?, clip_no_screening=?, callerid_mode=?, emergency_profile=?, active=? WHERE id=?");
                $stmt->bind_param('sssssissii', $routeName, $provider, $mainNumber, $trunkEndpoint, $outCtx, $clip, $mode, $emergencyProfile, $active, $id);
                $stmt->execute();
            } else {
                $stmt = $db->prepare("INSERT INTO spbx_outbound_routes (route_name, provider, main_number, trunk_endpoint, outgoing_context, clip_no_screening, callerid_mode, emergency_profile, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sssssissi', $routeName, $provider, $mainNumber, $trunkEndpoint, $outCtx, $clip, $mode, $emergencyProfile, $active);
                $stmt->execute();
            }
            spbx_outbound_rebuild_dialplan();
            $message = 'Ausgehende Route gespeichert und Dialplan neu geladen.';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->query("DELETE FROM spbx_outbound_routes WHERE id=" . $id);
            spbx_outbound_rebuild_dialplan();
            $message = 'Ausgehende Route gelöscht.';
        }
    } elseif ($action === 'rebuild') {
        spbx_outbound_rebuild_dialplan();
        $message = 'Ausgehender Dialplan neu aufgebaut.';
    }
}

$edit = null;
if (isset($_GET['new'])) {
    $edit = ['id'=>0,'route_name'=>'','provider'=>'A1','main_number'=>'','trunk_endpoint'=>'','outgoing_context'=>'','clip_no_screening'=>1,'callerid_mode'=>'extension','emergency_profile'=>'AT','active'=>1];
} elseif (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $db->query("SELECT * FROM spbx_outbound_routes WHERE id=" . $id . " LIMIT 1");
    $edit = $res ? $res->fetch_assoc() : null;
}

$routes = $db->query("SELECT * FROM spbx_outbound_routes ORDER BY id ASC");
$trunks = $db->query("SELECT endpoint_id, provider, main_number, trunk_name, name FROM spbx_trunks WHERE COALESCE(endpoint_id,'')<>'' ORDER BY provider, main_number, trunk_name, name");
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Anrufregeln - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">

    <style>
        /* 1.4.7 unified form css guard */
        .spbx-form .spbx-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 22px;
        }
        .spbx-form .spbx-field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }
        .spbx-form .spbx-field label {
            font-weight: 800;
            color: #071f45;
        }
        .spbx-form input[type="text"],
        .spbx-form input[type="password"],
        .spbx-form input[type="number"],
        .spbx-form input:not([type]),
        .spbx-form select {
            width: 100%;
            min-height: 44px;
            border: 1px solid #cbd8e8;
            border-radius: 12px;
            padding: 10px 14px;
            background: #fff;
            color: #061b3a;
            font: inherit;
            box-sizing: border-box;
        }
        .spbx-form input[type="checkbox"] {
            width: 22px;
            height: 22px;
            vertical-align: middle;
        }
        .spbx-form .spbx-card-muted {
            font-weight: 700;
            color: #5d7190;
            line-height: 1.35;
        }
        .spbx-form .spbx-actions {
            margin-top: 22px;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        @media (max-width: 900px) {
            .spbx-form .spbx-grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>

</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>
    <main class="spbx-main">
        <?php spbx_page_header('Anrufregeln', 'Eingehende und ausgehende Callflows'); ?>
        <div class="spbx-rule-tabs">
            <a href="call_rules.php">Eingehend</a>
            <a class="active" href="outbound_routes.php">Ausgehend</a>
        </div>
        <div class="spbx-content">
            <div class="spbx-card">
                <div class="spbx-card-head">
                    <div>
                        <div class="spbx-card-title"><?php echo $edit ? 'Ausgehende Regel bearbeiten' : 'Ausgehende Regeln'; ?></div>
                        <div class="spbx-card-muted">Incoming bleibt incoming. Ausgehend nutzt outgoing_&lt;rufnummer&gt;.</div>
                    </div>
                    <?php if (!$edit): ?>
                        <div>
                            <a class="spbx-button primary" href="outbound_routes.php?new=1">+ Neue Route</a>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="rebuild">
                                <button class="spbx-button secondary" type="submit">Dialplan neu aufbauen</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <?php foreach ($errors as $e): ?><div class="spbx-alert error"><?php echo ob_h($e); ?></div><?php endforeach; ?>
                <?php if ($message): ?><div class="spbx-alert success"><?php echo ob_h($message); ?></div><?php endif; ?>

                <?php if ($edit): ?>
                    <form method="post" class="spbx-form">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">

                        <div class="spbx-grid-2">
                            <div class="spbx-field">
                                <label>Name</label>
                                <input name="route_name" value="<?php echo ob_h($edit['route_name']); ?>" placeholder="A1 Ordination">
                            </div>
                            <div class="spbx-field">
                                <label>Provider</label>
                                <select name="provider">
                                    <option value="A1" <?php echo strtoupper($edit['provider'])==='A1'?'selected':''; ?>>A1</option>
                                    <option value="MAGENTA" <?php echo strtoupper($edit['provider'])==='MAGENTA'?'selected':''; ?>>Magenta später</option>
                                </select>
                            </div>
                            <div class="spbx-field">
                                <label>Hauptnummer</label>
                                <input name="main_number" value="<?php echo ob_h($edit['main_number']); ?>" placeholder="+43312423826">
                                <div class="spbx-card-muted">Context automatisch: outgoing_43312423826</div>
                            </div>
                            <div class="spbx-field">
                                <label>Trunk Endpoint</label>
                                <select name="trunk_endpoint" required>
                                    <option value="">Bitte wählen</option>
                                    <?php if ($trunks): while ($t = $trunks->fetch_assoc()):
                                        $ep = (string)$t['endpoint_id'];
                                        $label = trim(($t['provider'] ?: 'Trunk') . ' ' . ($t['main_number'] ?: '') . ' ' . ($t['trunk_name'] ?: ($t['name'] ?: $ep)));
                                    ?>
                                        <option value="<?php echo ob_h($ep); ?>" <?php echo $edit['trunk_endpoint']===$ep?'selected':''; ?>>
                                            <?php echo ob_h($label . ' - ' . $ep); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div class="spbx-field">
                                <label>CLIP no Screening</label>
                                <label><input type="checkbox" name="clip_no_screening" <?php echo (int)$edit['clip_no_screening']===1?'checked':''; ?>> aktiv</label>
                            </div>
                            <div class="spbx-field">
                                <label>Ausgehende CallerID</label>
                                <select name="callerid_mode">
                                    <option value="main" <?php echo $edit['callerid_mode']==='main'?'selected':''; ?>>Hauptnummer</option>
                                    <option value="extension" <?php echo $edit['callerid_mode']==='extension'?'selected':''; ?>>Hauptnummer + Nebenstelle</option>
                                </select>
                            </div>

                            <div class="spbx-field">
                                <label>Land / Notrufprofil</label>
                                <select name="emergency_profile">
                                    <option value="AT" <?php echo (($edit['emergency_profile'] ?? 'AT')==='AT')?'selected':''; ?>>Österreich</option>
                                    <option value="DE" <?php echo (($edit['emergency_profile'] ?? '')==='DE')?'selected':''; ?>>Deutschland</option>
                                    <option value="CH" <?php echo (($edit['emergency_profile'] ?? '')==='CH')?'selected':''; ?>>Schweiz</option>
                                    <option value="LI" <?php echo (($edit['emergency_profile'] ?? '')==='LI')?'selected':''; ?>>Liechtenstein</option>
                                    <option value="CUSTOM" <?php echo (($edit['emergency_profile'] ?? '')==='CUSTOM')?'selected':''; ?>>Benutzerdefiniert später</option>
                                </select>
                                <div class="spbx-card-muted">Es werden nur die Notrufnummern dieses Landes in internal_&lt;rufnummer&gt; erzeugt.</div>
                            </div>

                            <div class="spbx-field">
                                <label>Status</label>
                                <label><input type="checkbox" name="active" <?php echo (int)$edit['active']===1?'checked':''; ?>> aktiv</label>
                            </div>
                        </div>
                        <div class="spbx-actions">
                            <a class="spbx-button secondary" href="outbound_routes.php">Abbrechen</a>
                            <button class="spbx-button primary" type="submit">Speichern</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="spbx-table-wrap">
                        <table class="spbx-table">
                            <thead><tr><th>Name</th><th>Provider</th><th>Hauptnummer</th><th>Context</th><th>CallerID</th><th>Notrufprofil</th><th>Trunk</th><th>Status</th><th>Aktion</th></tr></thead>
                            <tbody>
                            <?php if ($routes && $routes->num_rows): while ($r = $routes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo ob_h($r['route_name']); ?></td>
                                    <td><?php echo ob_h($r['provider']); ?></td>
                                    <td><?php echo ob_h($r['main_number']); ?></td>
                                    <td><?php echo ob_h($r['outgoing_context']); ?></td>
                                    <td><?php echo $r['callerid_mode']==='extension' ? 'Hauptnummer + Nebenstelle' : 'Hauptnummer'; ?></td>
                                    <td><?php echo ob_h($r['emergency_profile'] ?? 'AT'); ?></td>
                                    <td><?php echo ob_h($r['trunk_endpoint']); ?></td>
                                    <td><?php echo (int)$r['active']===1 ? 'Aktiv' : 'Inaktiv'; ?></td>
                                    <td>
                                        <a class="spbx-button small secondary" href="outbound_routes.php?edit=<?php echo (int)$r['id']; ?>">Bearbeiten</a>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Route löschen?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                            <button class="spbx-button small danger" type="submit">Löschen</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="9">Keine ausgehenden Routen vorhanden.</td></tr>
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
