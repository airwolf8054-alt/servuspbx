ServusPBX Medical 1.0.25 - DECT Basis Handset Übersicht

Neu:
- Auf der Seite DECT-Basis bearbeiten werden unten die zugeordneten Handsets angezeigt.
- Anzeige:
  IDX
  Nebenstelle
  Name
  Endpoint / IPEI
- Button "Handsets verwalten" führt zur Zuordnungsseite der Basis.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_25_dect_base_handset_overview.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
