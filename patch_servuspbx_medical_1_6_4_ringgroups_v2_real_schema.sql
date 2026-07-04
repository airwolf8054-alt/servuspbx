-- ServusPBX Medical 1.6.4 - RingGroups v2 real schema

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('ringgroups_v2_real_schema', '1.6.4', 'Anrufregeln/Rufgruppen gegen echte DB-Struktur: spbx_trunks ohne context, Ziel ringgroups')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);
