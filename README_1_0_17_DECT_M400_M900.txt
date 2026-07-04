ServusPBX Medical 1.0.17 - DECT M400/M900

Neu:
- Geräteart:
  Tischtelefon
  DECT-Schnurlos
  SIP-Gerät

- Gerätetypen:
  Tischtelefon -> snom D815, snom D810
  DECT-Schnurlos -> snom M400, snom M900
  SIP-Gerät -> SIP User

- BLF ist nur für snom D815 und snom D810 vorgesehen.
- snom M400 und snom M900 bekommen keine BLF-Funktion.
- spbx_device_types bleibt erhalten und enthält alle fünf Gerätetypen.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_17_dect_m400_m900.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_17_dect_m400_m900.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
