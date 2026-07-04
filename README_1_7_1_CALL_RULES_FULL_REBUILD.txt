ServusPBX Medical 1.7.1 - Call Rules Full Rebuild

Korrigiert:
- Anrufregeln-Seite komplett neu aufgebaut.
- Kein kaputter Mix aus alten Ziel-Feldern mehr.
- DID Pattern Dropdown schreibt ins DID-Feld.
- Zieltypen:
  - Nebenstelle
  - Durchwahl aus DID
  - Rufgruppe
  - Benutzerdefiniert
- Generator erzeugt incoming sauber über Realtime.

Installation:
1. SQL optional.
2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_7_1_call_rules_full_rebuild.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
