ServusPBX Professional 2.4.2 - Queue Module Refactor

Korrigiert:
- Queue-Auswahl in Anrufregeln wird jetzt über zentrale Helper-Funktion geladen.
- Keine verbrauchten mysqli_result Dropdowns mehr.
- Queue-Modul hat zentrale Funktionen:
  spbx_queue_all()
  spbx_queue_select_options()
  spbx_queue_get()
  spbx_queue_members()
  spbx_queue_pause_reasons()

Zusätzlich:
- Begrüßungsansage und Notfallansage bleiben getrennt.
- Queue-Rebuild nutzt zentrale Datenfunktionen.

Nach Installation:
1. SQL einspielen.
2. Queues -> Neu erzeugen.
3. Anrufregeln neu öffnen, Queue 700 sollte auswählbar sein.
