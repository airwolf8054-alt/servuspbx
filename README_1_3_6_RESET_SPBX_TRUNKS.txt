ServusPBX Medical 1.3.6 - RESET spbx_trunks

Dieser Patch löscht spbx_trunks vollständig und erstellt die Tabelle sauber neu.

Warum:
- Die bestehende Tabelle enthält alte Entwicklungsfelder wie client_domain/auth_username.
- Diese alten NOT NULL Felder verursachen SQL-Fehler.
- Ein sauberer Reset ist jetzt sinnvoller als weitere Einzelmigrationen.

Wichtig:
- Es wird der bestehende A1-Trunk wieder sauber eingetragen:
  +43312423826
  endpoint trunk-a1-43312423826
  Durchwahlbereich 10-99
  Notrufprofil AT

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_3_6_reset_spbx_trunks.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_3_6_reset_spbx_trunks.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. SIP-Trunks öffnen.
4. A1-Trunk öffnen und speichern.
5. Danach prüfen:
   asterisk -rx "dialplan show internal_43312423826"
   asterisk -rx "dialplan show outgoing_43312423826"
