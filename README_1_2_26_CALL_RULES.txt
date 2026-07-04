ServusPBX Medical 1.2.26 - Anrufregeln

Wichtig:
- Der fehlerhafte 1.2.25 Inbound-Patch wird nicht weitergeführt.
- SIP-Trunks bleibt unverändert.
- Neuer Menüpunkt: Anrufregeln
- Neue Seite: /pages/call_rules.php

Funktionen:
- mehrere Rufnummern / Anrufregeln
- Bearbeiten / Löschen
- Öffnungszeiten mit mehreren Zeitfenstern pro Wochentag
- Feiertagsschaltung pro Rufnummer aktivierbar/deaktivierbar
- globale Feiertage in Asterisk AstDB bankholiday/YYYY-MM-DD
- manuelle Ansage 1 / 2
- geschlossen: Ansage + Auflegen oder Ansage + Hauptbox + Auflegen
- Dialplan-Generator schreibt in Tabelle extensions
- dialplan reload nach Speichern

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_2_26_call_rules.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_26_call_rules.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Voraussetzung:
   www-data ALL=(ALL) NOPASSWD: /usr/sbin/asterisk

4. Öffnen:
   http://<pbx>/pages/call_rules.php

Prüfung:
- SIP-Trunks Menüpunkt muss weiterhin auf /pages/trunk_a1.php zeigen.
- Anrufregeln Menüpunkt muss auf /pages/call_rules.php zeigen.
