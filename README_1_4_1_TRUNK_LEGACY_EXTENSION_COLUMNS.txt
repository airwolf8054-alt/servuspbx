ServusPBX Medical 1.4.1 - Trunk Legacy Extension Columns

Korrigiert:
- Fatal Error beim Trunk-Speichern:
  Field 'extension' doesn't have a default value

Nach 1.4.0 war ps_aors.extension berücksichtigt.
In 1.4.1 wird zusätzlich ps_registrations.extension berücksichtigt.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_4_1_trunk_legacy_extension_columns.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_1_trunk_legacy_extension_columns.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. SIP-Trunk erneut speichern.
