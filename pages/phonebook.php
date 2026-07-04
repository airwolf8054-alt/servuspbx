<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/asterisk_notify.php';

spbx_require_login();
$db = spbx_db();


function spbx_phonebook_notify_all($reason = '')
{
    $debug = [];
    $count = spbx_notify_all_snom_check_cfg($debug);
    spbx_audit_log(
        'phonebook_notify',
        'Telefonbuch geändert' . ($reason !== '' ? ' (' . $reason . ')' : '') . ': snom-check-cfg an ' . $count . ' Telefon(e) gesendet.',
        'info',
        null,
        null,
        'phonebook'
    );

    if ($count <= 0) {
        error_log('ServusPBX phonebook notify: keine Telefone erreicht. Debug: ' . implode(' || ', $debug));
    }

    return $count;
}


function spbx_phonebook_clean_number($value)
{
    return trim((string)$value);
}

function spbx_phonebook_input($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function spbx_phonebook_get($id)
{
    $db = spbx_db();
    $stmt = $db->prepare("SELECT * FROM spbx_phonebook WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function spbx_phonebook_save($id)
{
    $db = spbx_db();

    $company = spbx_phonebook_input('company');
    $lastname = spbx_phonebook_input('lastname');
    $firstname = spbx_phonebook_input('firstname');
    $number = spbx_phonebook_clean_number(spbx_phonebook_input('number'));
    $mobile = spbx_phonebook_clean_number(spbx_phonebook_input('mobile'));
    $email = spbx_phonebook_input('email');

    if ($lastname === '' || $number === '') {
        return 'Nachname und Telefonnummer sind Pflichtfelder.';
    }

    if ($id > 0) {
        $stmt = $db->prepare("
            UPDATE spbx_phonebook
            SET company=?, lastname=?, firstname=?, number=?, mobile=?, email=?
            WHERE id=?
        ");
        $stmt->bind_param('ssssssi', $company, $lastname, $firstname, $number, $mobile, $email, $id);
        $stmt->execute();
        spbx_audit_log('phonebook_update', 'Telefonbuchkontakt geändert.', 'info', null, null, 'phonebook');
        spbx_phonebook_notify_all('update');
    } else {
        $stmt = $db->prepare("
            INSERT INTO spbx_phonebook
            (company, lastname, firstname, number, mobile, email)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('ssssss', $company, $lastname, $firstname, $number, $mobile, $email);
        $stmt->execute();
        spbx_audit_log('phonebook_create', 'Telefonbuchkontakt angelegt.', 'info', null, null, 'phonebook');
        spbx_phonebook_notify_all('create');
    }

    return '';
}

function spbx_phonebook_delete($id)
{
    $db = spbx_db();
    $stmt = $db->prepare("DELETE FROM spbx_phonebook WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    spbx_audit_log('phonebook_delete', 'Telefonbuchkontakt gelöscht.', 'warning', null, null, 'phonebook');
    spbx_phonebook_notify_all('delete');
}

function spbx_phonebook_export()
{
    $db = spbx_db();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="servuspbx_phonebook.csv"');

    $out = fopen('php://output', 'w');
    fprintf($out, "\xEF\xBB\xBF");
    fputcsv($out, ['company', 'lastname', 'firstname', 'number', 'mobile', 'email'], ';');

    $res = $db->query("SELECT company, lastname, firstname, number, mobile, email FROM spbx_phonebook ORDER BY lastname, firstname, company");
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, [$row['company'], $row['lastname'], $row['firstname'], $row['number'], $row['mobile'], $row['email']], ';');
    }

    spbx_audit_log('phonebook_export', 'Telefonbuch exportiert.', 'info', null, null, 'phonebook');
    fclose($out);
    exit;
}

function spbx_phonebook_import()
{
    $db = spbx_db();

    if (empty($_FILES['csv_file']['tmp_name']) || !is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
        return 'Keine CSV-Datei hochgeladen.';
    }

    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if (!$handle) {
        return 'CSV-Datei konnte nicht gelesen werden.';
    }

    $count = 0;
    $line = 0;

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        $line++;

        if ($line === 1) {
            $first = strtolower(trim((string)($row[0] ?? '')));
            if ($first === 'company' || str_starts_with($first, "\xEF\xBB\xBFcompany")) {
                continue;
            }
        }

        $company = trim((string)($row[0] ?? ''));
        $lastname = trim((string)($row[1] ?? ''));
        $firstname = trim((string)($row[2] ?? ''));
        $number = trim((string)($row[3] ?? ''));
        $mobile = trim((string)($row[4] ?? ''));
        $email = trim((string)($row[5] ?? ''));

        if ($lastname === '' || $number === '') {
            continue;
        }

        $stmt = $db->prepare("
            INSERT INTO spbx_phonebook
            (company, lastname, firstname, number, mobile, email)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('ssssss', $company, $lastname, $firstname, $number, $mobile, $email);
        $stmt->execute();
        $count++;
    }

    fclose($handle);
    spbx_audit_log('phonebook_import', $count . ' Telefonbuchkontakte importiert.', 'info', null, null, 'phonebook');
    return $count . ' Kontakte importiert.';
}

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$message = '';
$editContact = null;

if ($action === 'export') {
    spbx_phonebook_export();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['form_action'] ?? '') === 'save') {
        $saveId = (int)($_POST['id'] ?? 0);
        $error = spbx_phonebook_save($saveId);
        if ($error === '') {
            header('Location: phonebook.php?saved=1');
            exit;
        }
        $action = $saveId > 0 ? 'edit' : 'new';
        $editContact = $_POST;
    } elseif (($_POST['form_action'] ?? '') === 'import') {
        $message = spbx_phonebook_import();
        $action = 'list';
    }
}


if ($action === 'delete_all') {
    if (!spbx_is_admin()) {
        http_response_code(403);
        exit('Forbidden');
    }
    $db->query("TRUNCATE TABLE spbx_phonebook");
    spbx_audit_log('phonebook_delete_all', 'Gesamtes Telefonbuch gelöscht.', 'error', null, null, 'phonebook');
    spbx_phonebook_notify_all('delete_all');
    header('Location: phonebook.php?deletedall=1');
    exit;
}

if ($action === 'delete' && $id > 0) {
    spbx_phonebook_delete($id);
    header('Location: phonebook.php?deleted=1');
    exit;
}

if ($action === 'edit' && $id > 0 && !$editContact) {
    $editContact = spbx_phonebook_get($id);
    if (!$editContact) {
        header('Location: phonebook.php');
        exit;
    }
}

if ($action === 'new' && !$editContact) {
    $editContact = ['id' => 0, 'company' => '', 'lastname' => '', 'firstname' => '', 'number' => '', 'mobile' => '', 'email' => ''];
}

if (isset($_GET['saved'])) $message = 'Kontakt gespeichert.';
if (isset($_GET['deleted'])) $message = 'Kontakt gelöscht.';
if (isset($_GET['deletedall'])) $message = 'Gesamtes Telefonbuch gelöscht.';

$q = trim((string)($_GET['q'] ?? ''));
$contacts = [];

if ($action === 'list') {
    if ($q !== '') {
        $like = '%' . $q . '%';
        $stmt = $db->prepare("
            SELECT * FROM spbx_phonebook
            WHERE company LIKE ?
               OR lastname LIKE ?
               OR firstname LIKE ?
               OR number LIKE ?
               OR mobile LIKE ?
               OR email LIKE ?
            ORDER BY lastname, firstname, company
            LIMIT 500
        ");
        $stmt->bind_param('ssssss', $like, $like, $like, $like, $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $db->query("SELECT * FROM spbx_phonebook ORDER BY lastname, firstname, company LIMIT 500");
    }

    while ($row = $res->fetch_assoc()) {
        $contacts[] = $row;
    }
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Telefonbuch - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">
</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('Telefonbuch', 'Kontakte erstellen, bearbeiten, importieren und exportieren'); ?>

        <div class="spbx-content spbx-phonebook-layout">
            <?php if ($message): ?>
                <div class="spbx-alert success"><?php echo spbx_h($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="spbx-alert error"><?php echo spbx_h($error); ?></div>
            <?php endif; ?>

            <?php if ($action === 'new' || $action === 'edit'): ?>
                <div class="spbx-card spbx-phonebook-form-card">
                    <div class="spbx-card-header-row">
                        <div>
                            <div class="spbx-card-title"><?php echo $action === 'edit' ? 'Kontakt bearbeiten' : 'Kontakt anlegen'; ?></div>
                            <div class="spbx-card-muted">Nachname und Telefonnummer sind Pflichtfelder.</div>
                        </div>
                    </div>

                    <form method="post" class="spbx-contact-form">
                        <input type="hidden" name="form_action" value="save">
                        <input type="hidden" name="id" value="<?php echo (int)($editContact['id'] ?? 0); ?>">

                        <div class="spbx-form-grid">
                            <div class="spbx-field">
                                <label>Firma / Einrichtung</label>
                                <input class="spbx-form-input" type="text" name="company" value="<?php echo spbx_h($editContact['company'] ?? ''); ?>">
                            </div>

                            <div class="spbx-field">
                                <label>Nachname *</label>
                                <input class="spbx-form-input" type="text" name="lastname" value="<?php echo spbx_h($editContact['lastname'] ?? ''); ?>" required autofocus>
                            </div>

                            <div class="spbx-field">
                                <label>Vorname</label>
                                <input class="spbx-form-input" type="text" name="firstname" value="<?php echo spbx_h($editContact['firstname'] ?? ''); ?>">
                            </div>

                            <div class="spbx-field">
                                <label>Telefonnummer *</label>
                                <input class="spbx-form-input" type="tel" name="number" value="<?php echo spbx_h($editContact['number'] ?? ''); ?>" required>
                            </div>

                            <div class="spbx-field">
                                <label>Mobilnummer</label>
                                <input class="spbx-form-input" type="tel" name="mobile" value="<?php echo spbx_h($editContact['mobile'] ?? ''); ?>">
                            </div>

                            <div class="spbx-field">
                                <label>E-Mail</label>
                                <input class="spbx-form-input" type="email" name="email" value="<?php echo spbx_h($editContact['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="spbx-form-actions">
                            <a class="spbx-button secondary" href="phonebook.php">Abbrechen</a>
                            <button class="spbx-button primary" type="submit">Speichern</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="spbx-card">
                    <div class="spbx-card-header-row">
                        <div>
                            <div class="spbx-card-title">Kontakte</div>
                            <div class="spbx-card-muted"><?php echo count($contacts); ?> Einträge angezeigt</div>
                        </div>
                        <div class="spbx-card-header-actions">
                            <a class="spbx-button primary" href="phonebook.php?action=new">+ Neuer Kontakt</a>
                            <a class="spbx-button secondary" href="phonebook.php?action=export">CSV Export</a>
<?php if (spbx_is_admin()): ?>
<a class="spbx-button danger"
   href="phonebook.php?action=delete_all"
   onclick="return confirm('Wirklich das gesamte Telefonbuch löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.');">
Telefonbuch löschen
</a>
<?php endif; ?>
                        </div>
                    </div>

                    <form method="get" class="spbx-phonebook-search">
                        <input class="spbx-search-input" type="text" name="q" value="<?php echo spbx_h($q); ?>" placeholder="Suchen nach Name, Firma, Nummer oder E-Mail">
                        <button class="spbx-button secondary" type="submit">Suchen</button>
                        <?php if ($q !== ''): ?>
                            <a class="spbx-button secondary" href="phonebook.php">Zurücksetzen</a>
                        <?php endif; ?>
                    </form>

                    <div style="height:16px;"></div>

                    <?php if (!$contacts): ?>
                        <div class="spbx-empty-state">
                            <strong>Keine Kontakte vorhanden</strong>
                            Lege den ersten Kontakt an oder importiere eine CSV-Datei.
                        </div>
                    <?php else: ?>
                        <div class="spbx-table-wrap spbx-phonebook-table">
                            <table class="spbx-table">
                                <thead>
                                    <tr>
                                        <th>Firma</th>
                                        <th>Name</th>
                                        <th>Telefon</th>
                                        <th>Mobil</th>
                                        <th>E-Mail</th>
                                        <th style="text-align:right;">Aktion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contacts as $c): ?>
                                        <tr>
                                            <td><?php echo spbx_h($c['company']); ?></td>
                                            <td><?php echo spbx_h(trim($c['lastname'] . ' ' . $c['firstname'])); ?></td>
                                            <td><?php echo spbx_h($c['number']); ?></td>
                                            <td><?php echo spbx_h($c['mobile']); ?></td>
                                            <td><?php echo spbx_h($c['email']); ?></td>
                                            <td>
                                                <div class="spbx-table-actions">
                                                    <a class="spbx-button small secondary" href="phonebook.php?action=edit&id=<?php echo (int)$c['id']; ?>">Bearbeiten</a>
                                                    <a class="spbx-button small danger" href="phonebook.php?action=delete&id=<?php echo (int)$c['id']; ?>" onclick="return confirm('Kontakt wirklich löschen?');">Löschen</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="spbx-card spbx-import-card">
                    <div class="spbx-card-title">CSV Import</div>
                    <div class="spbx-card-muted">Erwartete Spalten: company;lastname;firstname;number;mobile;email</div>

                    <form method="post" enctype="multipart/form-data" class="spbx-import-form">
                        <input type="hidden" name="form_action" value="import">
                        <input class="spbx-file-input" type="file" name="csv_file" accept=".csv,text/csv" required>
                        <button class="spbx-button primary" type="submit">Importieren</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
