ServusPBX Medical 1.2.16 - Context Guard

Korrigiert:
- Normale Nebenstellen dürfen keinen Trunk-/from-Kontext bekommen.
- ps_endpoints.context wird für alle Nicht-Trunks auf internal repariert.
- Die Nebenstellenmaske erzwingt internal.
- Die SIP-Trunk-Seite enthält einen Sicherheitsstopp, falls eine Trunk-ID versehentlich auf eine bestehende Nebenstelle zeigen würde.

Installation:
1. SQL-Patch importieren:
   patch_servuspbx_medical_1_2_16_context_guard.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_16_context_guard.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html

Prüfung:
SELECT id, extension, display_name, device_type, context FROM ps_endpoints ORDER BY id;

Erwartung:
- Telefone: context=internal
- Trunks: context=from_<rufnummer>
