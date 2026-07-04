ServusPBX Medical 1.0.42 - Snom Static Fkeys

Änderung:
- Provisioning wurde nach der hochgeladenen Snom-Vorlage neu aufgebaut.
- Anpassung auf unsere Datenbank:
  ps_endpoints, ps_auths, spbx_devices, spbx_device_keys, spbx_settings.
- BLF/Fkeys werden immer vollständig ausgegeben.
  D815: fkey0 bis fkey39 + fkey_label0 bis fkey_label39
  D810: fkey0 bis fkey15 + fkey_label0 bis fkey_label15
- Fehlende Tasten werden als leer ausgegeben.
- spbx_device_keys wird statisch befüllt, damit jede Taste einen definierten Zustand hat.
- Beim Provisioning werden fehlende statische Key-Zeilen zusätzlich automatisch nacherzeugt.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_42_snom_static_fkeys.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_42_snom_static_fkeys.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

Hinweis:
Optional kann phone_admin_password in spbx_settings gesetzt werden.
