ServusPBX Medical 1.0.22 - Extension Delete Fix

Korrigiert:
- Löschfunktion für Nebenstellen wieder eingebaut.
- Button "Löschen" in der Nebenstellenliste.
- Löscht zugehörige Einträge aus:
  spbx_extensions
  spbx_devices
  spbx_device_keys
  ps_endpoints
  ps_auths
  ps_aors
  ps_contacts
  voicemail
- Browser-Sicherheitsabfrage vor dem Löschen.
- Audit-Log Eintrag extension_delete.

Installation:
cd /tmp
unzip servuspbx_medical_1_0_22_extension_delete_fix.zip
sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages /var/www/html/
sudo chown -R www-data:www-data /var/www/html
