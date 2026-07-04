ServusPBX Medical 1.4.4 - ps_endpoints Legacy Defaults

Korrigiert:
- Fatal Error:
  Field 'display_name' doesn't have a default value

Änderung:
- Beim Trunk-Speichern wird ps_endpoints jetzt schemaabhängig aufgebaut.
- Falls Legacy-Spalten wie extension oder display_name existieren, werden sie befüllt.
- SQL-Patch setzt Defaults für bekannte NOT NULL Legacy-Felder.

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_4_4_ps_endpoints_legacy_defaults.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_4_ps_endpoints_legacy_defaults.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. SIP-Trunk erneut speichern.
