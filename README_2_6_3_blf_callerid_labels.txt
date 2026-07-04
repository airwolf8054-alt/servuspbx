ServusPBX Professional 2.6.3 - BLF CallerID Labels

Änderung:
- BLF-Funktionstasten verwenden beim Provisionieren wieder die CallerID des Ziel-Endpunkts aus ps_endpoints.
- Das gespeicherte fkeyXlabel bleibt nur Fallback.
- Leerzeichen in CallerID/Label werden für Snom als &#10; ausgegeben, damit Vorname/Nachname zweizeilig dargestellt werden.
- Lookup funktioniert über ps_endpoints.id und ps_endpoints.extension.

Beispiel:
ps_endpoints.callerid = "Max Mustermann" <11>
<fkey_label idx="0" perm="R">Max&#10;Mustermann</fkey_label>
