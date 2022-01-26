INSERT INTO s_content_types (internalName, name, source, config) VALUES
('testcontent', 'testContent', null, '{"internalName":"testcontent","name":"testcontent","fields":[{"name":"testcontent","type":"text","label":"testContent","showListing":true,"searchAble":true,"translatable":true,"helpText":"","description":"","custom":[],"options":[],"flags":[],"store":null,"required":false}],"fieldSets":[{"label":null,"fields":[{"name":"testcontent","type":"text","label":"testContent","showListing":true,"searchAble":true,"translatable":true,"helpText":"","description":"","custom":[],"options":[],"flags":[],"store":null,"required":false}],"options":[]}],"menuIcon":"sprite-application-block","menuPosition":200,"menuParent":"Content","source":null,"custom":null,"showInFrontend":false,"viewTitleFieldName":"","viewDescriptionFieldName":"","viewImageFieldName":"","viewMetaTitleFieldName":"","viewMetaDescriptionFieldName":"","seoUrlTemplate":"{$type.name}\\/{$item[$type.viewTitleFieldName]}","seoRobots":"index,follow"}');

create table s_custom_testcontent
(
    id          int unsigned auto_increment primary key,
    testcontent varchar(255) null,
    created_at  datetime     not null,
    updated_at  datetime     not null
);

INSERT INTO s_custom_testcontent (id, testcontent, created_at, updated_at) VALUES
 (1, 'testContent', '2022-01-21 12:55:21', '2022-01-21 12:55:21')
,(2, 'testContent2', '2022-01-21 12:55:28', '2022-01-21 12:55:28')
,(3, 'testContent3', '2022-01-21 12:55:37', '2022-01-21 12:55:37')
,(4, 'testContent4', '2022-01-21 12:55:43', '2022-01-21 12:55:43')
,(5, 'testContent5', '2022-01-21 12:55:49', '2022-01-21 12:55:49')
,(6, 'testContent6', '2022-01-21 12:55:56', '2022-01-21 12:55:56')
;
