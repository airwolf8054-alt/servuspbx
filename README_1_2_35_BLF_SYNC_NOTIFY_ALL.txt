ServusPBX Medical 1.2.35 - BLF Sync Notify All Phones

Geändert:
- Bei CallerID-Änderung werden weiterhin alle BLF-Labels mit key_type='blf'
  und key_value=<Durchwahl> aktualisiert.
- Danach werden alle aktiven provisionierten Telefone benachrichtigt:

  SELECT DISTINCT endpoint_id
  FROM spbx_devices
  WHERE active=1
    AND provisioning_enabled=1

- Pro Telefon:
  pjsip send notify snom-check-cfg endpoint <endpoint_id>

Warum:
- Entspricht der bewährten Logik der alten chan_sip-Anlage.
- Verhindert, dass einzelne Telefone wie D815 durch Mapping-/Key-Zuordnung nicht neu ziehen.

Installation:
1. SQL optional importieren:
   patch_servuspbx_medical_1_2_35_blf_sync_notify_all.sql

2. Dateien kopieren:
   cd /tmp
   unzip servuspbx_medical_1_2_35_blf_sync_notify_all.zip
   sudo rsync -av --delete login.php 2fa.php dashboard.php logout.php set_admin_password.php index.php inc assets css js images pages provision scripts /var/www/html/
   sudo chown -R www-data:www-data /var/www/html
