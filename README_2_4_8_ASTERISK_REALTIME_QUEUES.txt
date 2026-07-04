ServusPBX Professional 2.4.8 - Asterisk Realtime Queues

Korrigiert:
- Asterisk app_queue sucht die Realtime-Tabelle "queues".
- Unsere Verwaltungstabelle heißt spbx_queues.
- Der Rebuild schreibt jetzt zusätzlich:
  - queues
  - queue_members

Nach Installation:
1. SQL einspielen.
2. Asterisk:
   asterisk -rx "module reload app_queue.so"
   asterisk -rx "dialplan reload"
3. Prüfen:
   SELECT * FROM queues;
   SELECT * FROM queue_members;
   queue show spbxq_820
