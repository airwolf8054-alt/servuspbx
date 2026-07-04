ServusPBX Medical 1.0.45 - BLF Final Cleanup

Änderungen:
- key_type empty wurde durch none ersetzt.
- key_type speed wurde durch dest ersetzt.
- Speichern-Button ist jetzt oben.
- Button "Alle BLF zurücksetzen" ist jetzt oben.
- Reset setzt alle Tasten auf none und leert Label/Value.
- BLF-Editor wurde kompakter gemacht, damit eine Ebene möglichst vollständig ohne Scrollbalken sichtbar ist.
- Provisioning gibt weiterhin alle Fkeys statisch aus.
- Rufnummer wird im Provisioning als dest ausgegeben.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_45_blf_final_cleanup.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_45_blf_final_cleanup.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
