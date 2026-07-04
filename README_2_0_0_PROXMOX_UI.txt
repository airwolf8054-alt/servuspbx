ServusPBX Medical 2.0.0 - Proxmox UI Navigation

Geändert:
- Linkes Menü im Proxmox-inspirierten Stil.
- Weiße SVG Outline-Icons statt Emoji-Icons.
- Logo als Text:
  servus = weiß
  PBX = rot
- Menü gruppiert nach Übersicht, Telefonie, Routing, Daten, System.
- Keine Änderung an Telefonie-/Dialplan-/Provisioning-Logik.

Installation:
1. SQL optional:
   patch_servuspbx_medical_2_0_0_proxmox_ui.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_2_0_0_proxmox_ui.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Browser hart neu laden.
