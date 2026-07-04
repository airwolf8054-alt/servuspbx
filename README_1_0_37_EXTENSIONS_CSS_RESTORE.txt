ServusPBX Medical 1.0.37 - Extensions CSS Restore

Korrigiert:
- Nebenstellen-Seite hat wieder ServusPBX-Card-Layout.
- Nebenstellen-Formular ist wieder zweispaltig und gestylt.
- Tabelle, Badges und Aktionsbuttons sind wieder sauber formatiert.
- Keine Änderung an der Gerätetyp-Logik aus 1.0.36.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_37_extensions_css_restore.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
sudo chown -R www-data:www-data /var/www/html
