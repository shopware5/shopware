-- Reverts one of the changes done in Migrations_Migration1607 so it can be executed again.
SET @commentArticleElementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'commentArticle');

UPDATE `s_core_config_elements`
SET `name` = 'commentVoucherArticle'
WHERE `id` = @commentArticleElementId;

DELETE FROM `s_core_config_values`;
