ServusPBX Medical 1.2.42 - A1 Inbound Auth Guard

Korrigiert:
- Eingehende A1-Anrufe wurden wieder mit 401 Unauthorized beantwortet.
- Ursache war:
  ps_endpoints.auth war beim Provider-Trunk gesetzt.
- Zusätzlich war context wieder from_<nummer> statt incoming.

Fix:
- A1-Trunks:
  auth = NULL
  context = incoming
- outbound_auth bleibt unverändert.

Installation:
1. SQL importieren:
   patch_servuspbx_medical_1_2_42_a1_inbound_auth_guard.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_42_a1_inbound_auth_guard.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Reload:
   sudo /usr/sbin/asterisk -rx "pjsip reload"

Prüfung:
asterisk -rx "pjsip show endpoint trunk-a1-43312423826"

Erwartet:
- keine InAuth-Zeile
- auth leer
- outbound_auth gesetzt
- context incoming
