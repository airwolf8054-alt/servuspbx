ServusPBX Medical 1.0.43 - BLF CallerID Label Fix

Korrigiert:
- Bei Funktion "Nebenstelle" darf nicht "Rufumleitung" als Beschriftung stehen.
- Die Beschriftung kommt jetzt aus ps_endpoints.callerid.
- Wenn callerid leer ist, wird ps_endpoints.display_name verwendet.
- Bereits gespeicherte falsche Labels "Rufumleitung" werden per SQL-Patch korrigiert.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_43_blf_callerid_label_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_43_blf_callerid_label_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
