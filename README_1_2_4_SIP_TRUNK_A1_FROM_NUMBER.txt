ServusPBX Medical 1.2.4 - SIP-Trunk A1 + from_<rufnummer>

Änderungen:
- A1 Providerdaten:
  server_uri    = sip:siptrunk.a1.net
  client_domain = siptrunk.a1.net

- Neue SIP-Trunks werden mit endpoint_id im Format angelegt:
  from_<rufnummer>

  Beispiel:
  Rufnummer 431234567
  endpoint_id = from_431234567

- Beim Anlegen/Bearbeiten wird die Übersichtstabelle unten ausgeblendet.
  Die Übersicht erscheint nur auf der normalen SIP-Trunk-Listenansicht.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_4_sip_trunk_a1_from_number.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_4_sip_trunk_a1_from_number.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
