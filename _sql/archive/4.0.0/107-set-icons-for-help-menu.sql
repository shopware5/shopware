-- //

SET @help_parent = (SELECT `id` FROM `s_core_menu` WHERE `class` LIKE 'ico question_frame');
UPDATE `s_core_menu` SET `class` = "sprite-documents" WHERE `name` = "Zum Forum" AND parent = @help_parent;
UPDATE `s_core_menu` SET `class` = "sprite-lifebuoy" WHERE `name` = "Onlinehilfe aufrufen" AND parent = @help_parent;
UPDATE `s_core_menu` SET `class` = "sprite-shopware-logo" WHERE `name` = "Über Shopware" AND parent = @help_parent;

-- //@UNDO

SET @help_parent = (SELECT `id` FROM `s_core_menu` WHERE `class` LIKE 'ico question_frame');
UPDATE `s_core_menu` SET `class` = "ico2 book_open" WHERE `name` = "Zum Forum" AND parent = @help_parent;
UPDATE `s_core_menu` SET `class` = "ico2 book_open" WHERE `name` = "Onlinehilfe aufrufen" AND parent = @help_parent;
UPDATE `s_core_menu` SET `class` = "ico2 information_frame" WHERE `name` = "Über Shopware" AND parent = @help_parent;

--