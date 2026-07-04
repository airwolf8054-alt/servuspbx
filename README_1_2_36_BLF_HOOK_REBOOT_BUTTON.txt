ServusPBX Medical 1.2.36 - BLF Sync Hook Fix + Snom Reboot Button

Korrigiert:
- BLF-Sync war versehentlich im Voicemail-else Block.
- Dadurch lief der Sync nur, wenn Voicemail deaktiviert war.
- Jetzt läuft der Sync direkt nach $db->commit() und vor dem normalen snom-check-cfg Notify.

Neu:
- Button "Reboot" in der Nebenstellenliste für snom D810/D815.
- Verwendet:
  pjsip send notify snom-reboot endpoint <endpoint>

Geänderte Dateien:
- pages/extensions.php
- inc/asterisk_notify.php

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_36_blf_hook_reboot_button.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_36_blf_hook_reboot_button.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

Voraussetzung:
- www-data darf Asterisk CLI ausführen:
  www-data ALL=(ALL) NOPASSWD: /usr/sbin/asterisk
