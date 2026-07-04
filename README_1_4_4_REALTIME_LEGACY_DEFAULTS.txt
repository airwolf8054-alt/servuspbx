ServusPBX Medical 1.4.4 - Realtime Legacy Defaults

Korrigiert:
- Field 'display_name' doesn't have a default value
- und weitere bekannte Legacy-NOT-NULL-Spalten.

Wichtig:
Diesen SQL-Patch bitte diesmal einspielen.

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_4_4_realtime_legacy_defaults.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_4_realtime_legacy_defaults.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. SIP-Trunk erneut speichern.
