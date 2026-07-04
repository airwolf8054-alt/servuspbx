ServusPBX Medical 1.0.51 - Nebenstellen Übersicht final

Änderungen:
- Voicemail-Spalte aus der Nebenstellenübersicht entfernt.
- Horizontaler Scrollbalken entfernt.
- Tabelle nutzt wieder 100% Breite ohne feste Mindestbreite.
- Aktionsbuttons stehen nebeneinander:
  Tasten | Bearbeiten | Löschen
- Keine Logikänderung.
- Keine SQL-Änderung.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_51_extensions_overview_final.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
sudo chown -R www-data:www-data /var/www/html
