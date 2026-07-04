ServusPBX Medical 1.0.60 - CallerID nur Name

Korrigiert:
- ps_endpoints.callerid wird beim Speichern nur noch als Anzeigename gespeichert.
- Kein Format mehr: "Name" <Durchwahl>
- Provisioning übernimmt dadurch user_realname/user_idle_text nur mit dem Namen.
- SQL-Patch bereinigt bestehende Datensätze.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_60_callerid_name_only.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_60_callerid_name_only.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
