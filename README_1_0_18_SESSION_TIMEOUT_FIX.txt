ServusPBX Medical 1.0.18 - Session Timeout Fix

Behoben:
- Memory Exhausted in inc/auth.php durch rekursiven Session-Timeout.
- Ursache:
  spbx_session_start() rief bei Timeout spbx_logout() auf.
  spbx_logout() rief wiederum spbx_session_start() auf.
- Korrektur:
  Timeout beendet die Session direkt ohne rekursiven Funktionsaufruf.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_18_session_timeout_fix.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
