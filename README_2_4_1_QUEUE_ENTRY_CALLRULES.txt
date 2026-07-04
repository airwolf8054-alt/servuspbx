ServusPBX Professional 2.4.1 - Queue Entry + Call Rules

Korrigiert/Ergänzt:
- Queue ist in den Anrufregeln als Offen-Ziel auswählbar.
- Queue-Eingang hat jetzt zwei getrennte Ansagen:
  1. Begrüßungsansage / generelle Service-Ansage
  2. optionale Notfall-/Störungsansage
- Beide Ansagen haben TTS-Felder vorbereitet.
- *45/*46 werden beim Queue-Rebuild explizit in die Realtime-Extensions geschrieben.

Nach Installation:
1. SQL einspielen.
2. In Queues einmal "Neu erzeugen" klicken.
3. Prüfen:
   dialplan show *45@internal_<hauptnummer>
   oder direkt *45 wählen.
