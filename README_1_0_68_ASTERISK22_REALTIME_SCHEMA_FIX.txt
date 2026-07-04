ServusPBX Medical 1.0.68 - Asterisk 22 Realtime Schema Fix

Behoben:
- Asterisk 22 konnte registrierende Telefone nicht in ps_contacts speichern.
- Fehler:
  Unknown column 'qualify_2xx_only' in 'INSERT INTO'
  Unable to bind contact ... to AOR ...
- Dadurch blieb pjsip show contacts leer.
- Patch ergänzt ps_contacts.qualify_2xx_only und weitere Asterisk-22-Realtime-Felder.

Vorher Backup:
mysqldump -u root -p general > /root/general_before_1_0_68.sql

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_68_asterisk22_realtime_schema_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_68_asterisk22_realtime_schema_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Asterisk reload:
   sudo asterisk -rx "module reload res_pjsip.so"
   sudo asterisk -rx "dialplan reload"

4. Telefone neu registrieren lassen.

Prüfung:
sudo asterisk -rx "pjsip show contacts"
mysql -e "SELECT endpoint, uri, expiration_time FROM general.ps_contacts;"
