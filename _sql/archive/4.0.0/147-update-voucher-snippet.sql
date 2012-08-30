UPDATE `s_core_snippets` SET `name` = 'VoucherFailureMinimumCharge' WHERE `name` = 'VoucherMinimumCharge' AND namespace = 'frontend/basket/internalMessages';
-- //@UNDO
UPDATE `s_core_snippets` SET `name` = 'VoucherMinimumCharge' WHERE `name` = 'VoucherFailureMinimumCharge' AND namespace = 'frontend/basket/internalMessages';