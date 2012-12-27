
SET @parent = (SELECT id FROM s_core_plugins WHERE name='Locale');
DELETE FROM `s_core_plugin_configs` WHERE `pluginID` =  @parent;
INSERT INTO `s_core_plugin_configs` (`name`, `value`, `pluginID`, `localeID`, `shopID`) VALUES
( 'locales', 's:11:"de_DE,en_GB";', @parent, 1, 1);

SET @parent = (SELECT id FROM s_core_plugins WHERE name='HttpCache');
DELETE FROM `s_core_plugin_configs` WHERE `pluginID` =  @parent;
INSERT INTO `s_core_plugin_configs` (`name`, `value`, `pluginID`, `localeID`, `shopID`) VALUES
('cacheControllers', 's:201:"frontend/listing 300\nfrontend/index 300\nfrontend/detail 300\nfrontend/campaign 600\nwidgets/listing 300\nfrontend/custom 600\nfrontend/sitemap 600\nwidgets/index 300\nwidgets/checkout 300\nwidgets/compare 30\n";', @parent, 1, 1),
('noCacheControllers', 's:98:"frontend/checkout checkout\nfrontend/note checkout\nfrontend/detail detail\nfrontend/compare compare\n";', @parent, 1, 1),
('proxy', 's:0:"";', @parent, 1, 1),
('admin', 's:1:"1";', @parent, 1, 1);

-- //@UNDO

