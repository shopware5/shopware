-- //

ALTER TABLE  s_library_component_field ADD  store VARCHAR( 255 ) NOT NULL ,
ADD  display_field VARCHAR( 255 ) NOT NULL,
ADD  value_field VARCHAR( 255 ) NOT NULL;

-- //@UNDO

ALTER TABLE s_library_component_field
  DROP store,
  DROP display_field,
  DROP value_field;

--