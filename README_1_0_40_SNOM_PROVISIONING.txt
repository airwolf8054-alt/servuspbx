ServusPBX Medical 1.0.40 - Snom Provisioning

Neu:
- inc/provisioning_snom.php als zentrale Provisioning Engine
- provision/snomD815.php
- provision/snomD810.php
- Kompatibilität:
  provision/d815.php
  provision/d810.php

Datenquellen:
- ps_endpoints: Nebenstelle, Name, Endpoint-ID, Gerätetyp
- ps_auths: SIP Benutzer/Passwort
- spbx_devices: MAC, Seriennummer, Provisioning aktiv
- spbx_device_keys: Tastenbelegung

URLs:
http://PBX-IP/provision/snomD815.php?mac=000413ABCDEF
http://PBX-IP/provision/snomD810.php?mac=000413ABCDEF

Hinweis:
Die Struktur folgt dem alten Prinzip:
snomD815.php / snomD810.php sind schlanke Entry-Dateien und rufen eine gemeinsame Snom-Engine auf.

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_40_snom_provisioning.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_40_snom_provisioning.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
