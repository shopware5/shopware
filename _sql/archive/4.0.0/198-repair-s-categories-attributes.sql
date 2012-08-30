-- //
ALTER TABLE s_categories_attributes DROP testIT;
-- //@UNDO
ALTER TABLE s_categories_attributes ADD testIT VARCHAR(10) NULL DEFAULT NULL;
-- //
