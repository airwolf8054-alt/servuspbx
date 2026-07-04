ServusPBX Professional 2.6.0 - Provisioning Manager / No Network

Wichtigste Änderung:
- Desktop-Provisioning sendet keine Netzwerk-Datei network.xml mehr.
- IP, Gateway, DNS, Netmask werden nicht mehr provisioniert.
- DHCP ist damit Standard und Neustarts durch need_apply:1 bei Netzwerkparametern sollten wegfallen.

Neu:
- Menüpunkt Provisionierung.
- Optionen vorbereitet:
  - SIP-Identität
  - Funktionstasten
  - Telefonbuch
  - Firmware
  - Netzwerkparameter (Standard aus)
  - Neustart erzwingen (Standard aus)
- Tabelle spbx_provisioning_jobs für spätere Jobs vorbereitet.

Nach Installation:
1. SQL einspielen.
2. Provisionierung prüfen.
3. Telefon neu provisionieren.
4. Im Telefon-Log sollte network-settings nicht mehr von ServusPBX geladen werden.
