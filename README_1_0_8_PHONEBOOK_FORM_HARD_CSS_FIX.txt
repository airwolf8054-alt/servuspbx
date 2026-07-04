ServusPBX Medical 1.0.8 - Phonebook Form Hard CSS Fix

Korrigiert:
- Alle Inputs im Kontaktformular werden jetzt hart auf 100% Breite gesetzt.
- Alte Browser-/Defaultbreiten können nicht mehr durchschlagen.
- Grid, Abstände und Fokus-Stil korrigiert.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_8_phonebook_form_hard_css_fix.zip
sudo rsync -av --delete login.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
