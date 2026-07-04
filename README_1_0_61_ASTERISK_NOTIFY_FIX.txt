ServusPBX Medical 1.0.61 - Asterisk Notify Fix

Behoben:
- Der Notify-Befehl wurde bisher still per @exec im Hintergrund ausgeführt.
- Fehler waren dadurch nicht sichtbar.
- Notify läuft jetzt zentral über inc/asterisk_notify.php.
- Nach Speichern einer Nebenstelle wird ausgeführt:
  pjsip send notify snom-check-cfg endpoint <endpoint-id>
- Nach Speichern/Zurücksetzen der BLF-Tasten ebenfalls.

Neu:
- scripts/spbx-snom-check-cfg
  Sicherer Wrapper für:
  /usr/sbin/asterisk -rx "pjsip send notify snom-check-cfg endpoint <endpoint>"

Installation:
1. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_61_asterisk_notify_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

2. Wrapper installieren:
   sudo install -m 0755 scripts/spbx-snom-check-cfg /usr/local/sbin/spbx-snom-check-cfg

3. Test als root:
   sudo /usr/local/sbin/spbx-snom-check-cfg 10

4. Wenn PHP/Apache als www-data läuft, sudoers ergänzen:
   sudo visudo -f /etc/sudoers.d/servuspbx-snom-notify

   Inhalt:
   www-data ALL=(root) NOPASSWD: /usr/local/sbin/spbx-snom-check-cfg *

   Danach:
   sudo chmod 0440 /etc/sudoers.d/servuspbx-snom-notify

5. Test als www-data:
   sudo -u www-data sudo -n /usr/local/sbin/spbx-snom-check-cfg 10

Logs:
- Fehler/Erfolg stehen im Apache/PHP error_log:
  sudo tail -f /var/log/apache2/error.log

SQL:
- patch_servuspbx_medical_1_0_61_asterisk_notify_fix.sql ist optional.
