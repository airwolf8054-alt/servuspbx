ServusPBX Medical 1.2.30 - Anrufregeln Trunk-Dropdown Fix

Korrigiert:
- SIP-Trunk / Context Dropdown war leer.
- Trunks werden nun robust aus spbx_trunks geladen.
- Bereits mit einer aktiven Anrufregel verknüpfte Trunks werden im Dropdown markiert.
- Feiertags-Hinweistext in der Anrufregel entfernt.
- Feiertagsseite: Feld "Bezeichnung" bekommt korrektes Formular-CSS.

Nicht geändert:
- Dialplan-Logik
- SIP-Trunk-Seite
- Provisionierung

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_30_call_rules_trunk_dropdown_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_30_call_rules_trunk_dropdown_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
