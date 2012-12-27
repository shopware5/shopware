-- //

ALTER TABLE `s_library_component_field` ADD `default_value` VARCHAR( 255 ) NOT NULL ,
ADD `allow_blank` INT( 1 ) NOT NULL;

-- //@UNDO

ALTER TABLE `s_library_component_field` DROP `allow_blank`;
ALTER TABLE `s_library_component_field` DROP `default_value`;

--