ServusPBX Medical 1.1.3 - spbx_devices Cleanup

Ziel:
- spbx_devices als zentrale Geräte-/Provisioning-Tabelle normalisieren.
- Keine bestehenden Geräte löschen.
- Einheitliche Geräte-Identität über endpoint_id = ps_endpoints.id = Durchwahl.

Regeln:
- Tischtelefone:
  ps_endpoints.id = Durchwahl
  spbx_devices.endpoint_id = Durchwahl
  spbx_devices.mac = MAC
  spbx_devices.ipei = NULL
  provisioning_enabled = 1
  provision_file = snomD815.php / snomD810.php

- DECT-Handsets:
  ps_endpoints.id = Durchwahl
  spbx_devices.endpoint_id = Durchwahl
  spbx_devices.ipei = IPEI
  spbx_devices.mac = NULL
  provisioning_enabled = 0

- SIP-User:
  MAC NULL
  IPEI NULL
  provisioning_enabled = 0

Installation:
1. Backup:
   mysqldump -u root -p general > /root/general_before_1_1_3.sql

2. SQL-Patch importieren:
   patch_servuspbx_medical_1_1_3_spbx_devices_cleanup.sql

3. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_3_spbx_devices_cleanup.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

Prüfung:
SELECT endpoint_id, device_model, extension, mac, ipei, provision_file
FROM spbx_devices
ORDER BY extension;
