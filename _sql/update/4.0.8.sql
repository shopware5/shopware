SET NAMES 'utf8';
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- 1-remove-broken-subscribes.sql
-- SW-5202-remove-broken-subscribes

-- //

DELETE FROM `s_core_subscribes` WHERE  `listener` LIKE  'Shopware_Plugins_Core_Shop_Bootstrap::%';
DELETE FROM `s_core_subscribes` WHERE  `listener` LIKE  'Shopware_Plugins_Backend_Locale_Bootstrap::%';

-- 2-remove-needless-note-compare-snippet.sql
-- //

DELETE FROM `s_core_snippets` WHERE `name` = 'NoteLinkCompare' AND `namespace` = 'frontend/note/item';

-- 3-change-notification-snippet.sql
--  //

UPDATE `s_core_snippets`
SET `value` = "Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten. Eventuell wurde Ihre eMail-Adresse bereits validiert."
WHERE `name` = 'DetailNotifyInfoInvalid'
AND `namespace` = 'frontend/plugins/notification/index'
AND `value` = "Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten.";


UPDATE `s_core_snippets`
SET `value` = "An error has occurred while validating your e-mail address. Possibly your email address has already been validated."
WHERE `name` = 'DetailNotifyInfoInvalid'
AND `namespace` = 'frontend/plugins/notification/index'
AND `value` = "An error has occured while validating your e-mail address.";

-- 4-replicate-the-multilanguage-parent-category-id.sql
-- //

UPDATE s_core_multilanguage as m, s_core_shops as s SET m.parentID=s.category_id WHERE m.id = s.id;

-- 5-change-blog-settings-description.sql
-- //

SET @parent = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'blogcategory' AND `label` = 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen');

UPDATE `s_core_config_elements`
SET `label` = 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen (Nur alte Templatebasis)'
WHERE `id` = @parent;

UPDATE `s_core_config_element_translations`
SET `label` = 'Show blog entries from category (ID) on starting page (Only old template base)'
WHERE `id` = @parent;


-- 6-remove-broken-cms-support-field.sql
-- //
DELETE FROM `s_cms_support_fields` WHERE `name` LIKE "sdfg" AND `label` LIKE "sdf";

-- 7-fix-umlauts-in-frontend-account-snippet.sql
-- //

UPDATE `s_core_snippets` 
SET `value` = 'Nachdem Sie die erste Bestellung durchgeführt haben, können Sie hier auf vorherige Rechnungsadressen zugreifen.'
WHERE `name` LIKE 'SelectBillingInfoEmpty' 
AND `value` LIKE 'Nachdem Sie die erste Bestellung durchgef?hrt haben, k?nnen Sie hier auf vorherige Rechnungsadressen zugreifen.';

-- 8-add-frontend-account-snippet.sql
-- //

INSERT IGNORE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelMr', 'Mr', '2013-04-22 16:04:23', '2013-04-22 16:04:23'),
('frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelMr', 'Herr', '2013-04-22 16:04:23', '2013-04-22 16:04:23'),
('frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelMrs', 'Mrs', '2013-04-22 16:04:23', '2013-04-22 16:04:23'),
('frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelMrs', 'Frau', '2013-04-22 16:04:23', '2013-04-22 16:04:23');

-- 9-fix-esd-snippet.sql
-- //
UPDATE `s_core_snippets`
SET `value` = 'Dieser Download steht Ihnen nicht zur Verfügung!'
WHERE `name` LIKE 'DownloadsInfoAccessDenied'
AND `value` LIKE 'Dieser Download stehen Ihnen nicht zur Verfügung!';

