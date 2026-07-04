ServusPBX Medical 1.0.4 - Telefonbuch

Neu:
- Telefonbuch-Liste
- Suche
- Kontakt anlegen
- Kontakt bearbeiten
- Kontakt löschen
- CSV Export
- CSV Import

CSV Format:
company;lastname;firstname;number;mobile;email

Installation:
cd /tmp
unzip servuspbx_medical_1_0_4_phonebook.zip
sudo rsync -av --delete login.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
