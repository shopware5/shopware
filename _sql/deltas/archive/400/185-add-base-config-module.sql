
CREATE TABLE IF NOT EXISTS `s_core_config_forms` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NULL,
  `description` text COLLATE utf8_unicode_ci NULL,
  `position` INT( 11 ) NOT NULL,
  `plugin_id` int(11) unsigned NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `plugin_id` (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE `s_core_config_forms`
  ADD CONSTRAINT `s_core_config_forms_ibfk_1`
  FOREIGN KEY (`plugin_id`)
  REFERENCES `s_core_plugins` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `s_core_config_forms` (`name`, `label`, `description` , `plugin_id`)
SELECT p.name, p.label, IF(p.description='', NULL, p.description) as description, p.id
FROM  s_core_plugins p, s_core_plugin_elements pc
WHERE pc.pluginID=p.id
GROUP BY p.id;

CREATE TABLE IF NOT EXISTS `s_core_config_elements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `form_id` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` blob NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `required` int(1) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  `scope` int(11) unsigned NOT NULL,
  `filters` blob NULL,
  `validators` blob NULL,
  `options` blob NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_id_2` (`form_id`,`name`),
  KEY `form_id` (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE `s_core_config_elements`
  ADD CONSTRAINT `s_core_config_elements_ibfk_1`
  FOREIGN KEY (`form_id`)
  REFERENCES `s_core_config_forms` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `s_core_config_elements` (
  `form_id`, `name`, `value`, `label`, `description`,
  `type`, `required`, `position`, `scope`,
  `filters`, `validators`, `options`
)
SELECT
  f.id as form_id, e.name,
  IF(e.value='', NULL, e.value) as `value`,
  e.label,
  IF(e.description='', NULL, e.description) as `description`,
  e.type, e.required, e.order, e.scope,
  e.filters, e.validators,
  IF(e.options IN ('', 'Array'), NULL, e.options) as `options`
FROM  s_core_plugin_elements e, s_core_config_forms f
WHERE f.plugin_id=e.pluginID;

CREATE TABLE IF NOT EXISTS `s_core_config_values` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(11) unsigned NOT NULL,
  `shop_id` int(11) unsigned DEFAULT NULL,
  `value` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE `s_core_config_values`
  ADD CONSTRAINT `s_core_config_values_ibfk_1`
  FOREIGN KEY (`element_id`)
  REFERENCES `s_core_config_elements` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `s_core_config_values` (
  `element_id`, `shop_id`, `value`
)
SELECT fe.id, v.shopID, v.value
FROM s_core_plugin_configs v, s_core_plugin_elements e, s_core_config_forms f, s_core_config_elements fe
WHERE v.name=e.name
AND v.pluginID=e.pluginID
AND f.plugin_id=e.pluginID
AND fe.form_id=f.id
AND fe.name=e.name;

ALTER TABLE `s_core_config_forms`
ADD `parent_id` INT( 11 ) UNSIGNED NULL AFTER `id` ,
ADD INDEX ( `parent_id` );

-- //@UNDO

DROP TABLE IF EXISTS `s_core_config_values`;
DROP TABLE IF EXISTS `s_core_config_elements`;
DROP TABLE IF EXISTS `s_core_config_values`;