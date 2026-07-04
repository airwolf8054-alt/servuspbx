ServusPBX Medical 1.3.4 - Safe SQL Migration

Korrigiert den SQL-Fehler:
Unknown column 'clip_no_screening' in spbx_trunks

Ursache:
Der vorherige Patch wollte ext_from/ext_to AFTER clip_no_screening einfügen,
obwohl clip_no_screening in deinem Bestand noch nicht existierte.

Dieser Patch:
- legt spbx_trunks an, falls sie fehlt
- ergänzt alle benötigten Spalten ohne AFTER-Abhängigkeit
- übernimmt den bestehenden A1-Trunk als Datensatz, falls noch nicht vorhanden

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_3_4_trunk_sql_safe_columns.sql

2. Dateien wie aus 1.3.3 kopieren oder dieses ZIP komplett kopieren.

3. SIP-Trunk öffnen und speichern.
