ServusPBX Medical 1.2.15 - SIP-Trunk Status via sudo

Korrigiert:
- Status blieb auf Unbekannt, obwohl Asterisk Registered zeigt.
- PHP ruft jetzt explizit auf:
  sudo /usr/sbin/asterisk -rx "pjsip show registrations"
- Geparst wird anhand von spbx_trunks.endpoint_id.

Voraussetzung:
www-data ALL=(ALL) NOPASSWD: /usr/sbin/asterisk

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_15_sip_trunk_status_sudo.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_15_sip_trunk_status_sudo.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
