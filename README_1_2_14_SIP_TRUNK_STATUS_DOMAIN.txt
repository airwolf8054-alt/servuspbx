ServusPBX Medical 1.2.14 - SIP-Trunk Status + Domain

Korrigiert:
- Übersichtsspalte Server heißt jetzt Domain.
- Angezeigt wird client_domain/domain statt server_uri.
- Status wird aus Asterisk gelesen:
  asterisk -rx "pjsip show registrations"
- Registered wird als Registriert angezeigt.

Wichtig:
Der Webserver-Benutzer muss den Asterisk-CLI-Befehl ausführen dürfen.
Wenn Status weiterhin Unbekannt bleibt, bitte sudoers/Rechte für asterisk -rx prüfen.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_14_sip_trunk_status_domain.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_14_sip_trunk_status_domain.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
