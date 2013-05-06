-- //

UPDATE `s_core_snippets` 
SET `value` = 'Nachdem Sie die erste Bestellung durchgeführt haben, können Sie hier auf vorherige Rechnungsadressen zugreifen.'
WHERE `name` LIKE 'SelectBillingInfoEmpty' 
AND `value` LIKE 'Nachdem Sie die erste Bestellung durchgef?hrt haben, k?nnen Sie hier auf vorherige Rechnungsadressen zugreifen.';

-- //@UNDO

-- //
