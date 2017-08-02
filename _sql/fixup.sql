ALTER TABLE `s_core_sessions`
  ADD COLUMN `lifetime` INT NULL AFTER `expiry`;


DROP INDEX articles_by_category_sort_release ON s_articles;
DROP INDEX articles_by_category_sort_name ON s_articles;
ALTER TABLE s_articles RENAME TO product;
ALTER TABLE product ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_uuid_uindex ON product (uuid);
CREATE INDEX product_by_category_sort_name ON product (name, id);
CREATE INDEX product_by_category_sort_release ON product (datum, id);
ALTER TABLE product MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE product
    CHANGE COLUMN supplierID manufacture_id INT(11) unsigned,
    CHANGE COLUMN shippingtime shipping_time VARCHAR(11),
    CHANGE COLUMN datum created_at DATETIME,
    CHANGE COLUMN taxID tax_id INT(11) unsigned,
    CHANGE COLUMN pseudosales pseudo_sales INT(11) NOT NULL DEFAULT '0',
    CHANGE COLUMN metaTitle meta_title VARCHAR(255),
    CHANGE COLUMN changetime change_at DATETIME NOT NULL,
    CHANGE COLUMN pricegroupID price_group_id INT(11) unsigned,
    CHANGE COLUMN filtergroupID filter_group_id INT(11) unsigned,
    CHANGE COLUMN laststock last_stock INT(1) NOT NULL
;

ALTER TABLE product DROP pricegroupActive;
ALTER TABLE product ADD COLUMN tax_uuid VARCHAR(42) NULL AFTEr tax_id;
ALTER TABLE product ADD manufacture_uuid VARCHAR(42) NULL AFTER manufacture_id;

-- migration
UPDATE product p
SET p.uuid = CONCAT('SWAG-PRODUCT-UUID-', p.id),
    p.manufacture_uuid = CONCAT('SWAG-PRODUCT-MANUFACTURE-UUID-', p.manufacture_id),
    p.tax_uuid = CONCAT('SWAG-CONFIG-TAX-UUID-', p.tax_uuid)
;


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


ALTER TABLE s_articles_also_bought_ro RENAME TO product_also_bought_ro;
ALTER TABLE product_also_bought_ro CHANGE article_id product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_also_bought_ro CHANGE related_article_id related_product_id INT(11) NOT NULL;

-- migration
ALTER TABLE product_also_bought_ro ADD related_product_uuid VARCHAR(42) NULL;
ALTER TABLE product_also_bought_ro ADD product_uuid VARCHAR(42) NULL;
ALTER TABLE product_also_bought_ro
    MODIFY COLUMN sales INT(11) unsigned NOT NULL DEFAULT '0' AFTER related_product_uuid,
    MODIFY COLUMN related_product_id INT(11) NOT NULL AFTER product_uuid;

UPDATE product_also_bought_ro pabr SET
    pabr.product_uuid = CONCAT('SWAG-PRODUCT-UUID-',pabr.product_id),
    pabr.related_product_uuid = CONCAT('SWAG-PRODUCT-UUID-',pabr.related_product_id)
;

ALTER TABLE s_articles_attributes RENAME TO product_attribute;
ALTER TABLE product_attribute ADD uuid VARCHAR(42) NULL;
CREATE UNIQUE INDEX product_attribute_uuid_uindex ON product_attribute (uuid);
ALTER TABLE product_attribute CHANGE articledetailsID product_details_id INT(11) unsigned;
ALTER TABLE product_attribute CHANGE articleID product_id INT(11) unsigned;
ALTER TABLE product_attribute
    MODIFY COLUMN uuid VARCHAR(42) AFTER id;

ALTER TABLE product_attribute ADD product_detail_uuid VARCHAR(42) NULL;
ALTER TABLE product_attribute ADD product_uuid VARCHAR(42) NULL;
ALTER TABLE product_attribute
    MODIFY COLUMN product_uuid VARCHAR(42) AFTER product_id,
    MODIFY COLUMN product_detail_uuid VARCHAR(42) AFTER product_details_id;

-- migration
UPDATE product_attribute pa SET
    pa.uuid                = CONCAT('SWAG-PRODUCT-ATTRIBUTE-UUID-', pa.id),
    pa.product_uuid        = CONCAT('SWAG-PRODUCT-UUID-', pa.product_id),
    pa.product_detail_uuid = CONCAT('SWAG-PRODUCT-DETAIL-UUID-', pa.product_details_id)
;

ALTER TABLE s_articles_avoid_customergroups RENAME TO product_avoid_customergroup;
ALTER TABLE product_avoid_customergroup CHANGE articleID product_id INT(11) NOT NULL;
ALTER TABLE product_avoid_customergroup CHANGE customergroupID customergroup_id INT(11) NOT NULL;

ALTER TABLE product_avoid_customergroup ADD customer_group_uuid VARCHAR(42) NULL;
ALTER TABLE product_avoid_customergroup ADD product_uuid VARCHAR(42) NULL;
ALTER TABLE product_avoid_customergroup
    MODIFY COLUMN customergroup_id INT(11) NOT NULL AFTER customer_group_uuid;

-- migration
UPDATE product_avoid_customergroup pac SET
    pac.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', pac.product_id),
    pac.customer_group_uuid = CONCAT('SWAG-CONFIG-CUSTOMER-GROUP-UUID-', pac.customergroup_id)
;

ALTER TABLE s_articles_categories RENAME TO product_category;
ALTER TABLE product_category CHANGE articleID product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_category CHANGE categoryID category_id INT(11) unsigned NOT NULL;

ALTER TABLE product_category ADD uuid VARCHAR(42) NULL AFTER id;
ALTER TABLE product_category ADD product_uuid VARCHAR(42) NULL AFTER product_id;
ALTER TABLE product_category ADD category_uuid VARCHAR(42) NULL AFTER category_id;

-- migration
UPDATE product_category pc SET
    pc.uuid          = CONCAT('SWAG-PRODUCT-CATEGORY-UUID-', pc.id),
    pc.product_uuid  = CONCAT('SWAG-PRODUCT-UUID-', pc.product_id),
    pc.category_uuid = CONCAT('SWAG-CATEGORY-UUID-', pc.category_id);

ALTER TABLE s_articles_categories_ro RENAME TO product_category_ro;
ALTER TABLE product_category_ro CHANGE articleID product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_category_ro CHANGE categoryID category_id INT(11) unsigned NOT NULL;
ALTER TABLE product_category_ro CHANGE parentCategoryID parent_category_id INT(11) unsigned NOT NULL;

ALTER TABLE product_category_ro ADD uuid VARCHAR(42) NULL AFTER id;
ALTER TABLE product_category_ro ADD product_uuid VARCHAR(42) NULL AFTER product_id;
ALTER TABLE product_category_ro ADD category_uuid VARCHAR(42) NULL AFTER category_id;
ALTER TABLE product_category_ro ADD parent_category_uuid VARCHAR(42) NULL AFTER parent_category_id;

-- migration
UPDATE product_category_ro pcr SET
    pcr.uuid = CONCAT('SWAG-PRODUCT-CATEGORY-RO-UUID-', pcr.id),
    pcr.product_uuid = CONCAT('SWAG-PRODUCT-UUID', pcr.product_id),
    pcr.category_uuid = CONCAT('SWAG-CATEGORY-UUID-', pcr.category_id),
    pcr.parent_category_uuid = CONCAT('SWAG-CATEGORY-UUID-', pcr.parent_category_id)
;


ALTER TABLE s_articles_categories_seo RENAME TO product_category_seo;
ALTER TABLE product_category_seo CHANGE article_id product_id INT(11) NOT NULL;
ALTER TABLE product_category_seo ADD shop_uuid VARCHAR(42) NULL AFTER shop_id;
ALTER TABLE product_category_seo ADD product_uuid VARCHAR(42) NULL AFTER product_id;
ALTER TABLE product_category_seo ADD category_uuid VARCHAR(42) NULL AFTER category_id;

-- migration
UPDATE product_category_seo pcs SET
    pcs.shop_uuid     = CONCAT('SWAG-CONFIG-SHOP-UUID-', pcs.shop_id),
    pcs.product_uuid  = CONCAT('SWAG-PRODUCT-UUID-', pcs.product_id),
    pcs.category_uuid = CONCAT('SWAG-CATEGORY-UUID-', pcs.product_id)
;

DROP INDEX get_similar_articles ON s_articles_details;
DROP INDEX articles_by_category_sort_popularity ON s_articles_details;
DROP INDEX articleID ON s_articles_details;
ALTER TABLE s_articles_details RENAME TO product_detail;
ALTER TABLE product_detail CHANGE articleID product_id INT(11) unsigned NOT NULL DEFAULT '0';
ALTER TABLE product_detail ADD uuid VARCHAR(42) NULL AFTER id;
ALTER TABLE product_detail ADD product_uuid VARCHAR(42) NULL AFTER product_id;
CREATE UNIQUE INDEX product_detail_uuid_uindex ON product_detail (uuid);
CREATE INDEX product_id ON product_detail (product_id);
CREATE INDEX product_by_category_sort_popularity ON product_detail (sales, product_id);
CREATE INDEX get_similar_products ON product_detail (kind, sales);

ALTER TABLE product_detail CHANGE ordernumber order_number VARCHAR(255) NOT NULL;
ALTER TABLE product_detail CHANGE suppliernumber supplier_number VARCHAR(255);
ALTER TABLE product_detail CHANGE additionaltext additional_text VARCHAR(255);
ALTER TABLE product_detail CHANGE instock stock INT(11);
ALTER TABLE product_detail CHANGE unitID unit_id INT(11) unsigned;
ALTER TABLE product_detail CHANGE purchasesteps purchase_steps INT(11) unsigned;
ALTER TABLE product_detail CHANGE maxpurchase max_purchase INT(11) unsigned;
ALTER TABLE product_detail CHANGE minpurchase min_purchase INT(11) unsigned NOT NULL DEFAULT '1';
ALTER TABLE product_detail CHANGE purchaseunit purchase_unit DECIMAL(11,4) unsigned;
ALTER TABLE product_detail CHANGE referenceunit reference_unit DECIMAL(10,3) unsigned;
ALTER TABLE product_detail CHANGE packunit pack_unit VARCHAR(255);
ALTER TABLE product_detail CHANGE releasedate release_date DATETIME;
ALTER TABLE product_detail CHANGE shippingfree shipping_free INT(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE product_detail CHANGE shippingtime shipping_time VARCHAR(11);
ALTER TABLE product_detail CHANGE purchaseprice purchase_price DOUBLE NOT NULL DEFAULT '0';

-- migration
UPDATE product_detail pd SET
    pd.uuid = CONCAT('SWAG-PRODUCT-DETAIL-UUID-', pd.id),
    pd.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', pd.product_id)
;

ALTER TABLE s_articles_downloads RENAME TO product_download;
ALTER TABLE product_download ADD uuid VARCHAR(42) NULL AFTER id;
CREATE UNIQUE INDEX product_download_uuid_uindex ON product_download (uuid);
ALTER TABLE product_download CHANGE articleID product_id INT(11) unsigned NOT NULL;
ALTER TABLE product_download ADD product_uuid VARCHAR(42) NULL AFTER product_id;
ALTER TABLE product_download CHANGE filename file_name VARCHAR(255) NOT NULL;

-- migration
UPDATE product_download pd SET
    pd.uuid         = CONCAT('SWAG-PRODUCT-DOWNLOAD-UUID-', pd.id),
    pd.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', pd.product_id)
;


ALTER TABLE s_articles_downloads_attributes RENAME TO product_download_attribute;
ALTER TABLE product_download_attribute ADD uuid VARCHAR(42) NULL AFTER id;
CREATE UNIQUE INDEX product_download_attribute_uuid_uindex ON product_download_attribute (uuid);
ALTER TABLE product_download_attribute CHANGE downloadID download_id INT(11) unsigned AFTER uuid;
ALTER TABLE product_download_attribute ADD download_uuid VARCHAR(42) NULL AFTER download_id;

-- migration

UPDATE product_download_attribute pda SET
    pda.uuid          = CONCAT('SWAG-PRODUCT-DOWNLOAD-ATTRIBUTE-UUID-', pda.id),
    pda.download_uuid = CONCAT('SWAG-PRODUCT-DOWNLOAD-UUID-', pda.download_uuid)
;

ALTER TABLE s_articles_esd RENAME TO product_esd;
ALTER TABLE product_esd ADD uuid VARCHAR(42) NULL AFTER id;
CREATE UNIQUE INDEX product_esd_uuid_uindex ON product_esd (uuid);
ALTER TABLE product_esd CHANGE articledetailsID product_detail_id INT(11) NOT NULL DEFAULT '0';
ALTER TABLE product_esd CHANGE articleID product_id INT(11) NOT NULL DEFAULT '0';
ALTER TABLE product_esd CHANGE maxdownloads max_downloads INT(11) NOT NULL DEFAULT '0';
ALTER TABLE product_esd CHANGE datum created_at DATETIME NOT NULL;
ALTER TABLE product_esd
    ADD COLUMN product_uuid VARCHAR(42) NULL AFTER product_id,
    ADD COLUMN product_detail_uuid VARCHAR(42) NULL AFTER product_detail_id;

-- migration
UPDATE product_esd pe SET
    pe.uuid                = CONCAT('SWAG-PRODUCT-ES-UUID-', pe.id),
    pe.product_uuid        = CONCAT('SWAG-PRODUCT-UUID-', pe.product_id),
    pe.product_detail_uuid = CONCAT('SWAG-PRODUCT-DETAIL-UUID-', pe.product_detail_id)
;

ALTER TABLE s_articles_esd_attributes RENAME TO product_esd_attribute;
ALTER TABLE product_esd_attribute CHANGE esdID esd_id INT(11);

ALTER TABLE product_esd_attribute
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    ADD COLUMN product_esd_uuid VARCHAR(42) NULL AFTER esd_id;

CREATE UNIQUE INDEX product_esd_attribute_uuid_uindex ON product_esd_attribute (uuid);

-- migration
UPDATE product_esd_attribute pea SET
    pea.uuid = CONCAT('SWAG-PRODUCT-ES-ATTRIBUTE-UUID-', pea.id),
    pea.product_esd_uuid = CONCAT('SWAG-PRODUCT-ES-UUID-', pea.esd_id)
;

ALTER TABLE s_articles_esd_serials RENAME TO product_esd_serial;

ALTER TABLE product_esd_serial
    CHANGE COLUMN esdID esd_id INT(11) NOT NULL DEFAULT '0' AFTER id,
    CHANGE COLUMN serialnumber serial_number VARCHAR(255) NOT NULL
;

ALTER TABLE product_esd_serial
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    ADD COLUMN product_esd_uuid VARCHAR(42) NULL AFTER esd_id
;
CREATE UNIQUE INDEX product_esd_serial_uuid_uindex ON product_esd_serial (uuid);

-- migration
UPDATE product_esd_serial pes SET
    pes.uuid = CONCAT('SWAG-PRODUCT-ES-SERIAL-UUID-', pes.id),
    pes.product_esd_uuid = CONCAT('SWAG-PRODUCT-ES-UUID-', pes.esd_id)
;

DROP INDEX article_images_query ON s_articles_img;
DROP INDEX article_detail_id ON s_articles_img;
DROP INDEX article_cover_image_query ON s_articles_img;
ALTER TABLE s_articles_img RENAME TO product_image;
ALTER TABLE product_image
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    CHANGE COLUMN articleID product_id INT(11),
    CHANGE COLUMN article_detail_id product_detail_id INT(10) unsigned,
    ADD COLUMN product_uuid VARCHAR(42) NULL AFTER product_id,
    ADD COLUMN product_detail_uuid VARCHAR(42) NULL AFTER product_detail_id;

CREATE UNIQUE INDEX product_img_uuid_uindex ON product_image (uuid);
CREATE INDEX product_cover_image_query ON product_image (product_id, main, position);
CREATE INDEX product_detail_id ON product_image (product_detail_id);
CREATE INDEX product_image_query ON product_image (product_id, position);

-- migration
UPDATE product_image p SET
    p.uuid                = CONCAT('SWAG-PRODUCT-IMAGE-UUID-', p.id),
    p.product_uuid        = CONCAT('SWAG-PRODUCT-UUID-', p.product_id),
    p.product_detail_uuid = CONCAT('SWAG-PRODUCT-DETAIL-UUID-', p.product_detail_id)
;

ALTER TABLE s_articles_img_attributes RENAME TO product_image_attribute;
ALTER TABLE product_image_attribute
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    CHANGE COLUMN imageID image_id INT(11),
    ADD COLUMN product_image_uuid VARCHAR(42) NULL AFTER image_id
;
CREATE UNIQUE INDEX product_img_attribute_uuid_uindex ON product_image_attribute (uuid);

-- migration
UPDATE product_image_attribute p SET
    p.uuid = CONCAT('SWAG-PRODUCT-IMAGE-ATTRIBUTE-UUID-', p.id),
    p.product_image_uuid = CONCAT('SWAG-PRODUCT-IMAGE-UUID-', p.image_id)
;

ALTER TABLE s_article_img_mappings RENAME TO product_image_mapping;
ALTER TABLE product_image_mapping
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    ADD COLUMN product_image_uuid VARCHAR(42) NULL AFTER image_id
;
CREATE UNIQUE INDEX product_img_mapping_uuid_uindex ON product_image_mapping (uuid);

-- migration
UPDATE product_image_mapping p SET
    p.uuid = CONCAT('SWAG-PRODUCT-IMAGE-MAPPING-UUID-', p.id),
    p.product_image_uuid = CONCAT('SWAG-PRODUCT-IMAGE-UUID-', p.image_id)
;

-- TODO option_id ???
ALTER TABLE s_article_img_mapping_rules RENAME TO product_image_mapping_rule;
ALTER TABLE product_image_mapping_rule
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    ADD COLUMN product_image_mapping_uuid VARCHAR(42) NULL AFTER mapping_id
;
CREATE UNIQUE INDEX product_img_mapping_rule_uuid_uindex ON product_image_mapping_rule (uuid);

-- migration
UPDATE product_image_mapping_rule p SET
    p.uuid = CONCAT('SWAG-PRODUCT-IMAGE-MAPPING-RULE-UUID-', p.id),
    p.product_image_mapping_uuid = CONCAT('SWAG-PRODUCT-IMAGE-MAPPING-UUID-', p.mapping_id)
;

ALTER TABLE s_articles_information RENAME TO product_information;
ALTER TABLE product_information
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    CHANGE COLUMN articleID product_id INT(11) NOT NULL DEFAULT '0',
    ADD COLUMN product_uuid VARCHAR(42) NULL AFTER product_id
;
CREATE UNIQUE INDEX product_information_uuid_uindex ON product_information (uuid);

-- migration
UPDATE product_information p SET
    p.uuid = CONCAT('SWAG-PRODUCT-INFORMATION-UUID-', p.id),
    p.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.product_id)
;


ALTER TABLE s_articles_information_attributes RENAME TO product_information_attribute;
ALTER TABLE product_information_attribute
    CHANGE COLUMN informationID information_id INT(11),
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    ADD COLUMN information_uuid VARCHAR(42) NULL AFTER information_id
;
CREATE UNIQUE INDEX product_information_attribute_uuid_uindex ON product_information_attribute (uuid);

-- migration
UPDATE product_information_attribute p SET
    p.uuid             = CONCAT('SWAG-PRODUCT-INFORMATION-ATTRIBUTE-UUID-', p.id),
    p.information_uuid = CONCAT('SWAG-PRODUCT-INFORMATION-UUID-', p.information_id)
;

ALTER TABLE s_articles_notification RENAME TO product_notification;
ALTER TABLE product_notification
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    CHANGE COLUMN ordernumber order_number VARCHAR(255) NOT NULL,
    CHANGE COLUMN `date` created_At DATETIME NOT NULL,
    CHANGE COLUMN shopLink shop_link VARCHAR(255) NOT NULL
;
CREATE UNIQUE INDEX product_notification_uuid_uindex ON product_notification (uuid);

-- migration
UPDATE product_notification p SET
    p.uuid = CONCAT('SWAG-PRODUCT-NOTIFICATION-UUID-', p.id)
;

ALTER TABLE s_articles_prices RENAME TO product_price;
ALTER TABLE product_price
    ADD COLUMN uuid VARCHAR(42) NULL AFTER id,
    CHANGE COLUMN articledetailsID product_detail_id INT(11) NOT NULL DEFAULT '0',
    CHANGE COLUMN articleID product_id INT(11) NOT NULL DEFAULT '0',
    ADD COLUMN product_uuid VARCHAR(42) NULL AFTER product_id,
    ADD COLUMN product_detail_uuid VARCHAR(42) NULL AFTER product_detail_id
;
CREATE UNIQUE INDEX product_price_uuid_uindex ON product_price (uuid);

-- migration
UPDATE product_price p SET
    p.uuid = CONCAT('SWAG-PRODUCT-PRICE-UUID-', p.id),
    p.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.product_id),
    p.product_detail_uuid = CONCAT('SWAG-PRODUCT-DETAIL-UUID-', p.product_detail_id)
;

ALTER TABLE s_articles_prices_attributes RENAME TO product_price_attribute;
ALTER TABLE product_price_attribute
    ADD uuid VARCHAR(42) NULL AFTER id,
    CHANGE priceID price_id INT(11) unsigned,
    ADD price_uuid VARCHAR(42) NULL AFTER price_id
;

CREATE UNIQUE INDEX product_price_attribute_uuid_uindex ON product_price_attribute (uuid);

-- migration
UPDATE product_price_attribute p SET
p.uuid       = CONCAT('SWAG-PRODUCT-PRICE-ATTRIBUTE-UUID-', p.id),
p.price_uuid = CONCAT('SWAG-PRODUCT-PRICE-UUID-', p.price_id);


ALTER TABLE s_articles_relationships RENAME TO product_relationship;
ALTER TABLE product_relationship
    ADD uuid VARCHAR(42) NULL,
    CHANGE relatedarticle related_product VARCHAR(30) NOT NULL,
    CHANGE articleID product_id INT(30) NOT NULL,
    ADD product_uuid VARCHAR(42) NULL after product_id,
    ADD related_product_uuid VARCHAR(42) NULL after related_product
;
CREATE UNIQUE INDEX product_relationship_uuid_uindex ON product_relationship (uuid);

-- migration
UPDATE product_relationship p SET
    p.uuid = CONCAT('SWAG-PRODUCT-RELATIONSHIP-UUID-', p.id),
    p.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.product_id),
    p.related_product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.related_product_uuid)
;

ALTER TABLE s_articles_similar RENAME TO product_similar;
ALTER TABLE product_similar
    ADD uuid VARCHAR(42) NULL AFTER id,
    CHANGE relatedarticle related_product VARCHAR(255) NOT NULL,
    CHANGE articleID product_id INT(30) NOT NULL,
    ADD product_uuid VARCHAR(42) NULL after product_id,
    ADD related_product_uuid VARCHAR(42) NULL after related_product
;
CREATE UNIQUE INDEX product_similar_uuid_uindex ON product_similar (uuid);

UPDATE product_similar p SET
    p.uuid = CONCAT('SWAG-PRODUCT-RELATIONSHIP-UUID-', p.id),
    p.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.product_id),
    p.related_product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.related_product_uuid)
;

ALTER TABLE s_articles_similar_shown_ro RENAME TO product_similar_shown_ro;
ALTER TABLE product_similar_shown_ro
    CHANGE related_article_id related_product_id INT(11) NOT NULL,
    CHANGE article_id product_id INT(11) unsigned NOT NULL,
    CHANGE init_date created_at DATETIME NOT NULL,
    ADD uuid VARCHAR(42) NULL AFTER id,
    ADD product_uuid VARCHAR(42) NULL after product_id,
    ADD related_product_uuid VARCHAR(42) NULL after related_product_id
;

CREATE UNIQUE INDEX product_similar_shown_ro_uuid_uindex ON product_similar_shown_ro (uuid);

UPDATE product_similar_shown_ro p SET
    p.uuid = CONCAT('SWAG-PRODUCT-RELATIONSHIP-UUID-', p.id),
    p.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.product_id),
    p.related_product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.related_product_uuid)
;

ALTER TABLE s_articles_supplier RENAME TO product_manufacture;
ALTER TABLE product_manufacture
    ADD uuid VARCHAR(42) NULL AFTER id,
    CHANGE `changed` updated_at DATETIME NOT NULL
;
CREATE UNIQUE INDEX product_manufacture_uuid_uindex ON product_manufacture (uuid);

-- migration
UPDATE product_manufacture p SET
    p.uuid = CONCAT('SWAG-PRODUCT-MANUFACTURE-UUID-', p.id)
;

ALTER TABLE s_articles_supplier_attributes RENAME TO product_manufacture_attribute;
ALTER TABLE product_manufacture_attribute
    ADD uuid VARCHAR(42) NULL AFTER id,
    CHANGE supplierID manufacture_id INT(11) AFTER uuid,
    ADD manufacture_uuid VARCHAR(42) NULL AFTER manufacture_id
;
CREATE UNIQUE INDEX product_supplier_attribute_uuid_uindex ON product_manufacture_attribute (uuid);

-- migration
UPDATE product_manufacture_attribute p SET
    p.uuid             = CONCAT('SWAG-PRODUCT-MANUFACTURE-ATTRIBUTE-UUID-', p.id),
    p.manufacture_uuid = CONCAT('SWAG-PRODUCT-MANUFACTURE-UUID-', p.manufacture_id)
;

ALTER TABLE s_articles_top_seller_ro RENAME TO product_top_seller_ro;
ALTER TABLE product_top_seller_ro
    CHANGE article_id product_id INT(11) unsigned NOT NULL,
    CHANGE last_cleared cleared_at DATETIME,
    ADD uuid VARCHAR(42) NULL AFTER id,
    ADD product_uuid VARCHAR(42) NULL AFTER product_id
;

-- migration
UPDATE product_top_seller_ro p SET
    p.uuid = CONCAT('SWAG-PRODUCT-TOP-SELLER-RO-UUID-', p.id),
    p.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.product_id);
;

ALTER TABLE s_articles_translations RENAME TO product_translation;
ALTER TABLE product_translation
    CHANGE articleID product_id INT(11) NOT NULL,
    CHANGE languageID language_id INT(11) NOT NULL,
    ADD uuid VARCHAR(42) NULL AFTER id,
    ADD product_uuid VARCHAR(42) NULL AFTER product_id,
    ADD language_uuid VARCHAR(42) NULL AFTER language_id
;

-- migration
UPDATE product_translation p SET
    p.uuid = CONCAT('SWAG-PRODUCT-TRANSLATION-UUID-', p.id),
    p.product_uuid = CONCAT('SWAG-PRODUCT-UUID-', p.product_id),
    p.language_uuid = CONCAT('SWAG-CONFIG-LOCALES-UUID-', p.language_uuid)
;

ALTER TABLE s_articles_vote RENAME TO product_vote;
ALTER TABLE product_vote
    CHANGE articleID product_id INT(11) NOT NULL,
    CHANGE datum created_at DATETIME,
    CHANGE answer_date answer_at DATETIME,
    ADD uuid VARCHAR(42) NULL,
    ADD product_uuid VARCHAR(42) NULL,
    ADD shop_uuid VARCHAR(42)
;

ALTER TABLE `product`
    ALTER `name` DROP DEFAULT,
    CHANGE COLUMN `name` `title` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci' AFTER `supplier_id`
;
