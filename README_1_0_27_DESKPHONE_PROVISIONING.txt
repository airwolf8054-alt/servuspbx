ServusPBX Medical 1.0.27 - Tischtelefon Provisionierung

Neu:
- provision/snomD810.php
- provision/snomD815.php
- inc/provisioning_snom.php
- Provisionierung über MAC-Adresse
- Geräte werden aus spbx_devices geladen
- SIP Daten kommen aus ps_auths / ps_endpoints / spbx_extensions
- Nebenstellenliste zeigt für D810/D815 eine Provisioning-URL

Beispiel:
http://PBX-IP/provision/snomD815.php?mac=000413ABCDEF
http://PBX-IP/provision/snomD810.php?mac=000413ABCDEF

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_27_deskphone_provisioning.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_27_deskphone_provisioning.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
