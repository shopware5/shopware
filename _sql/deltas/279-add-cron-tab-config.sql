-- //

INSERT INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, 78, 'CronJob', 'Cronjobs', NULL, 50, 0, NULL);

-- //@UNDO

DELETE FROM `s_core_config_forms` WHERE name = 'CronJob';

-- //