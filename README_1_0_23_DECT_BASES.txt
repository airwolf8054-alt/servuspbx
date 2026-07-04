ServusPBX Medical 1.0.23 - DECT Basisstationen

Neu:
- Menüpunkt DECT
- DECT-Basisstationen:
  snom M400
  snom M900
- MAC-Adresse der Basis als Pflichtfeld
- M400 Limit: IDX 1 bis 10
- M900 Limit: IDX 1 bis 1000
- Handsets werden aus vorhandenen SNOM DECT Handset Endpoints gewählt
- IDX wird automatisch vergeben
- Beim Löschen einer Zuordnung wird die IDX wieder frei
- Beim nächsten Hinzufügen wird die niedrigste freie IDX zuerst verwendet

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_23_dect_bases.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_23_dect_bases.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
