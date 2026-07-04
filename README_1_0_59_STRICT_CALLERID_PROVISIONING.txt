ServusPBX Medical 1.0.59 - Strict CallerID Provisioning

Korrigiert:
- user_realname idx=1 kommt ausschließlich aus ps_endpoints.callerid.
- user_idle_text idx=1 kommt ausschließlich aus ps_endpoints.callerid.
- Kein Zusammenbauen aus Anzeigename + Durchwahl.
- uploads bleibt nach </phone-settings>.

Wichtig:
Wenn im XML noch "Name <Durchwahl>" erscheint, steht dieser Wert so in ps_endpoints.callerid.
Dann bitte dort korrigieren, nicht im Provisioning.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_0_59_strict_callerid_provisioning.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_59_strict_callerid_provisioning.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
