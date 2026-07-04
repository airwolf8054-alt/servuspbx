ServusPBX Medical 1.0.36 - Extensions Rebuild

Wichtig:
pages/extensions.php wurde komplett neu aufgebaut, um den SIP-User-Rückfall dauerhaft zu entfernen.

Fix:
- Gerätetyp kommt nur noch aus Geräteart + Gerätetyp.
- Tischtelefon + snom D815 bleibt snomD815.
- Tischtelefon + snom D810 bleibt snomD810.
- DECT wird snom_dect_handset.
- SIP-Gerät wird sip_user.
- spbx_devices und ps_endpoints werden beide gesetzt.
- Nebenstellenliste liest direkt aus spbx_devices.device_model.
- Aktionsbuttons sind sauber gekapselt.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_36_extensions_rebuild.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_36_extensions_rebuild.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Bereits falsch gespeicherte Einträge einmalig korrigieren, Beispiel:
   UPDATE spbx_devices d JOIN spbx_extensions e ON e.extension=d.extension SET d.device_model='snomD815' WHERE e.extension='10';
   UPDATE ps_endpoints p JOIN spbx_extensions e ON e.endpoint_id=p.id SET p.device_model='snomD815' WHERE e.extension='10';
