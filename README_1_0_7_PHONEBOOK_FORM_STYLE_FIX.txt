ServusPBX Medical 1.0.7 - Phonebook Form Style Fix

Korrigiert:
- Kontaktformular verwendet jetzt durchgehend ServusPBX Input-Style.
- Felder sind nicht mehr zu schmal.
- Fokus-Rahmen, Padding und Radius sind einheitlich.
- iPad-Layout bleibt einspaltig und touchfreundlich.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_7_phonebook_form_style_fix.zip
sudo rsync -av --delete login.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
