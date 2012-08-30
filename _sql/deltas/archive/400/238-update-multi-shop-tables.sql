-- //

-- Create new tables
CREATE TABLE IF NOT EXISTS `s_core_shops` (
  `id` int(11) unsigned NOT NULL,
  `main_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) NOT NULL,
  `host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `base_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hosts` text COLLATE utf8_unicode_ci NOT NULL,
  `secure` int(1) unsigned NOT NULL,
  `secure_host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `secure_base_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `template_id` int(11) unsigned DEFAULT NULL,
  `document_template_id` int(11) unsigned DEFAULT NULL,
  `category_id` int(11) unsigned DEFAULT NULL,
  `locale_id` int(11) unsigned DEFAULT NULL,
  `currency_id` int(11) unsigned DEFAULT NULL,
  `customer_group_id` int(11) unsigned DEFAULT NULL,
  `fallback_id` int(11) unsigned DEFAULT NULL,
  `default` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `main_id` (`main_id`),
  KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `s_core_shop_currencies` (
  `shop_id` int(11) unsigned NOT NULL,
  `currency_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`shop_id`,`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Add default templates
INSERT IGNORE INTO `s_core_templates` (`id`, `template`, `name`, `description`, `author`, `license`, `esi`, `style_support`, `emotion`) VALUES
(1, 'gray', 'Gray', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(2, 'clean', 'Clean', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(3, 'turquoise', 'Turquoise', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(4, 'orange', 'Orange', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(5, 'green', 'Green', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(6, 'red', 'Red', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(7, 'blue', 'Blue', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(8, 'dark', 'Dark', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(9, 'gradient', 'Gradient', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(10, 'pink', 'Pink', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(11, 'emotion', 'Emotion', NULL, 'shopware AG', 'AGPL', 1, 0, 1),
(12, 'black', 'Black', NULL, 'shopware AG', 'AGPL', 0, 0, 0),
(13, 'brown', 'Brown', NULL, 'shopware AG', 'AGPL', 0, 0, 0);


-- Import old shops
INSERT INTO `s_core_shops` (`id`, `name`, `host`, `hosts`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `default`, `fallback_id`)
SELECT
	id, name,
	IF(`domainaliase`='', NULL, TRIM(SUBSTRING_INDEX(`domainaliase`, '\n', 1))) as host,
	IF(`domainaliase`='', NULL, domainaliase) as hosts,
	IFNULL((SELECT id FROM s_core_templates WHERE m.template LIKE CONCAT('%', template)), 11) as `template_id`,
    IFNULL((SELECT id FROM s_core_templates WHERE m.doc_template LIKE CONCAT('%', template)), 11) as `document_template_id`,
	parentID as `category_id`,
	locale as `locale_id`,
	defaultcurrency as `currency_id`,
	(SELECT id FROM s_core_customergroups WHERE groupkey=defaultcustomergroup) as `customer_group_id`,
	`default`,
    IF(fallback=0, NULL, fallback) as `fallback_id`
FROM s_core_multilanguage m;

UPDATE `s_core_shops` SET `base_path` = NULL, `secure_base_path` = NULL;

-- Import shop currencies
INSERT INTO `s_core_shop_currencies`
SELECT m.id, c.id
FROM s_core_multilanguage m
JOIN s_core_currencies c
ON c.id = m.switchCurrencies
OR m.switchCurrencies LIKE CONCAT(c.id, '|%')
OR m.switchCurrencies LIKE CONCAT('%|', c.id)
OR m.switchCurrencies LIKE CONCAT('%|', c.id, '|%');

-- Fix old iso codes
UPDATE s_core_translations t, s_core_multilanguage m
SET t.objectlanguage=m.id
WHERE t.objectlanguage=m.isocode;
UPDATE s_core_multilanguage m
SET m.isocode=m.id;

-- //@UNDO

DROP TABLE IF EXISTS `s_core_shops`;
DROP TABLE IF EXISTS `s_core_shop_currencies`;

-- //
