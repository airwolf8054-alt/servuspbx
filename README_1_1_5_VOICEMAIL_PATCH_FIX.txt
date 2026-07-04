ServusPBX Medical 1.1.5 - Voicemail Patch Fix

Korrigiert:
- Fehler aus 1.1.4:
  Unknown column 'mailboxes' in ps_aors
- ps_aors.mailboxes wird nicht mehr verwendet.
- Mailbox-Zuordnung erfolgt ausschließlich über ps_endpoints.mailboxes.
- Voicemail-Kontext bleibt internal.
- Voicemail bleibt Mail-only.

Installation:
1. Backup:
   mysqldump -u root -p general > /root/general_before_1_1_5.sql

2. SQL-Patch importieren:
   patch_servuspbx_medical_1_1_5_voicemail_patch_fix.sql

3. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_5_voicemail_patch_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
