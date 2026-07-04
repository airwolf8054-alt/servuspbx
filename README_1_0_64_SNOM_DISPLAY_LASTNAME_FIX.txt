ServusPBX Medical 1.0.64 - Snom Display Name Fix

Änderung:
- ps_endpoints.callerid bleibt weiterhin Vorname Nachname.
- Im Provisioning wird für:
  <user_realname idx='1'>
  <user_idle_text idx='1'>
  nur der Nachname ausgegeben, wenn vorhanden.
- Wenn kein Nachname vorhanden ist, wird der Vorname ausgegeben.
- Ausgabe wird auf max. 12 Zeichen begrenzt.
- Ziel: keine Laufschrift am snom D815/D810 Hauptdisplay.

Beispiel:
CallerID: Stefan Reisenhofer
Provisioning:
<user_realname idx='1' perm='R'>Reisenhofer</user_realname>
<user_idle_text idx='1' perm='R'>Reisenhofer</user_idle_text>

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_0_64_snom_display_lastname_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_64_snom_display_lastname_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
