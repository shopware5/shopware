-- //

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'Anmeldung / Registrierung');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'accountPasswordCheck', 'b:1;', 'Aktuelles Passwort bei Passwort-Änderungen abfragen', NULL, 'boolean', 1, 0, 0, NULL, NULL, NULL);

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'InputFilter');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'refererCheck', 'b:1;', 'Referrer-Check aktivieren', NULL, 'boolean', 1, 0, 0, NULL, NULL, NULL);

INSERT IGNORE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/index', 1, 2, 'AccountLabelCurrentPassword', 'Your current password*:', '2013-01-23 08:57:47', '2013-01-23 08:57:47'),
('frontend', 1, 2, 'AccountCurrentPassword', 'Your current password is wrong', '2013-01-23 08:57:47', '2013-01-23 08:57:47');


-- //@UNDO

-- //
