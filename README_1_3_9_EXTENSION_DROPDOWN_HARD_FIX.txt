ServusPBX Medical 1.3.9 - Nebenstellen-Dropdown Hard Fix

Korrigiert:
- Dropdown zeigte nur die aktuelle Nebenstelle.

Neue Logik:
1. internal_<rufnummer> wird direkt gegen spbx_trunks.main_number gematcht.
2. Falls nicht gefunden: outgoing_<rufnummer> über spbx_outbound_routes.
3. Falls noch immer nicht gefunden: erste aktive Trunk-Range.
4. Falls auch das fehlt: Standard 10-99, damit das Dropdown nie leer bleibt.

Anzeige:
- aktuelle Nebenstelle bleibt auswählbar
- freie Nummern sind auswählbar
- belegte Nummern sind sichtbar, aber gesperrt

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_3_9_extension_dropdown_hard_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_3_9_extension_dropdown_hard_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
