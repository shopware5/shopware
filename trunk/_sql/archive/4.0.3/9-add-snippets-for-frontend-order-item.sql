-- SW-3826 - Adds snippets for orders in "my orders" frontend overview

-- //

INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoCompleted', 'Komplett abgeschlossen');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoPartiallyCompleted', 'Teilweise abgeschlossen');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoClarificationNeeded', 'Klärung notwendig');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoReadyForShipping', 'Zur Lieferung bereit');

INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoCompleted', 'Completed');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoPartiallyCompleted', 'Partially completed');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoClarificationNeeded', 'Clarification needed');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoReadyForShipping', 'Ready for shipping');

-- //@UNDO

-- //



