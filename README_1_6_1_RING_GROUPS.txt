ServusPBX Medical 1.6.1 - Rufgruppen

Neu:
- Menüpunkt Rufgruppen
- Gruppenrufnummer, z. B. 500
- Mitglieder aus vorhandenen Nebenstellen
- Strategien:
  - Alle gleichzeitig
  - Nacheinander
  - Zufällig
- Klingelzeit
- Timeout: Auflegen oder zu Nebenstelle
- Dialplan-Generator

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_6_1_ring_groups.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_1_ring_groups.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Menü Rufgruppen öffnen.
4. Rufgruppe anlegen, z. B. 500.
5. Test:
   asterisk -rx "dialplan show ringgroups"
   asterisk -rx "dialplan show internal_43312423826"
