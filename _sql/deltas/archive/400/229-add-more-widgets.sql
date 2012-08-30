-- //
INSERT INTO `s_core_widgets` (`id`, `name`, `label`)
VALUES (NULL, 'swag-last-orders-widget', 'Letzte Bestellungen'), (NULL, 'swag-notice-widget', 'Notizzettel'), (NULL, 'swag-merchant-widget', 'Händlerfreischaltung');

-- //@UNDO

DELETE FROM `s_core_widgets` WHERE `name` = 'swag-last-orders-widget';
DELETE FROM `s_core_widgets` WHERE `name` = 'swag-notice-widget';
DELETE FROM `s_core_widgets` WHERE `name` = 'swag-merchant-widget';

-- //