ServusPBX 2.6.6 - Provisioning Reboot Test

Ziel: Nur den Update-/Firmware-Mechanismus entschärfen, sonst keine Logik ändern.

Änderungen:
- inc/provisioning_snom.php: update_policy von auto_update auf settings_only geändert.
- inc/provisioning_snom.php: <dhcp perm="R">on</dhcp> ergänzt.
- Firmware-URL/Firmware-Tag wird weiterhin nicht ausgegeben.

Test:
1. ZIP einspielen.
2. Telefon neu provisionieren.
3. Prüfen, ob der Reboot-Loop weg ist.

Keine SQL-Änderung notwendig.
