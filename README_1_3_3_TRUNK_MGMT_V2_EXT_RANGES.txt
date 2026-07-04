ServusPBX Medical 1.3.3 - Trunk-Verwaltung 2.0 + Durchwahlbereiche

Neu:
- Trunk-Verwaltung komplett neu aufgebaut.
- Provider: A1, Magenta, easybell.
- Je Provider nur notwendige Felder.
- A1:
  Contact User automatisch
  Auth User automatisch
  incoming zentral
  inbound auth=NULL
- Durchwahlbereich pro Trunk:
  von / bis
- Beim Trunk-Speichern werden automatisch erzeugt:
  incoming bleibt zentral
  internal_<rufnummer>
  outgoing_<rufnummer>
  Outbound-Route
  Notrufprofil
- Nebenstellenmaske zeigt freie Durchwahlen aus dem Bereich.

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_3_3_trunk_mgmt_v2_ext_ranges.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_3_3_trunk_mgmt_v2_ext_ranges.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. SIP-Trunks öffnen.
4. Trunk bearbeiten oder neu anlegen.
5. Speichern.
6. Danach Nebenstelle öffnen/anlegen.

Prüfung:
asterisk -rx "dialplan show internal_43312423826"
asterisk -rx "dialplan show outgoing_43312423826"
SELECT * FROM spbx_trunks;
SELECT * FROM spbx_outbound_routes;
