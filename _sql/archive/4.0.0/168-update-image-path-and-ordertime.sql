ALTER TABLE s_order CHANGE ordertime ordertime DATETIME NULL DEFAULT NULL;
UPDATE s_order SET ordertime = NULL WHERE ordertime = '0000-00-0000-00-00';
UPDATE s_core_config SET value = '/media/image' WHERE s_core_config.id =10;
-- //@UNDO
ALTER TABLE s_order CHANGE ordertime ordertime DATETIME NOT NULL DEFAULT '0000-00-0000-00-00';
UPDATE s_order SET ordertime = '0000-00-0000-00-00' WHERE ordertime IS NULL;
UPDATE s_core_config SET value = '/images/articles' WHERE s_core_config.id =10;