ServusPBX Medical 1.0.49 - Nebenstellen Übersicht Cleanup

Änderungen:
- Endpoint-ID-Spalte aus der Nebenstellenübersicht entfernt.
- Durchwahl bleibt die ps_endpoints.id bei Tischtelefonen.
- DECT bleibt IPEI als ps_endpoints.id.
- Provisioning-URL-Button wird für alle snomD815/snomD810 mit MAC angezeigt.
- MAC wird über spbx_devices.endpoint_id geladen, mit Fallback über extension für ältere Datensätze.
- Tabellenlayout entsprechend kompakter angepasst.

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_49_extensions_overview_cleanup.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_49_extensions_overview_cleanup.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
