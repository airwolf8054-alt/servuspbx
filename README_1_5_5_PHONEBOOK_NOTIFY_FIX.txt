ServusPBX Medical 1.5.5 - Telefonbuch Notify Fix

Korrigiert:
- Beim Erstellen, Ändern, Löschen, Importieren und Löschen des gesamten Telefonbuchs wird jetzt automatisch snom-check-cfg an alle aktiven provisionierten Telefone gesendet.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_5_5_phonebook_notify_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_5_5_phonebook_notify_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Test:
   Telefonbuchkontakt anlegen oder ändern.
   Danach sollte jedes aktive Snom ein check-cfg Notify bekommen.
