ServusPBX Medical 1.0.50 - Nebenstellen Buttons nebeneinander

Änderung:
- Aktionsbuttons in der Nebenstellenübersicht sind wieder nebeneinander:
  Tasten | Bearbeiten | Löschen
- Alle drei Buttons gleich breit und gleich hoch.
- Aktionsspalte auf ca. 330 px verbreitert.
- Keine Logikänderung.
- Keine SQL-Änderung.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_50_extension_buttons_inline.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
sudo chown -R www-data:www-data /var/www/html
