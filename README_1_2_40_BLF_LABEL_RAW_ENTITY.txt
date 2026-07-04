ServusPBX Medical 1.2.40 - BLF Label rohe &#10; Entity

Änderung:
- BLF-Labels verwenden wieder die bewährte alte Snom-Logik:
  Leerzeichen -> &#10;

Beispiel:
Stefan Reisenhofer wird im Provisioning als:
Stefan&#10;Reisenhofer

Wichtig:
- Normale XML-Zeichen werden weiterhin escaped.
- Nur die absichtlich erzeugte Entity &#10; wird roh ausgegeben.
- Dadurch sieht das Telefon nicht mehr den Text '&#10;', sondern nutzt ihn als Zeilenumbruch.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_40_blf_label_raw_entity.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_40_blf_label_raw_entity.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Notify auslösen.
