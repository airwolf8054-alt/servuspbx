ServusPBX Medical 1.8.2 - Öffnungszeiten Defaults

Basis:
- Diese Version wurde direkt aus der hochgeladenen echten /var/www/html/pages/call_rules.php erstellt.

Korrigiert:
- Wenn keine gespeicherten Zeiten vorhanden sind, wird Mo-Fr Zeitraum 1 mit 06:00-23:59 vorbelegt.
- Samstag/Sonntag bleiben leer.
- Bereits gespeicherte Zeiten haben Vorrang.
- Beim Speichern werden die sichtbaren Defaultzeiten normal gespeichert.

Wichtig:
- Kein Eingriff in den funktionierenden DID-Wählplan.
- Keine Änderung an Ringgruppen.
- Keine Änderung an inc/call_rules.php.

Installation:
1. Optional SQL einspielen:
   patch_servuspbx_medical_1_8_2_call_rule_time_defaults_real_file.sql

2. Datei kopieren:
   sudo cp pages/call_rules.php /var/www/html/pages/call_rules.php
   sudo chown www-data:www-data /var/www/html/pages/call_rules.php
