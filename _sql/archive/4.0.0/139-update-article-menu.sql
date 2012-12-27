UPDATE s_core_menu SET onclick = '' WHERE s_core_menu.id =66;
UPDATE s_core_menu SET onclick = '' WHERE s_core_menu.id =2;

-- //@UNDO

UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Article'', { articleId: 0 });' WHERE s_core_menu.id =2;
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Article'');' WHERE s_core_menu.id =66;
