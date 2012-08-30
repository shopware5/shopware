UPDATE s_core_menu
SET `onclick` = 'openNewModule(''Shopware.apps.ArticleList'');'
WHERE `onclick` = 'openNewModule(''Shopware.apps.Article'', { controller: ''List'' });';
-- //@UNDO
UPDATE s_core_menu
SET `onclick` = 'openNewModule(''Shopware.apps.Article'', { controller: ''List'' });'
WHERE `onclick` = 'openNewModule(''Shopware.apps.ArticleList'');';
