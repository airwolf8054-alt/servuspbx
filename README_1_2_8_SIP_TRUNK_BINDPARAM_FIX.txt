ServusPBX Medical 1.2.8 - SIP-Trunk bind_param Fix

Korrigiert:
- Formularfehler beim Speichern:
  The number of elements in the type definition string must match the number of bind variables

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_8_sip_trunk_bindparam_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_8_sip_trunk_bindparam_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
