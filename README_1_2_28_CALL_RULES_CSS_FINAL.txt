ServusPBX Medical 1.2.28 - Anrufregeln CSS Finalisierung

Korrigiert:
- Formularfelder laufen nicht mehr aus dem Layout.
- Öffnungszeiten-Tabelle verwendet nun ein festes, sauberes Grid.
- Zeitraum 1 und Zeitraum 2 bleiben innerhalb der Card.
- Checkboxen behalten normale Größe.

Nicht geändert:
- Dialplan-Logik
- SIP-Trunks
- Provisionierung

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_28_call_rules_css_final.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_28_call_rules_css_final.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
