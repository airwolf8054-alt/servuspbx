ServusPBX Professional 2.0.3 - Dashboard SVG Icons

Geändert:
- Dashboard-Statistiken verwenden SVG Icons statt Emoji Icons.
- Dashboard-Aktionskarten verwenden dieselbe Icon-Sprache wie die Navigation.
- Rein optische Änderung.

Installation:
1. SQL optional:
   patch_servuspbx_professional_2_0_3_dashboard_icons.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_professional_2_0_3_dashboard_icons.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Browser hart neu laden.
