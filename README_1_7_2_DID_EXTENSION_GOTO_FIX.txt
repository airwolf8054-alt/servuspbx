ServusPBX Medical 1.7.2 - DID Extension Goto Fix

Korrigiert:
- Bei DID-Pattern _+43312423826XX wurde fälschlich nach internal_43312423826,XX,1 gesprungen.
- Generator erzeugt nun:
  Set(DIDEXT=${EXTEN:-2})
  Goto(internal_43312423826,${DIDEXT},1)

Installation:
1. SQL optional.
2. Dateien kopieren.
3. Anrufregel speichern, damit Dialplan neu erzeugt wird.
