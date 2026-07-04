ServusPBX Medical 1.0.46 - BLF Desktop Layout Fix

Korrigiert:
- D815 rechte Spalte zeigt jetzt Taste 6 bis Taste 10.
- D810 rechte Spalte zeigt fortlaufend Taste 3 bis Taste 4.
- BLF-Beschriftung wird zentral aus ps_endpoints.callerid gezogen.
- Rechts/Links verwenden dieselbe Label-Logik.
- BLF-Editor wurde stärker für 24-Zoll Desktop optimiert.
- iPad bleibt nutzbar, aber die BLF-Bearbeitung ist jetzt klar Desktop-orientiert.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_46_blf_desktop_layout_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_46_blf_desktop_layout_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
