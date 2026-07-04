ServusPBX Medical 1.0.31 - Nebenstellen Gerätetyp Fix

Korrigiert:
- Sporadisches Umspringen von snom D815/D810 auf SIP User.
- Gerätetyp wird beim Speichern normalisiert und stabil übernommen.
- spbx_devices.device_model und ps_endpoints.device_model werden synchron gehalten.
- Die Nebenstellenliste zeigt den Gerätetyp bevorzugt aus spbx_devices.
- Anzeige in der Liste ist robust gemappt.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_31_extension_device_model_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_31_extension_device_model_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
