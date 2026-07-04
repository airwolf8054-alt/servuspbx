ServusPBX Medical 1.0.47 - Provisioning BLF Label Fix

Korrigiert:
- Im Provisioning File wurde bei manchen BLF-Tasten noch die Durchwahl als Label ausgegeben.
- Provisioning löst bei key_type='blf' das Label jetzt direkt aus ps_endpoints.callerid auf.
- Fallback: ps_endpoints.display_name, danach Durchwahl.
- Der gespeicherte key_label-Wert wird bei BLF im Provisioning nicht mehr blind verwendet.
- SQL-Patch korrigiert bestehende BLF-Labels in spbx_device_keys.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_47_provisioning_blf_label_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_47_provisioning_blf_label_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
