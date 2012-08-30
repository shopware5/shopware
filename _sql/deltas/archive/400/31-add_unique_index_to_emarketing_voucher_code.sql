ALTER TABLE `s_emarketing_voucher_codes` ADD UNIQUE `code` ( `code` );

-- //@UNDO
ALTER TABLE s_emarketing_voucher_codes DROP INDEX code;