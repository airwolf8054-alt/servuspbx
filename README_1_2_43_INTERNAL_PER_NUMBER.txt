ServusPBX Medical 1.2.43 - Internal Context pro Rufnummer

Problem:
- Nebenstelle 10 war noch im context internal.
- Dort fehlte die ausgehende Regel:
  06641549314 rejected because extension not found in context internal

Neue Architektur:
- Eingehend bleibt: incoming
- Pro Rufnummer:
  internal_43312423826
  outgoing_43312423826

Automatisch im internal_<rufnummer>-Context:
- _XX    -> PJSIP/${EXTEN}
- _XXX   -> PJSIP/${EXTEN}
- _XXXX  -> PJSIP/${EXTEN}
- AT Notrufe/Kurznummern: 112,122,133,144,141,1450
- DE Notrufe/Kurznummern: 110,112,115,116117
- _0X. und _+X. -> outgoing_<rufnummer>

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_43_internal_per_number.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_43_internal_per_number.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. GUI:
   /pages/outbound_routes.php öffnen
   "Dialplan neu aufbauen" klicken

4. Reload:
   sudo /usr/sbin/asterisk -rx "pjsip reload"

Prüfung:
asterisk -rx "dialplan show internal_43312423826"
asterisk -rx "dialplan show outgoing_43312423826"
asterisk -rx "pjsip show endpoint 10"
