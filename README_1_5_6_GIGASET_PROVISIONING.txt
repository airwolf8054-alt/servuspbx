ServusPBX Medical 1.5.6 - Gigaset Provisioning

Neu:
- provision/GigasetP82x.php
- provision/GigasetP85x.php

Gerätemodelle:
- Gigaset P82x:
  8 BLF-Tasten, 4 links und 4 rechts vom Display
- Gigaset P85x:
  wie snom D815, 10 BLF-Tasten

Integration:
- Geräteauswahl bei Nebenstellen erweitert
- Tastenbelegung unterstützt Gigaset P82x/P85x
- Provisioning nutzt bestehende SIP-, BLF- und Telefonbuchdaten

Hinweis:
Die Gigaset XML-Struktur ist bewusst als ServusPBX-Template angelegt. Falls ein konkretes Gigaset-Firmwareformat spezielle Tag-Namen verlangt, können wir diese Datei gezielt anpassen, ohne die restliche Logik zu ändern.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_5_6_gigaset_provisioning.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_5_6_gigaset_provisioning.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
