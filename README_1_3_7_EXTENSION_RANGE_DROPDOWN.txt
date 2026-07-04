ServusPBX Medical 1.3.7 - Nebenstellen-Dropdown

Änderung:
- Beim Nebenstellen-Anlegen/Bearbeiten werden alle Durchwahlen aus dem Trunk-Bereich angezeigt.
- Die aktuelle Nebenstelle bleibt auswählbar.
- Freie Durchwahlen sind auswählbar.
- Bereits belegte Durchwahlen werden angezeigt, aber deaktiviert.
- Belegte Durchwahlen zeigen den Namen der Nebenstelle.

Beispiel:
10 - aktuelle Nebenstelle
11
12 - belegt: Simone Klarheit
13

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_3_7_extension_range_dropdown.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_3_7_extension_range_dropdown.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
