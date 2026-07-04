ServusPBX Medical 1.1.4 - Voicemail Mail-Only + internal

Änderungen:
- Kein default Context mehr.
- Voicemail-Kontext ist internal.
- ps_endpoints.mailboxes wird auf <Durchwahl>@internal gesetzt.
- Voicemail nur per E-Mail-Zustellung.
- Keine PIN-Abfrage mehr in der Nebenstellenmaske.
- E-Mail-Adresse pro Nebenstelle bleibt optional.
- Haupt-Voicemail-Adresse als zentrales Setting vorbereitet.
- *97/*98 VoicemailMain werden aus dem internal Dialplan entfernt.

Wichtig:
Asterisk voicemail.conf/extconfig.conf muss Realtime-Voicemail verwenden, z.B.:
voicemail => odbc,general,voicemail

Installation:
1. Backup:
   mysqldump -u root -p general > /root/general_before_1_1_4.sql

2. SQL-Patch importieren:
   patch_servuspbx_medical_1_1_4_voicemail_mailonly_internal.sql

3. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_4_voicemail_mailonly_internal.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

4. Asterisk reload:
   sudo asterisk -rx "module reload app_voicemail.so"
   sudo asterisk -rx "module reload res_pjsip.so"
   sudo asterisk -rx "dialplan reload"

Prüfung:
SELECT id, extension, mailboxes, context FROM ps_endpoints;
SELECT context, mailbox, fullname, email, attach, deletevoicemail FROM voicemail;
