ServusPBX Medical 1.0.21 - Device ENUM Wide Fix

Änderung:
- ENUMs werden NICHT mehr verkleinert.
- snom M400 und snom M900 bleiben in der Datenbank erhalten.
- Nebenstellen-Webinterface zeigt trotzdem nur:
  Tischtelefon -> snom D815, snom D810
  DECT-Schnurlos -> SNOM DECT Handset
  SIP-Gerät -> SIP User

Grund:
- M400/M900 Basisstationen werden später in einem eigenen Modul verwaltet.
- Dadurch gibt es keine ENUM-Migrationsfehler mehr durch vorhandene Werte.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_21_device_enum_wide.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_21_device_enum_wide.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
