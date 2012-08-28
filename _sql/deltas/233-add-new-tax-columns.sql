-- //
ALTER TABLE `s_order_basket` ADD `tax_rate` DOUBLE NOT NULL AFTER `netprice`;
ALTER TABLE `s_order_details` ADD `tax_rate` DOUBLE NOT NULL AFTER `taxID`;
-- //@UNDO
ALTER TABLE `s_order_basket` DROP `tax_rate`;
ALTER TABLE `s_order_details` DROP `tax_rate`;
-- //