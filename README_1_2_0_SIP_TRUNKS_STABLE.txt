ServusPBX Medical 1.2.0 - SIP-Trunks Stable

Wichtig:
Dieser Patch verwirft die kaputte neue sip_trunks.php-Struktur aus 1.1.7-1.1.9.
Die SIP-Trunk-Verwaltung basiert jetzt auf der bestehenden Seite pages/trunk_a1.php.

Änderungen:
- Menütext: SIP-Trunk A1 -> SIP-Trunks.
- pages/trunk_a1.php wird zur SIP-Trunk-Übersicht.
- pages/sip_trunks.php bleibt als Kompatibilitäts-Datei und lädt trunk_a1.php.
- Übersichtstabelle für SIP-Trunks.
- Anlegen, Bearbeiten, Löschen.
- Provider-Vorlagen:
  - A1
  - Easybell
  - Magenta
- Speicherung in:
  - spbx_trunks
  - ps_endpoints
  - ps_auths
  - ps_aors
  - ps_registrations
- Eingehender Context: from-trunk.
- Platzhalter-Dialplan from-trunk,s,1 erstellt.

Installation:
1. Backup:
   mysqldump -u root -p general > /root/general_before_1_2_0.sql

2. SQL-Patch importieren:
   patch_servuspbx_medical_1_2_0_sip_trunks_stable.sql

3. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_0_sip_trunks_stable.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

4. Asterisk reload:
   sudo asterisk -rx "module reload res_pjsip.so"
   sudo asterisk -rx "module reload res_pjsip_outbound_registration.so"
   sudo asterisk -rx "dialplan reload"

Prüfung:
- Menüpunkt SIP-Trunks öffnen.
- Neuen A1/Easybell/Magenta-Trunk anlegen.
- Danach:
  sudo asterisk -rx "pjsip show registrations"
  SELECT * FROM spbx_trunks;
  SELECT id, client_uri, server_uri, outbound_auth FROM ps_registrations;
