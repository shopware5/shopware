ALTER TABLE s_library_component ADD x_type VARCHAR( 255 ) NOT NULL AFTER name;
-- //@UNDO
ALTER TABLE s_library_component DROP x_type;