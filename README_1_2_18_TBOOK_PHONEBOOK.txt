ServusPBX Medical 1.2.18 - Snom tbook Telefonbuch

Wichtig:
Der vorherige Patch 1.2.18_snom_addressbook_upload wird NICHT verwendet.

Neu:
- provision/tbook.php erzeugt das bewährte Snom-Telefonbuchformat:
  <tbook complete="true" e="2">
    <item context="active" type="office">
      <first_name>...</first_name>
      <last_name>...</last_name>
      <number>...</number>
    </item>
  </tbook>

Quelle:
1. Tabelle directory, wenn vorhanden.
2. Fallback auf ps_endpoints, falls directory noch leer/nicht vorhanden ist.

Provisioning:
- snomD815.php und snomD810.php setzen:
  phonebook_url = http://10.43.4.244/provision/tbook.php
  directory_url = http://10.43.4.244/provision/tbook.php
- uploads/gui_xml_state_adressbook zeigt ebenfalls auf tbook.php, falls verwendet.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_2_18_tbook_phonebook.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_18_tbook_phonebook.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

Test:
curl http://10.43.4.244/provision/tbook.php

Danach Nebenstelle speichern, damit snom-check-cfg ausgelöst wird.
