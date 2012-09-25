
DELETE FROM `s_core_config_elements`
WHERE `name` IN ('revision', 'version');

UPDATE `s_core_config_elements` SET value = 'i:8;', `type` = 'number' WHERE name = 'chartrange';
UPDATE `s_core_config_elements` SET value = 's:8:"51,51,51";' WHERE name = 'captchaColor';
UPDATE `s_core_config_elements` SET value = 's:15:"Shopware 4 Demo";' WHERE name = 'shopName';
DELETE FROM `s_core_config_values` WHERE id < 56;