UPDATE `s_core_config_elements`
SET `type` = 'interval', `value` = 'i:86400;'
WHERE `name` LIKE 'cache%'
AND `type` = 'text'
AND `value` LIKE '%86400%';

-- //@UNDO

UPDATE `s_core_config_elements`
SET `type` = 'text'
WHERE `type` = 'interval';