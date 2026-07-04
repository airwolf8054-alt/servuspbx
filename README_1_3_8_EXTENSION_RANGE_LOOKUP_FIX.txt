ServusPBX Medical 1.3.8 - Nebenstellen Range Lookup Fix

Korrigiert:
- Nebenstellen-Dropdown zeigte nur die aktuelle Nebenstelle.
- Ursache: Die Durchwahlrange wurde nicht zuverlässig zum Context internal_<rufnummer> gefunden.

Neue Lookup-Reihenfolge:
1. internal_<rufnummer> direkt gegen spbx_trunks.main_number
2. fallback über spbx_outbound_routes
3. fallback auf erste aktive Trunk-Range

Erwartung:
Bei Range 10-99:
- aktuelle Nebenstelle bleibt auswählbar
- freie Nummern werden auswählbar angezeigt
- belegte Nummern werden angezeigt, aber gesperrt

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_3_8_extension_range_lookup_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_3_8_extension_range_lookup_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
