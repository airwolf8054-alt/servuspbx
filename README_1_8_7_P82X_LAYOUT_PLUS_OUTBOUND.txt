ServusPBX Medical 1.8.7 - P82x Layout + Outbound Plus Fix

Korrigiert:
- Gigaset P82x Tastenbelegung:
  Links Taste 1 | Rechts Taste 5
  Links Taste 2 | Rechts Taste 6
  Links Taste 3 | Rechts Taste 7
  Links Taste 4 | Rechts Taste 8

- Ausgehende Normalisierung:
  0664... wird wieder zu +43664...
  nicht zu 0043664...

Hinweis:
- Nach Änderung einer ausgehenden Route die Route einmal speichern, damit der Dialplan neu erzeugt wird.
- Falls der Dialplan schon falsch generiert ist, reicht Route speichern.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_8_7_p82x_layout_plus_outbound.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_8_7_p82x_layout_plus_outbound.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
