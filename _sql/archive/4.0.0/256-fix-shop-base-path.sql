-- //

SET @value = (SELECT `value` FROM `s_core_config` WHERE `name` LIKE 'sHOST');
SET @value = (SELECT REPLACE(`value`, @value, '') FROM `s_core_config` WHERE `name` LIKE 'sBASEPATH');
UPDATE `s_core_shops` SET `base_path` = NULL WHERE `base_path` = '';
UPDATE `s_core_shops` SET `base_path` = TRIM(@value) WHERE `base_path` IS NULL AND `main_id` IS NULL;
UPDATE `s_core_shops` SET `default` = IF(`id`=1, 1, 0);
UPDATE `s_core_shops` SET `base_path` = NULL WHERE `base_path` = '';

-- //@UNDO


--