ServusPBX Medical 1.2.37 - BLF Label Zeilenumbruch

Neu:
- BLF-Labels werden im Snom-Provisioning automatisch zweizeilig ausgegeben.
- Beispiel:
  Stefan Reisenhofer
  wird im XML als:
  Stefan&#10;Reisenhofer

Wichtig:
- Die Datenbank bleibt sauber:
  key_label bleibt "Stefan Reisenhofer".
- Der Umbruch passiert nur in der Snom-Provisionierung.

Effekt:
- Auf D810/D815 werden BLF-Namen als:
  Vorname
  Nachname
  angezeigt, auch wenn der Name kurz genug wäre und sonst einzeilig bleiben würde.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_37_blf_label_linebreak.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_37_blf_label_linebreak.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Bei den Telefonen Notify auslösen oder Nebenstelle speichern.
