ServusPBX Professional 2.5.0 - Desktop Firmware Tag Fix

Geändert:
- Desktop-Provisioning verwendet:
  <firmware perm='R'>URL</firmware>
  statt:
  <firmware_status perm='R'>URL</firmware_status>

- Firmware-URL wird modellabhängig gebaut:
  SNOM:
  https://downloads.snom.com/fw/<version>/bin/<model>-<version>-SIP-r.swu

  Gigaset:
  https://downloads.grape.gigaset.net/fw/<version>/bin/<model>-<version>-SIP-r.swu

- Modellmapping:
  snomD810 -> snomD810
  snomD812 -> snomD812
  snomD815 -> snomD815
  GigasetP810 -> gigasetP810
  GigasetP82x -> fw_gigaset_p82x_model, Default gigasetP820
  GigasetP85x -> fw_gigaset_p85x_model, Default gigasetP850

Wichtig:
- Danach Telefone neu provisionieren.
- Falls Telefone weiterhin booten, bitte die abgerufene Provisioning-XML direkt im Browser prüfen.
