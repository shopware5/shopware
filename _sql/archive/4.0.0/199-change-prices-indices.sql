ALTER TABLE `s_articles_prices` DROP INDEX `pricegroup_2` ,
ADD INDEX `pricegroup_2` ( `pricegroup` , `from` , `articledetailsID` );

ALTER TABLE `s_articles_prices` DROP INDEX `pricegroup` ,
ADD INDEX `pricegroup` ( `pricegroup` , `to` , `articledetailsID` );

-- //@UNDO
ALTER TABLE `s_articles_prices` DROP INDEX `pricegroup_2` ,
ADD UNIQUE `pricegroup_2` ( `pricegroup` , `from` , `articledetailsID` );

ALTER TABLE `s_articles_prices` DROP INDEX `pricegroup` ,
ADD UNIQUE `pricegroup` ( `pricegroup` , `to` , `articledetailsID` );

