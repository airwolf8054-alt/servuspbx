ServusPBX Medical 1.2.24 - D810 Context-Key Belegung

Änderung:
- Nur snomD810:
  context_key idx=0 = keyevent F_ADR_BOOK

Unverändert:
- snomD815:
  context_key idx=0 = keyevent F_DND

Beide Geräte:
- idx=1 = F_CALL_LIST
- idx=2 = F_RINGER_SILENT
- idx=3 = F_LABEL_PAGE_NEXT

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_24_d810_context_keys.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_24_d810_context_keys.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. D810-Nebenstelle speichern, damit snom-check-cfg ausgelöst wird.
