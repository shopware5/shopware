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

ALTER TABLE s_article_img_mapping_rules RENAME TO product_img_mapping_rule;
ALTER TABLE product_img_mapping_rule ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_img_mapping_rule_uuid_uindex ON product_img_mapping_rule (uuid);
ALTER TABLE product_img_mapping_rule
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_article_img_mappings RENAME TO product_img_mapping;
ALTER TABLE product_img_mapping ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_img_mapping_uuid_uindex ON product_img_mapping (uuid);
ALTER TABLE product_img_mapping
    MODIFY COLUMN image_id INT(11) NOT NULL AFTER uuid;

ALTER TABLE s_articles_also_bought_ro RENAME TO product_also_bought_ro;
ALTER TABLE product_also_bought_ro CHANGE article_id product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_also_bought_ro CHANGE related_article_id related_product_id INT(11) NOT NULL;

ALTER TABLE s_articles_attributes RENAME TO product_attribute;
ALTER TABLE product_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_attribute_uuid_uindex ON product_attribute (uuid);
ALTER TABLE product_attribute CHANGE articledetailsID product_details_id INT(11) unsigned;
ALTER TABLE product_attribute CHANGE articleID product_id INT(11) unsigned;
ALTER TABLE product_attribute
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_avoid_customergroups RENAME TO product_avoid_customergroup;
ALTER TABLE product_avoid_customergroup CHANGE articleID product_id INT(11) NOT NULL;
ALTER TABLE product_avoid_customergroup CHANGE customergroupID customergroup_id INT(11) NOT NULL;

ALTER TABLE s_articles_categories RENAME TO product_category;
ALTER TABLE product_category CHANGE articleID product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_category CHANGE categoryID category_id INT(11) unsigned NOT NULL;

ALTER TABLE s_articles_categories_ro RENAME TO product_category_ro;
ALTER TABLE product_category_ro CHANGE articleID product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_category_ro CHANGE categoryID category_id INT(11) unsigned NOT NULL;
ALTER TABLE product_category_ro CHANGE parentCategoryID parent_category_id INT(11) unsigned NOT NULL;

ALTER TABLE s_articles_categories_seo RENAME TO product_category_seo;
ALTER TABLE product_category_seo CHANGE article_id product_id INT(11) NOT NULL;

DROP INDEX get_similar_articles ON s_articles_details;
DROP INDEX articles_by_category_sort_popularity ON s_articles_details;
DROP INDEX articleID ON s_articles_details;
ALTER TABLE s_articles_details RENAME TO product_detail;
ALTER TABLE product_detail ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_detail_uuid_uindex ON product_detail (uuid);
ALTER TABLE product_detail CHANGE articleID product_id INT(11) unsigned NOT NULL DEFAULT '0';
CREATE INDEX product_id ON product_detail (product_id);
CREATE INDEX product_by_category_sort_popularity ON product_detail (sales, product_id);
CREATE INDEX get_similar_products ON product_detail (kind, sales);
ALTER TABLE product_detail
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_downloads RENAME TO product_download;
ALTER TABLE product_download ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_download_uuid_uindex ON product_download (uuid);
ALTER TABLE product_download CHANGE articleID product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_download
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_downloads_attributes RENAME TO product_download_attribute;
ALTER TABLE product_download_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_download_attribute_uuid_uindex ON product_download_attribute (uuid);
ALTER TABLE product_download_attribute CHANGE downloadID download_id INT(11) unsigned;
ALTER TABLE product_download_attribute
    MODIFY COLUMN download_id INT(11) unsigned AFTER uuid;

ALTER TABLE s_articles_esd RENAME TO product_esd;
ALTER TABLE product_esd ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_esd_uuid_uindex ON product_esd (uuid);
ALTER TABLE product_esd CHANGE articledetailsID product_detail_id INT(11) NOT NULL DEFAULT '0';
ALTER TABLE product_esd CHANGE articleID product_id INT(11) NOT NULL DEFAULT '0';
ALTER TABLE product_esd
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_esd_attributes RENAME TO product_esd_attribute;
ALTER TABLE product_esd_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_esd_attribute_uuid_uindex ON product_esd_attribute (uuid);
ALTER TABLE product_esd_attribute CHANGE esdID esd_id INT(11);
ALTER TABLE product_esd_attribute
    MODIFY COLUMN esd_id INT(11) AFTER uuid;

ALTER TABLE s_articles_esd_serials RENAME TO product_esd_serial;
ALTER TABLE product_esd_serial ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_esd_serial_uuid_uindex ON product_esd_serial (uuid);
ALTER TABLE product_esd_serial CHANGE esdID esd_id INT(11) NOT NULL DEFAULT '0';
ALTER TABLE product_esd_serial
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

DROP INDEX article_images_query ON s_articles_img;
DROP INDEX article_detail_id ON s_articles_img;
DROP INDEX article_cover_image_query ON s_articles_img;
ALTER TABLE s_articles_img RENAME TO product_img;
ALTER TABLE product_img ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_img_uuid_uindex ON product_img (uuid);
ALTER TABLE product_img CHANGE articleID product_id INT(11);
CREATE INDEX product_cover_image_query ON product_img (product_id, main, position);
ALTER TABLE product_img CHANGE article_detail_id product_detail_id INT(10) unsigned;
CREATE INDEX product_detail_id ON product_img (product_detail_id);
CREATE INDEX product_image_query ON product_img (product_id, position);
ALTER TABLE product_img
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_img_attributes RENAME TO product_img_attribute;
ALTER TABLE product_img_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_img_attribute_uuid_uindex ON product_img_attribute (uuid);
ALTER TABLE product_img_attribute CHANGE imageID image_id INT(11);
ALTER TABLE product_img_attribute
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_information RENAME TO product_information;
ALTER TABLE product_information ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_information_uuid_uindex ON product_information (uuid);
ALTER TABLE product_information CHANGE articleID product_id INT(11) NOT NULL DEFAULT '0';
ALTER TABLE product_information
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_information_attributes RENAME TO product_information_attribute;
ALTER TABLE product_information_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_information_attribute_uuid_uindex ON product_information_attribute (uuid);
ALTER TABLE product_information_attribute CHANGE informationID information_id INT(11);
ALTER TABLE product_information_attribute
    MODIFY COLUMN information_id INT(11) AFTER uuid;

ALTER TABLE s_articles_notification RENAME TO product_notification;
ALTER TABLE product_notification ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_notification_uuid_uindex ON product_notification (uuid);
ALTER TABLE product_notification
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_prices RENAME TO product_price;
ALTER TABLE product_price ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_price_uuid_uindex ON product_price (uuid);
ALTER TABLE product_price CHANGE articledetailsID product_detail_id INT(11) NOT NULL DEFAULT '0';
ALTER TABLE product_price CHANGE articleID product_id INT(11) NOT NULL DEFAULT '0';
ALTER TABLE product_price
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_prices_attributes RENAME TO product_price_attribute;
ALTER TABLE product_price_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_price_attribute_uuid_uindex ON product_price_attribute (uuid);
ALTER TABLE product_price_attribute CHANGE priceID price_id INT(11) unsigned;
ALTER TABLE product_price_attribute
    MODIFY COLUMN price_id INT(11) unsigned AFTER uuid;

ALTER TABLE s_articles_relationships RENAME TO product_relationship;
ALTER TABLE product_relationship ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_relationship_uuid_uindex ON product_relationship (uuid);
ALTER TABLE product_relationship CHANGE relatedarticle related_product VARCHAR(30) NOT NULL;
ALTER TABLE product_relationship CHANGE articleID product_id INT(30) NOT NULL;
ALTER TABLE product_relationship
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_similar RENAME TO product_similar;
ALTER TABLE product_similar ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_similar_uuid_uindex ON product_similar (uuid);
ALTER TABLE product_similar CHANGE relatedarticle related_product VARCHAR(255) NOT NULL;
ALTER TABLE product_similar CHANGE articleID product_id INT(30) NOT NULL;
ALTER TABLE product_similar
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_similar_shown_ro RENAME TO product_similar_shown_ro;
ALTER TABLE product_similar_shown_ro ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_similar_shown_ro_uuid_uindex ON product_similar_shown_ro (uuid);
ALTER TABLE product_similar_shown_ro CHANGE related_article_id related_product_id INT(11) NOT NULL;
ALTER TABLE product_similar_shown_ro CHANGE article_id product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_similar_shown_ro
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_supplier RENAME TO product_supplier;
ALTER TABLE product_supplier ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_supplier_uuid_uindex ON product_supplier (uuid);
ALTER TABLE product_supplier
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE s_articles_supplier_attributes RENAME TO product_supplier_attribute;
ALTER TABLE product_supplier_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_supplier_attribute_uuid_uindex ON product_supplier_attribute (uuid);
ALTER TABLE product_supplier_attribute CHANGE supplierID supplier_id INT(11);
ALTER TABLE product_supplier_attribute
    MODIFY COLUMN supplier_id INT(11) AFTER uuid;

ALTER TABLE s_articles_top_seller_ro RENAME TO product_top_seller_ro;
ALTER TABLE product_top_seller_ro CHANGE article_id product_id INT(11) unsigned NOT NULL;

ALTER TABLE s_articles_translations RENAME TO product_translation;
ALTER TABLE product_translation CHANGE articleID product_id INT(11) NOT NULL;
ALTER TABLE product_translation CHANGE languageID language_id INT(11) NOT NULL;


ALTER TABLE s_articles_vote RENAME TO product_vote;
ALTER TABLE product_vote ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_vote_uuid_uindex ON product_vote (uuid);
ALTER TABLE product_vote CHANGE articleID product_id INT(11) NOT NULL;

ALTER TABLE `product`
    ALTER `name` DROP DEFAULT;
ALTER TABLE `product`
    CHANGE COLUMN `name` `title` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci' AFTER `supplierID`;

## supplier uuid updates
UPDATE product_supplier ps SET ps.uuid = CONCAT('SWAG-PRODUCT-SUPPLIER-UUID-', ps.id);
ALTER TABLE `product_supplier`
    ALTER `uuid` DROP DEFAULT;
ALTER TABLE `product_supplier`
    CHANGE COLUMN `uuid` `uuid` VARCHAR(42) NOT NULL COLLATE 'utf8_unicode_ci' AFTER `id`;

## product_detail
UPDATE product_detail pd SET pd.uuid = pd.ordernumber;
ALTER TABLE `product_detail`
    ALTER `uuid` DROP DEFAULT;
ALTER TABLE `product_detail`
    CHANGE COLUMN `uuid` `uuid` VARCHAR(42) NOT NULL COLLATE 'utf8_unicode_ci' AFTER `id`;

## product uuid updates
ALTER TABLE `product`
    CHANGE COLUMN `supplierID` `supplier_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `uuid`,
    CHANGE COLUMN `taxID` `tax_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `active`,
    CHANGE COLUMN `pricegroupID` `pricegroup_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `changetime`,
    CHANGE COLUMN `filtergroupID` `filtergroup_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `pricegroupActive`;
ALTER TABLE `product`
    ALTER `pricegroupActive` DROP DEFAULT;
ALTER TABLE `product`
    CHANGE COLUMN `pricegroupActive` `pricegroup_active` INT(1) UNSIGNED NOT NULL AFTER `pricegroup_id`;
UPDATE product p SET p.uuid = CONCAT('SWAG-PRODUCT-UUID-', p.id);
ALTER TABLE `product`
    ALTER `uuid` DROP DEFAULT;
ALTER TABLE `product`
    CHANGE COLUMN `uuid` `uuid` VARCHAR(42) NOT NULL COLLATE 'utf8_unicode_ci' AFTER `id`;;
ALTER TABLE `product`
    ADD COLUMN `supplier_uuid` VARCHAR(42) NULL DEFAULT NULL AFTER `supplier_id`;
UPDATE product p SET p.supplier_uuid = CONCAT('SWAG-PRODUCT-SUPPLIER-UUID-', p.supplier_id);
ALTER TABLE `product`
    DROP INDEX `supplierID`,
    DROP COLUMN `supplier_id`,
    ADD CONSTRAINT `fk_product_supplier_uuid_product_supplier_uuid` FOREIGN KEY (`supplier_uuid`) REFERENCES `product_supplier` (`uuid`) ON UPDATE NO ACTION ON DELETE CASCADE,
    ADD INDEX `product_supplier_uuid` (`supplier_uuid`);
ALTER TABLE `product`
    CHANGE COLUMN `datum` `created_at` DATE NULL DEFAULT NULL AFTER `shippingtime`;
ALTER TABLE `product`
    ADD COLUMN `main_detail_uuid` VARCHAR(42) NULL DEFAULT NULL AFTER `main_detail_id`;
UPDATE product p INNER JOIN product_detail pd ON pd.id = p.main_detail_id SET p.main_detail_uuid = pd.uuid;
ALTER TABLE `product`
    DROP COLUMN `main_detail_id`,
    DROP INDEX `main_detailID`,
    ADD INDEX `product_main_detail_uuid` (`main_detail_uuid`);
ALTER TABLE `product`
    ADD CONSTRAINT `fk_product_main_detail_uuid_product_detail_uuid` FOREIGN KEY (`main_detail_uuid`) REFERENCES `product_detail` (`uuid`) ON UPDATE NO ACTION ON DELETE CASCADE;

