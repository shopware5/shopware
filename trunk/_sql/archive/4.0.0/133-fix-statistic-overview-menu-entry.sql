UPDATE `s_core_menu` SET `controller` = 'Overview', `action` = '' WHERE `class` LIKE 'sprite-report-paper';
-- //@UNDO
UPDATE `s_core_menu` SET `controller` = 'Article', `action` = 'List' WHERE `class` LIKE 'sprite-report-paper';
