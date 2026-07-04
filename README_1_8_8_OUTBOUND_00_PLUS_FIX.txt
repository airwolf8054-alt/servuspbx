ServusPBX Medical 1.8.8 - Outbound 00 Plus Fix

Korrigiert:
- 00436641549314 -> +436641549314
- 06641549314 -> +436641549314
- +436641549314 bleibt unverändert

Ursache:
- Die lokale 0-Regel hat auch 0043... erwischt und daraus +43043... gemacht.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_8_8_outbound_00_plus_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_8_8_outbound_00_plus_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Ausgehende Route einmal speichern, damit der Dialplan neu erzeugt wird.
