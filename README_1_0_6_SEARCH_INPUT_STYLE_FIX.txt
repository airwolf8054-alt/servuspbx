ServusPBX Medical 1.0.6 - Search Input Style Fix

Korrigiert:
- Suchfeld im Telefonbuch hat jetzt denselben ServusPBX-Stil wie alle anderen Eingabefelder.
- Fokus-Rahmen und Placeholder angepasst.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_6_search_input_style_fix.zip
sudo rsync -av --delete login.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
