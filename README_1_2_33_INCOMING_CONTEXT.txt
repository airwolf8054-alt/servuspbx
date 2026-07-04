ServusPBX Medical 1.2.33 - Zentraler Incoming Context

Neu:
- Alle eingehenden Trunks verwenden wieder den bewährten Context: incoming.
- Anrufregeln schreiben in context incoming.
- Pro DID werden Aliase erzeugt: Original, ohne +, mit +.
- Alte from_<rufnummer>-Dialplan-Einträge werden per SQL entfernt.

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_2_33_incoming_context.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_33_incoming_context.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Reload:
   sudo /usr/sbin/asterisk -rx "pjsip reload"

4. Anrufregel speichern.

Prüfung:
SELECT id, context FROM ps_endpoints WHERE device_type='trunk';
SELECT * FROM extensions WHERE context='incoming' ORDER BY exten, CAST(priority AS UNSIGNED);
asterisk -rx "dialplan show +43312423826@incoming"
