ServusPBX Medical 1.0.5 - Telefonbuch UI Fix

Korrigiert:
- Telefonbuch optisch in Cards aufgeteilt
- Suchleiste sauber ausgerichtet
- Buttons oben rechts
- Tabelle mit sauberem Header
- Leerer Zustand als Hinweisbox
- CSV Import in eigener Card
- iPad-taugliche Anordnung

Installation:
cd /tmp
unzip servuspbx_medical_1_0_5_phonebook_ui_fix.zip
sudo rsync -av --delete login.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
