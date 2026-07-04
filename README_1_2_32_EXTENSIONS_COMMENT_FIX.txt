ServusPBX Medical 1.2.32 - extensions.comment Schema-Fix

Korrigiert:
- PHP Fatal error:
  Unknown column 'comment' in 'INSERT INTO'
- Ursache:
  Die aktuelle extensions-Tabelle hat keine Spalte comment.
- Fix:
  inc/call_rules.php prüft jetzt automatisch:
  SHOW COLUMNS FROM extensions LIKE 'comment'
  und schreibt je nach Schema mit oder ohne comment-Spalte.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_32_extensions_comment_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_32_extensions_comment_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
