ServusPBX Medical 1.2.31 - Anrufregeln SIP-Trunk Dropdown Fallback

Korrigiert:
- SIP-Trunk / Context Dropdown blieb leer.
- Dropdown liest jetzt:
  1. spbx_trunks
  2. Fallback ps_endpoints mit device_type='trunk'
- Context wird verwendet; falls in spbx_trunks leer, wird aus main_number from_<nummer> abgeleitet.
- Bereits verknüpfte Contexts werden markiert.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_31_call_rules_trunk_fallback.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_31_call_rules_trunk_fallback.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
