-- //
ALTER TABLE `s_emotion` ADD `landingpage_block` VARCHAR( 255 ) NOT NULL AFTER `is_landingpage`;
-- //@UNDO
ALTER TABLE `s_emotion` DROP `landingpage_block`;
--