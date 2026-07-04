ServusPBX Medical 1.4.8 - Pattern-Hints + ast_config Metric

Änderungen:
- Keine Einzel-Hints mehr je Nebenstelle.
- Stattdessen pro internal_<rufnummer>:
  _X.,hint,PJSIP/${EXTEN}

- ast_config cat_metric wird bereinigt:
  internal_<rufnummer> -> 2000
  outgoing_<rufnummer> -> 3000

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_4_8_pattern_hints_ast_config_metric.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_8_pattern_hints_ast_config_metric.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Reload:
   sudo /usr/sbin/asterisk -rx "dialplan reload"

Prüfung:
SELECT * FROM ast_config WHERE filename='extensions.conf';
asterisk -rx "dialplan show internal_43312423826"
