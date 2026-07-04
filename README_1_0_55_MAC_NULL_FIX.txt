ServusPBX Medical 1.0.55 - MAC NULL Fix

Behoben:
- Fehler beim Anlegen eines SNOM DECT Handsets:
  Duplicate entry '' for key 'uniq_mac'
- DECT-Handsets haben keine MAC-Adresse und speichern jetzt NULL statt leerem String.
- SIP-User speichern ebenfalls NULL für MAC und IPEI.
- Tischtelefone speichern MAC und NULL für IPEI.
- SQL-Patch setzt bestehende leere MAC/IPEI-Werte auf NULL.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_55_mac_null_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_55_mac_null_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
