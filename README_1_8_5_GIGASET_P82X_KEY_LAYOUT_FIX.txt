ServusPBX Medical 1.8.5 - Gigaset P82x Key Layout Fix

Korrigiert:
- Gigaset P82x zeigt Taste 1-4 links.
- Gigaset P82x zeigt Taste 5-8 rechts.
- fkey-Index bleibt unverändert:
  links fkey0-3
  rechts fkey4-7

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_8_5_gigaset_p82x_key_layout_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_8_5_gigaset_p82x_key_layout_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
