ServusPBX Medical 1.2.13 - SIP-Trunk line=yes

Korrigiert:
- Asterisk-Fehler:
  An endpoint has been specified on outbound registration ... without enabling line support

Änderung:
- ps_registrations.line wird ergänzt.
- Bestehende Registrations mit endpoint bekommen line='yes'.
- Neue Trunks setzen line automatisch auf yes.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_2_13_sip_trunk_line_yes.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_13_sip_trunk_line_yes.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Reload:
   sudo asterisk -rx "module reload res_pjsip_outbound_registration.so"
