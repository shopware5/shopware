SET NAMES 'utf8';
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- 1-add-esd-config.sql
-- //

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'ESD');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'redirectDownload', 'b:0;', 'Auf Download-Datei direkt verlinken', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL);


SET @parent = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'redirectDownload');

INSERT IGNORE INTO `s_core_config_element_translations` (`id` ,`element_id` ,`locale_id` ,`label` ,`description`)
VALUES (NULL , @parent, '2', 'Link to download file directly', NULL);

-- 2-add-default-email-templates.sql
-- //

UPDATE `s_cms_support` SET `email_template` = 'Return - Shopware Demoshop

Customer no.: {sVars.kdnr}
eMail: {sVars.email}

Invoice no.: {sVars.rechnung}
Article no.: {sVars.artikel}

Comment:
{sVars.info}'
WHERE `name` LIKE "Return"
AND `email_template` LIKE "INSERT INTO s_user_service%";



UPDATE `s_cms_support` SET `email_template` = 'Defective product - Shopware Demoshop

Company: {sVars.firma}
Customer no.: {sVars.kdnr}
eMail: {sVars.email}

Invoice no.: {sVars.rechnung}
Article no.: {sVars.artikel}

Description of failure:
--------------------------------
{sVars.fehler}

Type: {sVars.rechner}
System {sVars.system}
How does the problem occur:
{sVars.wie}'
WHERE `name` LIKE "Defective product"
AND `email_template` LIKE "INSERT INTO s_user_service%";

