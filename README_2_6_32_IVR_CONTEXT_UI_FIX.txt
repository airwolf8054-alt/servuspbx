ServusPBX Professional 2.6.32 - IVR Context/UI Fix

Geändert:
- Beschreibung-Feld im IVR entfernt.
- IVR-Ziele bleiben in der Zielauswahl verfügbar.
- Jedes IVR bekommt einen eigenen Asterisk-Kontext: ivr_<Durchwahl>.
- ast_config erhält pro IVR-Kontext den Realtime-Switch.
- Interne Kontexte routen IVR-Durchwahlen direkt in den jeweiligen IVR-Kontext.

Hinweis:
SQL-Struktur bleibt unverändert. Nach dem Einspielen bitte im IVR einmal "Dialplan neu erzeugen" ausführen.
