ServusPBX Medical 1.2.2 - SIP-Trunks Layout Fix

Korrigiert:
- trunk_a1.php fiel auf nacktes HTML zurück.
- Dadurch fehlten Sidebar, Header und ServusPBX-Layout.
- Die Seite basiert jetzt wieder auf dem bestehenden ServusPBX-Seitenrahmen der alten trunk_a1.php.
- Die SIP-Trunk-Funktionen bleiben erhalten.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_2_sip_trunks_layout_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_2_sip_trunks_layout_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
