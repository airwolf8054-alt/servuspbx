ServusPBX Medical 1.2.18 - Snom Telefonbuch inline aus spbx_phonebook

Wichtig:
Die vorherigen Telefonbuch-Patches 1.2.18_snom_addressbook_upload und 1.2.19_inline_tbook_provisioning nicht verwenden.

Umsetzung:
- Telefonbuch wird direkt im Snom-Provisioning ausgegeben.
- Position:
  </phone-settings>
  <tbook ...>
  <uploads>

Quelle:
- spbx_phonebook

Erkannte Feldnamen:
- first_name oder firstname
- last_name oder lastname
- number oder phone_number
- type optional, Standard office
- active optional

Test:
1. Provisioning direkt prüfen:
   curl "http://10.43.4.244/provision/snomD815.php?mac=<MAC>"

2. Darin muss genau ein Root-Element <settings> enthalten sein und darin:
   <phone-settings>...</phone-settings>
   <tbook complete="true" e="2">...</tbook>
   <uploads>...</uploads>

3. Danach Nebenstelle speichern, damit snom-check-cfg ausgelöst wird.
