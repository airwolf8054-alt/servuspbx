ServusPBX Medical 1.1.6 - Voicemail Context Based

Änderungen:
- ps_endpoints.mailboxes wird nicht mehr fest auf @internal gesetzt.
- Mailbox-Format ist jetzt:
  <Durchwahl>@<ps_endpoints.context>

Beispiel:
10@internal
10@pflege
10@verwaltung

Damit ist ServusPBX vorbereitet für:
- größere Praxen
- Seniorenheime
- Primärversorgungszentren
- mehrere Abteilungen/Bereiche

Regel:
- ps_endpoints ist führend.
- ps_endpoints.context bestimmt den Mailbox-Kontext.
- voicemail.context = ps_endpoints.context.
- voicemail.mailbox = ps_endpoints.extension.

Voicemail bleibt:
- Mail-only
- WAV-Anhang
- lokale Nachricht nach Mailversand löschen
- keine PIN-Abfrage in der GUI

Installation:
1. Backup:
   mysqldump -u root -p general > /root/general_before_1_1_6.sql

2. SQL-Patch importieren:
   patch_servuspbx_medical_1_1_6_voicemail_context_based.sql

3. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_6_voicemail_context_based.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

4. Asterisk reload:
   sudo asterisk -rx "module reload app_voicemail.so"
   sudo asterisk -rx "module reload res_pjsip.so"
   sudo asterisk -rx "dialplan reload"

Prüfung:
SELECT id, extension, context, mailboxes FROM ps_endpoints;
SELECT context, mailbox, fullname, email, attach, deletevoicemail FROM voicemail;
