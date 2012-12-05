-- //

INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(147, 'blogdetailtemplates', 0x733a31303a223a5374616e646172643b223b, 'Verfügbare Templates Blog-Detailseite', NULL, 'textarea', 0, 0, 0, NULL, NULL, NULL);

ALTER TABLE `s_blog` ADD `template` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- //@UNDO

DELETE FROM `s_core_config_elements` WHERE `name` = 'blogdetailtemplates';

ALTER TABLE `s_blog` DROP `template`;

-- //
