-- //

DELETE FROM `s_core_menu` WHERE `s_core_menu`.`name` LIKE 'Fenster';
DELETE FROM `s_core_menu` WHERE `s_core_menu`.`name` LIKE 'Nebeneinander';
DELETE FROM `s_core_menu` WHERE `s_core_menu`.`name` LIKE 'Untereinander';
DELETE FROM `s_core_menu` WHERE `s_core_menu`.`name` LIKE 'Alle schliessen';
DELETE FROM `s_core_menu` WHERE `s_core_menu`.`name` LIKE 'Alle minimie.';
UPDATE `s_core_menu` SET  `name` =  '' WHERE  `s_core_menu`.`name` LIKE 'Hilfe';

-- //@UNDO

INSERT INTO `s_core_menu` VALUES(null, 0, '', 'Fenster', '', '', 'ico window', 0, 1, NULL);
SET @menu_parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Fenster');
INSERT INTO `s_core_menu` VALUES(null, @menu_parent, '', 'Nebeneinander', 'sWindows._groupHorizontal();', 'background-position: 5px 5px;', 'ico2 application_tile_horizontal', 0, 1, NULL);
INSERT INTO `s_core_menu` VALUES(null, @menu_parent, '', 'Untereinander', 'sWindows._groupVertical();', 'background-position: 5px 5px;', 'ico2 application_tile_vertical', 0, 1, NULL);
INSERT INTO `s_core_menu` VALUES(null, @menu_parent, '', 'Alle schliessen', 'sWindows._closeAll();', 'background-position: 5px 5px;', 'ico2 schliessen', 0, 1, NULL);
INSERT INTO `s_core_menu` VALUES(null, @menu_parent, '', 'Alle minimie.', 'sWindows._minAll();', 'background-position: 5px 5px;', 'ico2 minimieren', 0, 1, NULL);
UPDATE `s_core_menu` SET  `name` =  'Hilfe' WHERE  `s_core_menu`.`name` LIKE '' AND `s_core_menu`.`class` LIKE 'ico question_frame';

-- //