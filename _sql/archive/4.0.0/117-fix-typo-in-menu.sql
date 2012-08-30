-- //

UPDATE `s_core_menu` set `name` = 'Block Messages Demo', `onclick` = 'createBlockMessagesDemo()' WHERE `onclick` = 'createBlogMessagesDemo()';

-- //@UNDO

UPDATE `s_core_menu` set `name` = 'Blog Messages Demo', `onclick` = 'createBlogMessagesDemo()' WHERE `onclick` = 'createBlockMessagesDemo()';

--
