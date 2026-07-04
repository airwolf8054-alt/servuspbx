ServusPBX Medical 1.0.35 - Dauerhafter Gerätetyp-Fix

Behoben:
- Tischtelefone springen nicht mehr auf SIP User.
- Gerätetyp wird beim Speichern ausschließlich aus Geräteart + Gerätetyp bestimmt.
- Keine Guard-/Fallback-Mischlogik mehr.
- spbx_devices und ps_endpoints werden exakt über endpoint_id synchronisiert.
- Nebenstellenliste liest den Gerätetyp direkt aus spbx_devices.device_model.

Wichtig:
Bereits falsch gespeicherte Datensätze müssen einmalig per SQL korrigiert werden.
Beispiele stehen im SQL-Patch.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_35_permanent_device_model_fix.sql

2. Falsch gespeicherte Alt-Datensätze korrigieren, z.B.:
   UPDATE spbx_devices d JOIN spbx_extensions e ON e.extension=d.extension SET d.device_model='snomD815' WHERE e.extension='10';
   UPDATE ps_endpoints p JOIN spbx_extensions e ON e.endpoint_id=p.id SET p.device_model='snomD815' WHERE e.extension='10';

3. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_35_permanent_device_model_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
