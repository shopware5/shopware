-- //

CREATE TABLE IF NOT EXISTS `s_core_widgets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `s_core_widget_views` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `widget_id` int(11) unsigned NOT NULL,
  `auth_id` int(11) unsigned NOT NULL,
  `column` int(11) unsigned NOT NULL,
  `position` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `widget_id` (`widget_id`,`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT IGNORE INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, 78, 'Widget', 'Widgets', NULL, 30, 0, NULL);

ALTER TABLE `s_core_widget_views` ADD `label` VARCHAR( 255 ) NOT NULL AFTER `auth_id` ;

-- //@UNDO

DROP TABLE IF EXISTS `s_core_widgets`;
DROP TABLE IF EXISTS `s_core_widget_views`;

-- //