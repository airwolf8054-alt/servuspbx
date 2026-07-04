ServusPBX Medical 1.0.12 - 2FA per E-Mail-Code

Änderung:
- TOTP/Authenticator wurde durch E-Mail-Code ersetzt.
- Passwort korrekt -> 6-stelliger Code per Mail.
- Code ist 10 Minuten gültig.
- Seite: 2FA Sicherheit -> E-Mail-Adresse + Aktivierung.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_12_mail_2fa.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_12_mail_2fa.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

Hinweis:
Damit E-Mail-Versand funktioniert, muss PHP mail() bzw. ein lokaler Mailer/Postfix korrekt eingerichtet sein.
