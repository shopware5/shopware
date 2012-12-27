/**
 * Insert sql queries for shopware 3.5.6
 */
 
SET NAMES 'latin1';

/*
 * No Ticket - Update version info
 *
 * @autho   Heiner Lohaus
 * @since   3.5.6 - 2012/01/13
 */
UPDATE `s_core_config` SET `value` = '3.5.6' WHERE `name` = 'sVERSION';
INSERT IGNORE INTO `s_core_config` (`group`, `name`, `value`)
VALUES (0, 'sREVISION', '8259')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);


/*
 * @author  Heiner Lohaus
 * @since   3.5.6 - 2012/01/13
 * @ticket  #6789
 */
ALTER TABLE `s_order_billingaddress` ADD UNIQUE (
	`orderID`
);
ALTER TABLE `s_order_shippingaddress` ADD UNIQUE (
	`orderID`
);

/*
 * @author  Stefan Hamann
 * @since   3.5.6 - 2012/01/13
 * @ticket  #6593
 */
ALTER TABLE `s_emarketing_lastarticles` ADD INDEX ( `sessionID` );
