ServusPBX Medical 1.0.66 - D810 BLF + CallerID Fix

Korrigiert:
- BLF-Ausgabe ist jetzt bei D815 und D810 identisch:
  <fkey ...>blf 12</fkey>
- Kein sip:-Prefix mehr:
  nicht mehr: blf sip:12@10.43.4.244
- snomD810.php ist wieder ein schlanker Wrapper auf dieselbe Provisioning Engine wie D815.
- Dadurch zieht D810 auch dieselbe CallerID-/Displaylogik wie D815.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_0_66_d810_blf_callerid_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_66_d810_blf_callerid_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
