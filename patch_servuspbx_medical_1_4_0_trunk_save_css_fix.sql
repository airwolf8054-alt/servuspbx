-- ============================================================
-- ServusPBX Medical 1.4.0
-- Trunk Save Fix + CSS Guard
-- ============================================================
--
-- Fix:
-- - ps_aors Legacy-Feld extension wird beim Trunk-Speichern befüllt.
-- - Trunkformular bekommt stabilen ServusPBX Formular-Style.
-- ============================================================

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('trunk_save_ps_aors_extension_fix', '1.4.0', 'ps_aors.extension wird bei Legacy-Schema befüllt'),
('trunk_form_css_guard', '1.4.0', 'Trunk Formularfelder werden zuverlässig im ServusPBX Stil dargestellt')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.4.0
