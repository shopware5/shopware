-- //

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'SEO/Router-Einstellungen');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'preferBasePath', 'b:1;', 'Shopware-Kernel aus URL entfernen ', 'Entfernt "shopware.php" aus URLs. Verhindert, dass Suchmaschinen fälschlicherweise DuplicateContent im Shop erkennen. Wenn kein ModRewrite zur Verfügung steht, muss dieses Häcken entfernt werden.', 'boolean', 1, 0, 0, NULL, NULL, NULL);

-- //@UNDO

-- //
