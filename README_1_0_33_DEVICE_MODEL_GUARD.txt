ServusPBX Medical 1.0.33 - Device Model Guard

Behoben:
- Tischtelefone dürfen beim Speichern nicht mehr ungewollt zu SIP User werden.
- spbx_ext_guard_device_model() schützt gegen falsche Defaults.
- Sync aktualisiert nur noch den exakten Endpoint.
- SQL-Patch enthält eine Diagnoseabfrage und Beispiel-Reparatur für bereits beschädigte Datensätze.

Wichtig:
Bereits falsch gespeicherte Datensätze müssen einmalig korrigiert werden.
Beispiel für Nebenstelle 10 im SQL-Patch anpassen und ausführen.

Installation:
1. SQL-Patch importieren bzw. Diagnose ausführen:
   patch_servuspbx_medical_1_0_33_device_model_guard.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_33_device_model_guard.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
