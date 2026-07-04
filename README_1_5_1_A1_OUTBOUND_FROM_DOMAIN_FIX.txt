ServusPBX Medical 1.5.1 - A1 Outbound From-Domain Fix

Problem:
Ausgehender INVITE an A1 hatte:
From: <sip:+4331242382610@192.168.0.10>
Contact: <sip:+4331242382610@192.168.0.10:5060>
P-Asserted-Identity: <sip:+4331242382610@192.168.0.10>

A1 lehnt das mit 403 Forbidden ab.

Fix:
Beim A1-Trunk werden schemaabhängig gesetzt:
- ps_endpoints.from_user = Hauptnummer
- ps_endpoints.from_domain = siptrunk.a1.net
- ps_endpoints.contact_user = Hauptnummer

CALLERID(num) kann weiter für CLIP no Screening verwendet werden.

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_5_1_a1_outbound_from_domain_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_5_1_a1_outbound_from_domain_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Reload:
   sudo /usr/sbin/asterisk -rx "pjsip reload"

4. Prüfen:
   asterisk -rx "pjsip show endpoint trunk-a1-43312423826"
