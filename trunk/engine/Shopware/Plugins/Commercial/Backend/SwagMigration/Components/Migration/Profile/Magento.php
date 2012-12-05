<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware SwagMigration Components - Magento
 *
 * @category  Shopware
 * @package Shopware\Plugins\SwagMigration\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Migration_Profile_Magento extends Shopware_Components_Migration_Profile
{
	/**
	 * Returns the directory of the article images.
	 * @return string {String} | image path
	 */
	public function getProductImagePath()
	{
		return '/media/catalog/product';
	}

	/**
	 * Returns the sql statement to select the config base path
	 * @return string {String} | sql for the config base path
	 */
	public function getConfigSelect()
	{
		return "
			SELECT `path` as name, `value`
			FROM {$this->quoteTable('core_config_data')}
		";
	}

	/**
	 * Returns the sql statement to select the shop system languages
	 * @return string {String} | sql for languages
	 */
	public function getLanguageSelect()
	{
		return $this->getShopSelect();
	}

	/**
	 * Returns the sql statement to select the shop system sub shops
	 * @return string {String} | sql for sub shops
	 */
	public function getShopSelect()
	{
		return "
			SELECT `store_id` as id, `name` as name
			FROM {$this->quoteTable('core_store')}
			WHERE `store_id`!=0
		";
	}

	/**
	 * Returns the sql statement to select the shop system price groups
	 * @return string {String} | sql for price groups
	 */
	public function getPriceGroupSelect()
	{
		return "
			SELECT `customer_group_id` as id, `customer_group_code` as name
			FROM {$this->quoteTable('customer_group')}
		";
	}

	/**
	 * Returns the sql statement to select the shop system payments
	 * @return string {String} | sql for the payments
	 */
	public function getPaymentMeanSelect()
	{
		return "
			SELECT `method` as id, `method` as name
			FROM {$this->quoteTable('sales_flat_quote_payment')}
		";
	}

	/**
	 * Returns an array of the order states mapping, with keys and descriptions
	 * @return array {Array} | order states: key - description
	 */
	public function getOrderStatus()
	{
		return array(
			'pending' => 'Pending',
			'holded' => 'On Hold',
			'processing' => 'Processing',
			'complete' => 'Complete'
		);
	}

	/**
	 * Returns the sql statement to select the shop system tax rates
	 * @return string {String} | sql for the tax rates
	 */
	public function getTaxRateSelect()
	{
		return "
			SELECT `class_id` as id, `class_name` as name
			FROM {$this->quoteTable('tax_class')}
		";

	}

	/**
	 * Returns the sql statement to select the shop system article attributes
	 * @return string {String} | sql for the article attributes
	 */
	public function getAttributeSelect()
	{
		return "
			SELECT
				-- ea.attribute_id 	as `id`,
				ea.attribute_code 	as `id`,
				ea.frontend_label	as `name`,
				ea.backend_type 	as `type`,
				ea.is_required		as `required`
			FROM {$this->quoteTable('eav_attribute', 'ea')}, {$this->quoteTable('eav_entity_type', 'et')}
			WHERE ea.`entity_type_id`=et.entity_type_id
			AND et.entity_type_code='catalog_product'
			AND ea.frontend_input!=''
			AND (ea.is_user_defined=1 OR ea.attribute_code IN ('visibility', 'meta_description', 'meta_title', 'url_key'))
			AND ea.attribute_code NOT IN ('cost', 'manufacturer')
			ORDER BY `name`
		";
	}

	/**
	 * Returns the sql statement to select the shop system articles
	 * @return string {String} | sql for the articles
	 */
	public function getProductSelect()
	{
		$attributes = array(
			'description', 'name', 'short_description', 
			'status', 'weight', 'manufacturer',
			'price', 'cost', 'tax_class_id', 
			'meta_keyword', 'special_price'
		);
		
		$custom_select = '';
		foreach ($this->getAttributes() as $attributeID=>$attribute) {
			$custom_select .= ",
				$attributeID.value									as $attributeID";
			$attributes[] = $attributeID;
		}
		
		$sql = "
			SELECT 
				
				catalog_product.entity_id						as productID,
				catalog_product.sku								as ordernumber,
				catalog_product.created_at						as added,
				
				name.value										as name,	
				NULL											as additionaltext,
				description.value								as description_long,
				short_description.value							as description,
				meta_keyword.value								as keywords,
				manufacturer_option.value						as supplier,
				weight.value									as weight,
				
				cs.qty											as instock,
				cs.min_qty										as stockmin,
				cs.min_sale_qty									as minpurchase,
				cs.max_sale_qty									as maxpurchase,
								
				tax_class_id.value								as taxID,
				cost.value										as baseprice,
				IFNULL(special_price.value, price.value)		as price,
				IF(special_price.value IS NULL, 0, price.value) as pseudoprice
				
				$custom_select
			
			FROM {$this->quoteTable('catalog_product_entity', 'catalog_product')}
			
			{$this->createTableSelect('catalog_product', $attributes, 0)}
			
			LEFT JOIN {$this->quoteTable('cataloginventory_stock_item', 'cs')}
			ON cs.`product_id`=catalog_product.`entity_id`
			AND cs.`stock_id`=1
			
			LEFT JOIN {$this->quoteTable('eav_attribute_option_value', 'manufacturer_option')}
			ON manufacturer_option.value_id=manufacturer.value
		";

        return $sql;
	}

	/**
	 * Returns the sql statement to select the shop system article translations
	 * @return string {String} | sql for the article translations
	 */
	public function getProductTranslationSelect()
	{
		$attributes = array(
			'description', 'name', 'short_description', 'meta_keyword', 
		);
		
		$custom_select = '';
		foreach ($this->getAttributes() as $attributeID=>$attribute) {
			$custom_select .= ",
				$attributeID.value									as $attributeID";
			$attributes[] = $attributeID;
		}
		$sql = "
			SELECT 
				
				catalog_product.entity_id						as productID,
				store.store_id									as languageID,
				
				name.value										as name,
				NULL											as additionaltext,
				description.value								as description_long,
				short_description.value							as description,
				meta_keyword.value								as keywords
				
				$custom_select
			
			FROM {$this->quoteTable('catalog_product_entity', 'catalog_product')}
			
			INNER JOIN {$this->quoteTable('core_store', 'store')}
			ON store.store_id!=0
			
			{$this->createTableSelect('catalog_product', $attributes, new Zend_Db_Expr('store.store_id'))}
		";
        return $sql;
	}

	/**
	 * Returns the sql statement to select the shop system article prices
	 * @return string {String} | sql for the article prices
	 */
	public function getProductPriceSelect()
	{
		return "
			SELECT
				`entity_id` as `productID`,
				`qty` as `from`,
				`value` as `price`,
				0 as `percent`,
				`customer_group_id` as `pricegroup`
			FROM {$this->quoteTable('catalog_product_entity_tier_price')}
			ORDER BY `productID`, `pricegroup`, `from`
		";
	}

	/**
	 * Returns the sql statement to select the shop system article image allocation
	 * @return string {String} | sql for the article image allocation
	 */
	public function getProductImageSelect()
	{
		return "
			SELECT 
				g.entity_id as productID,
				g.value as image,
				gv.label as description,
				gv.position,
				IF(gv.position=1, 1, 0) as main
			FROM 
				{$this->quoteTable('catalog_product_entity_media_gallery', 'g')},
				{$this->quoteTable('catalog_product_entity_media_gallery_value', 'gv')}
			WHERE gv.`value_id`=g.`value_id`
			AND gv.`store_id`=0
			ORDER BY productID, position
		";
	}

	/**
	 * Returns the sql statement to select the shop system article category allocation
	 * @return string {String} | sql for the article category allocation
	 */
	public function getProductCategorySelect()
	{
		return "
			SELECT product_id as productID, category_id as categoryID
			FROM {$this->quoteTable('catalog_category_product')}
			ORDER BY position
		";
	}

	/**
	 * Returns the sql statement to select the shop system categories.
	 * If the shop system have more than one sub shop the sql statements will join with "UNION ALL".
	 * @return string {String} | sql for the categories
	 */
	public function getCategorySelect()
	{
		$sql = array();
		foreach ($this->getShops() as $shopID=>$shop) {
			$sql[] = "
				SELECT 
					IF(entity_id=g.`root_category_id`, 0, entity_id) as categoryID,
					IF(parent_id=g.`root_category_id`, 0, parent_id) as parentID,
					s.`store_id` as languageID,
					c.name as description,
					c.position as position,
					c.meta_keywords as metakeywords,
					c.meta_description as metadescription,
					c.meta_title as cmsheadline,
					c.description as cmstext,
					c.is_active as active
				FROM 
					{$this->quoteTable('core_store', 's')},
					{$this->quoteTable('core_store_group', 'g')},
					{$this->quoteTable('catalog_category_flat_store_'.$shopID, 'c')}
				WHERE g.`group_id`=s.`group_id`
				AND c.`path` LIKE CONCAT('1/', g.`root_category_id`, '/%')
				AND s.`store_id`={$this->Db()->quote($shopID)}
				ORDER BY 0
			";
		}
		return '('.implode(') UNION ALL (', $sql).')';
	}

	/**
	 * Returns the sql statement to select the shop system article ratings
	 * @return string {String} | sql for the article ratings
	 */
	public function getProductRatingSelect()
	{
		return "
			SELECT 
				r.`entity_pk_value` as `productID`,
				rd.`customer_id` as `customerID`,
				rd.`nickname` as `name`,
				5 as `rating`,
				r.`created_at` as `date`,
				IF(r.`status_id`=1, 1, 0) as `active`,
				rd.`detail` as `comment`,
				rd.`title`
			FROM {$this->quoteTable('review', 'r')}, {$this->quoteTable('review_detail', 'rd')}
			WHERE r.`review_id`=rd.`review_id`
			AND r.`entity_id`=1
		";
	}

	/**
	 * Returns the sql statement to select the shop system customer
	 * @return string {String} | sql for the customer data
	 */
	public function getCustomerSelect()
	{
		$attributes = array(
			'gender', 'firstname', 'middlename', 'lastname', 'company', 
			'dob', 'password_hash', 'taxvat',
			'default_billing', 'default_shipping'
		);
		$addressAttributes = array(
			//'firstname', 'middlename', 'lastname', 'company', 'region',
			'city', 'country_id', 'postcode', 'street', 'telephone', 'fax'
		);
		
		return "
			SELECT 
				
				customer.entity_id						as customerID,
				customer.increment_id					as customernumber,
				customer.email							as email,
				customer.store_id						as subshopID,
				customer.created_at						as firstlogin,
				customer.updated_at						as lastlogin,
				customer.is_active 						as active,
				customer.group_id						as customergroupID,
				
				IF(gender.value=2, 'ms', 'mr')			as billing_salutation,
				company.value 							as billing_company,
				TRIM(CONCAT(firstname.value, ' ', middlename.value))
														as billing_firstname, 
				lastname.value 							as billing_lastname,
				street.value							as billing_street,
				-- 										as billing_streetnumber,
				city.value								as billing_city,
				country_id.value						as billing_countryiso,
				postcode.value							as billing_zipcode,
				
				-- IF(gender.value, 'ms', 'mr')			as shipping_salutation,
				-- `company`							as shipping_company,
				-- `firstname`							as shipping_firstname,
				-- `lastname` 							as shipping_lastname,
				-- `street` 							as shipping_street,
				--  									as shipping_streetnumber,
				-- `city`								as shipping_city,
				-- `country_id`							as shipping_countryiso,
				-- `postcode`							as shipping_zipcode,
				
				telephone.value							as phone,
				fax.value								as fax,
				dob.value 								as birthday,
				-- password_hash.value 					as password,
				taxvat.value 							as ustid,
				IF(newsletter.subscriber_id, 1, 0)		as newsletter
			
			FROM {$this->quoteTable('customer_entity', 'customer')}
			
			LEFT JOIN {$this->quoteTable('newsletter_subscriber', 'newsletter')}
			ON newsletter.customer_id=customer.entity_id
			AND newsletter.subscriber_status=1
			
			{$this->createTableSelect('customer', $attributes)}
			
			LEFT JOIN {$this->quoteTable('customer_address_entity', 'customer_address')}
			ON customer_address.parent_id=customer.entity_id
			AND customer_address.entity_id=default_billing.value
			
			{$this->createTableSelect('customer_address', $addressAttributes)}
		";
	}

	/**
	 * Returns the sql statement to select the shop system customer
	 * @return string {String} | sql for the customer data
	 */
	public function getOrderSelect()
	{
		return "
			SELECT 
				o.`entity_id`								as orderID,
				o.`increment_id`							as ordernumber,
				
				o.`store_id`								as subshopID,
				o.`customer_id`								as customerID,
				p.`method`									as paymentID,
				o.`shipping_method`							as dispatchID,
				o.`status`									as statusID,
				-- 											as trackingID,
				-- 											as languageID,
				-- 											as transactionID,
				
				o.`customer_note`							as customercomment,
				o.`order_currency_code`						as currency,
				o.`base_to_order_rate`						as currency_factor,
				-- 											as cleared_date,
				o.`remote_ip` 								as remote_addr,
				o.`created_at`								as date,
				
				o.`customer_taxvat`							as ustid,
				ba.`telephone`								as phone,
				ba.`fax`									as fax,
				
				ba.`company`								as billing_company,
				ba.`firstname`								as billing_firstname,
				ba.`lastname`								as billing_lastname,
				ba.`street`									as billing_street,
				-- 											as billing_streetnumber,
				-- 											as billing_text1,
				ba.`city` 									as billing_city,
				ba.`country_id`								as billing_countryiso,
				ba.`postcode`								as billing_zipcode,
				IF(o.`customer_gender`=2, 'ms', 'mr')		as billing_salutation,
				
				sa.`company`								as shipping_company,
				sa.`firstname`								as shipping_firstname,
				sa.`lastname` 								as shipping_lastname,
				sa.`street` 								as shipping_street,
				-- 											as shipping_streetnumber,
				-- 											as shipping_text1,
				sa.`city`									as shipping_city,
				sa.`country_id`								as shipping_countryiso,
				sa.`postcode`								as shipping_zipcode,
				IF(o.`customer_gender`=2, 'ms', 'mr')		as shipping_salutation,
				
				o.`grand_total`-o.`tax_amount`				as invoice_amount_net,
				o.`grand_total`								as invoice_amount,
				o.`shipping_incl_tax`						as invoice_shipping,
				o.`shipping_amount`							as invoice_shipping_net
				
			FROM 
				{$this->quoteTable('sales_flat_quote', 'q')},
				{$this->quoteTable('sales_flat_quote_payment', 'p')},
				{$this->quoteTable('sales_flat_order', 'o')}
			LEFT JOIN {$this->quoteTable('sales_flat_order_address', 'ba')}
			ON ba.parent_id=o.entity_id
			AND ba.address_type='billing'
			LEFT JOIN {$this->quoteTable('sales_flat_order_address', 'sa')}
			ON sa.parent_id=o.entity_id
			AND sa.address_type='shipping'
			WHERE o.quote_id = q.entity_id
			AND p.quote_id = q.entity_id
		";

	}

    /**
   	 * Returns the sql statement to select all shop system order details
   	 * @return string {String} | sql for order details
   	 */
	public function getOrderDetailSelect()
	{
		return "		
			SELECT	
				order_id as orderID,
				product_id  as productID,
				sku as article_ordernumber,
				name,
				price_incl_tax as price,
				qty_ordered as quantity,
				tax_percent as tax,
				0 as modus
			FROM {$this->quoteTable('sales_flat_order_item')}
		";
	}

    /**
     * Returns the sql statement to select the shop system article attribute fields
     * @param string $type
     * @param null $attributes
     * @param null $store_id
     * @param bool $full_select
     * @return string
     */
	public function createTableSelect($type='catalog_product', $attributes=null, $store_id=null, $full_select=false)
	{
		$sql = "
			SELECT
				ea.attribute_code 	as `name`,
				ea.attribute_id 	as `id`,
				ea.backend_type 	as `type`,
				ea.is_required		as `required`
			FROM {$this->quoteTable('eav_attribute', 'ea')}, {$this->quoteTable('eav_entity_type', 'et')}
			WHERE ea.`entity_type_id`=et.entity_type_id
			AND et.entity_type_code=?
			AND ea.frontend_input!=''
		";
		if(!empty($attributes)) {
			$sql .= 'AND ea.attribute_code IN ('.$this->Db()->quote($attributes).')';
		} else {
			$sql .= 'ORDER BY `required` DESC, `name`';
		}
		$attribute_fields = $this->Db()->fetchAssoc($sql, array($type));
		
		if(empty($attributes)) {
			$attributes = array_keys($attribute_fields);
		}
		
		$select_fields = array();
		$join_fields = '';
		
		foreach ($attributes as $attribute) {
			if(empty($attribute_fields[$attribute])) {
				$join_fields .= "
					LEFT JOIN (SELECT 1 as attribute_id, NULL as value) as `$attribute`
					ON 1
				";
			} else {
				if($attribute_fields[$attribute]['type']=='static') {
					$select_fields[] = "{$this->quoteTable($type)}.{$attribute}";
				} else {
					$join_fields .= "
						LEFT JOIN {$this->quoteTable($type.'_entity_'.$attribute_fields[$attribute]['type'], $attribute)}
						ON	{$attribute}.attribute_id = {$attribute_fields[$attribute]['id']}
						AND {$attribute}.entity_id = {$this->quoteTable($type)}.entity_id
					";
					if($store_id!==null) {
						$join_fields .= "
						AND {$attribute_fields[$attribute]['name']}.store_id = {$this->Db()->quote($store_id)}
						";
					}
					$select_fields[] = "{$attribute}.value as `{$attribute}`";
				}
			}
		}
		if(!$full_select) {
			return $join_fields;
		} else {
			$select_fields = implode(', ', $select_fields);
			return "
				SELECT $select_fields
				FROM {$this->quoteTable($type.'_entity', $type)}
				$join_fields
			";
		}
	}
}