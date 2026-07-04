ServusPBX Medical 1.2.23 - Snom Text-Softkeys aktivieren

Korrigiert:
- context_key_text war bereits on.
- text_softkey stand noch auf off.
- Dadurch wurden bei D810/D815 nur Icons ohne Beschriftung angezeigt.

Änderung:
<text_softkey perm="R">on</text_softkey>

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_23_text_softkey_on.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_23_text_softkey_on.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Nebenstelle speichern, damit snom-check-cfg ausgelöst wird.

Prüfung:
curl "http://10.43.4.244/provision/snomD815.php?mac=000413E30A0C" | grep -E "text_softkey|context_key_text"
