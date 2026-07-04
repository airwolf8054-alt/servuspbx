ServusPBX Medical 1.0.34 - Nebenstellen Button Fix

Korrigiert:
- Aktionsbuttons in der Nebenstellenliste sind jetzt sauber in einer eigenen Spalte.
- Buttons stehen untereinander mit gleicher Breite.
- Kein Verschieben mehr in andere Spalten.
- Keine globale CSS-Regel mehr, die andere Tabellen beeinflusst.
- Tasten/Bearbeiten/Löschen sind stabil auf Desktop und iPad.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_34_extension_buttons_fix.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
sudo chown -R www-data:www-data /var/www/html
