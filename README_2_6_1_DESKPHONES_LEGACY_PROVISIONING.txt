ServusPBX Professional 2.6.1 - Deskphones / Legacy Provisioning

Ziel
----
Die Tischtelefon-Provisionierung basiert wieder auf der alten, bewährten flachen phones-Struktur.
Die neue Tabelle heißt deskphones und entspricht dem alten phones-Table, erweitert bis fkey59 für Geräte mit mehr physischen Tasten wie snom D895M.

Geändert
--------
- Neue SQL-Patchdatei:
  patch_servuspbx_professional_2_6_1_deskphones_legacy_provisioning.sql

- Neue/angepasste Provisioning-Dateien:
  provision/snomD862.php
  provision/snomD865.php
  provision/snomD892.php
  provision/snomD895.php

- inc/provisioning_snom.php:
  Liest Tischtelefone jetzt aus deskphones statt spbx_devices/spbx_device_keys.
  SIP-Passwort und Caller-ID kommen weiter aus ps_auths/ps_endpoints.
  FKeys kommen aus deskphones.fkeyXaction/value/label.

- pages/function_keys.php:
  Speichert Funktionstasten direkt in deskphones.fkeyXaction/value/label.
  spbx_device_keys ist für Tischtelefone nicht mehr die führende Quelle.

- pages/extensions.php:
  Neue Tischtelefone werden zusätzlich in deskphones angelegt/aktualisiert.
  Neue Modelle aus der Gerätetabelle wurden ergänzt:
  snomD862, snomD865, snomD892, snomD895.

Geräte-Logik Context Taste 0
----------------------------
Telefonbuch:
- snomD810
- snomD812
- snomD862
- snomD895
- GigasetP810
- GigasetP82x

DND:
- snomD815
- snomD865
- snomD892
- GigasetP85x

Wichtig
-------
Vor dem Testen muss der SQL-Patch importiert werden.
Bestehende spbx_devices/spbx_device_keys werden dabei nach deskphones migriert.
Die alten Tabellen bleiben absichtlich erhalten, damit DECT/Altlogik nicht ungewollt beschädigt wird.
