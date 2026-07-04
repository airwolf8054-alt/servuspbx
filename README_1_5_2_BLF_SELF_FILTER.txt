ServusPBX Medical 1.5.2 - BLF Self Filter

Änderung:
- In der Tastenbelegung wird die eigene Nebenstelle nicht mehr als BLF-Ziel angeboten.
- Beim Speichern wird eine Selbst-BLF-Belegung automatisch auf Leer gesetzt.
- Bestehende Selbst-BLFs können per SQL bereinigt werden.

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_5_2_blf_self_filter.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_5_2_blf_self_filter.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
