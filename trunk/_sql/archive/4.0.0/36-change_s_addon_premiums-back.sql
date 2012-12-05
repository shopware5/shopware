-- //
ALTER TABLE `s_addon_premiums` CHANGE `pseudo_id` `articleID` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `sub_shop_id` `subshopID` INT( 11 ) NOT NULL;

-- //@UNDO

ALTER TABLE `s_addon_premiums` CHANGE `articleID` `pseudo_id` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `subshopID` `sub_shop_id` INT( 11 ) NOT NULL;

UPDATE s_addon_premiums p, s_articles_details d SET p.article_detail_id = d.id
WHERE d.ordernumber = p.ordernumber;


-- //
