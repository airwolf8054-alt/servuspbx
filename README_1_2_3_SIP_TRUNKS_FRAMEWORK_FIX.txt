ServusPBX Medical 1.2.3 - SIP-Trunks Framework Fix

Korrigiert:
- trunk_a1.php verwendete kein ServusPBX Layout.
- Sidebar/Header/CSS fehlten.
- Die Seite nutzt jetzt wieder explizit den gleichen App-Rahmen wie die ursprüngliche funktionierende trunk_a1.php:
  - inc/auth.php
  - inc/branding.php
  - spbx_sidebar()
  - spbx_page_header()
  - css/servuspbx.css

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_3_sip_trunks_framework_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_3_sip_trunks_framework_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
