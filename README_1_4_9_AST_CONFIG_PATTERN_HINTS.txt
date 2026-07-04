ServusPBX Medical 1.4.9 - Pattern-Hints in ast_config

Korrektur:
Die BLF-Hints werden jetzt in ast_config erzeugt, nicht mehr in der extensions-Tabelle.

Ergebnis in ast_config:
filename: extensions.conf
category: internal_43312423826
var_name: exten
var_val: _X.,hint,PJSIP/${EXTEN}

Zusätzlich:
category: internal_43312423826
var_name: switch
var_val: Realtime/@extensions

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_4_9_ast_config_pattern_hints.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_9_ast_config_pattern_hints.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Reload:
   sudo /usr/sbin/asterisk -rx "module reload pbx_config.so"
   sudo /usr/sbin/asterisk -rx "dialplan reload"

Prüfung:
SELECT * FROM ast_config WHERE filename='extensions.conf' ORDER BY category,var_metric;
asterisk -rx "dialplan show internal_43312423826"
