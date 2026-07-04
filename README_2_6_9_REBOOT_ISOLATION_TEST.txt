ServusPBX 2.6.9 - Reboot-Isolationstest D815

Basis: 2.6.8 Legacy snomD815 XML-Fix

Ziel dieses Teststands:
- Reboot-Loop beim Snom D815 eingrenzen.
- Nur fuer D815-Test gedacht.

Geaendert in provision/snomD815.php:
- uploads-Block voruebergehend deaktiviert.
- gui-languages-Block voruebergehend deaktiviert.
- Funktionstasten-/BLF-Ausgabe voruebergehend deaktiviert.
- call_screen_fkeys_* und fkeys_on_dialing voruebergehend deaktiviert.

Nicht geaendert:
- SIP Account / Registrierung
- Grundlegende Telefoneinstellungen
- Telefonbuch (tbook) bleibt aktiv
- Keine SQL-Aenderung

Test:
1. ZIP einspielen.
2. D815 neu provisionieren.
3. Beobachten, ob Phone-Prozess / Telefon weiter neu startet.
4. Wenn stabil: Ursache liegt wahrscheinlich in uploads/gui-language/function-key Bereich.
5. Wenn instabil: weiter mit Minimal-Provisioning.
