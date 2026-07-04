ServusPBX Medical 1.6.5 - Call Rules idempotenter Insert

Korrigiert:
- Duplicate entry 'incoming-...-11' for key 'uniq_context_exten_priority'
- Dialplan-Rebuild der Anrufregeln kann nun wiederholt laufen.
- INSERT in extensions nutzt ON DUPLICATE KEY UPDATE.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_6_5_call_rules_idempotent_insert.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_5_call_rules_idempotent_insert.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
