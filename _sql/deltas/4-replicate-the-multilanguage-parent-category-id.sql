-- //

UPDATE s_core_multilanguage as m, s_core_shops as s SET m.parentID=s.category_id WHERE m.id = s.id;

-- //@UNDO

-- //


