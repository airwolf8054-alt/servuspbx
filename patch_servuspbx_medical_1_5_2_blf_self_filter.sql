-- ============================================================
-- ServusPBX Medical 1.5.2
-- BLF Self Filter
-- ============================================================
--
-- Eigene Nebenstelle darf nicht als BLF-Taste auf dem eigenen Telefon liegen.
-- ============================================================

UPDATE spbx_device_keys k
JOIN spbx_devices d ON d.id = k.device_id
SET k.key_type='none',
    k.key_label='',
    k.key_value=''
WHERE k.key_type='blf'
  AND k.key_value = d.extension;

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('blf_self_filter', '1.5.2', 'Eigene Nebenstelle wird in BLF-Auswahl ausgeblendet und beim Speichern ignoriert')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
