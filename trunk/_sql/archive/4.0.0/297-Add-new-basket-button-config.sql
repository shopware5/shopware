-- //

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `name` LIKE 'Checkout');

UPDATE `s_core_config_elements` SET `value` = 's:7:"#f5f5f5";', `type` = 'color' WHERE `name` = 'basketHeaderColor';
UPDATE `s_core_config_elements` SET `value` = 's:4:"#000";', `type` = 'color' WHERE `name` = 'basketHeaderFontColor';
UPDATE `s_core_config_elements` SET `value` = 's:7:"#f5f5f5";', `type` = 'color' WHERE `name` = 'basketTableColor';

INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @parent, 'detailModal', 'b:1;', 'Artikeldetails in Modalbox anzeigen', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL);

UPDATE `s_core_config_elements` SET `position` = 1 WHERE `type` != 'boolean' AND `form_id`=@parent;
UPDATE `s_core_config_elements` SET `description` = NULL WHERE `type` != 'color' AND `form_id`=@parent;

-- //@UNDO


--