ServusPBX Medical 1.5.9 - LED Policy laut SNOM Support

Neu im Provisioning für alle Desktop-Telefone, die die gemeinsame Provisioning-Engine nutzen:
- snom D810
- snom D815
- Gigaset P82x
- Gigaset P85x

Eingefügt:
<led_blink_fast perm="">early RINGING PICKUP call_center_status_exceed PhoneHasCallInStateRinging alerting_local alerting_remote</led_blink_fast>
<led_orange perm="">early AWAY INACTIVE BE_RIGHT_BACK KeyConfigActive dialog_dnd</led_orange>

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_5_9_led_policy_snom_support.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_5_9_led_policy_snom_support.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Telefone neu provisionieren:
   snom-check-cfg oder Reboot.
