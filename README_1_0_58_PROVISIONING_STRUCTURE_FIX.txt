ServusPBX Medical 1.0.58 - Provisioning Struktur Fix

Korrigiert:
- <uploads> wird jetzt NACH </phone-settings> ausgegeben.
- Reihenfolge:
  <settings>
    <phone-settings e='2'>
      ...
      fkey / fkey_label
    </phone-settings>
    <uploads>
      ...
    </uploads>
  </settings>

- user_realname idx=1 verwendet exakt ps_endpoints.callerid.
- user_idle_text idx=1 verwendet exakt ps_endpoints.callerid.
- Kein Zusammenbauen aus display_name + extension mehr.

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_58_provisioning_structure_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_58_provisioning_structure_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
