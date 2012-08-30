-- //

SET @parentID = (SELECT id FROM s_core_config_forms WHERE label='Shopeinstellungen');
INSERT INTO `s_core_config_forms` (
`parent_id` ,
`name` ,
`label` ,
`description` ,
`position` ,
`scope` ,
`plugin_id`
)
VALUES (
@parentID, 'Document', 'PDF-Belegerstellung', NULL , '90', '0', NULL
);

-- //@UNDO

SET @parentID = (SELECT id FROM s_core_config_forms WHERE label='Shopeinstellungen');
DELETE FROM s_core_config_forms WHERE name = 'Document' AND parent_id = @parentID;

--