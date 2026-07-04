ServusPBX Medical 1.0.29 - BLF Stable Fix

Korrigiert:
- SQL-Fehler "Unknown column fkey_idx" durch sauberes Neu-Erstellen von spbx_device_keys.
- Aktionsbuttons in der Nebenstellenliste laufen nicht mehr auseinander.
- Button "Tastenbelegung" wurde zu "Tasten" gekürzt.
- Tasten-Button hat eigene CSS-Klasse.
- function_keys.php prüft, ob die benötigte Spalte fkey_idx vorhanden ist.
- Provisioning vermeidet doppelte fkey0-Ausgabe.

Wichtig:
Der SQL-Patch löscht spbx_device_keys und erstellt die Tabelle neu.
Während der Entwicklung ist das gewollt, damit das Schema stabil ist.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_0_29_blf_stable_fix.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_0_29_blf_stable_fix.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
