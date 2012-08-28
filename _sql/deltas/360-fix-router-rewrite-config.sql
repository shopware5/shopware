-- //

DELETE FROM `s_core_config_elements` WHERE `name` IN ('routerusemodrewrite', 'redirectbasefile');
UPDATE s_core_config_elements SET value=NULL WHERE name='seostaticurls';

-- //@UNDO


-- //