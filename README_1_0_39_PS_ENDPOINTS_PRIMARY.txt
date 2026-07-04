ServusPBX Medical 1.0.39 - ps_endpoints als Primärquelle

Änderung:
- Nebenstellenliste startet aus ps_endpoints.
- Durchwahl kommt aus ps_endpoints.extension.
- Name kommt aus ps_endpoints.display_name.
- Endpoint-ID kommt aus ps_endpoints.id.
- Gerätetyp kommt aus ps_endpoints.device_model.
- Bei SNOM DECT Handset ist ps_endpoints.id die IPEI.
- spbx_extensions liefert nur Webinterface-Zusatzdaten.
- spbx_devices liefert nur Hardwaredaten wie MAC/Seriennummer.

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_39_ps_endpoints_primary.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_39_ps_endpoints_primary.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
