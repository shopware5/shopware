-- //

SET @parent = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'blogcategory' AND `label` = 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen');

UPDATE `s_core_config_elements`
SET `label` = 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen (Nur alte Templatebasis)'
WHERE `id` = @parent;

UPDATE `s_core_config_element_translations`
SET `label` = 'Show blog entries from category (ID) on starting page (Only old template base)'
WHERE `id` = @parent;


-- //@UNDO

-- //


