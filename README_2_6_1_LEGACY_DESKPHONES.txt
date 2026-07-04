ServusPBX Professional 2.6.1 - Legacy Deskphones Provisioning

Ziel dieses Standes:
- Generator-/Abstraktionslogik fuer Tischtelefon-Tasten wird nicht mehr verwendet.
- Tischtelefone verwenden die flache Legacy-Tabelle `deskphones`.
- Feldnamen bleiben bewusst kompatibel zum alten System: fkey0action/fkey0value/fkey0label ... fkey41action/fkey41value/fkey41label.
- Maximum: 42 Tasten (snom D895M: 14 physische Tasten x 3 Ebenen). Alle anderen Modelle bleiben darunter.
- Provisioning liest aus `deskphones` und erzeugt wieder direkt Snom/Gigaset XML.

Vor dem Testen importieren:
  mysql -u root -p general < patch_servuspbx_professional_2_6_1_legacy_deskphones.sql

Neue/ergänzte Modelle:
- snomD862
- snomD865
- snomD892
- snomD895

Hinweis:
spbx_devices bleibt fuer DECT/Sondergeraete im Projekt, Tischtelefone werden aber in `deskphones` gefuehrt.
