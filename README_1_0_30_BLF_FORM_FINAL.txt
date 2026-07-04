ServusPBX Medical 1.0.30 - BLF Formular Final

Änderung:
- BLF-Editor wieder als optische Tasten-Karten.
- Nur noch vier Typen:
  Leer
  Nebenstelle
  Rufnummer
  Rufumleitung
- Nebenstelle per Dropdown.
- Rufnummer hat getrennte Felder:
  Rufnummer
  Beschriftung
- D815: 10 physische Tasten x 4 Ebenen.
- D810: 4 physische Tasten x 4 Ebenen.
- Provisioning bleibt über spbx_device_keys.

Wichtig:
Der SQL-Patch erstellt spbx_device_keys neu.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_30_blf_form_final.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_30_blf_form_final.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
