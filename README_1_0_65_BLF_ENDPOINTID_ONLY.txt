ServusPBX Medical 1.0.65 - BLF Endpoint-ID Only

Änderung:
- BLF Provisioning schreibt nicht mehr:
  blf sip:96@10.43.4.244

- Sondern:
  blf 96

Quelle bleibt spbx_device_keys.key_value, welches bei Nebenstellen-BLF die Durchwahl/ps_endpoints.id enthält.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_0_65_blf_endpointid_only.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_65_blf_endpointid_only.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
