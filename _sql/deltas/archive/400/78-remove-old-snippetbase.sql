INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL , 'frontend/basket/internalMessages', 1, 1, 'VoucherMinimumCharge', 'Der Mindestumsatz für diesen Gutschein beträgt {sMinimumCharge} ', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureSupplier', 'Dieser Gutschein ist nur für Produkte von {sSupplier} gültig', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureProducts', 'Dieser Gutschein ist nur für bestimmte Produkte gültig.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureCustomerGroup', 'Dieser Gutschein ist für Ihre Kundengruppe nicht verfügbar', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureOnlyOnes', 'Pro Bestellung kann nur ein Gutschein eingelöst werden', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureNotFound', 'Gutschein konnte nicht gefunden werden oder ist nicht mehr gültig', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(NULL, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureAlreadyUsed', 'Dieser Gutschein wurde bereits bei einer vorherigen Bestellung eingelöst', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- //@UNDO