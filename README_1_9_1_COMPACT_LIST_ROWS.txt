ServusPBX Medical 1.9.1 - Compact List Rows

Geändert:
- Alle Tabellen-/Listenzeilen sind nun kompakter.
- Höhe orientiert sich an der Nebenstellenliste.
- Buttons und Formularfelder in Tabellen wurden ebenfalls kompakter gesetzt.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_9_1_compact_list_rows.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_9_1_compact_list_rows.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Browser Cache hart neu laden.
