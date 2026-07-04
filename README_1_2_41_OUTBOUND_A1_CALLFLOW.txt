ServusPBX Medical 1.2.41 - Ausgehender A1 Callflow

Wichtig:
- Eingehend bleibt auf context incoming.
- Ausgehend nutzt pro Rufnummer outgoing_<rufnummer>.

A1 Logik:
- 0... wird zu +43...
- +... bleibt unverändert
- _1XX / Notrufe bleiben im wählbaren Format
- CLIP no Screening:
  Hauptnummer + Nebenstelle, z.B. +4331242382610

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_2_41_outbound_a1_callflow.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_41_outbound_a1_callflow.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. /pages/outbound_routes.php öffnen.
4. Dialplan neu aufbauen klicken.

Prüfung:
SELECT * FROM spbx_outbound_routes;
SELECT * FROM extensions WHERE context LIKE 'outgoing_%' ORDER BY context, exten, CAST(priority AS UNSIGNED);
asterisk -rx "dialplan show outgoing_43312423826"
