-- //
ALTER TABLE `s_addon_premiums` DROP `article_detail_id`;

ALTER TABLE `s_addon_premiums` CHANGE `articleID` `pseudo_ordernumber` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- //@UNDO
ALTER TABLE `s_addon_premiums` CHANGE `pseudo_ordernumber` `articleID` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE `s_addon_premiums` ADD `article_detail_id` INT NOT NULL;
-- //
