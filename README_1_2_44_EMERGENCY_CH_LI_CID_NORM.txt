ServusPBX Medical 1.2.44 - Notrufe CH/LI + CallerID-Normalisierung

Ergänzt:
- Schweiz/Liechtenstein:
  112,117,118,144,145
- Schweiz zusätzlich:
  1414,143,147

CallerID eingehend:
- +436641549314 wird zu 00436641549314
- Umsetzung im eingehenden Callflow per:
  ExecIf($["${CALLERID(num):0:1}"="+"]?Set(CALLERID(num)=00${CALLERID(num):1}))

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_44_emergency_ch_li_cid_norm.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_44_emergency_ch_li_cid_norm.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

3. /pages/outbound_routes.php öffnen und "Dialplan neu aufbauen" klicken.

4. Eine Anrufregel speichern, damit incoming neu generiert wird.

5. Reload:
   sudo /usr/sbin/asterisk -rx "dialplan reload"
