ServusPBX Medical 1.0.67 - Asterisk Internal Dialplan

Neu:
- Realtime-Dialplan in Datenbanktabelle extensions.
- Context: internal.
- Pattern:
  _XX
  _XXX
- Ablauf:
  Dial(PJSIP/${EXTEN},30)
  danach Goto(internal,0,1)
- Ziel 0:
  Dial(PJSIP/0,30)
  danach Hangup.

Zusätzlich:
- ps_endpoints wird um gängige PJSIP-Realtime-Felder ergänzt.
- Bestehende Endpoints werden auf context=internal gesetzt.
- ps_aors und ps_auths werden bei Bedarf angelegt.
- ps_endpoints.aors/auth werden auf die Endpoint-ID gesetzt.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_67_asterisk_internal_dialplan.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_67_asterisk_internal_dialplan.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. Asterisk reload:
   sudo asterisk -rx "dialplan reload"
   sudo asterisk -rx "module reload res_pjsip.so"

Wichtig:
- Falls deine MySQL/MariaDB-Version ADD COLUMN IF NOT EXISTS nicht unterstützt,
  bei Duplicate-Column-Meldungen die betroffene ALTER-Zeile überspringen.
- Die Tabelle `extensions` muss in extconfig.conf als Realtime-Dialplan eingebunden sein.
  Beispiel:
  extensions => mysql,asterisk,extensions
