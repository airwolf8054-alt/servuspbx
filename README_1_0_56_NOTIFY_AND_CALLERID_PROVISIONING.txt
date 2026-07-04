ServusPBX Medical 1.0.56 - Notify + CallerID Provisioning

Änderungen:
1. Nach Speichern von Endpoint/Nebenstelle wird ausgeführt:
   asterisk -rx "pjsip send notify snom-check-cfg endpoint <endpoint-id>"

2. Nach Speichern oder Zurücksetzen der BLF-Tasten wird derselbe Notify-Befehl für das Gerät ausgelöst.

3. Provisioning:
   user_realname idx=1 kommt direkt aus ps_endpoints.callerid.
   user_idle_text idx=1 kommt direkt aus ps_endpoints.callerid.

Beispiel:
<user_realname idx="1" perm="R">"Thomas Englst" &lt;52&gt;</user_realname>
<user_idle_text idx="1" perm="R">"Thomas Englst" &lt;52&gt;</user_idle_text>

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_56_notify_and_callerid_provisioning.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_56_notify_and_callerid_provisioning.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

Hinweis:
Der Apache/PHP-Benutzer muss asterisk -rx ausführen dürfen.
Falls nötig sudoers ergänzen oder www-data in passende Gruppe aufnehmen.
