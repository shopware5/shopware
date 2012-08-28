-- //

SET @settings_parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Einstellungen');
SET @snippets_parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Textbausteine' AND `parent` = @settings_parent);
DELETE FROM `s_core_menu` WHERE `parent` = @snippets_parent;
DELETE FROM `s_core_menu` WHERE `id` = @snippets_parent;

INSERT INTO `s_core_menu` VALUES(NULL, @settings_parent, '', 'Textbausteine', "openNewModule('Shopware.apps.Snippet');", 'background-position: 5px 5px;', 'sprite-edit', 0, 1, NULL, NULL);

-- //@UNDO

SET @settings_parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Einstellungen');
SET @snippets_parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Textbausteine' AND `parent` = @settings_parent);
DELETE FROM `s_core_menu` WHERE `id` = @snippets_parent;
INSERT INTO `s_core_menu` VALUES(NULL, @settings_parent, '', 'Textbausteine', '', 'background-position: 5px 5px;', 'sprite-edit', 0, 1, NULL, NULL);

SET @new_snippets_parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Textbausteine' AND `parent` = @settings_parent);
INSERT INTO `s_core_menu` VALUES(NULL, @new_snippets_parent, '', 'Neue Templatebasis', 'openNewModule(''Shopware.apps.Snippet'');', 'background-position: 5px 5px', 'ico2 plugin', 0, 1, NULL, 17);
INSERT INTO `s_core_menu` VALUES(NULL, @new_snippets_parent, '', 'Alte Templatebasis*', 'loadSkeleton(''snippets'')', 'background-position: 5px 5px', 'ico2 plugin', 1, 1, NULL, NULL);

-- //