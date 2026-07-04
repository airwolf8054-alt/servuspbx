ServusPBX Medical 1.4.2 - Trunk ps_endpoints.extension Fix

Korrigiert:
- Fatal Error beim Trunk-Speichern:
  Field 'extension' doesn't have a default value
  in pages/trunk_a1.php beim ps_endpoints Insert.

Abgedeckte Legacy-Felder:
- ps_aors.extension
- ps_registrations.extension
- ps_endpoints.extension

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_4_2_trunk_ps_endpoints_extension_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_2_trunk_ps_endpoints_extension_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. SIP-Trunk erneut speichern.
