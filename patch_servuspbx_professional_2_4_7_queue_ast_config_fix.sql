-- ServusPBX Professional 2.4.7 - Queue ast_config Fix

DELETE FROM ast_config
WHERE filename='extensions.conf'
  AND category='queue-services';

INSERT INTO ast_config
(cat_metric, var_metric, commented, filename, category, var_name, var_val)
VALUES
(4010, 0, 0, 'extensions.conf', 'queue-services', 'switch', 'Realtime/@extensions');

INSERT INTO spbx_settings (setting_key, setting_value, description) VALUES
('queue_ast_config_fix', '2.4.7', 'queue-services Context in ast_config angelegt')
ON DUPLICATE KEY UPDATE
  setting_value=VALUES(setting_value),
  description=VALUES(description);
