ServusPBX Medical 1.0.16 - Geräteart-Auswahl

Neu:
- Nebenstellenformular hat zuerst Geräteart:
  Tischtelefon
  DECT-Schnurlos
  SIPONLY

- Gerätetyp wird abhängig davon angezeigt:
  Tischtelefon -> snom D815, snom D810
  DECT-Schnurlos -> snom M900
  SIPONLY -> SIP User

- snom M400 wurde auf snom M900 umgestellt.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_16_extension_device_groups.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_16_extension_device_groups.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
