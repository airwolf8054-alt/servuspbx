ServusPBX Medical 1.0.57 - Snom Upload XML Files

Änderungen:
- Die hochgeladenen XML-Dateien wurden 1:1 in den Ordner /provision übernommen.
- Provisioning enthält nach </phone-settings> eine neue <uploads>-Sektion.
- Links zeigen auf den bestehenden Ordner /provision:
  /provision/identity.xml
  /provision/information.xml
  /provision/maintenance.xml
  /provision/preferences.xml
  /provision/adressbook.xml
  /provision/state_settings.xml
  /provision/network.xml
- DKey/context/idle-key Einträge wurden in phone-settings ergänzt.

Übernommene XML-Dateien:
adressbook.xml
gui_lang_DE.xml
gui_lang_EN.xml
information.xml
maintenance.xml
network.xml
preferences.xml
state_ringtone.xml
state_settings.xml
usb_bluetooth.xml

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_57_snom_uploads_files.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_57_snom_uploads_files.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
