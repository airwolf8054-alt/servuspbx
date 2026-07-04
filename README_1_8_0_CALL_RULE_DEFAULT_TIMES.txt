ServusPBX Medical 1.8.0 - Call Rule Default Times

Korrigiert:
- Vorhandene Zeiten werden weiterhin aus der DB geladen.
- Leere Tage/Felder werden im Formular mit Standardzeiten vorbefüllt:
  Montag-Freitag 06:00-23:59
  Samstag/Sonntag leer

Wichtig:
- Der funktionierende DID-Wählplan aus 1.7.6 bleibt unverändert.
- Beim Speichern werden die sichtbaren Zeiten in spbx_call_rule_time_windows geschrieben.

Installation:
1. SQL optional.
2. Dateien kopieren.
