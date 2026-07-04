ServusPBX Medical 1.2.25 - Eingehender Callflow

Neu:
- GUI: /pages/inbound_routes.php
- mehrere Öffnungszeit-Fenster pro Wochentag
- Feiertagsschaltung pro Route aktiv/deaktivierbar
- globale Feiertage in Asterisk AstDB bankholiday/YYYY-MM-DD
- manuelle Ansage 1 / 2
- geschlossen: Ansage+Auflegen oder Ansage+Hauptbox+Auflegen
- Dialplan wird in extensions erzeugt und dialplan reload ausgeführt

Installation:
1. SQL importieren: patch_servuspbx_medical_1_2_25_inbound_callflow.sql
2. Dateien kopieren wie bisher per rsync.
3. Öffnen: /pages/inbound_routes.php
