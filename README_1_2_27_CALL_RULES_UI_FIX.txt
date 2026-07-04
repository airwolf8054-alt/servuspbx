ServusPBX Medical 1.2.27 - Anrufregeln UI Fix

Korrigiert:
- Formularfelder übernehmen nun einheitlich das ServusPBX-Layout.
- Öffnungszeiten werden als Tabelle dargestellt:
  Tag | Zeitraum 1 | Zeitraum 2

Nicht geändert:
- SIP-Trunks
- bestehende Anrufregel-Logik
- Dialplan-Generator

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_27_call_rules_ui_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_27_call_rules_ui_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
