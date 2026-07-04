ServusPBX Medical 1.7.4 - DID Prefix Offset Fix

Korrigiert:
- ${EXTEN:-2} wurde bei Asterisk aus dem Pattern zu XX.
- Der Generator nutzt jetzt feste Prefix-Längen.

Beispiele:
- DID _+43312423826XX:
  Set(DIDEXT=${EXTEN:12})
  Goto(internal_43312423826,${DIDEXT},1)

- DID _XX:
  Set(DIDEXT=${EXTEN})
  Goto(internal_<context>,${DIDEXT},1)

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_7_4_did_prefix_offset_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_7_4_did_prefix_offset_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Anrufregel einmal speichern, damit der Dialplan neu erzeugt wird.
