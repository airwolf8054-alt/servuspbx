ServusPBX Medical 1.7.7 - Call Rule Time Prefill Fix

Korrigiert:
- Öffnungszeiten werden beim Bearbeiten der Anrufregel wieder vorbefüllt.
- start_time/end_time werden sauber auf HH:MM normalisiert.
- sort_order 0/1 wird korrekt Zeitraum 1/2 zugeordnet.

Wichtig:
Der funktionierende DID-Dialplan aus 1.7.6 wurde nicht verändert.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_7_7_call_rule_time_prefill_fix.sql

2. Dateien kopieren.
