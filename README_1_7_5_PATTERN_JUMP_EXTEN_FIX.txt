ServusPBX Medical 1.7.5 - Pattern Jump EXTEN Fix

Korrigiert:
- Bei _+43312423826XX wurde nach incoming,_+43312423826XX,12 gesprungen.
- Dadurch wurde ${EXTEN} später zum Pattern und ${EXTEN:12} ergab 6XX.
- Jetzt wird bei Pattern-Regeln nach incoming,${EXTEN},12 und incoming,${EXTEN},90 gesprungen.

Erwartetes Ergebnis:
Set(DIDEXT=${EXTEN:12})
Goto(internal_43312423826,${DIDEXT},1)

Installation:
1. SQL optional.
2. Dateien kopieren.
3. Anrufregel speichern, damit der Dialplan neu erzeugt wird.
