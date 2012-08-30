ALTER TABLE `s_core_templates` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
CHANGE `basename` `template` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `name` `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `description` `description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `author` `author` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `license` `license` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `esi_compatible` `esi` TINYINT( 1 ) UNSIGNED NOT NULL ,
CHANGE `style_assist_compatible` `style_assist` TINYINT( 1 ) UNSIGNED NOT NULL ,
CHANGE `emotions_compatible` `emotion` TINYINT( 1 ) UNSIGNED NOT NULL;
TRUNCATE TABLE `s_core_templates`;

-- //@UNDO

ALTER TABLE `s_core_templates` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
CHANGE `template` `basename` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `esi` `esi_compatible` TINYINT( 1 ) UNSIGNED NOT NULL ,
CHANGE `style_assist` `style_assist_compatible` TINYINT( 1 ) UNSIGNED NOT NULL ,
CHANGE `emotion` `emotions_compatible` TINYINT( 1 ) UNSIGNED NOT NULL;
