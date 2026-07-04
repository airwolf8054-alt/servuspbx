ServusPBX Medical 1.0.13 - Security Audit

Neu:
- Audit-Log Tabelle spbx_audit_log
- Audit-Log Seite: pages/audit_log.php
- Login-Erfolg/Fehler wird protokolliert
- 2FA-Erfolg/Fehler wird protokolliert
- Sperre nach 5 Fehlversuchen für 15 Minuten
- Session Timeout nach 15 Minuten Inaktivität
- E-Mail 2FA-Code ist 5 Minuten gültig
- Button "Code erneut senden" auf 2fa.php
- Telefonbuch Import/Export/Löschen/Gesamtlöschung wird protokolliert

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_13_security_audit.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_13_security_audit.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
