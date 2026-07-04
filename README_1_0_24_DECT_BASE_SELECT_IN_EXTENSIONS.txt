ServusPBX Medical 1.0.24 - DECT Basis in Nebenstellen

Neu:
- Bei Geräteart DECT-Schnurlos / SNOM DECT Handset erscheint in der Nebenstellenmaske ein Feld "DECT-Basisstation".
- Beim Speichern wird das Handset automatisch der gewählten Basis zugeordnet.
- Die IDX wird automatisch vergeben.
- Es wird immer die niedrigste freie IDX verwendet.
- Wird die Basis gewechselt, wird die alte Zuordnung entfernt und auf der neuen Basis eine neue freie IDX vergeben.
- Bei "Keine Basis zuordnen" wird eine bestehende Zuordnung entfernt.

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_24_dect_base_select_in_extensions.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_24_dect_base_select_in_extensions.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
