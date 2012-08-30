-- //


UPDATE `s_core_config_elements`
SET `value` = NULL, `type` = 'datetime', `scope` = '1'
WHERE `s_core_config_elements`.`id` =658;

DELETE FROM `s_core_config_values` WHERE `element_id` =658;

UPDATE `s_core_config_elements`
SET `value` = 'b:1;'
WHERE `type` LIKE 'boolean'
AND `value` = 's:1:"1";';

UPDATE `s_core_config_elements`
SET `value` = 'b:0;'
WHERE `type` LIKE 'boolean'
AND `value` = 's:1:"0";';

-- //@UNDO

-- //