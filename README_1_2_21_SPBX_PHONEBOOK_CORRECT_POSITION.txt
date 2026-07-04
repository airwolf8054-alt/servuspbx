ServusPBX Medical 1.2.21 - Snom Telefonbuch korrekt positioniert

Korrigiert:
- XML-Fehler: Extra content at the end of the document
- Ursache: Telefonbuch wurde in snomD815.php/snomD810.php falsch bzw. außerhalb des Root-Elements gepatcht.

Richtige Umsetzung:
- Die Snom-Provisionierung wird zentral in inc/provisioning_snom.php erzeugt.
- Das Telefonbuch wird dort ausgegeben, wo es im alten funktionierenden Script war:
  nach </phone-settings> und vor <uploads>.

Quelle:
- spbx_phonebook

Erkannte Spalten:
- first_name oder firstname
- last_name oder lastname
- number oder phone_number
- type optional, Standard office
- active optional

Test ohne xmllint:
curl -o /tmp/snom.xml "http://10.43.4.244/provision/snomD815.php?mac=<MAC>"
head -c 200 /tmp/snom.xml
grep -o "<tbook[^>]*>" /tmp/snom.xml
grep -o "</tbook>" /tmp/snom.xml

Erwartete Reihenfolge:
grep -n "phone-settings\|tbook\|uploads" /tmp/snom.xml
