ServusPBX Medical 1.0.63 - CallerID Split + context_key_text off

Änderungen:
1. Beim Speichern einer Nebenstelle:
   - CallerID wird aus dem Anzeigenamen gebildet.
   - Vorname max. 12 Zeichen.
   - Nachname max. 12 Zeichen.
   - Speicherung in ps_endpoints.callerid als: Vorname Nachname

2. Provisioning:
   - Wenn Vorname und Nachname vorhanden sind, wird ein XML-Linebreak erzwungen:
     Vorname&#10;Nachname
   - Dadurch zeigt snom D815/D810 den Vornamen oben und Nachnamen unten.
   - Wenn nur ein Teil vorhanden ist, wird nur eine Zeile ausgegeben.

3. Provisioning:
   - <context_key_text perm='R'>off</context_key_text>

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_0_63_callerid_split_contextkey_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_63_callerid_split_contextkey_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
