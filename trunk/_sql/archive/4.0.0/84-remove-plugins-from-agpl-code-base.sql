-- //

SET @couponPluginId = (SELECT id FROM `s_core_plugins` WHERE name = 'CouponsSelling');

DELETE FROM s_core_plugin_configs WHERE pluginID = @couponPluginId;
DELETE FROM s_core_plugin_elements WHERE pluginID = @couponPluginId;
DELETE FROM s_core_plugin_configs WHERE pluginID = @couponPluginId;
DELETE FROM s_core_subscribes WHERE pluginID = @couponPluginId;
DELETE FROM s_core_menu WHERE pluginID = @couponPluginId;

DROP TABLE IF EXISTS `s_plugin_coupons`;
DROP TABLE IF EXISTS `s_plugin_coupons_codes`;

SET @businessPluginId = (SELECT id FROM `s_core_plugins` WHERE name = 'BusinessEssentials');

DELETE FROM s_core_plugin_configs WHERE pluginID = @businessPluginId;
DELETE FROM s_core_plugin_elements WHERE pluginID = @businessPluginId;
DELETE FROM s_core_plugin_configs WHERE pluginID = @businessPluginId;
DELETE FROM s_core_subscribes WHERE pluginID = @businessPluginId;
DELETE FROM s_core_menu WHERE pluginID = @businessPluginId;

DROP TABLE IF EXISTS `s_core_plugins_b2b_cgsettings`;
DROP TABLE IF EXISTS `s_core_plugins_b2b_private`;
DROP TABLE IF EXISTS `s_core_plugins_b2b_tpl_config`;
DROP TABLE IF EXISTS `s_core_plugins_b2b_tpl_variables`;

SET @licensePluginId = (SELECT id FROM `s_core_plugins` WHERE name = 'License');

DELETE FROM s_core_plugin_configs WHERE pluginID = @licensePluginId;
DELETE FROM s_core_plugin_elements WHERE pluginID = @licensePluginId;
DELETE FROM s_core_plugin_configs WHERE pluginID = @licensePluginId;
DELETE FROM s_core_subscribes WHERE pluginID = @licensePluginId;
DELETE FROM s_core_menu WHERE pluginID = @licensePluginId;

-- //@UNDO

-- //