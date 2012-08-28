ALTER TABLE `s_media` CHANGE `memory_size` `file_size` INT UNSIGNED NOT NULL;

-- //@UNDO
ALTER TABLE `s_media` CHANGE `file_size` `memory_size` DECIMAL( 14, 2 ) NOT NULL;
