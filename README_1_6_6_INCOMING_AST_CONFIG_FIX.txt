ServusPBX Medical 1.6.6 - incoming ast_config Fix

Korrigiert:
- eingehende Anrufe landen im Context incoming
- extensions-Tabelle enthält incoming-Einträge
- aber Asterisk kannte den Context incoming nicht

Fix:
- ast_config bekommt:
  filename: extensions.conf
  category: incoming
  var_name: switch
  var_val: Realtime/@extensions

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_6_6_incoming_ast_config_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_6_incoming_ast_config_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Einmalig:
   sudo /usr/sbin/asterisk -rx "module reload pbx_config.so"

4. Prüfen:
   asterisk -rx "dialplan show incoming"
