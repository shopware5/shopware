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
 * Shopware SwagMigration Components - Veyton
 *
 * @category  Shopware
 * @package Shopware\Plugins\SwagMigration\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Migration_Profile_Veyton extends Shopware_Components_Migration_Profile_XtCommerce
{
    /**
     * Prefix of each database tables
     * @var string
     */
	protected $db_prefix = 'xt_';

    /**
   	 * Returns the directory of the article images.
   	 * @return string {String} | image path
   	 */
	public function getProductImagePath()
	{
		return 'media/images/org/';
	}

    /**
   	 * Returns the sql statement to select the shop system sub shops
   	 * @return string {String} | sql for sub shops
   	 */
	public function getShopSelect()
	{
		return "
			SELECT `shop_id` as id, `shop_title` as name, `shop_domain` as domain
			FROM {$this->quoteTable('stores')}
		";
	}

    /**
   	 * Returns the sql statement to select the shop system customer groups
   	 * @return string {String} | sql for customer groups
   	 */
	public function getCustomerGroupSelect()
	{
		return "
			SELECT `customers_status_id` as id, `customers_status_name` as name
			FROM {$this->quoteTable('customers_status_description')}
			WHERE language_code='de'
		";
	}

    /**
   	 * Returns the sql statement to select the shop system payments
   	 * @return string {String} | sql for the payments
   	 */
	public function getPaymentMeanSelect()
	{
		return "
			SELECT `payment_code` as id, `payment_name` as name
			FROM {$this->quoteTable('payment', 'p')}, {$this->quoteTable('payment_description', 'pd')}
			WHERE p.`payment_id`=pd.`payment_id`
			AND `language_code`='de'
		";
	}

    /**
   	 * Returns the sql statement to select the shop system order states
   	 * @return string {String} | sql for the order states
   	 */
	public function getOrderStatusSelect()
	{
		return "
			SELECT s.`status_id` as id, `status_name` as name
			FROM {$this->quoteTable('system_status', 's')}
			LEFT JOIN {$this->quoteTable('system_status_description', 'sd')}
			ON sd.status_id=s.status_id
			AND sd.language_code='de'
			WHERE `status_class` = 'order_status'
		";
	}

    /**
   	 * Returns the sql statement to select the shop system articles
   	 * @return string {String} | sql for the articles
   	 */
	public function getProductSelect()
	{
		return "
			SELECT 
				a.products_id							as productID,

				a.products_quantity						as instock,
				a.products_average_quantity				as stockmin,
				a.products_shippingtime					as shippingtime,
				a.products_model						as ordernumber,
				-- a.products_image						as image,
				a.products_price						as price,
				a.date_available						as releasedate,
				a.date_added							as added,
				-- a.last_modified 						as changed,
				a.products_weight						as weight,
				a.products_tax_class_id					as taxID,
				s.manufacturers_name					as supplier,
				a.products_status						as active,
				
				a.products_fsk18						as fsk18,
				a.products_ean							as ean,
				
				d.products_name 						as name,
				d.products_description 					as description_long,
				d.products_short_description 			as description,
				d.products_keywords 					as keywords
				
			FROM {$this->quoteTable('products', 'a')}
			
			LEFT JOIN {$this->quoteTable('manufacturers', 's')}
			ON s.manufacturers_id=a.manufacturers_id
			
			LEFT JOIN {$this->quoteTable('products_description', 'd')}
			ON d.products_id=a.products_id
			AND d.language_code='de'
		";
	}

    /**
   	 * Returns the sql statement to select the shop system article prices
   	 * @return string {String} | sql for the article prices
   	 */
	public function getProductPriceSelect()
	{
		$sql = "
			SELECT `customers_status_id`
			FROM {$this->quoteTable('customers_status')}
			WHERE `customers_status_graduated_prices`=1
		";
		$price_groups = $this->db->fetchCol($sql);
		
		$sql = "
			(
				SELECT 
					`products_id` as productID,
					`discount_quantity` as `from`,
					`price`,
					'' as pricegroup
				FROM {$this->quoteTable('products_price_group_all')}
				ORDER BY productID, `from`
			)
		";
		
		if(!empty($price_groups)) {
			foreach ($price_groups as $price_group) {
				$sql .= "
				UNION ALL (
					SELECT 
						`products_id` as productID,
						`discount_quantity` as `from`,
						`price`,
						'$price_group' as pricegroup
					FROM {$this->quoteTable('products_price_group_'.$price_group)}
					ORDER BY productID, `from`
				)
				";
			}
		}
		
		return $sql;
	}

    /**
   	 * Returns the sql statement to select the shop system article image allocation
   	 * @return string {String} | sql for the article image allocation
   	 */
	public function getProductImageSelect()
	{
		return "
			SELECT 
				IFNULL(p.products_id, ml.link_id) as productID,
				m.file as image,
				md.media_name as description,
				ml.sort_order as position,
				IF(p.products_id, 1, 0) as main
				
			FROM {$this->quoteTable('media', 'm')}
			
			LEFT JOIN {$this->quoteTable('media_link', 'ml')}
			ON ml.m_id=m.id
			
			LEFT JOIN {$this->quoteTable('products', 'p')}
			ON p.products_image=m.file
			
			LEFT JOIN {$this->quoteTable('media_description', 'md')}
			ON md.id=m.id
			AND md.`language_code`='de'
			
			WHERE m.`type`='images'
			AND m.`class`='product'
			AND m.`status`='true'
		";
	}

    /**
   	 * Returns the sql statement to select the shop system article translations
   	 * @return string {String} | sql for the article translations
   	 */
	public function getProductTranslationSelect()
	{
		return "
			SELECT 
				d.products_id as productID,
				d.language_code as languageID,
				d.products_name as name,
				d.products_description as description_long,
				d.products_short_description as description,
				d.products_keywords as keywords
			FROM {$this->quoteTable('products_description', 'd')}  
			WHERE `language_code`!='de'
		";
	}

    /**
   	 * Returns the sql statement to select the shop system article relations
   	 * @return string {String} | sql for the article relations
   	 */
	public function getProductRelationSelect()
	{
		return "
			SELECT `products_id` as productID, `products_id_cross_sell` as relatedID, 1 as groupID
			FROM {$this->quoteTable('products_cross_sell')}
		";
	}

    /**
   	 * Returns the sql statement to select the shop system customer
   	 * @return string {String} | sql for the customer data
   	 */
	public function getCustomerSelect()
	{
		return "
			SELECT
				u.customers_id 										as customerID,
				u.customers_id 										as customernumber,
				
				IF(a.customers_gender IN ('m', 'Herr'), 'mr', 'ms')	as billing_salutation,
				a.customers_firstname								as billing_firstname,
				a.customers_lastname 								as billing_lastname,
				a.customers_company 								as billing_company,
				'' 													as billing_department,
				a.customers_street_address 							as billing_street,
				'' 													as billing_streetnumber,
				a.customers_postcode 								as billing_zipcode,
				a.customers_city 									as billing_city,
				a.customers_country_code 							as billing_countryiso,
				
				IF(a.customers_gender IN ('m', 'Herr'), 'mr', 'ms') as shipping_salutation,
				o.delivery_firstname 								as shipping_firstname,
				o.delivery_lastname 								as shipping_lastname,
				o.delivery_company 									as shipping_company,
				'' 													as shipping_department,
				o.delivery_street_address  							as shipping_street,
				'' 													as shipping_streetnumber,
				o.delivery_postcode  								as shipping_zipcode,
				o.delivery_city  									as shipping_city,
				o.delivery_country_code 							as shipping_countryiso,
				
				a.customers_phone 									as phone,
				a.customers_fax 									as fax,
				u.customers_email_address 							as email,
				a.customers_dob 									as birthday,
				u.customers_vat_id 									as ustid,
				
				u.customers_password 								as md5_password,
				
				u.shop_id											as subshopID,
				u.customers_status									as customergroupID,
				
				u.date_added 										as firstlogin,
				IFNULL(o.date_purchased, u.date_added)				as lastlogin
				
			FROM {$this->quoteTable('customers', 'u')}
			
			JOIN {$this->quoteTable('customers_addresses', 'a')}
			ON a.customers_id=u.customers_id
			AND a.address_class='default'

			LEFT JOIN {$this->quoteTable('orders', 'o')}
			ON o.orders_id = (
				SELECT orders_id
				FROM {$this->quoteTable('orders')}
				WHERE customers_id=u.customers_id
				ORDER BY orders_id DESC
				LIMIT 1
			)
		";
	}

    /**
   	 * Returns the sql statement to select the shop system article category allocation
   	 * @return string {String} | sql for the article category allocation
   	 */
	public function getProductCategorySelect()
	{
		return parent::getProductCategorySelect().', `master_link` DESC';
	} 

    /**
   	 * Returns the sql statement to select the shop system categories
   	 * @return string {String} | sql for the categories
   	 */
	public function getCategorySelect()
	{
		return "
			SELECT
				co.categories_id as categoryID,
				co.parent_id as parentID,
				lg.languages_id as languageID,
				cd.categories_name as description,
				co.sort_order as position,
				cd.categories_heading_title as cmsheadline,
				cd.categories_description as cmstext,
				co.categories_status as active
			FROM 
				{$this->quoteTable('categories', 'co')},
				{$this->quoteTable('categories_description', 'cd')},
				{$this->quoteTable('languages', 'lg')}
			WHERE co.categories_id=cd.categories_id
			AND lg.code = cd.language_code
		";

	}

    /**
   	 * Returns the sql statement to select the shop system article ratings
   	 * @return string {String} | sql for the article ratings
   	 */
	public function getProductRatingSelect()
	{
		return "
			SELECT
				`products_id` as `productID`,
				c.`customers_id` as `customerID`,
				a.`customers_firstname` as `name`,
				c.`customers_email_address` as `email`,
				`review_rating` as `rating`,
				`review_date` as `date`,
				`review_status` as `active`,
				`review_text` as `comment`,
				`review_title` as `title`
			FROM
				{$this->quoteTable('products_reviews', 'r')},
				{$this->quoteTable('customers', 'c')},
				{$this->quoteTable('customers_addresses', 'a')}
			WHERE r.customers_id=c.customers_id
			AND a.customers_id=c.customers_id
			AND a.address_class='default'
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
				o.`orders_id`								as orderID,
				o.`orders_id`								as ordernumber,
				`customers_id`								as customerID,
				`customers_vat_id`							as ustid,
				
				IF(`billing_gender` IN ('m','Herr'), 'mr', 'ms') 
															as billing_salutation,
				`billing_firstname`, 
				`billing_lastname`,
				`billing_company`,
				-- `billing_company_2`,
				-- `billing_company_3`,
				`billing_street_address`					as billing_street,
				-- `billing_suburb`,
				`billing_city`,
				`billing_postcode`							as billing_zipcode,
				`billing_country_code`						as billing_countryiso,		
						
				IF(`delivery_gender` IN ('m','Herr'), 'mr', 'ms')
															as shipping_salutation,
				`delivery_firstname`						as shipping_firstname,
				`delivery_lastname`							as shipping_lastname,
				`delivery_company`							as shipping_company,
				-- `delivery_company_2`,
				-- `delivery_company_3`,
				`delivery_street_address`					as shipping_street,
				-- `delivery_suburb`,
				`delivery_city`								as shipping_city,
				`delivery_postcode`							as shipping_zipcode,
				`delivery_country_code`						as shipping_countryiso,
								
				`billing_phone`								as phone,
				`billing_fax`								as fax,
				`payment_code`								as paymentID,
				`shipping_code`								as dispatchID,
				`currency_code`								as currency,
				`currency_value`							as currency_factor,
				`language_code`								as languageID,
				`comments`									as customercomment,
				`date_purchased`							as date,
				`orders_status`								as status,
				-- `orders_date_finished`,
				IF(o.`allow_tax`=1,0,1)						as tax_free,
				`customers_ip`								as remote_addr,
				`shop_id`									as subshopID,
				
				SUM(
					ROUND(op.`products_price`*op.`products_quantity`, 2)
				) + IFNULL(ROUND(`orders_total_price`*`orders_total_quantity`, 2), 0)
															as invoice_amount_net,
				SUM(
					ROUND(op.`products_price` * (IF(op.`allow_tax`=1,op.`products_tax`,0)+100)/100, 2) *
					op.`products_quantity`
				) +	IFNULL(ROUND(`orders_total_price`*(IF(ot.`allow_tax`=1,`orders_total_tax`,0)+100)/100, 2),0)								
															as invoice_amount,
				ROUND(`orders_total_price`*`orders_total_quantity`, 2)
															as invoice_shipping_net,
				ROUND(`orders_total_price`*(IF(ot.`allow_tax`=1,`orders_total_tax`,0)+100)/100, 2)
															as invoice_shipping
				
			FROM {$this->quoteTable('orders', 'o')}
			
			LEFT JOIN {$this->quoteTable('orders_total', 'ot')}
			ON ot.`orders_id`=o.`orders_id`
			AND ot.`orders_total_key`='shipping'
			
			LEFT JOIN {$this->quoteTable('orders_products', 'op')}
			ON op.`orders_id`=o.`orders_id`
			
			GROUP BY o.`orders_id`
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
			
				orders_id as orderID,
				products_id  as productID,
				
				products_model as article_ordernumber,
				products_name as name,
				ROUND(`products_price`*(IF(`allow_tax`=1,`products_tax`,0)+100)/100,2) as price,
				products_quantity as quantity,
				products_tax_class as taxID,
				0 as modus
				
			FROM {$this->quoteTable('orders_products')}
		";
	}
}