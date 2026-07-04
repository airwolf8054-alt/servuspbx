ServusPBX Medical 1.2.19 - Inline Snom tbook Provisioning

Änderung:
- Das Snom Telefonbuch wird wieder direkt im Provisioning ausgegeben.
- Position: nach </phone-settings> und vor <uploads>.
- Format:
  <tbook complete="true" e="2">
    <item context="active" type="office">
      <first_name>...</first_name>
      <last_name>...</last_name>
      <number>...</number>
    </item>
  </tbook>

Quelle:
1. directory, wenn Einträge vorhanden sind
2. Fallback ps_endpoints, wenn directory leer ist

tbook.php bleibt zusätzlich zum Testen erhalten:
curl http://10.43.4.244/provision/tbook.php

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_19_inline_tbook_provisioning.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_19_inline_tbook_provisioning.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
