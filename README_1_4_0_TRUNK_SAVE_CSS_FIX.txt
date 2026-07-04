ServusPBX Medical 1.4.0 - Trunk Save Fix + CSS Guard

Korrigiert:
- Fatal Error beim Speichern:
  Field 'extension' doesn't have a default value
- Ursache:
  ps_aors hat in deinem Bestand ein Legacy-Pflichtfeld extension.
- Fix:
  Beim Speichern wird ps_aors.extension jetzt mit der Endpoint-ID befüllt.

Außerdem:
- Trunkformular bekommt einen CSS Guard, damit alle Inputs/Selects sauber im ServusPBX Stil dargestellt werden.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_4_0_trunk_save_css_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_0_trunk_save_css_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. SIP-Trunk erneut speichern.
