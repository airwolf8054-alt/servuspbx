ServusPBX Medical 1.5.3 - A1 no from_user

Änderung:
- A1 Trunks setzen from_user künftig nicht mehr.
- from_user wird per SQL für bestehende A1-Trunks geleert.
- from_domain bleibt siptrunk.a1.net.
- contact_user bleibt Hauptnummer.

Grund:
from_user blockiert bei A1 CLIP no Screening.

Installation:
1. SQL einspielen:
   patch_servuspbx_medical_1_5_3_a1_no_from_user.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_5_3_a1_no_from_user.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
