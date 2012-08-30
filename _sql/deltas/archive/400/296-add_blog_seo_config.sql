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
    NULL , '249', 'routerblogtemplate', 's:20:"{$blogArticle.title}";', 'SEO-Urls Blog-Template', NULL , 'text', '0', '0', '1', NULL , NULL , NULL
);

-- //@UNDO

DELETE FROM `s_core_config_elements` WHERE `name` = 'routerblogtemplate';


--