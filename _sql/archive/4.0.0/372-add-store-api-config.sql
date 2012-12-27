-- //

SET @help_parent = (SELECT id FROM s_core_config_forms WHERE name = 'Other');

INSERT IGNORE INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, @help_parent , 'StoreApi', 'StoreApi', NULL, 0, 0, 45);

SET @help_parent2 = (SELECT id FROM s_core_config_forms WHERE name = 'StoreApi');

INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @help_parent2 , 'StoreApiUrl', 's:41:"http://store.shopware-preview.de/storeApi";', 'Store API Url', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d);


-- //@UNDO



-- //