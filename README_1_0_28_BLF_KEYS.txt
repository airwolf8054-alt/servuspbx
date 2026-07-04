ServusPBX Medical 1.0.28 - BLF / Funktionstasten

Neu:
- pages/function_keys.php
- Button "Tastenbelegung" in der Nebenstellenübersicht
- Sichtbar für Admin und User
- snom D815: 10 physische Tasten x 4 Ebenen = 40 fkeys
- snom D810: 4 physische Tasten x 4 Ebenen = 16 fkeys
- Speicherung in spbx_device_keys
- Provisioning übernimmt die Tasten in snomD810/snomD815 XML

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_28_blf_keys.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_28_blf_keys.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
