ServusPBX Medical 1.6.7 - Incoming DID Patterns + internal_<rufnummer>

Korrigiert:
- DID-Feld akzeptiert jetzt Asterisk Patterns wie _XX, _XXX, _XXXX, _X.
- Bei Zieltyp Nebenstelle wird nicht mehr statisch internal verwendet.
- Nebenstellenziele gehen auf den passenden internal_<rufnummer>-Context.
- Für Durchwahlregeln kann als Ziel "Gewählte Durchwahl (${EXTEN})" verwendet werden.

Beispiel:
DID: _XX
Zieltyp: Nebenstelle
Nebenstelle: Gewählte Durchwahl (${EXTEN})

Ergebnis:
GotoIfTime(...?internal_43312423826,${EXTEN},1)

Installation:
1. SQL optional:
   patch_servuspbx_medical_1_6_7_incoming_did_patterns_internal_context.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_6_7_incoming_did_patterns_internal_context.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
