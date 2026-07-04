ServusPBX Medical 1.6.0 - Provisioning perm=R

Änderungen:
- LED-Support-Vorgaben werden mit perm="R" gesetzt:
  <led_blink_fast perm="R">early RINGING PICKUP call_center_status_exceed PhoneHasCallInStateRinging alerting_local alerting_remote</led_blink_fast>
  <led_orange perm="R">early AWAY INACTIVE BE_RIGHT_BACK KeyConfigActive dialog_dnd</led_orange>

- Allgemeine Provisioning-Settings werden auf perm="R" normalisiert.
- Klingeltöne/Ringer-Settings bleiben bewusst unverändert.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_6_0_provision_perm_r.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_0_provision_perm_r.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Telefon neu provisionieren:
   snom-check-cfg oder Reboot.
