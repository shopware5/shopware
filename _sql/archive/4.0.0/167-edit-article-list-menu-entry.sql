Update `s_core_menu`
SET `controller` = 'ArticleList', `action` = NULL
WHERE `controller` LIKE 'Article'
AND `action` LIKE 'List';
-- //@UNDO
