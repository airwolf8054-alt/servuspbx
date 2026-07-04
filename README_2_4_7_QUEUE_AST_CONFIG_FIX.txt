ServusPBX Professional 2.4.7 - Queue ast_config Fix

Korrigiert:
- queue-services wurde in Realtime extensions erzeugt,
  aber der Context war in ast_config nicht zuverlässig vorhanden.
- Es wird jetzt explizit angelegt:
  [queue-services]
  switch => Realtime/@extensions

Nach Installation:
1. SQL einspielen.
2. Asterisk laden:
   asterisk -rx "module reload pbx_config.so"
   asterisk -rx "dialplan reload"
3. Prüfen:
   dialplan show queue-services
   dialplan show 820@queue-services
