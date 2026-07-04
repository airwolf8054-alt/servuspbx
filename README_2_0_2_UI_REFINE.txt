ServusPBX Professional 2.0.2 - UI Refinement

Geändert:
- ServusPBX Logo mit ® rechts oben.
- Professional Edition bleibt als Untertitel.
- Bahnschrift SemiBold für Logo/Titel.
- UI weiter Richtung Proxmox:
  - weniger runde Ecken
  - ruhigere Karten
  - kompaktere Tabellen
  - klarere Buttons
  - technische Admin-Optik

Keine Änderung an:
- Dialplan
- Provisioning-Logik
- Anrufregeln
- Trunks
- Datenbanklogik

Installation:
1. SQL optional:
   patch_servuspbx_professional_2_0_2_ui_refine.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_professional_2_0_2_ui_refine.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Browser hart neu laden.
