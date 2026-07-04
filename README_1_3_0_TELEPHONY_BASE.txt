ServusPBX Medical 1.3.0 - Telephony Base

Dies ist ein konsolidierter Patch für die Telefonie-Basis.

Wichtig:
- Eingehend bleibt unverändert auf context incoming.
- Ausgehend wird pro Rufnummer über outgoing_<rufnummer> geregelt.
- Nebenstellen verwenden internal_<rufnummer>.
- Notrufnummern sind jetzt datenbankgestützt.

Enthalten:
- Tabelle spbx_outbound_routes wird erstellt, falls sie fehlt.
- emergency_profile wird ergänzt, falls es fehlt.
- Tabelle spbx_country_emergency_numbers wird erstellt.
- Standardprofile AT, DE, CH, LI werden befüllt.
- Bestehende Trunks werden zu Outbound-Routen synchronisiert.
- A1-Trunks werden inbound geschützt:
  auth=NULL
  context=incoming

Callflow:
- internal_<rufnummer>
  - _XX, _XXX, _XXXX bleiben interne Nebenstellen
  - alles ab 5 Stellen geht zu outgoing_<rufnummer>
  - Notrufe nur vom gewählten Land/Profil
- outgoing_<rufnummer>
  - _X. nimmt alles an
  - 0... wird zu +43...
  - CLIP no Screening: Hauptnummer + Nebenstelle

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_3_0_telephony_base.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_3_0_telephony_base.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. GUI:
   /pages/outbound_routes.php öffnen
   Notrufprofil prüfen
   Dialplan neu aufbauen klicken

4. Reload:
   sudo /usr/sbin/asterisk -rx "pjsip reload"

Prüfung:
- asterisk -rx "dialplan show internal_43312423826"
- asterisk -rx "dialplan show outgoing_43312423826"
- asterisk -rx "pjsip show endpoint 10"
- asterisk -rx "pjsip show endpoint trunk-a1-43312423826"
