ServusPBX Medical 1.2.7 - SIP-Trunk Provider UI

Änderungen:
- Titel im Formular:
  - SIP-Trunk anlegen
  - SIP-Trunk bearbeiten
- Providerwechsel aktualisiert Domain/Proxy ohne Seitenreload.
- Magenta Domain/Server:
  sip1.magenta.at

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_7_sip_trunk_provider_ui.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_7_sip_trunk_provider_ui.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
