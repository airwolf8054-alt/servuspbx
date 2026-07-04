ServusPBX Professional 2.4.3 - Queue Member Duplicate Fix

Korrigiert:
- 500er Fehler beim Speichern einer Queue:
  Duplicate entry '<queue>-<endpoint>' for key uniq_queue_member
- Ursache war dieselbe Nebenstelle gleichzeitig als fixer und optionaler Agent.
- Neue Logik:
  - fixe Agenten gewinnen gegenüber optional
  - doppelte Auswahl erzeugt keinen Fatal Error mehr
  - Speichern läuft durch

Nach Installation:
1. SQL optional.
2. Queue neu speichern.
3. Queues -> Neu erzeugen.
4. Anrufregeln neu öffnen.
