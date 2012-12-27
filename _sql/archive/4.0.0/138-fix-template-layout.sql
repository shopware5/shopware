ALTER TABLE `s_core_templates` CHANGE `style_assist` `style_support` TINYINT( 1 ) UNSIGNED NOT NULL;

-- //@UNDO

ALTER TABLE `s_core_templates` CHANGE  `style_support` `style_assist` TINYINT( 1 ) UNSIGNED NOT NULL;