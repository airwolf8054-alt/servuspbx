ServusPBX Medical 1.6.3 - Anrufregeln zu Rufgruppen

Neu:
- In "Anrufregeln" kann als Offen-Ziel jetzt "Rufgruppe" gewählt werden.
- Ziel wird als ringgroups,<rufgruppe>,1 gespeichert.
- Beispiel: eingehende Hauptnummer -> Rufgruppe 500 Empfang.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_6_3_incoming_to_ringgroups.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_3_incoming_to_ringgroups.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Anrufregel öffnen und Offen-Ziel "Rufgruppe" auswählen.
