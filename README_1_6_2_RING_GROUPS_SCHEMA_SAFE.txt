ServusPBX Medical 1.6.2 - Rufgruppen Schema-Safe

Korrigiert:
- Unknown column 'group_number' in ORDER BY
- bestehende alte spbx_ring_groups Tabellen werden automatisch erweitert
- strategy/timeout_action werden auf VARCHAR geändert

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_6_2_ring_groups_schema_safe.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_2_ring_groups_schema_safe.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Rufgruppen öffnen.
