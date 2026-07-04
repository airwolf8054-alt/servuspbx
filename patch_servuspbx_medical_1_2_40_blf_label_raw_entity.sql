-- ============================================================
-- ServusPBX Medical 1.2.40
-- BLF Label Snom-Zeilenumbruch mit roher Entity
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('snom_blf_label_linebreak', '1.2.40', 'BLF-Labels verwenden rohe &#10; Entity wie in der alten funktionierenden Snom-Provisionierung')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.2.40
