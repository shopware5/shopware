-- //

ALTER TABLE `s_core_plugins` ADD `update_source` VARCHAR( 255 ) NULL ,
ADD `update_version` VARCHAR( 255 ) NULL;

-- //@UNDO

ALTER TABLE `s_core_plugins`
  DROP `update_source`,
  DROP `update_version`;

-- //