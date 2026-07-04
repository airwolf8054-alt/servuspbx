ServusPBX Medical 1.5.7 - device_model VARCHAR Fix

Korrigiert:
- Data truncated for column 'device_model'
- betrifft GigasetP82x / GigasetP85x und zukünftige Modelle

Wichtig:
Bitte SQL einspielen:
patch_servuspbx_medical_1_5_7_device_model_varchar_fix.sql

Der Patch stellt diese Spalten auf VARCHAR(40), falls vorhanden:
- ps_endpoints.device_model
- spbx_devices.device_model
- spbx_extensions.device_model

Danach Gigaset P85x erneut anlegen.
