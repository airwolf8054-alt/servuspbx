<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/asterisk.php';

spbx_require_login();
$user = spbx_current_user();

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$errors = [];
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_holiday') {
        $date = trim((string)($_POST['holiday_date'] ?? ''));
        $name = trim((string)($_POST['holiday_name'] ?? 'Feiertag'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'Ungültiges Feiertagsdatum.';
        }

        if ($name === '') {
            $name = 'Feiertag';
        }

        if (!$errors) {
            spbx_ast_db_put('bankholiday', $date, $name);
            $ok = 'Feiertag gespeichert.';
        }
    }

    if ($action === 'delete_holiday') {
        $date = trim((string)($_POST['holiday_date'] ?? ''));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            spbx_ast_db_del('bankholiday', $date);
            $ok = 'Feiertag gelöscht.';
        }
    }
}

$holidays = spbx_ast_db_show_family('bankholiday');
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Feiertage - ServusPBX Medical</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/servuspbx.css">

<style>
.holidays-form input {
    width: 100%;
    max-width: 100%;
    min-height: 42px;
    box-sizing: border-box;
    border: 1px solid #cbd8ea;
    border-radius: 10px;
    padding: 0 12px;
    background: #fff;
}
</style>

</head>
<body>
<div class="spbx-app">
    <?php spbx_sidebar(); ?>

    <main class="spbx-main">
        <?php spbx_page_header('Feiertage', 'Globale Feiertage für Anrufregeln'); ?>

        <div class="spbx-content">
            <div class="spbx-card">
                <div class="spbx-card-head">
                    <div>
                        <div class="spbx-card-title">Globale Feiertage</div>
                        <div class="spbx-card-muted">Gespeichert in Asterisk AstDB: bankholiday/YYYY-MM-DD</div>
                    </div>
                </div>

                <?php foreach ($errors as $e): ?>
                    <div class="spbx-alert error"><?php echo h($e); ?></div>
                <?php endforeach; ?>

                <?php if ($ok): ?>
                    <div class="spbx-alert success"><?php echo h($ok); ?></div>
                <?php endif; ?>

                <form method="post" class="spbx-form holidays-form">
                    <input type="hidden" name="action" value="save_holiday">

                    <div class="spbx-grid-2">
                        <div class="spbx-field">
                            <label>Datum</label>
                            <input type="date" name="holiday_date" required>
                        </div>

                        <div class="spbx-field">
                            <label>Bezeichnung</label>
                            <input name="holiday_name" placeholder="z.B. Heiliger Abend">
                        </div>
                    </div>

                    <div class="spbx-actions">
                        <button class="spbx-button primary" type="submit">Feiertag speichern</button>
                    </div>
                </form>

                <div class="spbx-table-wrap" style="margin-top:16px;">
                    <table class="spbx-table">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Bezeichnung</th>
                                <th>Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($holidays): foreach ($holidays as $date => $name): ?>
                                <tr>
                                    <td><?php echo h($date); ?></td>
                                    <td><?php echo h($name); ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Feiertag löschen?');">
                                            <input type="hidden" name="action" value="delete_holiday">
                                            <input type="hidden" name="holiday_date" value="<?php echo h($date); ?>">
                                            <button class="spbx-button small danger" type="submit">Löschen</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="3">Keine Feiertage eingetragen.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
