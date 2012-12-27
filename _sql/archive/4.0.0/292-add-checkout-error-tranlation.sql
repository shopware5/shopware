-- //

UPDATE `s_core_snippets` SET `localeID`=2 WHERE `name`='CheckoutArticleNotFound';

INSERT INTO `s_core_snippets` (`shopID` ,`localeID` ,`name` ,`value`)
VALUES ( 1,  1,  'CheckoutArticleNotFound',  'Das Produkt konnte nicht gefunden werden.');

-- //@UNDO

-- //