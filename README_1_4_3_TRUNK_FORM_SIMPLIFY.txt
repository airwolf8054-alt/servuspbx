ServusPBX Medical 1.4.3 - Trunk Formular vereinfachen

Änderungen:
- Server URI ist nicht mehr sichtbar.
- Server URI wird automatisch aus Domain erzeugt:
  sip:<domain>
- Durchwahlbereich Von/Bis steht nebeneinander.
- Hinweis zur automatischen Context-Erzeugung verbessert.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_4_3_trunk_form_simplify.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_3_trunk_form_simplify.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
