-- //

SET @parent = (SELECT id FROM s_core_config_elements WHERE name='seoqueryalias');
DELETE FROM `s_core_config_values` WHERE element_id=@parent;

SET @parent = (SELECT id FROM s_core_config_elements WHERE name='routercategorytemplate');
DELETE FROM `s_core_config_values` WHERE element_id=@parent;


SET @parent = (SELECT id FROM s_core_config_elements WHERE name='seostaticurls');
DELETE FROM `s_core_config_values` WHERE element_id=@parent;

SET @parent = (SELECT id FROM s_core_config_elements WHERE name='routerlastupdate');
DELETE FROM `s_core_config_values` WHERE element_id=@parent;


UPDATE `s_core_config_elements` SET `value` = 's:41:"{sCategoryPath categoryID=$sCategory.id}/";' WHERE name='routercategorytemplate';
UPDATE `s_core_config_elements` SET `value` = 's:50:"sViewport=cat&sCategory={$sCategoryStart},listing/";' WHERE name='seostaticurls';
UPDATE `s_core_config_elements` SET `value` = 's:127:"sSearch=q,
sPage=p,
sPerPage=n,
sSupplier=s,
sFilterProperties=f,
sCategory=c,
sCoreId=u,
sTarget=t,
sValidation=v,
sTemplate=l";' WHERE name='seoqueryalias';

-- //@UNDO


-- //