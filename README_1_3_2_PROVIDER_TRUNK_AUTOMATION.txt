ServusPBX Medical 1.3.2 - Provider-geführte Trunk-Automation

Ziel:
- Vorerst nur A1, Magenta und easybell.
- Pro Provider nur notwendige Felder.
- Technische SIP-Felder werden automatisch gesetzt, soweit bekannt.
- incoming bleibt zentral.
- internal_<rufnummer> und outgoing_<rufnummer> werden beim Trunk-Speichern automatisch erzeugt.

A1:
- Contact User automatisch = Hauptnummer in +E164
- Auth User automatisch = Benutzername/Hauptnummer
- Endpoint inbound:
  auth=NULL
  context=incoming
- Notrufprofil standardmäßig AT

easybell:
- Auth User bleibt sichtbar, weil er abweichen kann.
- Notrufprofil standardmäßig DE

Magenta:
- vorerst generisch/minimal
- Notrufprofil standardmäßig AT

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_3_2_provider_trunk_automation.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_3_2_provider_trunk_automation.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Einen Trunk öffnen und speichern.
   Danach sollten internal_<rufnummer> und outgoing_<rufnummer> existieren.

Prüfung:
asterisk -rx "dialplan show internal_43312423826"
asterisk -rx "dialplan show outgoing_43312423826"
SELECT * FROM spbx_outbound_routes;
