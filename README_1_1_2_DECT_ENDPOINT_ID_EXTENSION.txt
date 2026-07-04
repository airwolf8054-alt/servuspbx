ServusPBX Medical 1.1.2 - DECT Endpoint-ID = Durchwahl

Änderung:
- SNOM DECT Handsets verwenden künftig als ps_endpoints.id die Durchwahl.
- Die IPEI wird nicht mehr als PJSIP Endpoint-ID verwendet.
- Die IPEI bleibt als Gerätekennung in spbx_devices.ipei erhalten.

Künftig:
ps_endpoints.id        = 96
ps_endpoints.extension = 96
ps_auths.id            = 96
ps_aors.id             = 96
spbx_devices.ipei      = 02555A2FFB

Wichtig:
Bestehende alte DECT-Handsets mit IPEI als Endpoint-ID bitte löschen und neu anlegen.
Das ist sauberer als eine automatische Migration.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_1_2_dect_endpoint_id_extension.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_2_dect_endpoint_id_extension.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
