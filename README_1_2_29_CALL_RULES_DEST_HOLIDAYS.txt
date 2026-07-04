ServusPBX Medical 1.2.29 - Anrufregeln Zielauswahl + Feiertage

Geändert:
- Anrufregeln:
  - Feiertagsliste entfernt.
  - Es bleibt nur die Checkbox "Feiertagsschaltung für diese Rufnummer aktiv".
  - Offen-Ziel hat jetzt Zieltyp + Nebenstellen-Auswahl.
  - Benutzerdefiniert zeigt Context/Extension.

Neu:
- /pages/holidays.php
- Menüpunkt "Feiertage"
- Feiertage bleiben global in Asterisk AstDB bankholiday/YYYY-MM-DD.

Nicht geändert:
- Dialplan-Logik
- SIP-Trunks
- Tischtelefon-Provisionierung

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_29_call_rules_dest_holidays.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_29_call_rules_dest_holidays.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
