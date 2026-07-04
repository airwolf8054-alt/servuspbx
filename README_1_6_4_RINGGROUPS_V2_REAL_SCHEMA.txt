ServusPBX Medical 1.6.4 - RingGroups v2 real schema

Korrigiert:
- call_rules.php erwartete fälschlich spbx_trunks.context.
- Deine echte Tabelle spbx_trunks hat kein context Feld.
- Eingehend bleibt korrekt auf zentralem Context incoming.

Rufgruppen:
- Anrufregeln können Zieltyp Rufgruppe verwenden.
- Ziel wird als ringgroups,<nummer>,1 gespeichert.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_6_4_ringgroups_v2_real_schema.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_4_ringgroups_v2_real_schema.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
