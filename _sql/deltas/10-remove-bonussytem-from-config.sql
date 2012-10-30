-- //

SET @parent = (SELECT id FROM `s_core_config_elements` WHERE `name` LIKE 'bonusSystem');
DELETE FROM `s_core_config_values` WHERE `element_id` = @parent;
DELETE FROM `s_core_config_elements` WHERE `name` LIKE 'bonusSystem';

-- //@UNDO

-- //chi
