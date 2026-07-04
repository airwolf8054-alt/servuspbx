ServusPBX Medical 1.4.5 - Outbound/Internal + Identify Fix

Fixes:
1. internal_43312423826 bekommt:
   _XXXXX. -> outgoing_43312423826,${EXTEN},1

2. outgoing_43312423826 bekommt:
   _X. -> A1 Outbound Dialplan

3. Leerer Identify-Eintrag wird gelöscht:
   ps_endpoint_id_ips trunk-a1-43312423826

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_4_5_outbound_internal_identify_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_4_5_outbound_internal_identify_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Reload:
   sudo /usr/sbin/asterisk -rx "dialplan reload"
   sudo /usr/sbin/asterisk -rx "pjsip reload"

Prüfung:
asterisk -rx "dialplan show internal_43312423826"
asterisk -rx "dialplan show outgoing_43312423826"
asterisk -rx "pjsip show identifies"
