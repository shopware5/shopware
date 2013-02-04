SET NAMES 'utf8';
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- 1-add-account-config.sql
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


-- 2-fix-article-attribute-types.sql
-- //

UPDATE `s_core_engine_elements` SET `type`='text' WHERE `type`='textfield' AND `name` IN ('attr1', 'attr2');

-- 3-add-shop-routing-option.sql
-- //

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'SEO/Router-Einstellungen');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'preferBasePath', 'b:1;', 'Shopware-Kernel aus URL entfernen ', 'Entfernt "shopware.php" aus URLs. Verhindert, dass Suchmaschinen fälschlicherweise DuplicateContent im Shop erkennen. Wenn kein ModRewrite zur Verfügung steht, muss dieses Häcken entfernt werden.', 'boolean', 1, 0, 0, NULL, NULL, NULL);

