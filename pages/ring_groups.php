<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/ring_groups.php';

spbx_require_admin();

$db = spbx_db();
spbx_ring_groups_install_schema();

function spbx_rg_h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function spbx_rg_post($key, $default = '') {
    return trim((string)($_POST[$key] ?? $default));
}

function spbx_rg_extensions()
{
    $db = spbx_db();
    $rows = [];

    $res = $db->query("
        SELECT extension, display_name
        FROM spbx_extensions
        WHERE extension REGEXP '^[0-9]{2,4}$'
        ORDER BY CAST(extension AS UNSIGNED), extension
    ");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
    }

    return $rows;
}

function spbx_rg_get($id)
{
    $db = spbx_db();
    $stmt = $db->prepare("SELECT * FROM spbx_ring_groups WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $g = $stmt->get_result()->fetch_assoc();

    if (!$g) return null;

    $members = [];
    $stmt = $db->prepare("
        SELECT extension
        FROM spbx_ring_group_members
        WHERE group_id=?
          AND active=1
        ORDER BY sort_order ASC, CAST(extension AS UNSIGNED), extension
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($m = $res->fetch_assoc()) {
        $members[] = (string)$m['extension'];
    }

    $g['members'] = $members;
    return $g;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $groupNo = spbx_rg_post('group_number');
        $name = spbx_rg_post('name');
        $strategy = spbx_rg_post('strategy', 'ringall');
        $ringTime = (int)spbx_rg_post('ring_time', '25');
        $timeoutAction = spbx_rg_post('timeout_action', 'hangup');
        $timeoutTarget = spbx_rg_post('timeout_target');
        $active = isset($_POST['active']) ? 1 : 0;
        $members = $_POST['members'] ?? [];

        if (!preg_match('/^[0-9]{2,4}$/', $groupNo)) {
            $error = 'Rufgruppennummer muss 2- bis 4-stellig sein.';
        } elseif ($name === '') {
            $error = 'Name fehlt.';
        } elseif (!in_array($strategy, ['ringall','hunt','random'], true)) {
            $error = 'Ungültige Strategie.';
        } else {
            $ringTime = max(5, min(120, $ringTime));
            if (!in_array($timeoutAction, ['hangup','extension'], true)) {
                $timeoutAction = 'hangup';
            }

            $cleanMembers = [];
            foreach ($members as $m) {
                $m = trim((string)$m);
                if (preg_match('/^[0-9]{2,4}$/', $m) && !in_array($m, $cleanMembers, true) && $m !== $groupNo) {
                    $cleanMembers[] = $m;
                }
            }

            try {
                $db->begin_transaction();

                if ($id > 0) {
                    $stmt = $db->prepare("
                        UPDATE spbx_ring_groups
                        SET group_number=?, name=?, strategy=?, ring_time=?, timeout_action=?, timeout_target=?, active=?
                        WHERE id=?
                    ");
                    $stmt->bind_param('sssissii', $groupNo, $name, $strategy, $ringTime, $timeoutAction, $timeoutTarget, $active, $id);
                    $stmt->execute();
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO spbx_ring_groups
                        (group_number, name, strategy, ring_time, timeout_action, timeout_target, active)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param('sssissi', $groupNo, $name, $strategy, $ringTime, $timeoutAction, $timeoutTarget, $active);
                    $stmt->execute();
                    $id = (int)$db->insert_id;
                }

                $stmt = $db->prepare("DELETE FROM spbx_ring_group_members WHERE group_id=?");
                $stmt->bind_param('i', $id);
                $stmt->execute();

                $pos = 0;
                foreach ($cleanMembers as $ext) {
                    $stmt = $db->prepare("
                        INSERT INTO spbx_ring_group_members
                        (group_id, extension, sort_order, active)
                        VALUES (?, ?, ?, 1)
                    ");
                    $stmt->bind_param('isi', $id, $ext, $pos);
                    $stmt->execute();
                    $pos++;
                }

                $db->commit();

                spbx_ring_groups_rebuild_dialplan();

                spbx_audit_log('ring_group_save', 'Rufgruppe ' . $groupNo . ' gespeichert.', 'info', null, null, 'ring_groups');
                header('Location: ring_groups.php?saved=1');
                exit;
            } catch (Throwable $e) {
                $db->rollback();
                $error = 'Speichern fehlgeschlagen: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM spbx_ring_group_members WHERE group_id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $stmt = $db->prepare("DELETE FROM spbx_ring_groups WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            spbx_ring_groups_rebuild_dialplan();

            spbx_audit_log('ring_group_delete', 'Rufgruppe gelöscht.', 'warning', null, null, 'ring_groups');
            header('Location: ring_groups.php?deleted=1');
            exit;
        }
    }

    if ($action === 'rebuild') {
        spbx_ring_groups_rebuild_dialplan();
        header('Location: ring_groups.php?rebuilt=1');
        exit;
    }
}

if (isset($_GET['saved'])) $message = 'Rufgruppe gespeichert.';
if (isset($_GET['deleted'])) $message = 'Rufgruppe gelöscht.';
if (isset($_GET['rebuilt'])) $message = 'Dialplan für Rufgruppen neu erzeugt.';

$edit = null;
if (isset($_GET['new'])) {
    $edit = [
        'id' => 0,
        'group_number' => '500',
        'name' => '',
        'strategy' => 'ringall',
        'ring_time' => 25,
        'timeout_action' => 'hangup',
        'timeout_target' => '',
        'active' => 1,
        'members' => [],
    ];
} elseif (isset($_GET['edit'])) {
    $edit = spbx_rg_get((int)$_GET['edit']);
}

$extensions = spbx_rg_extensions();
spbx_ring_groups_install_schema();
$groups = $db->query("SELECT * FROM spbx_ring_groups ORDER BY CAST(group_number AS UNSIGNED), group_number");
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Rufgruppen - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
    <style>
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
        .spbx-form input,
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
        .spbx-rg-members {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 8px;
        }
        .spbx-rg-member {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border: 1px solid #d7e3f3;
            border-radius: 14px;
            background: #fff;
            font-weight: 800;
        }
        .spbx-rg-member input {
            width: 20px;
            min-height: 20px;
        }
        .spbx-field-full {
            grid-column: 1 / -1;
        }
        @media (max-width: 900px) {
            .spbx-form .spbx-grid-2,
            .spbx-rg-members {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('Rufgruppen', 'Gruppenrufe für Empfang, Stationen und Abteilungen'); ?>

        <div class="spbx-content">
            <div class="spbx-card">
                <div class="spbx-card-head">
                    <div>
                        <div class="spbx-card-title"><?php echo $edit ? 'Rufgruppe bearbeiten' : 'Rufgruppen'; ?></div>
                        <div class="spbx-card-muted">Rufgruppen sind intern direkt über ihre Gruppennummer erreichbar.</div>
                    </div>
                    <div class="spbx-actions">
                        <?php if (!$edit): ?>
                            <a class="spbx-button primary" href="ring_groups.php?new=1">+ Neue Rufgruppe</a>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="action" value="rebuild">
                                <button class="spbx-button secondary" type="submit">Dialplan neu erzeugen</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($message): ?><div class="spbx-alert success"><?php echo spbx_rg_h($message); ?></div><?php endif; ?>
                <?php if ($error): ?><div class="spbx-alert error"><?php echo spbx_rg_h($error); ?></div><?php endif; ?>

                <?php if ($edit): ?>
                    <form method="post" class="spbx-form">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">

                        <div class="spbx-grid-2">
                            <div class="spbx-field">
                                <label>Rufgruppennummer *</label>
                                <input name="group_number" value="<?php echo spbx_rg_h($edit['group_number']); ?>" placeholder="500" required>
                            </div>

                            <div class="spbx-field">
                                <label>Name *</label>
                                <input name="name" value="<?php echo spbx_rg_h($edit['name']); ?>" placeholder="Empfang" required>
                            </div>

                            <div class="spbx-field">
                                <label>Strategie</label>
                                <select name="strategy">
                                    <option value="ringall" <?php echo $edit['strategy']==='ringall'?'selected':''; ?>>Alle gleichzeitig</option>
                                    <option value="hunt" <?php echo $edit['strategy']==='hunt'?'selected':''; ?>>Nacheinander</option>
                                    <option value="random" <?php echo $edit['strategy']==='random'?'selected':''; ?>>Zufällig</option>
                                </select>
                            </div>

                            <div class="spbx-field">
                                <label>Klingelzeit pro Versuch</label>
                                <input type="number" name="ring_time" min="5" max="120" value="<?php echo (int)$edit['ring_time']; ?>">
                            </div>

                            <div class="spbx-field">
                                <label>Bei Timeout</label>
                                <select name="timeout_action">
                                    <option value="hangup" <?php echo $edit['timeout_action']==='hangup'?'selected':''; ?>>Auflegen</option>
                                    <option value="extension" <?php echo $edit['timeout_action']==='extension'?'selected':''; ?>>Zu Nebenstelle</option>
                                </select>
                            </div>

                            <div class="spbx-field">
                                <label>Timeout Ziel</label>
                                <input name="timeout_target" value="<?php echo spbx_rg_h($edit['timeout_target']); ?>" placeholder="z. B. 10">
                            </div>

                            <div class="spbx-field">
                                <label>Status</label>
                                <label><input type="checkbox" name="active" <?php echo (int)$edit['active']===1?'checked':''; ?>> aktiv</label>
                            </div>

                            <div class="spbx-field spbx-field-full">
                                <label>Mitglieder</label>
                                <div class="spbx-rg-members">
                                    <?php foreach ($extensions as $e): ?>
                                        <?php
                                            $ext = (string)$e['extension'];
                                            $checked = in_array($ext, $edit['members'] ?? [], true);
                                        ?>
                                        <label class="spbx-rg-member">
                                            <input type="checkbox" name="members[]" value="<?php echo spbx_rg_h($ext); ?>" <?php echo $checked?'checked':''; ?>>
                                            <span><?php echo spbx_rg_h($ext . ' - ' . ($e['display_name'] ?: $ext)); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="spbx-actions" style="margin-top:22px">
                            <a class="spbx-button secondary" href="ring_groups.php">Abbrechen</a>
                            <button class="spbx-button primary" type="submit">Speichern</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="spbx-table-wrap">
                        <table class="spbx-table">
                            <thead>
                            <tr>
                                <th>Nummer</th>
                                <th>Name</th>
                                <th>Strategie</th>
                                <th>Klingelzeit</th>
                                <th>Status</th>
                                <th>Aktion</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($groups && $groups->num_rows): while ($g = $groups->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo spbx_rg_h($g['group_number']); ?></td>
                                    <td><?php echo spbx_rg_h($g['name']); ?></td>
                                    <td><?php echo spbx_rg_h($g['strategy']); ?></td>
                                    <td><?php echo (int)$g['ring_time']; ?>s</td>
                                    <td><?php echo (int)$g['active']===1 ? 'Aktiv' : 'Inaktiv'; ?></td>
                                    <td>
                                        <a class="spbx-button small secondary" href="ring_groups.php?edit=<?php echo (int)$g['id']; ?>">Bearbeiten</a>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Rufgruppe wirklich löschen?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
                                            <button class="spbx-button small danger" type="submit">Löschen</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="6">Noch keine Rufgruppen vorhanden.</td></tr>
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
