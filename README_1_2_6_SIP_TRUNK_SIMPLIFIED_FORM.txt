ServusPBX Medical 1.2.6 - SIP-Trunk Formular vereinfacht

Entfernt aus dem Formular:
- Server URI
- Context eingehend
- Transport
- Codecs
- Expiration

Automatisch:
- server_uri aus Providerprofil
- context = from_<rufnummer>
- transport = transport-<provider>
- codecs aus Providerprofil
- expiration aus Providerprofil

A1:
- server_uri = sip:siptrunk.a1.net
- client_domain = siptrunk.a1.net
- transport = transport-a1

Neue Transports:
- transport-a1
- transport-easybell
- transport-magenta

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_6_sip_trunk_simplified_form.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_6_sip_trunk_simplified_form.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
