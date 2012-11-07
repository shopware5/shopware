UPDATE `s_core_config_mails` SET `mailtype` = '2';
UPDATE `s_core_config_mails` SET `mailtype` = '3' WHERE `s_core_config_mails`.`name` LIKE 'sORDERSTATEMAIL%';
UPDATE `s_core_config_mails` set stateID = SUBSTRING(`name` , 16) WHERE name LIKE "sORDERSTATEMAIL%";
