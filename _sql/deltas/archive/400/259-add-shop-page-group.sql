-- //

INSERT INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, 77, 'PageGroup', 'Shopseiten-Gruppen', NULL, 90, 0, NULL);

CREATE TABLE IF NOT EXISTS `s_cms_static_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) NOT NULL,
  `mapping_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mapping_id` (`mapping_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

INSERT INTO `s_cms_static_groups` (`id`, `name`, `key`, `active`, `mapping_id`) VALUES
(1, 'Links', 'gLeft', 1, NULL),
(2, 'Unten (Spalte 1)', 'gBottom', 1, NULL),
(3, 'Unten (Spalte 2)', 'gBottom2', 1, NULL),
(4, 'In Bearbeitung', 'gDisabled', 0, NULL),
(7, 'Englisch links', 'eLeft', 1, 1),
(9, 'Englisch unten (Spalte 1)', 'eBottom', 1, 2),
(10, 'Englisch unten (Spalte 2)', 'eBottom2', 1, 3);

CREATE TABLE IF NOT EXISTS `s_core_shop_pages` (
  `shop_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`shop_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- //@UNDO

SET @parent = (SELECT id FROM s_core_config_forms WHERE name='PageGroup');
DELETE FROM s_core_config_forms WHERE id = @parent;
DROP TABLE IF EXISTS `s_cms_static_groups`;
DROP TABLE IF EXISTS `s_core_shop_pages`;

--