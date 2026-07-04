ServusPBX Medical 1.7.6 - DID Pattern Direct Branch

Korrigiert:
- Keine Sprünge mehr auf incoming,_+433...XX.
- Dadurch bleibt EXTEN die echte gewählte Nummer.
- Durchwahl wird direkt im Pattern erzeugt:
  Set(DURCHWAHL=${EXTEN:12})
  Goto(internal_43312423826,${DURCHWAHL},1)

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_7_6_did_pattern_direct_branch.sql

2. Dateien kopieren.

3. Anrufregel speichern, damit der Dialplan neu erzeugt wird.
