ServusPBX Medical 1.1.9 - SIP-Trunks Redeclare Fix

Korrigiert:
- PHP Fatal error: Cannot redeclare spbx_h()
- sip_trunks.php verwendet jetzt die vorhandene Helper-Funktion aus inc/db.php.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_1_9_sip_trunks_redeclare_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_9_sip_trunks_redeclare_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
