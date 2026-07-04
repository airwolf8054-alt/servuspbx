ServusPBX Medical 1.0.32 - Nebenstellen Query Fix

Behoben:
- Fatal Error: Unknown column p.device_model in SELECT
- Die Nebenstellenliste liest den Gerätetyp jetzt aus spbx_devices.device_model.
- Der fehlerhafte SELECT-Ausdruck mit p.device_model wurde entfernt.

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_32_extensions_query_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_32_extensions_query_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
