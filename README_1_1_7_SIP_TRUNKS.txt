ServusPBX Medical 1.1.7 - SIP-Trunks

Änderungen:
- Menütext: SIP-Trunk A1 -> SIP-Trunks.
- Neue Seite: pages/sip_trunks.php.
- Übersichtstabelle mit Name, Provider, Benutzer, Server, Context, Status.
- Anlegen, Bearbeiten, Löschen.
- Provider-Vorlagen:
  - A1
  - Easybell
  - Magenta
- Trunks werden vorbereitet in:
  - spbx_trunks
  - ps_endpoints
  - ps_auths
  - ps_aors
  - ps_registrations
- Eingehender Context: from-trunk.
- Platzhalter-Dialplan from-trunk,s,1 erstellt.

Installation:
1. Backup:
   mysqldump -u root -p general > /root/general_before_1_1_7.sql

2. SQL-Patch importieren:
   patch_servuspbx_medical_1_1_7_sip_trunks.sql

3. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_1_7_sip_trunks.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

4. Asterisk reload:
   sudo asterisk -rx "module reload res_pjsip.so"
   sudo asterisk -rx "module reload res_pjsip_outbound_registration.so"
   sudo asterisk -rx "dialplan reload"

Prüfung:
sudo asterisk -rx "pjsip show registrations"
SELECT * FROM spbx_trunks;
SELECT id, client_uri, server_uri, outbound_auth FROM ps_registrations;
