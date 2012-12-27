--  //

ALTER TABLE `s_addon_premiums` CHANGE `pseudo_ordernumber` `ordernumber_export` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- //@UNDO

ALTER TABLE `s_addon_premiums` CHANGE `ordernumber_export` `pseudo_ordernumber` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- //