ServusPBX Medical 1.0.15 - Nebenstellen

Neu:
- Nebenstellenverwaltung fertig aufgebaut.
- Telefon + Nebenstelle sind ein Objekt.
- Unterstützte Gerätetypen:
  snom D815
  snom D810
  snom M900
  SIP User
- Beim Speichern werden automatisch gepflegt:
  spbx_extensions
  spbx_devices
  ps_endpoints
  ps_auths
  ps_aors
  voicemail
- Passwort wird automatisch erzeugt, wenn leer.
- MAC Pflicht bei snom D815/D810.
- IPEI Pflicht bei snom M900.
- SIP User nutzt die Durchwahl als Endpoint-ID.
- Audit-Log für Anlegen/Ändern/Löschen.
- CSS ist auf spbx-ext-* gekapselt und iPad-tauglich.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_15_extensions.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
