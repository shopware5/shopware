-- //

ALTER TABLE `s_core_config_values` DROP FOREIGN KEY `s_core_config_values_ibfk_1` ;
ALTER TABLE `s_core_config_elements` DROP FOREIGN KEY `s_core_config_elements_ibfk_1` ;
ALTER TABLE `s_core_config_forms` DROP FOREIGN KEY `s_core_config_forms_ibfk_1` ;

DELETE FROM s_core_plugins WHERE name IN ('Paypal', 'HttpCache');

DELETE sb
FROM `s_core_subscribes` sb
LEFT JOIN `s_core_plugins` p
ON p.id=pluginID
WHERE p.id IS NULL;

DELETE sb
FROM `s_core_config_forms` sb
LEFT JOIN `s_core_plugins` p
ON p.id=plugin_id
WHERE p.id IS NULL AND sb.plugin_id IS NOT NULL;

DELETE sb
FROM `s_core_config_elements` sb
LEFT JOIN `s_core_config_forms` p
ON p.id=form_id
WHERE p.id IS NULL;

-- //@UNDO

-- //