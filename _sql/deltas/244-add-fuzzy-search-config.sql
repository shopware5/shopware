-- //

DELETE FROM `s_core_shops` WHERE id=0;
ALTER TABLE `s_core_shops` CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- //@UNDO

-- //
