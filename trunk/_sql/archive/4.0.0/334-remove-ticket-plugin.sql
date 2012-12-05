--
SET @parent = (SELECT id FROM s_core_plugins WHERE name='Ticket');
DELETE FROM s_core_plugins WHERE id = @parent;
DELETE FROM s_core_plugin_configs WHERE pluginID = @parent;
DELETE FROM s_core_plugin_elements WHERE pluginID = @parent;
DELETE FROM s_core_subscribes WHERE pluginID = @parent;
SET @parent = (SELECT id FROM s_core_config_forms WHERE plugin_id=@parent);
DELETE FROM s_core_config_forms WHERE id = @parent;
DELETE FROM s_core_config_elements WHERE form_id = @parent;
-- //@UNDO

-- //