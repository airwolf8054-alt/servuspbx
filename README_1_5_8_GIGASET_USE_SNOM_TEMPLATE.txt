ServusPBX Medical 1.5.8 - Gigaset nutzt D815/Snom Template

Korrektur:
- GigasetP82x.php und GigasetP85x.php verwenden jetzt dasselbe Provisioning-Template wie snomD815.
- Kein separates Gigaset-Fantasie-XML mehr.

Unterschiede:
- Gigaset P85x: 40 Funktionstasten wie snom D815
- Gigaset P82x: 32 Funktionstasten, UI zeigt 8 physische Tasten pro Ebene, 4 links und 4 rechts

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_5_8_gigaset_use_snom_template.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_5_8_gigaset_use_snom_template.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Provisioning URL testen:
   /provision/GigasetP85x.php?mac=<MAC>
   /provision/GigasetP82x.php?mac=<MAC>
