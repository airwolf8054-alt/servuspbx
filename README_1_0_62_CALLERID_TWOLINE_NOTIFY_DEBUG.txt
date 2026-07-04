ServusPBX Medical 1.0.62 - CallerID zweizeilig + Notify Debug

Änderungen:
1. Provisioning:
   user_realname und user_idle_text werden aus ps_endpoints.callerid erzeugt:
   - Vorname max. 12 Zeichen
   - Nachname max. 12 Zeichen
   - getrennt mit XML-Linebreak &#10;
   Beispiel:
   Thomas&#10;Englst

2. Asterisk Notify:
   - Helper versucht jetzt zusätzlich sudo /usr/sbin/asterisk und sudo /usr/bin/asterisk.
   - Fehler werden detailliert ins Apache/PHP error_log geschrieben.
   - In der Nebenstellenübersicht gibt es bei D815/D810 einen Notify-Testbutton.

3. Installation Wrapper/Sudoers:
   sudo install -m 0755 scripts/spbx-snom-check-cfg /usr/local/sbin/spbx-snom-check-cfg
   sudo visudo -f /etc/sudoers.d/servuspbx-snom-notify

   Inhalt:
   www-data ALL=(root) NOPASSWD: /usr/local/sbin/spbx-snom-check-cfg *
   www-data ALL=(root) NOPASSWD: /usr/sbin/asterisk -rx *
   www-data ALL=(root) NOPASSWD: /usr/bin/asterisk -rx *

   Danach:
   sudo chmod 0440 /etc/sudoers.d/servuspbx-snom-notify

Test:
   sudo -u www-data sudo -n /usr/local/sbin/spbx-snom-check-cfg 10
   sudo tail -f /var/log/apache2/error.log

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_0_62_callerid_twoline_notify_debug.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_62_callerid_twoline_notify_debug.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
