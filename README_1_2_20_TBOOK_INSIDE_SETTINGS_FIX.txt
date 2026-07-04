ServusPBX Medical 1.2.20 - tbook inside settings Fix

Korrigiert:
- XML-Fehler:
  Extra content at the end of the document

Ursache:
- tbook wurde außerhalb des Root-Elements ausgegeben.

Fix:
- tbook wird direkt vor dem finalen </settings> ausgegeben.
- Damit bleibt genau ein XML-Root-Element vorhanden:
  <settings>
    <phone-settings>...</phone-settings>
    <uploads>...</uploads>
    <tbook complete="true" e="2">...</tbook>
  </settings>

Quelle:
- spbx_phonebook

Test:
curl "http://10.43.4.244/provision/snomD815.php?mac=<MAC>" | xmllint --noout -
curl "http://10.43.4.244/provision/snomD810.php?mac=<MAC>" | xmllint --noout -
