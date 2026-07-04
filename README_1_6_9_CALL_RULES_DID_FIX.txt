ServusPBX Medical 1.6.9 - Call Rules DID Fix

Korrigiert:
- Pattern-Vorschlag übernimmt den Wert ins Rufnummer/DID Feld.
- Zieltyp "Durchwahl aus DID" speichert nicht mehr auto_internal/AUTO_DID.
- Stattdessen wird z.B. internal_43312423826,${EXTEN:-2},1 gespeichert.
- Listenansicht zeigt Ziele verständlicher an.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_6_9_call_rules_did_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_9_call_rules_did_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
