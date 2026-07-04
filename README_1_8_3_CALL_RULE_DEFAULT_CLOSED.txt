ServusPBX Medical 1.8.3 - Anrufregeln standardmäßig geschlossen

Korrigiert:
- Neue/leere Öffnungszeiten werden mit 00:00-00:00 vorbelegt.
- Gilt für alle Tage und beide Zeiträume.
- Vorhandene gespeicherte Zeiten bleiben erhalten.
- Beim Speichern werden sichtbare 00:00-Werte übernommen.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_8_3_call_rule_default_closed.sql

2. Datei kopieren:
   sudo cp pages/call_rules.php /var/www/html/pages/call_rules.php
   sudo chown www-data:www-data /var/www/html/pages/call_rules.php
