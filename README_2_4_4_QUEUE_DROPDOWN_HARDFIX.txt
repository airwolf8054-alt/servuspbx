ServusPBX Professional 2.4.4 - Queue Dropdown Hardfix

Korrigiert:
- Queue Dropdown in Anrufregeln lädt Queues jetzt robust.
- Erst über spbx_queue_select_options(), dann direkter SQL-Fallback.
- Wenn keine Queue gefunden wird, erscheint ein Hinweis direkt unter dem Dropdown.
- Queue Duplicate-Fix aus 2.4.3 bleibt enthalten.

Nach Installation:
1. SQL optional.
2. Browser hart neu laden.
3. Anrufregel öffnen.
