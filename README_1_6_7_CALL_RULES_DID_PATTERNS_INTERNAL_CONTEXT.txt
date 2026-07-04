ServusPBX Medical 1.6.7 - Call Rules DID Patterns + internal_<rufnummer>

Korrigiert:
- DID/Rufnummer erlaubt jetzt Asterisk Patterns:
  _XX
  _XXX
  _XXXX
  _+43312423826XX

- Zieltyp Nebenstelle wird im Dialplan nicht mehr hart auf internal gesetzt.
  Stattdessen wird anhand der angerufenen Hauptnummer automatisch internal_<rufnummer> verwendet.

Neu:
- Zieltyp "Durchwahl aus DID".
  Beispiel:
  DID: _+43312423826XX
  Ziel: Durchwahl aus DID
  Ergebnis: Goto(internal_43312423826,${EXTEN:12},1)

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_6_7_call_rules_did_patterns_internal_context.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_7_call_rules_did_patterns_internal_context.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Anrufregel speichern, damit der Dialplan neu erzeugt wird.
