-- ============================================================
-- ServusPBX Medical 1.2.13
-- SIP-Trunk Registration line=yes
-- ============================================================

ALTER TABLE `ps_registrations`
  ADD COLUMN IF NOT EXISTS `line` varchar(10) DEFAULT NULL;

UPDATE `ps_registrations`
SET `line`='yes'
WHERE `endpoint` IS NOT NULL
  AND `endpoint` <> '';

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('sip_trunk_registration_line', 'yes', 'Outbound Registrations mit endpoint setzen automatisch line=yes')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- sudo asterisk -rx "module reload res_pjsip_outbound_registration.so"
-- ============================================================
-- Ende ServusPBX Medical 1.2.13
-- ============================================================
