ServusPBX Medical 1.2.17 - Snom Telefonbuch Provisioning

Neu:
- provision/phonebook.xml.php erzeugt ein Snom-kompatibles Telefonbuch aus ps_endpoints.
- snomD815.php und snomD810.php setzen:
  <phonebook_url perm="R">http://10.43.4.244/provision/phonebook.xml.php</phonebook_url>

Inhalt:
- alle aktiven phone-Endpoints
- Name = display_name
- Nummer = extension

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_17_phonebook_provisioning.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_17_phonebook_provisioning.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

Test:
curl http://10.43.4.244/provision/phonebook.xml.php

Danach Telefon speichern oder BLF speichern, damit snom-check-cfg ausgelöst wird.
