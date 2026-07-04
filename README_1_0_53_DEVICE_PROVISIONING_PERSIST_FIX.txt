ServusPBX Medical 1.0.53 - Device Provisioning Persist Fix

Behoben:
- Beim Anlegen eines neuen Geräts durfte die Provisionierung eines vorherigen Geräts überschrieben werden.
- spbx_devices wird jetzt explizit über endpoint_id aktualisiert oder neu angelegt.
- Kein blindes ON DUPLICATE KEY UPDATE mehr.
- D815/D810 MAC-Adresse wird gegen andere Nebenstellen geprüft.
- provision_file wird bei D815/D810 korrekt gesetzt.
- Bestehende Geräte bleiben beim Anlegen weiterer Nebenstellen erhalten.

SQL:
1. Patch ausführen:
   patch_servuspbx_medical_1_0_53_device_provisioning_persist_fix.sql

Wichtig:
Falls ALTER TABLE wegen bestehendem Key mit "Duplicate key name" abbricht, ist das unkritisch.
Falls die Diagnose doppelte endpoint_id zeigt, diese Altlasten zuerst bereinigen.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_53_device_provisioning_persist_fix.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
sudo chown -R www-data:www-data /var/www/html
