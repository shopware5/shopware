ALTER TABLE `s_core_sessions`
  ADD COLUMN `lifetime` INT NULL AFTER `expiry`;


DROP INDEX articles_by_category_sort_release ON s_articles;
DROP INDEX articles_by_category_sort_name ON s_articles;
ALTER TABLE s_articles RENAME TO product;
ALTER TABLE product ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_uuid_uindex ON product (uuid);
CREATE INDEX product_by_category_sort_name ON product (name, id);
CREATE INDEX product_by_category_sort_release ON product (datum, id);
ALTER TABLE product
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;


ALTER TABLE s_article_configurator_dependencies RENAME TO product_configurator_dependency;
ALTER TABLE product_configurator_dependency ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_dependency_uuid_uindex ON product_configurator_dependency (uuid);
ALTER TABLE product_configurator_dependency
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_article_configurator_groups RENAME TO product_configurator_group;
ALTER TABLE product_configurator_group ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_group_uuid_uindex ON product_configurator_group (uuid);
ALTER TABLE product_configurator_group
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;


ALTER TABLE s_article_configurator_groups_attributes RENAME TO product_configurator_group_attribute;
ALTER TABLE product_configurator_group_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_group_attribute_uuid_uindex ON product_configurator_group_attribute (uuid);
ALTER TABLE product_configurator_group_attribute
    MODIFY COLUMN groupID INT(11) unsigned NOT NULL AFTER uuid;


ALTER TABLE s_article_configurator_option_relations RENAME TO product_configurator_option_relation;
ALTER TABLE product_configurator_option_relation ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_option_relation_uuid_uindex ON product_configurator_option_relation (uuid);
ALTER TABLE product_configurator_option_relation CHANGE article_id product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_configurator_option_relation
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_article_configurator_options RENAME TO product_configurator_option;
ALTER TABLE product_configurator_option ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_option_uuid_uindex ON product_configurator_option (uuid);
ALTER TABLE product_configurator_option
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_article_configurator_options_attributes RENAME TO product_configurator_option_attribute;
ALTER TABLE product_configurator_option_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_option_attribute_uuid_uindex ON product_configurator_option_attribute (uuid);
ALTER TABLE product_configurator_option_attribute
    MODIFY COLUMN optionID INT(11) unsigned NOT NULL AFTER uuid;

ALTER TABLE s_article_configurator_price_variations RENAME TO product_configurator_price_variation;
ALTER TABLE product_configurator_price_variation ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_price_variation_uuid_uindex ON product_configurator_price_variation (uuid);
ALTER TABLE product_configurator_price_variation
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_article_configurator_set_group_relations RENAME TO product_configurator_set_group_relation;
ALTER TABLE product_configurator_set_group_relation ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_set_group_relation_uuid_uindex ON product_configurator_set_group_relation (uuid);
ALTER TABLE product_configurator_set_group_relation
    MODIFY COLUMN group_id INT(11) unsigned NOT NULL DEFAULT '0' AFTER uuid;

ALTER TABLE s_article_configurator_set_option_relations RENAME TO product_configurator_set_option_relation;

ALTER TABLE s_article_configurator_sets RENAME TO product_configurator_set;
ALTER TABLE product_configurator_set ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_set_uuid_uindex ON product_configurator_set (uuid);
ALTER TABLE product_configurator_set
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_article_configurator_template_prices RENAME TO product_configurator_template_price;
ALTER TABLE product_configurator_template_price ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_template_price_uuid_uindex ON product_configurator_template_price (uuid);

ALTER TABLE s_article_configurator_template_prices_attributes RENAME TO product_configurator_template_price_attribute;
ALTER TABLE product_configurator_template_price_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_template_price_attribute_uuid_uindex ON product_configurator_template_price_attribute (uuid);

ALTER TABLE s_article_configurator_templates RENAME TO product_configurator_template;
ALTER TABLE product_configurator_template ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_template_uuid_uindex ON product_configurator_template (uuid);
ALTER TABLE product_configurator_template CHANGE article_id product_id INT(11) unsigned NOT NULL DEFAULT '0';
ALTER TABLE product_configurator_template
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_article_configurator_templates_attributes RENAME TO product_configurator_template_attribute;
ALTER TABLE product_configurator_template_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_configurator_template_attribute_uuid_uindex ON product_configurator_template_attribute (uuid);
ALTER TABLE product_configurator_template_attribute
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;
