-- //
SET @help_parent = (SELECT `id` FROM `s_core_menu` WHERE `class` LIKE 'ico question_frame');
DELETE FROM s_core_menu WHERE name = 'Lizenz*' AND parent = @help_parent;
DELETE FROM s_core_menu WHERE name = 'Shopware Account' AND parent = @help_parent;

-- //@UNDO

SET @help_parent = (SELECT `id` FROM `s_core_menu` WHERE `class` LIKE 'ico question_frame');
INSERT INTO `s_core_menu` VALUES(NULL, 40, '', 'Lizenz*', "loadSkeleton('lizenz')", 'background-position: 5px 5px;', 'ico2 key', 0, 1, NULL, NULL);
INSERT INTO `s_core_menu` VALUES(NULL, 40, '', 'Über Shopware', "window.Growl('{release}<br />(c)2010-2011 shopware AG');", 'background-position: 5px 5px;', 'ico2 information_frame', 0, 1, NULL, NULL);

--