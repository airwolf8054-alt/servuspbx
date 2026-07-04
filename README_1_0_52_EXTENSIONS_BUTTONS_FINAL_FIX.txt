ServusPBX Medical 1.0.52 - Extensions Buttons Final Fix

Korrigiert:
- Endpoint-ID-Spalte bleibt entfernt.
- Voicemail-Spalte bleibt entfernt.
- Kein horizontaler Scrollbalken.
- Aktionsbuttons stehen wieder nebeneinander:
  Tasten | Bearbeiten | Löschen
- Höhere CSS-Spezifität überschreibt alte vertikale Grid-Regeln.
- Keine SQL-Änderung.
- Keine Logikänderung.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_52_extensions_buttons_final_fix.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
sudo chown -R www-data:www-data /var/www/html
