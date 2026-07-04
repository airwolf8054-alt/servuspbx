-- ServusPBX Medical 1.7.2 - DID Extension Goto Fix

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('did_extension_goto_fix', '1.7.2', 'Durchwahl aus DID nutzt Set(DIDEXT=...) und Goto(...,${DIDEXT},1)')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
