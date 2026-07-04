-- ============================================================
-- ServusPBX Medical 1.2.42
-- A1 Inbound Auth Guard
-- ============================================================
--
-- Fehlerbild:
-- Eingehender A1 INVITE wird mit 401 Unauthorized beantwortet.
--
-- Ursache:
-- ps_endpoints.auth war beim registrierenden Provider-Trunk gesetzt.
-- Für A1 darf inbound keine Auth erzwungen werden.
-- outbound_auth bleibt unverändert gesetzt.
--
-- Zusätzlich muss der eingehende Context wieder incoming sein.
-- ============================================================

UPDATE ps_endpoints p
JOIN spbx_trunks t ON t.endpoint_id = p.id
SET p.auth = NULL,
    p.context = 'incoming'
WHERE p.device_type = 'trunk'
  AND UPPER(COALESCE(t.provider,'A1')) = 'A1';

INSERT INTO `spbx_settings` (`setting_key`, `setting_value`, `description`) VALUES
('a1_inbound_auth_guard', '1.2.42', 'A1 Provider-Trunks erzwingen inbound keine Auth und verwenden context incoming')
ON DUPLICATE KEY UPDATE
  `setting_value`=VALUES(`setting_value`),
  `description`=VALUES(`description`);

-- Danach:
-- asterisk -rx "pjsip reload"
-- Prüfen:
-- asterisk -rx "pjsip show endpoint trunk-a1-43312423826"
-- Erwartung:
--   keine InAuth-Zeile
--   auth leer
--   outbound_auth gesetzt
--   context incoming
