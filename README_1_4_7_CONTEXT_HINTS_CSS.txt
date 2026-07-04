ServusPBX Medical 1.4.7 - Context Hints + CSS

Neu:
- Beim Erzeugen von internal_<rufnummer> werden Hints angelegt:
  10,hint,PJSIP/10
  100,hint,PJSIP/100
  1000,hint,PJSIP/1000

Wichtig:
- SQL ändert extensions.priority auf VARCHAR(20), damit 'hint' gespeichert werden kann.

Außerdem:
- CSS Guard für /pages/outbound_routes.php, damit die Formularfelder wieder wie die restliche Oberfläche aussehen.

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_4_7_context_hints_css.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_7_context_hints_css.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Reload:
   sudo /usr/sbin/asterisk -rx "dialplan reload"

4. Prüfen:
   asterisk -rx "dialplan show internal_43312423826"
