ServusPBX Professional 2.1.0 - SNOM M400 Provisioning

Neu:
- provision/snomM400.php
- SNOM M400 mit 20 Handset-Slots.
- Nicht genutzte Handsets erhalten 0xFFFFFFFFFF.
- Handset-IPEI wird aus spbx_devices.ipei gelesen.
- DECT Webinterface zeigt M400 IDX 1-20.
- Nebenstellenliste liest IPEI wieder aus spbx_devices.ipei.

SQL empfohlen:
patch_servuspbx_professional_2_1_0_snom_m400_provisioning.sql

Hinweis:
- M900 bleibt unverändert.
- Für M400 Basis im Webinterface eine DECT-Basis mit MAC anlegen.
- Handsets unter DECT > Handsets zuordnen.
- Provision URL: /provision/snomM400.php?mac=<MAC>
