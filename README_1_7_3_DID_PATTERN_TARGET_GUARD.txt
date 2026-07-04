ServusPBX Medical 1.7.3 - DID Pattern Target Guard

Korrigiert:
- Bei _+43312423826XX wurde noch nach internal_43312423826,XX,1 gesprungen.
- Der Generator erkennt jetzt falsch gespeicherte XX/XXX/XXXX Ziele automatisch.
- Er erzeugt:
  Set(DIDEXT=${EXTEN:-2})
  Goto(internal_43312423826,${DIDEXT},1)

Installation:
1. SQL einspielen, damit falsch gespeicherte Regeln bereinigt werden:
   patch_servuspbx_medical_1_7_3_did_pattern_target_guard.sql

2. Dateien kopieren.

3. Anrufregel einmal speichern oder Dialplan neu erzeugen.
