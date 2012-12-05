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
 * Shopware SwagMigration Components - XtCommerce
 *
 * @category  Shopware
 * @package Shopware\Plugins\SwagMigration\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Migration_Profile_XtCommerce extends Shopware_Components_Migration_Profile
{
    /**
   	 * Returns the directory of the article images.
   	 * @return string {String} | image path
   	 */
	public function getProductImagePath()
	{
		return 'images/product_images/original_images/';
	}

    /**
   	 * Returns the sql statement to select default shop system language
   	 * @return string {String} | sql for default language
   	 */
	public function getDefaultLanguageSelect()
	{
		return 'SELECT `languages_id` FROM `languages` ORDER BY `sort_order` ASC';
	}

    /**
   	 * Returns the sql statement to select the shop system languages
   	 * @return string {String} | sql for languages
   	 */
	public function getLanguageSelect()
	{
		return "
			SELECT `languages_id` as id, `name`
			FROM {$this->quoteTable('languages')}
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
			FROM {$this->quoteTable('customers_status')}
			WHERE language_id={$this->Db()->quote($this->getDefaultLanguage())}
		";
	}

    /**
   	 * Returns the sql statement to select the shop system payments
   	 * @return string {String} | sql for the payments
   	 */
	public function getPaymentMeanSelect()
	{
		return "
			SELECT `payment_method` as id, `payment_class` as name
			FROM {$this->quoteTable('orders')}
			GROUP BY `payment_class`
		";
	}

    /**
   	 * Returns the sql statement to select the shop system order states
   	 * @return string {String} | sql for the order states
   	 */
	public function getOrderStatusSelect()
	{
		return "
			SELECT `orders_status_id` as id, `orders_status_name` as name
			FROM {$this->quoteTable('orders_status')}
			WHERE `language_id`={$this->Db()->quote($this->getDefaultLanguage())}
		";
	}

    /**
   	 * Returns the sql statement to select the shop system tax rates
   	 * @return string {String} | sql for the tax rates
   	 */
	public function getTaxRateSelect()
	{
		return "
			SELECT `tax_class_id` as id, `tax_class_title` as name
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
				SELECT 'fsk18' as id, 'FSK 18' as name
			UNION
				SELECT 'asin' as id, 'ASIN' as name
			UNION
				SELECT 'ean' as id, 'EAN' as name
			UNION
				SELECT 'tags' as id, 'Stichworte' as name
			UNION
				SELECT 'meta_title' as id, 'Meta Title' as name
			UNION
				SELECT 'meta_description' as id, 'Meta Description' as name
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
				-- a.products_average_quantity			as stockmin,
				a.products_shippingtime					as shippingtime,
				a.products_model						as ordernumber,
				-- a.products_image						as image,
				a.products_price						as net_price,
				a.products_date_available 				as releasedate,
				a.products_date_added					as added,
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
				d.products_keywords 					as tags,
				d.products_meta_title					as meta_title,
				d.products_meta_description 			as meta_description,
				d.products_meta_keywords 				as keywords,
				d.products_url							as link

			FROM {$this->quoteTable('products', 'a')}
			
			LEFT JOIN {$this->quoteTable('manufacturers', 's')}
			ON s.manufacturers_id=a.manufacturers_id

			LEFT JOIN {$this->quoteTable('products_description', 'd')}
			ON d.products_id=a.products_id
			AND d.language_id={$this->Db()->quote($this->getDefaultLanguage())}
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
		
		$sql = array();
		
		if(!empty($price_groups)) {
			foreach ($price_groups as $price_group) {
				$sql[] = "
					SELECT 
						`products_id` as productID,
						`quantity` as `from`,
						`personal_offer` as `net_price`,
						'$price_group' as pricegroup
					FROM {$this->quoteTable('personal_offers_by_customers_status_'.$price_group)}
					WHERE `personal_offer`!=0
					ORDER BY productID, `from`
				";
			}
		}
		return '('.implode(') UNION ALL (', $sql).')';
	}

    /**
   	 * Returns the sql statement to select the shop system article image allocation
   	 * @return string {String} | sql for the article image allocation
   	 */
	public function getProductImageSelect()
	{
		return "
			(
				SELECT `products_id` as productID, `products_image` as image, 1 as main, 0 as position
				FROM products
				WHERE `products_image`!='' AND `products_image` IS NOT NULL
			) UNION ALL (
				SELECT `products_id` as productID, `image_name` as image, 0 as main, image_nr as position
				FROM products_images
			)
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
				d.products_id 					as productID,
				d.language_id 					as languageID,
				d.products_name 				as name,
				d.products_description 			as description_long,
				d.products_short_description 	as description,
				d.products_keywords 			as tags,
				d.products_meta_title			as meta_title,
				d.products_meta_description 	as meta_description,
				d.products_meta_keywords		as keywords
			FROM {$this->quoteTable('products_description', 'd')}  
			WHERE `language_id`!={$this->Db()->quote($this->getDefaultLanguage())}
		";
	}

    /**
   	 * Returns the sql statement to select the shop system article relations
   	 * @return string {String} | sql for the article relations
   	 */
	public function getProductRelationSelect()
	{
		return "
			SELECT `products_id` as productID, `xsell_id` as relatedID, `products_xsell_grp_name_id` as groupID
			FROM {$this->quoteTable('products_xsell')}
		";
	}

    /**
     * This function creates an database index on the orders table
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryCustomers($offset=0)
	{
		if($offset===0) {
			try {
				$sql = 'ALTER TABLE `orders` DROP INDEX `customers_id`;';
				$this->Db()->exec($sql);
			} catch (Exception $e) {}
			try {
				$sql = 'ALTER TABLE `orders` ADD INDEX ( `customers_id` );';
				$this->Db()->exec($sql);
			} catch (Exception $e) {}
		}
		return parent::queryCustomers($offset);
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
				
				IF(a.entry_gender IN ('m', 'Herr'), 'mr', 'ms')		as billing_salutation,
				a.entry_firstname									as billing_firstname,
				a.entry_lastname 	 								as billing_lastname,
				a.entry_company		 								as billing_company,
				'' 													as billing_department,
				a.entry_street_address	 							as billing_street,
				'' 													as billing_streetnumber,
				a.entry_postcode 									as billing_zipcode,
				a.entry_city	 									as billing_city,
				c.countries_iso_code_2 								as billing_countryiso,
				
				IF(a.entry_gender IN ('m', 'Herr'), 'mr', 'ms') 	as shipping_salutation,
				o.delivery_firstname 								as shipping_firstname,
				o.delivery_lastname 								as shipping_lastname,
				o.delivery_company 									as shipping_company,
				'' 													as shipping_department,
				o.delivery_street_address  							as shipping_street,
				'' 													as shipping_streetnumber,
				o.delivery_postcode  								as shipping_zipcode,
				o.delivery_city  									as shipping_city,
				o.delivery_country_iso_code_2 						as shipping_countryiso,
				
				u.customers_telephone 								as phone,
				u.customers_fax 									as fax,
				u.customers_email_address 							as email,
				DATE(u.customers_dob)								as birthday,
				u.customers_vat_id 									as ustid,
				u.customers_newsletter								as newsletter,
				
				u.customers_password 								as md5_password,
				
				u.customers_status									as customergroupID,
				
				u.customers_date_added 								as firstlogin,
				IFNULL(o.date_purchased, u.customers_date_added)	as lastlogin,
				IF(u.delete_user=1, 0, 1)							as active
				
			FROM {$this->quoteTable('customers', 'u')}
			
			JOIN {$this->quoteTable('address_book', 'a')}
			ON a.customers_id=u.customers_id
			AND a.address_book_id=u.customers_default_address_id

			LEFT JOIN {$this->quoteTable('orders', 'o')}
			ON o.orders_id = (
				SELECT orders_id
				FROM {$this->quoteTable('orders')}
				WHERE customers_id=u.customers_id
				ORDER BY orders_id DESC
				LIMIT 1
			)
			
			LEFT JOIN {$this->quoteTable('countries', 'c')}
			ON c.countries_id=a.entry_country_id
		";
	}

    /**
   	 * Returns the sql statement to select the shop system article category allocation
   	 * @return string {String} | sql for the article category allocation
   	 */
	public function getProductCategorySelect()
	{
		return "
			SELECT `products_id` as productID, `categories_id` as categoryID
			FROM {$this->quoteTable('products_to_categories')}
			ORDER BY `products_id`
		";
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
				parent_id as parentID,
				language_id as languageID,
				categories_name as description,
				sort_order as position,
				categories_meta_keywords as metakeywords,
				categories_meta_description as metadescription,
				categories_heading_title as cmsheadline,
				categories_description as cmstext,
				categories_status as active		
			FROM 
				{$this->quoteTable('categories', 'co')},
				{$this->quoteTable('categories_description', 'cd')}
			WHERE co.categories_id=cd.categories_id
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
				r.`customers_id` as `customerID`,
				r.`customers_name` as `name`,
				c.`customers_email_address` as `email`,
				`reviews_rating` as `rating`,
				`date_added` as `date`,
				1 as `active`,
				`reviews_text` as `comment`,
				'' as `title`
			FROM {$this->quoteTable('reviews', 'r')}
			
			LEFT JOIN {$this->quoteTable('reviews_description', 'd')}
			ON d.reviews_id=r.reviews_id
				
			LEFT JOIN {$this->quoteTable('customers', 'c')}
			ON r.customers_id=c.customers_id
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
				o.`orders_id`									as orderID,
				o.`orders_id`									as ordernumber,
				u.`customers_id`								as customerID,
				o.`customers_vat_id`							as ustid,
				
				IF(a.entry_gender IN ('m', 'Herr'), 'mr', 'ms')	as billing_salutation,
				`billing_firstname`, 
				`billing_lastname`,
				`billing_company`,
				-- `billing_company_2`,
				-- `billing_company_3`,
				`billing_street_address`						as billing_street,
				-- `billing_suburb`,
				`billing_city`,
				`billing_postcode`								as billing_zipcode,
				`billing_country_iso_code_2`					as billing_countryiso,		
						
				IF(a.entry_gender IN ('m', 'Herr'), 'mr', 'ms') as shipping_salutation,
				`delivery_firstname`							as shipping_firstname,
				`delivery_lastname`								as shipping_lastname,
				`delivery_company`								as shipping_company,
				-- `delivery_company_2`,
				-- `delivery_company_3`,
				`delivery_street_address`						as shipping_street,
				-- `delivery_suburb`,
				`delivery_city`									as shipping_city,
				`delivery_postcode`								as shipping_zipcode,
				`delivery_country_iso_code_2`					as shipping_countryiso,
								
				o.`customers_telephone`							as phone,
				-- `billing_fax`								as fax,
				`payment_class`									as paymentID,
				`shipping_class`								as dispatchID,
				`currency`										as currency,
				`currency_value`								as currency_factor,
				-- `language_code`								as languageID,
				`comments`										as customercomment,
				`date_purchased`								as date,
				`orders_status`									as status,
				-- `orders_date_finished`,
				-- IF(o.`allow_tax`=1,0,1)						as tax_free,
				o.`customers_ip`								as remote_addr,
				-- `shop_id`									as subshopID,
				
				(
					SELECT `value` 
					FROM {$this->quoteTable('orders_total')}
					WHERE `class` = 'ot_shipping'
					AND `orders_id`=o.`orders_id`
				)												as invoice_shipping,
				(
					SELECT `value` 
					FROM {$this->quoteTable('orders_total')}
					WHERE `class` = 'ot_shipping'
					AND `orders_id`=o.`orders_id`
				)												as invoice_shipping_net,
				(
					SELECT `value` 
					FROM {$this->quoteTable('orders_total')}
					WHERE `class` = 'ot_total'
					AND `orders_id`=o.`orders_id`
				)												as invoice_amount,
				(
					SELECT `value` 
					FROM {$this->quoteTable('orders_total')}
					WHERE `class`='ot_total'
					AND `orders_id`=o.`orders_id`
				)-(
					SELECT SUM(`value`)
					FROM {$this->quoteTable('orders_total')}
					WHERE `class`='ot_tax'
					AND `orders_id`=o.`orders_id`
				)												as invoice_amount_net
				
			FROM {$this->quoteTable('orders', 'o')}
			
			LEFT JOIN {$this->quoteTable('customers', 'u')}
			ON u.customers_id=o.customers_id
			
			LEFT JOIN {$this->quoteTable('address_book', 'a')}
			ON a.customers_id=u.customers_id
			AND a.address_book_id=u.customers_default_address_id
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
				`orders_id` as orderID,
				`products_id` as productID,
				`products_model` as article_ordernumber,
				`products_name` as name,
				`products_price` as price,
				`products_quantity` as quantity
				
			FROM {$this->quoteTable('orders_products')}
		";
	}
}