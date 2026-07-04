ServusPBX Medical 1.3.1 - Nebenstellen-Context + A1 Contact User Auto

Neu:
- In der Nebenstellenmaske gibt es ein Feld:
  Standort / Context
- Auswahl aus aktiven Outbound-Routen:
  internal_<rufnummer>
- Damit kann ein Telefon von Standort/Trunk A nach B verschoben werden.

A1:
- Contact User wird automatisch aus der Hauptnummer erzeugt.
- Beispiel:
  main_number +43312423826
  contact_user +43312423826
- Das Feld muss in der GUI nicht mehr gepflegt werden.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_3_1_extension_context_a1_contact_user.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_3_1_extension_context_a1_contact_user.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Bei geänderten Nebenstellen:
   speichern und danach pjsip reload.
