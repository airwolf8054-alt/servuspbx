ServusPBX Medical 1.9.0 - Outbound Normalize Single Priority

Korrigiert:
- Duplicate entry outgoing_...-_X.-10.
- 00436641549314 -> +436641549314
- 06641549314 -> +436641549314
- +436641549314 bleibt unverändert.

Änderung:
- Keine zusätzliche ExecIf-Priority mehr.
- Zielnummer wird in einer einzigen Set-Priority normalisiert.
- INSERT in extensions ist zusätzlich idempotent.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_9_0_outbound_normalize_single_priority.sql

2. Dateien kopieren.

3. Ausgehende Route einmal speichern.
