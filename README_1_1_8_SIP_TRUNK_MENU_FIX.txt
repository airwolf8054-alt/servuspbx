ServusPBX Medical 1.1.8 - SIP-Trunk Menü Fix

Korrigiert:
- Menü zeigte noch auf /pages/trunk_a1.php.
- Der richtige Link ist jetzt /pages/sip_trunks.php.
- Die alte Datei trunk_a1.php bleibt erhalten und leitet auf sip_trunks.php weiter.
- A1-Text im Header wurde entfernt.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_1_8_sip_trunk_menu_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_8_sip_trunk_menu_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
