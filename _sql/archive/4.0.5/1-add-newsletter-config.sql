-- //

SET @help_parent = (SELECT id FROM s_core_config_forms WHERE name='Other');

INSERT IGNORE INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, @help_parent , 'Newsletter', 'Newsletter', NULL, 0, 0, NULL);

SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Newsletter');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'MailCampaignsPerCall', 'i:1000;', 'Anzahl der Mails, die pro Cronjob-Aufruf versendet werden', NULL, 'number', 1, 0, 0, NULL, NULL, NULL);

-- //@UNDO

-- //
