ServusPBX Medical 1.0.3 - Dashboard + iPad Navigation

Neu:
- Dashboard mit Tageswerten:
  eingehende Anrufe
  ausgehende Anrufe
  verpasste Anrufe
  registrierte Nebenstellen
- Telefone und Nebenstellen zusammengeführt:
  Navigation enthält keine eigene Telefonseite mehr.
- Sidebar mit Icons.
- iPad-/Tablet-Optimierung:
  größere Touchflächen
  responsive Navigation
  2-spaltige Kacheln auf iPad
- phones.php leitet auf extensions.php weiter.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_3_dashboard_ipad_nav.zip
sudo rsync -av --delete login.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
