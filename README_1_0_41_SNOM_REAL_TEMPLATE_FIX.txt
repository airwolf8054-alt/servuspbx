ServusPBX Medical 1.0.41 - Snom Real Template Fix

Korrigiert:
- BLF-Ausgabe war falsch/minimal:
  vorher: blf 10
  jetzt:   blf sip:10@PBX
- Funktionstasten verwenden perm="R" wie in der alten Vorlage.
- Snom-Parameter sind wieder näher an der produktiven Vorlage:
  user_active idx=1
  user_realname idx=1
  user_name idx=1
  user_host idx=1
  user_outbound idx=1
  user_pass idx=1
  user_mailbox idx=1
  user_mwi idx=1
  codec_priority_list
  dtmf_type
  sip_transport
  keepalive_interval
  phonebook_url
  firmware_update_time

Installation:
1. SQL-Patch optional importieren:
   patch_servuspbx_medical_1_0_41_snom_real_template_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_41_snom_real_template_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
