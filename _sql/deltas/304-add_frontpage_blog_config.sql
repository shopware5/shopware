-- //

INSERT INTO `s_core_config_elements` (
`id` ,
`form_id` ,
`name` ,
`value` ,
`label` ,
`description` ,
`type` ,
`required` ,
`position` ,
`scope` ,
`filters` ,
`validators` ,
`options`
)
VALUES (
NULL , '144', 'blogcategory', 's:0:"";', 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen', '', 'text', '0', '0', '1', NULL , NULL , NULL
);

INSERT INTO `s_core_config_elements` (
`id` ,
`form_id` ,
`name` ,
`value` ,
`label` ,
`description` ,
`type` ,
`required` ,
`position` ,
`scope` ,
`filters` ,
`validators` ,
`options`
)
VALUES (
NULL , '144', 'bloglimit', 's:1:"3";', 'Anzahl Blog-Einträge auf Startseite', '', 'text', '0', '0', '1', NULL , NULL , NULL
);



-- //@UNDO

DELETE FROM `s_core_config_elements` WHERE name= 'blogcategory';
DELETE FROM `s_core_config_elements` WHERE name= 'bloglimit';

--