ServusPBX Medical 1.0.54 - IPEI NULL Fix

Behoben:
- Fehler beim Anlegen eines snomD815/snomD810:
  Duplicate entry '' for key 'uniq_ipei'
- Tischtelefone haben keine IPEI und speichern jetzt NULL statt leerem String.
- DECT-Handsets speichern weiterhin die IPEI.
- SQL-Patch setzt bestehende leere IPEI-Werte auf NULL.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_54_ipei_null_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_54_ipei_null_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
