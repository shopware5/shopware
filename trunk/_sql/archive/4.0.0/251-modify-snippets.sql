-- //

UPDATE `s_core_snippets` SET `value` = 'Ihr Kundenkonto wurde deaktiviert, bitte wenden Sie sich zwecks Klärung persönlich an uns!' WHERE  `name` = 'LoginFailureActive'  AND namespace = 'frontend/account/internalMessages' AND localeID = (SELECT `id` FROM `s_core_locales` WHERE `locale` = 'de_DE');
UPDATE `s_core_snippets` SET `value` = 'Der Mindestumsatz für diesen Gutschein beträgt {sMinimumCharge} €' WHERE  `name` = 'VoucherFailureMinimumCharge'  AND namespace = 'frontend/basket/internalMessages' AND localeID = (SELECT `id` FROM `s_core_locales` WHERE `locale` = 'de_DE');

-- //@UNDO



-- //
