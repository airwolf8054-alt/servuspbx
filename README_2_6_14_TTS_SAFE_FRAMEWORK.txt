ServusPBX Professional 2.6.14 - TTS Safe Framework

Ziel: Trottelsichere Piper-TTS-Bedienung.

Änderungen:
- System -> Text-to-Speech überarbeitet.
- Keine separate Ansagen-Seite.
- Bestehende TTS-Textfelder in Eingehende Regeln und Queues bleiben der zentrale Eingabeort.
- Beim Speichern wird aus dem vorhandenen Text automatisch eine MP3 erzeugt.
- Zentrale Stimme für alle TTS-Felder.
- Piper/ffmpeg Statusanzeige mit einfachem Installationsbutton.
- Pfade werden im normalen Betrieb nicht mehr als Konfigurationsfelder angezeigt.
- Installationsscript scripts/install_piper_tts.sh ergänzt.

Hinweis:
Der Button benötigt sudo-Rechte für das Installationsscript:
www-data ALL=(root) NOPASSWD: /var/www/html/scripts/install_piper_tts.sh
