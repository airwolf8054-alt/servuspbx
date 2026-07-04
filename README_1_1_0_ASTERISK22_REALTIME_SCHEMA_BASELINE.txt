ServusPBX Medical 1.1.0 - Asterisk 22 Realtime Schema Baseline

Ziel:
- Asterisk-Realtime-Tabellen auf einen stabilen Asterisk-22-kompatiblen Stand bringen.
- Fehlende Tabellen und Spalten ergänzen.
- Bestehende ServusPBX-spezifische Felder bleiben erhalten.
- ps_contacts wird geleert, damit Asterisk Runtime-Contacts neu schreiben kann.

Wichtig:
Vorher Backup erstellen:
mysqldump -u root -p general > /root/general_before_1_1_0.sql

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_1_0_asterisk22_realtime_schema_baseline.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_0_asterisk22_realtime_schema_baseline.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Asterisk neu laden:
   sudo asterisk -rx "module reload res_pjsip.so"
   sudo asterisk -rx "dialplan reload"
   sudo asterisk -rx "core reload"

4. Telefone neu registrieren lassen.

Prüfung:
sudo asterisk -rx "pjsip show contacts"
mysql -e "SELECT endpoint, uri, expiration_time FROM general.ps_contacts;"
