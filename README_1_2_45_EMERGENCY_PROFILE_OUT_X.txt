ServusPBX Medical 1.2.45 - Notrufprofile pro Trunk + outgoing _X.

Änderungen:
- Outbound-Route hat jetzt Land / Notrufprofil:
  AT, DE, CH, LI, CUSTOM
- internal_<rufnummer> bekommt nur die Notrufnummern dieses Profils.
- Interne Nebenstellen:
  _XX
  _XXX
  _XXXX
- Alles ab 5 Stellen:
  _XXXXX. -> outgoing_<rufnummer>
- outgoing_<rufnummer> nimmt alles mit _X. an.
  Dadurch funktionieren auch lokale Nummern ohne Vorwahl.

Notrufprofile:
AT: 112,122,133,144,141,1450
DE: 110,112,115,116117
CH: 112,117,118,144,145,1414,143,147
LI: 112,117,118,144

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_2_45_emergency_profile_out_x.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_45_emergency_profile_out_x.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. /pages/outbound_routes.php öffnen.
4. Land / Notrufprofil prüfen.
5. Dialplan neu aufbauen.
6. pjsip reload, falls Nebenstellen-Context geändert wurde:
   sudo /usr/sbin/asterisk -rx "pjsip reload"
