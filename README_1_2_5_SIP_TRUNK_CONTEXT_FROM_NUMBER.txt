ServusPBX Medical 1.2.5 - SIP-Trunk Context from_<rufnummer>

Korrigiert:
- Endpoint-ID wird NICHT mehr aus der Rufnummer gebildet.
- Endpoint-ID bleibt:
  trunk-<provider>-<name>

Neu:
- Der eingehende Context wird aus der Rufnummer gebildet:
  from_<rufnummer>

Beispiel:
Rufnummer: 431234567
Endpoint-ID: trunk-a1-hauptnummer
Context: from_431234567

A1 bleibt:
server_uri = sip:siptrunk.a1.net
client_domain = siptrunk.a1.net

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_5_sip_trunk_context_from_number.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_5_sip_trunk_context_from_number.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
