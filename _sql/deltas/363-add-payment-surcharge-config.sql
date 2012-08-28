-- //

SET @formId = (SELECT id FROM s_core_config_forms WHERE label='Rabatte / Zuschläge');
INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @formId, 'paymentSurchargeAbsolute', 's:24:"Zuschlag für Zahlungsart";', 'Pauschaler Aufschlag für Zahlungsart (Bezeichnung)', NULL, 'text', 1, 0, 1, NULL, NULL, NULL),
(NULL, @formId, 'paymentSurchargeAbsoluteNumber', 's:19:"sw-payment-absolute";', 'Pauschaler Aufschlag für Zahlungsart (Bestellnummer)', NULL, 'text', 1, 0, 1, NULL, NULL, NULL);

-- //@UNDO

DELETE FROM `s_core_config_elements` WHERE `name` IN ('paymentSurchargeAbsolute', 'paymentSurchargeAbsoluteNumber');

-- //
