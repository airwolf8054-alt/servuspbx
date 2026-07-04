-- ============================================================
-- ServusPBX Medical 1.1.2
-- DECT Handset Endpoint-ID = Durchwahl
-- ============================================================

-- Neue Regel:
-- DECT-Handsets verwenden als PJSIP Endpoint-ID nicht mehr die IPEI,
-- sondern die Durchwahl.
--
-- ps_endpoints.id        = Durchwahl, z.B. 96
-- ps_endpoints.extension = Durchwahl, z.B. 96
-- ps_auths.id           = Durchwahl
-- ps_aors.id            = Durchwahl
-- spbx_devices.ipei     = IPEI des Handsets
--
-- Hinweis:
-- Bestehende alte DECT-Endpoints mit IPEI als ps_endpoints.id bitte in der GUI löschen
-- und danach neu anlegen. Das ist sauberer als eine automatische Migration,
-- weil Auth/AOR/DECT-IDX-Zuordnungen sonst falsch verknüpft bleiben können.

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('dect_handset_endpoint_id', 'extension', 'DECT-Handsets verwenden die Durchwahl als ps_endpoints.id; IPEI bleibt Gerätekennung')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Ende ServusPBX Medical 1.1.2
