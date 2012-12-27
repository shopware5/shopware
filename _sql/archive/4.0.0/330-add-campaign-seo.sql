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
`scope`
)
VALUES (
NULL , '249', 'routercampaigntemplate', 's:64:"{sCategoryPath categoryID=$campaign.categoryId}/{$campaign.name}";', 'SEO-Urls Blog-Template', NULL , 'text', '0', '0', '1'
);
-- //@UNDO


--