ServusPBX Medical 1.0.44 - Snom Key Layout Fix

Korrigiert:
- D815 Tastenlayout im Editor entspricht jetzt der Snom-Anordnung:
  links:  fkey0 bis fkey4
  rechts: fkey5 bis fkey9
- D810:
  links:  fkey0 bis fkey1
  rechts: fkey2 bis fkey3
- Die gespeicherten fkey_idx bleiben unverändert.
- Nur die optische Anordnung im Editor wurde korrigiert.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_44_snom_key_layout_fix.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
sudo chown -R www-data:www-data /var/www/html
