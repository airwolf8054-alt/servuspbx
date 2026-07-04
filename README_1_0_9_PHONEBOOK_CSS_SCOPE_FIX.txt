ServusPBX Medical 1.0.9 - Phonebook CSS Scope Fix

Korrigiert:
- Kontaktformular-CSS wirkt nur noch auf das Kontaktformular.
- CSV-Import/File-Input wird nicht mehr durch Formularregeln verändert.
- Suchfeld bleibt sauber gestylt.
- Listenansicht und Importbereich bleiben optisch stabil.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_9_phonebook_css_scope_fix.zip
sudo rsync -av --delete login.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
