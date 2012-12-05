
CREATE TABLE IF NOT EXISTS `s_core_shops_new` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `main_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) NOT NULL,
  `host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `base_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `base_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `customer_scope` int(1) NOT NULL,
  `default` int(1) unsigned NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `main_id` (`main_id`),
  KEY `host` (`host`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT IGNORE INTO `s_core_shops_new` (
  `id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `hosts`, `secure`, `secure_host`, `secure_base_path`,
  `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`
)
SELECT
  `id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `hosts`, `secure`, `secure_host`, `secure_base_path`,
  `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`
FROM s_core_shops;

DROP TABLE IF EXISTS s_core_shops;
RENAME TABLE s_core_shops_new TO s_core_shops;

UPDATE `s_core_shops` SET `base_url` = `base_path` WHERE `base_path` IS NOT NULL AND `main_id` IS NOT NULL;
UPDATE `s_core_shops` SET `secure_base_path` = NULL, `secure_host` = NULL, `host` = NULL, `base_path` = NULL WHERE `main_id` IS NOT NULL;

-- //@UNDO

-- //
