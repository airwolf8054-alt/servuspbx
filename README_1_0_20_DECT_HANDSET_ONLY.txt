ServusPBX Medical 1.0.20 - DECT Handset Only

Änderung:
- snom M400 und snom M900 werden aus der Nebenstellenmaske entfernt.
- DECT wird als "SNOM DECT Handset" geführt.
- Bei DECT wird nur die IPEI abgefragt.
- Die IPEI wird als ps_endpoints.id verwendet.
- M400/M900 Basisstationen werden später in einem separaten Bereich verwaltet.
- BLF bleibt nur für snom D815 und snom D810.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_20_dect_handset_only.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_20_dect_handset_only.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
