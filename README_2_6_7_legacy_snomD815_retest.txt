ServusPBX 2.6.7 Legacy snomD815 Re-Test

Ziel dieses Teststands:
- snomD815.php basiert wieder direkt auf dem alten bewährten PHP-Provisioning-Script.
- Nur notwendige Anpassungen wurden gemacht:
  - Tabelle phones -> deskphones
  - DB-Anbindung über inc/db.php / spbx_db()
  - update_policy = settings_only
  - dhcp = on
  - setting_server lokal: http://<Server-IP>/provision/snomD815.php?mac={mac}
  - Firmware-Zeilen wurden für diesen Test deaktiviert
  - D8C-Erweiterungsmodul-Blöcke wurden für diesen Test deaktiviert, damit keine fehlenden D8c_* Tabellen stören

Wichtig:
- Dieser Stand ist bewusst nur ein Reboot-Test für snomD815.
- Keine SQL-Änderung notwendig.
- Wenn dieses Script stabil läuft, übertragen wir die gleiche Legacy-Logik sauber auf die übrigen Modelle.
