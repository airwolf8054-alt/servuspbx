ServusPBX Medical 1.0.11 - 2FA

Neu:
- TOTP 2-Faktor Anmeldung
- Seite: pages/security_2fa.php
- Login leitet nach Passwortprüfung auf 2fa.php weiter, wenn 2FA aktiv ist
- zentrale Admin-Funktion spbx_is_admin()
- Telefonbuch löschen nutzt jetzt spbx_is_admin()

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_11_2fa.sql

2. ZIP entpacken und kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_11_2fa.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Im Webinterface:
   2FA Sicherheit öffnen
   Schlüssel erzeugen
   Secret in Authenticator-App eintragen
   6-stelligen Code eingeben
   2FA aktivieren

Hinweis:
2FA ist ein wichtiger Sicherheitsbaustein, ersetzt aber keine vollständige NIS2-Gesamtbetrachtung.
