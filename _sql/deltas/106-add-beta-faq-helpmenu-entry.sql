-- //

SET @help_parent = (SELECT `id` FROM `s_core_menu` WHERE `class` LIKE 'ico question_frame');
INSERT INTO `s_core_menu` (`id` ,`parent` ,`hyperlink` ,`name` ,`onclick` ,`style` ,`class` ,`position` ,`active` ,`pluginID` ,`resourceID`)
VALUES (NULL , @help_parent, '', 'Beta-FAQ', 'window.open(''http://wiki.shopware.de'',''Shopware Wiki'',''width=1024,height=600,scrollbars=yes'')', '', 'sprite-book-question', '0', '1', NULL , NULL);

-- //@UNDO

SET @help_parent = (SELECT `id` FROM `s_core_menu` WHERE `class` LIKE 'ico question_frame');
DELETE FROM s_core_menu WHERE parent = @help_parent AND class LIKE 'sprite-book-question';

--