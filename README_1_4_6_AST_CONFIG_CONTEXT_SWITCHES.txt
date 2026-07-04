ServusPBX Medical 1.4.6 - ast_config Context Switches

Problem:
Asterisk kannte internal_43312423826 nicht:
There is no existence of 'internal_43312423826' context

Ursache:
Die Realtime-Dialplan-Einträge in extensions reichen nicht.
Der Context selbst braucht:
[internal_43312423826]
switch => Realtime/@extensions

[outgoing_43312423826]
switch => Realtime/@extensions

Fix:
- ServusPBX erzeugt diese Context-Switches in ast_config.
- Beim Outbound-Rebuild werden alle internal_<rufnummer> und outgoing_<rufnummer> neu in ast_config geschrieben.

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_4_6_ast_config_context_switches.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_6_ast_config_context_switches.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Prüfen, ob extconfig.conf ast_config nutzt:
   grep "extensions.conf" /etc/asterisk/extconfig.conf

   Erwartung:
   extensions.conf => odbc,asterisk,ast_config

4. Reload:
   sudo /usr/sbin/asterisk -rx "dialplan reload"

5. Prüfen:
   asterisk -rx "dialplan show internal_43312423826"
   asterisk -rx "dialplan show outgoing_43312423826"
