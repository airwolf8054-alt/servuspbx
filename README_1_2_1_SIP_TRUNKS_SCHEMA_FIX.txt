ServusPBX Medical 1.2.1 - SIP-Trunks Schema Fix

Korrigiert:
- spbx_trunk_providers verwendet client_domain, nicht domain.
- spbx_trunks.id ist AUTO_INCREMENT int.
- spbx_trunks.endpoint_id ist die Asterisk/Realtime-ID.
- Provider enum wird um easybell und magenta erweitert.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_2_1_sip_trunks_schema_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_1_sip_trunks_schema_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Asterisk reload:
   sudo asterisk -rx "module reload res_pjsip.so"
   sudo asterisk -rx "module reload res_pjsip_outbound_registration.so"
   sudo asterisk -rx "dialplan reload"
