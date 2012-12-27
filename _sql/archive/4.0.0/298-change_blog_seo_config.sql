-- //

UPDATE `s_core_config_elements`
SET `value` = 's:71:"{sCategoryPath categoryID=$blogArticle.categoryId}/{$blogArticle.title}";'
WHERE `name` = 'routerblogtemplate';


-- //@UNDO


UPDATE `s_core_config_elements`
SET `value` = 's:20:"{$blogArticle.title}";'
WHERE `name` = 'routerblogtemplate';

--