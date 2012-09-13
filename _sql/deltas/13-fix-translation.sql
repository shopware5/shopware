-- //

UPDATE s_core_config_element_translations
SET `label` = REPLACE(label, 'article', 'product')
WHERE locale_id = 2;

UPDATE s_core_config_element_translations
SET `label` = REPLACE(label, 'Article', 'Product')
WHERE locale_id = 2;

UPDATE s_core_config_element_translations
SET `description` = REPLACE(description, 'article', 'product')
WHERE locale_id = 2;

UPDATE s_core_config_element_translations
SET `description` = REPLACE(description, 'Article', 'Product')
WHERE locale_id = 2;



UPDATE s_core_config_form_translations
SET `label` = REPLACE(label, 'article', 'product')
WHERE locale_id = 2;

UPDATE s_core_config_form_translations
SET `label` = REPLACE(label, 'Article', 'Product')
WHERE locale_id = 2;

UPDATE s_core_config_form_translations
SET `description` = REPLACE(description, 'article', 'product')
WHERE locale_id = 2;

UPDATE s_core_config_form_translations
SET `description` = REPLACE(description, 'Article', 'Product')
WHERE locale_id = 2;


-- //@UNDO


-- //