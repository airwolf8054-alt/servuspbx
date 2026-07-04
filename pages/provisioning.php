<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/branding.php';
require_once __DIR__ . '/../inc/provisioning_manager.php';

spbx_require_admin();
spbx_provisioning_manager_install_schema();
$db = spbx_db();

function ph($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['send_network','send_firmware','send_identity','send_keys','send_phonebook','force_reboot'];
    foreach ($keys as $key) {
        $value = isset($_POST[$key]) ? '1' : '0';
        $stmt = $db->prepare("UPDATE spbx_provisioning_options SET option_value=? WHERE option_key=?");
        if ($stmt) {
            $stmt->bind_param('ss', $value, $key);
            $stmt->execute();
        }
    }
    $message = 'Provisioning-Einstellungen gespeichert.';
}

$options = [];
$res = $db->query("SELECT * FROM spbx_provisioning_options ORDER BY id");
if ($res) {
    while ($r = $res->fetch_assoc()) $options[$r['option_key']] = $r;
}

function checked_opt($options, $key) {
    return (($options[$key]['option_value'] ?? '0') === '1') ? 'checked' : '';
}
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Provisionierung - ServusPBX Professional</title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="stylesheet" href="../css/servuspbx.css">
</head>
<body>
<div class="spbx-app">
<?php spbx_sidebar(); ?>
<main class="spbx-main">
<?php spbx_page_header('Provisionierung', 'Modulare Provisionierung ohne unnötige Netzwerk-Neustarts'); ?>
<div class="spbx-content">
<?php if ($message): ?><div class="spbx-alert success"><?php echo ph($message); ?></div><?php endif; ?>

<form method="post" class="spbx-card" style="padding:18px;">
<div class="spbx-card-title">Provisioning-Module</div>
<div class="spbx-card-muted" style="margin-bottom:16px;">
Netzwerkparameter sind standardmäßig deaktiviert. Telefone bleiben auf DHCP; IP, Gateway, DNS und Netmask werden nicht mehr provisioniert.
</div>

<label style="display:block;margin:10px 0;">
<input type="checkbox" name="send_identity" <?php echo checked_opt($options,'send_identity'); ?>> SIP-Identität senden
</label>
<label style="display:block;margin:10px 0;">
<input type="checkbox" name="send_keys" <?php echo checked_opt($options,'send_keys'); ?>> Funktionstasten senden
</label>
<label style="display:block;margin:10px 0;">
<input type="checkbox" name="send_phonebook" <?php echo checked_opt($options,'send_phonebook'); ?>> Telefonbuch senden
</label>
<label style="display:block;margin:10px 0;">
<input type="checkbox" name="send_firmware" <?php echo checked_opt($options,'send_firmware'); ?>> Firmware-URL senden
</label>

<hr>
<label style="display:block;margin:10px 0;">
<input type="checkbox" name="send_network" <?php echo checked_opt($options,'send_network'); ?>> Netzwerkparameter senden
</label>
<div class="spbx-card-muted">
Nur für bewusst statisch konfigurierte Sonderfälle verwenden. Bei DHCP ausgeschaltet lassen.
</div>

<hr>
<label style="display:block;margin:10px 0;">
<input type="checkbox" name="force_reboot" <?php echo checked_opt($options,'force_reboot'); ?>> Neustart nach Provisionierung erzwingen
</label>

<div style="margin-top:18px;">
<button class="spbx-button primary" type="submit">Speichern</button>
</div>
</form>
</div>
</main>
</div>
</body>
</html>
