ServusPBX Medical 1.0.38 - Gerätetyp-Anzeige aus ps_endpoints

Korrigiert:
- Nebenstellenliste liest den Gerätetyp direkt aus ps_endpoints.device_model.
- Bearbeiten-Seite lädt den Gerätetyp bevorzugt ebenfalls aus ps_endpoints.device_model.
- spbx_devices wird nur noch für MAC/IPEI/Provisioning-Daten genutzt.
- Keine Anzeige mehr aus spbx_devices.device_model.

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_38_endpoint_device_display.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_38_endpoint_device_display.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
