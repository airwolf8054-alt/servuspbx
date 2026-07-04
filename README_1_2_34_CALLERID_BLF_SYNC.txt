ServusPBX Medical 1.2.34 - CallerID -> BLF Label Sync

Neu:
- Wenn sich die CallerID / Anzeige einer Nebenstelle ändert:
  1. werden alle BLF-Tasten mit key_type='blf' und key_value=<Durchwahl> gesucht
  2. key_label wird auf die neue CallerID-Anzeige gesetzt
  3. alle betroffenen aktiven Geräte werden per snom-check-cfg neu provisioniert

Neue Datei:
- inc/blf_sync.php

Optional:
- scripts/sync_blf_labels.php
  Einmaliger Abgleich aller bestehenden BLF-Labels:
  php /var/www/html/scripts/sync_blf_labels.php

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_34_callerid_blf_sync.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_34_callerid_blf_sync.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

Voraussetzung:
- www-data darf Asterisk CLI ausführen:
  www-data ALL=(ALL) NOPASSWD: /usr/sbin/asterisk
