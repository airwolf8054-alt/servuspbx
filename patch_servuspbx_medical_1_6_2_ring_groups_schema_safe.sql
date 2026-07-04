-- ServusPBX Medical 1.6.2 - Rufgruppen Schema-Safe

CREATE TABLE IF NOT EXISTS `spbx_ring_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_number` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(120) NOT NULL DEFAULT '',
  `strategy` varchar(20) NOT NULL DEFAULT 'ringall',
  `ring_time` int(11) NOT NULL DEFAULT 25,
  `timeout_action` varchar(20) NOT NULL DEFAULT 'hangup',
  `timeout_target` varchar(40) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_group_number` (`group_number`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_ring_groups' AND COLUMN_NAME='group_number');
SET @s := IF(@c=0, "ALTER TABLE spbx_ring_groups ADD COLUMN group_number VARCHAR(20) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_ring_groups' AND COLUMN_NAME='name');
SET @s := IF(@c=0, "ALTER TABLE spbx_ring_groups ADD COLUMN name VARCHAR(120) NOT NULL DEFAULT ''", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_ring_groups' AND COLUMN_NAME='strategy');
SET @s := IF(@c=0, "ALTER TABLE spbx_ring_groups ADD COLUMN strategy VARCHAR(20) NOT NULL DEFAULT 'ringall'", "ALTER TABLE spbx_ring_groups MODIFY strategy VARCHAR(20) NOT NULL DEFAULT 'ringall'");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_ring_groups' AND COLUMN_NAME='ring_time');
SET @s := IF(@c=0, "ALTER TABLE spbx_ring_groups ADD COLUMN ring_time INT NOT NULL DEFAULT 25", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_ring_groups' AND COLUMN_NAME='timeout_action');
SET @s := IF(@c=0, "ALTER TABLE spbx_ring_groups ADD COLUMN timeout_action VARCHAR(20) NOT NULL DEFAULT 'hangup'", "ALTER TABLE spbx_ring_groups MODIFY timeout_action VARCHAR(20) NOT NULL DEFAULT 'hangup'");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_ring_groups' AND COLUMN_NAME='timeout_target');
SET @s := IF(@c=0, "ALTER TABLE spbx_ring_groups ADD COLUMN timeout_target VARCHAR(40) DEFAULT NULL", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='spbx_ring_groups' AND COLUMN_NAME='active');
SET @s := IF(@c=0, "ALTER TABLE spbx_ring_groups ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1", "SELECT 1");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `spbx_ring_group_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT 0,
  `extension` varchar(20) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_group` (`group_id`),
  KEY `idx_extension` (`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

UPDATE spbx_ring_groups SET group_number=COALESCE(NULLIF(group_number,''), CAST(id AS CHAR)) WHERE COALESCE(group_number,'')='';
UPDATE spbx_ring_groups SET name=COALESCE(NULLIF(name,''), CONCAT('Rufgruppe ', group_number)) WHERE COALESCE(name,'')='';
UPDATE spbx_ring_groups SET strategy='ringall' WHERE strategy NOT IN ('ringall','hunt','random') OR strategy IS NULL OR strategy='';
UPDATE spbx_ring_groups SET ring_time=25 WHERE ring_time IS NULL OR ring_time<5;
UPDATE spbx_ring_groups SET timeout_action='hangup' WHERE timeout_action NOT IN ('hangup','extension') OR timeout_action IS NULL OR timeout_action='';

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('ring_groups_schema_safe', '1.6.2', 'Rufgruppen Modul ergänzt fehlende Spalten automatisch und nutzt keine Schema-Annahmen')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`), `description`=VALUES(`description`);
