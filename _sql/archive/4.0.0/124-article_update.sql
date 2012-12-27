
UPDATE s_articles_similar SET relatedarticle = (SELECT articleID FROM s_articles_details where ordernumber = s_articles_similar.relatedarticle);
UPDATE s_articles_relationships SET relatedarticle = (SELECT articleID FROM s_articles_details where ordernumber = s_articles_relationships.relatedarticle);
ALTER TABLE s_articles_img CHANGE articleID articleID INT( 11 ) NULL DEFAULT NULL ;
UPDATE s_articles_img SET articleID = NULL WHERE articleID = 0;

ALTER TABLE s_articles CHANGE supplierID supplierID INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE taxID taxID INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE unitID unitID INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE pricegroupID pricegroupID INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE filtergroupID filtergroupID INT( 11 ) NULL DEFAULT NULL ;

UPDATE s_articles SET supplierID = NULL WHERE supplierID = 0;
UPDATE s_articles SET taxID = NULL WHERE taxID = 0;
UPDATE s_articles SET pricegroupID = NULL WHERE pricegroupID = 0;
UPDATE s_articles SET filtergroupID = NULL WHERE filtergroupID = 0;

UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Article'', { articleId: 0 });' WHERE s_core_menu.id =2;
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Article'');' WHERE s_core_menu.id =66;


-- //@UNDO
UPDATE s_articles_similar SET relatedarticle = (SELECT ordernumber FROM s_articles_details where articleID = s_articles_similar.relatedarticle);
UPDATE s_articles_relationships SET relatedarticle = (SELECT ordernumber FROM s_articles_details where articleID = s_articles_relationships.relatedarticle);
ALTER TABLE s_articles_img CHANGE articleID articleID INT( 11 ) NOT NULL DEFAULT '0'
UPDATE s_articles_img SET articleID = 0 WHERE articleID IS NULL;

ALTER TABLE s_articles CHANGE supplierID supplierID INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE taxID taxID INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE unitID unitID INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE pricegroupID pricegroupID INT( 11 ) UNSIGNED NOT NULL ,
CHANGE filtergroupID filtergroupID INT( 11 ) NOT NULL ;

UPDATE s_articles SET supplierID = 0 WHERE supplierID IS NULL;
UPDATE s_articles SET taxID = 0 WHERE taxID IS NULL;
UPDATE s_articles SET pricegroupID = 0 WHERE pricegroupID IS NULL;
UPDATE s_articles SET filtergroupID = 0 WHERE filtergroupID IS NULL;


