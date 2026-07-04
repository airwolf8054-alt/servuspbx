<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/blf_sync.php';

$db = spbx_db();

$res = $db->query("
    SELECT extension, callerid, display_name
    FROM ps_endpoints
    WHERE extension IS NOT NULL
      AND extension<>''
      AND (device_type IS NULL OR device_type <> 'trunk')
");

$countKeys = 0;
$countDevices = 0;

if ($res) {
    while ($r = $res->fetch_assoc()) {
        $sync = spbx_sync_blf_labels_for_extension(
            $r['extension'],
            $r['callerid'] ?? '',
            $r['display_name'] ?? $r['extension']
        );
        $countKeys += (int)($sync['updated_keys'] ?? 0);
        $countDevices += (int)($sync['notified_devices'] ?? 0);
    }
}

echo "BLF labels updated: " . $countKeys . PHP_EOL;
echo "Devices notified: " . $countDevices . PHP_EOL;
?>