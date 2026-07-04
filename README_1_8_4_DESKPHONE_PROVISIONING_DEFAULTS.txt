ServusPBX Medical 1.8.4 - Deskphone Provisioning Defaults

Neu:
- Zentrale Defaults für SNOM und Gigaset Tischtelefone:
  - Callscreen-Fkeys
  - fkeys_on_dialing
  - block_url_dialing
  - locale de_DE
  - LED-Usage
  - mute_is_dnd_in_idle off
  - user_active idx 2-12 off

Nicht betroffen:
- DECT-Basen M400/M900 werden nicht separat geändert.

Wichtig:
- Bestehende Werte werden im zentralen Tischtelefon-Provisioning überschrieben.
- Klingeltöne/Ringer bleiben unverändert.

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_8_4_deskphone_provisioning_defaults.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_8_4_deskphone_provisioning_defaults.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. SNOM/Gigaset Tischtelefone neu provisionieren.
