ServusPBX Medical 1.0.19 - DECT Handsets

Neu:
- DECT-Basisstationen M400/M900 haben eine MAC-Adresse.
- Handsets werden separat erfasst:
  IDX 1 bis 10
  IPEI
  Nebenstelle
  Name
- Neue Tabelle spbx_dect_handsets.
- Handset-Felder werden nur bei Geräteart DECT-Schnurlos angezeigt.
- BLF bleibt für DECT-Geräte deaktiviert.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_19_dect_handsets.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_19_dect_handsets.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
