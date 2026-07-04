-- ServusPBX Professional 2.2.1 - D8xx/P8xx Models

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('d8xx_p8xx_models', '2.2.1', 'snom D812 und Gigaset P810 ergänzt, Tastenlayouts und Context-Taste 0 angepasst')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
