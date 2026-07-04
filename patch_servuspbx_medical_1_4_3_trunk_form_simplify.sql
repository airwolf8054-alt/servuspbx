-- ============================================================
-- ServusPBX Medical 1.4.3
-- Trunk Formular vereinfachen
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('trunk_form_simplified', '1.4.3', 'Server URI ausgeblendet und automatisch aus Domain erzeugt; Durchwahlbereich Von/Bis nebeneinander')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.4.3
