ServusPBX Medical 1.1.1 - Asterisk 22 Schema Baseline Fix

Korrigiert:
- Fehler im 1.1.0 Patch:
  Unknown column 'default_realm' in INSERT INTO ps_globals
- Ursache:
  ps_globals existierte bereits, daher wurde CREATE TABLE IF NOT EXISTS nicht angewendet.
  Die fehlenden Spalten mussten vor dem INSERT per ALTER TABLE ergänzt werden.
- 1.1.1 ergänzt ps_globals vollständig, bevor der INSERT/UPDATE ausgeführt wird.

Installation:
1. Backup:
   mysqldump -u root -p general > /root/general_before_1_1_1.sql

2. SQL-Patch importieren:
   patch_servuspbx_medical_1_1_1_asterisk22_schema_baseline_fix.sql

3. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_1_asterisk22_schema_baseline_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

4. Asterisk reload:
   sudo asterisk -rx "module reload res_pjsip.so"
   sudo asterisk -rx "dialplan reload"
   sudo asterisk -rx "core reload"
