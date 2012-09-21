INSERT INTO `s_core_config_forms` (`name`, `label`, `description` , `plugin_id`)
SELECT p.name, p.label, IF(p.description='', NULL, p.description) as description, p.id
FROM  s_core_plugins p, s_core_plugin_elements pc
WHERE pc.pluginID=p.id
GROUP BY p.id;

INSERT INTO `s_core_config_elements` (
  `form_id`, `name`, `value`, `label`, `description`,
  `type`, `required`, `position`, `scope`,
  `filters`, `validators`, `options`
)
SELECT
  f.id as form_id, e.name,
  IF(e.value='', NULL, e.value) as `value`,
  e.label,
  IF(e.description='', NULL, e.description) as `description`,
  e.type, e.required, e.order, e.scope,
  e.filters, e.validators,
  IF(e.options IN ('', 'Array'), NULL, e.options) as `options`
FROM  s_core_plugin_elements e, s_core_config_forms f
WHERE f.plugin_id=e.pluginID;

INSERT INTO `s_core_config_values` (
  `element_id`, `shop_id`, `value`
)
SELECT fe.id, v.shopID, v.value
FROM s_core_plugin_configs v, s_core_plugin_elements e, s_core_config_forms f, s_core_config_elements fe
WHERE v.name=e.name
AND v.pluginID=e.pluginID
AND f.plugin_id=e.pluginID
AND fe.form_id=f.id
AND fe.name=e.name;