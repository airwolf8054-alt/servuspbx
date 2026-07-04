ServusPBX Medical 1.5.0 - AstConfigWriter

Neu:
- Eigene Datei:
  inc/ast_config_writer.php

Korrigiert:
- Hints werden jetzt exakt wie in deiner alten funktionierenden Anlage in ast_config erzeugt.

Pro internal_<rufnummer>:
- exten => _XX,hint,PJSIP/${EXTEN}
- exten => _XXX,hint,PJSIP/${EXTEN}
- exten => _XXXX,hint,PJSIP/${EXTEN}
- switch => Realtime/@extensions

Pro outgoing_<rufnummer>:
- switch => Realtime/@extensions

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_5_0_ast_config_writer.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_5_0_ast_config_writer.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Reload:
   sudo /usr/sbin/asterisk -rx "module reload pbx_config.so"
   sudo /usr/sbin/asterisk -rx "dialplan reload"

Prüfung:
SELECT * FROM ast_config WHERE filename='extensions.conf' ORDER BY category,var_metric;
asterisk -rx "dialplan show internal_43312423826"
