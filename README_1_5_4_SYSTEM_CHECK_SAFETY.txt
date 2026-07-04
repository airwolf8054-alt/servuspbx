ServusPBX Medical 1.5.4 - Systemprüfung / Deppensicherheit

Neu:
- Menüpunkt "Öffnungszeiten" aus dem Hauptmenü entfernt.
- Neuer Menüpunkt "Systemprüfung".

Die Systemprüfung kontrolliert:
- extensions.conf => odbc,asterisk,ast_config
- extensions => odbc,asterisk,extensions
- ast_config Tabelle
- extensions Tabelle
- spbx_outbound_routes
- spbx_trunks
- internal_<rufnummer> Contexts
- outgoing_<rufnummer> Contexts
- BLF Pattern-Hints
- A1 inbound auth leer
- A1 inbound context incoming
- A1 from_user leer
- keine Selbst-BLFs

Installation:
1. SQL optional einspielen:
   patch_servuspbx_medical_1_5_4_system_check_safety.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_5_4_system_check_safety.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Im Menü "Systemprüfung" öffnen.
