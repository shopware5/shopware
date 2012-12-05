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
 * Shopware SwagMigration Components - Prestashop
 *
 * @category  Shopware
 * @package Shopware\Plugins\SwagMigration\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Migration_Profile_Prestashop extends Shopware_Components_Migration_Profile
{

    protected $db_prefix = 'ps_';

    /**
   	 * Returns the directory of the article images.
   	 * @return string {String} | image path
   	 */
	public function getProductImagePath()
	{
		return 'img/p/';
	}

    /**
   	 * Returns the sql statement to select default shop system language
   	 * @return string {String} | sql for default language
   	 */
	public function getDefaultLanguageSelect()
	{
		return "SELECT `id_lang` FROM {$this->quoteTable('lang')} WHERE active=1 ORDER BY id_lang ASC";
	}

    /**
   	 * Returns the sql statement to select the shop system languages
   	 * @return string {String} | sql for languages
   	 */
	public function getLanguageSelect()
	{
        return "SELECT `id_lang` as id, name as name FROM {$this->quoteTable('lang')}";

	}

    /**
   	 * Returns the sql statement to select the shop system sub shops
   	 * @return string {String} | sql for sub shops
   	 */
	public function getShopSelect()
	{
		return "
			SELECT s.id_shop as id, s.name as name, CONCAT(su.domain, su.physical_uri) as url
			FROM {$this->quoteTable('shop', 's')}
			LEFT JOIN {$this->quoteTable('shop_url', 'su')} ON su.id_shop = s.id_shop
		";
	}

    /**
   	 * Returns the sql statement to select the shop system customer groups
   	 * @return string {String} | sql for customer groups
   	 */
	public function getCustomerGroupSelect()
	{
		return "
			SELECT g.id_group as id, gl.name as name
			FROM {$this->quoteTable('group', 'g')}
			LEFT JOIN {$this->quoteTable('group_lang', 'gl')} ON g.id_group=gl.id_group
			WHERE gl.id_lang={$this->Db()->quote($this->getDefaultLanguage())}
		";
	}

    /**
   	 * Returns the sql statement to select the shop system payments
   	 * @return string {String} | sql for the payments
   	 */
	public function getPaymentMeanSelect()
	{
		return "
			SELECT o.module as id, o.module as name
			FROM {$this->quoteTable('orders', 'o')}
			GROUP BY o.module
		";
	}

    /**
   	 * Returns the sql statement to select the shop system order states
   	 * @return string {String} | sql for the order states
   	 */
	public function getOrderStatusSelect()
	{
		return "
			SELECT `id_order_state` as id, `name` as name
			FROM {$this->quoteTable('order_state_lang')}
			WHERE `id_lang`={$this->Db()->quote($this->getDefaultLanguage())}
		";
	}

    /**
   	 * Returns the sql statement to select the shop system tax rates
   	 * @return string {String} | sql for the tax rates
   	 */
	public function getTaxRateSelect()
	{
		return "
			SELECT `id_tax` as id, `rate` as name
			FROM {$this->quoteTable('tax')}
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
            id_attribute_group as id, name as name
			FROM {$this->quoteTable('attribute_group_lang')}
            WHERE id_lang={$this->Db()->quote($this->getDefaultLanguage())}
            GROUP BY id_attribute_group
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
				a.id_product							as productID,

				st.quantity     						as instock,
				-- a.products_average_quantity			as stockmin,
                -- a.products_shippingtime					as shippingtime,
				if(a.reference='', CONCAT('sw', a.id_product), a.reference)						as ordernumber,
				-- a.products_image						as image,
				a.price         						as net_price,
				a.available_date         				as releasedate,
				a.date_add          					as added,
				a.date_upd 						        as changed,
				a.weight        						as weight,
				tr.id_tax           					as taxID,
				s.name              					as supplier,
				a.active        						as active,

				a.ean13					        		as ean,

				d.name                                  as name,
				d.description        					as description_long,
				d.description_short          			as description,
				d.meta_title        					as meta_title,
				d.meta_description           			as meta_description,
				d.meta_keywords          				as keywords

			FROM {$this->quoteTable('product', 'a')}

			LEFT JOIN {$this->quoteTable('tax_rule', 'tr')}
			ON tr.id_tax_rules_group=a.id_tax_rules_group

			LEFT JOIN {$this->quoteTable('manufacturer', 's')}
			ON s.id_manufacturer=a.id_manufacturer

            LEFT JOIN {$this->quoteTable('stock_available', 'st')}
            ON st.id_product=a.id_product

			LEFT JOIN {$this->quoteTable('product_lang', 'd')}
			ON d.id_product=a.id_product

			WHERE d.id_lang={$this->Db()->quote($this->getDefaultLanguage())}
			AND st.id_product_attribute=0
		";
	}

    /**
   	 * Returns the sql statement to select the shop system article prices
   	 * @return string {String} | sql for the article prices
   	 */
	public function getProductPriceSelect()
	{
		$sql = "
			SELECT `id_group`
			FROM {$this->quoteTable('group')}
		";
		$price_groups = $this->db->fetchCol($sql);

		$sql = array();

		if(!empty($price_groups)) {
			foreach ($price_groups as $price_group) {
				$sql[] = "
					SELECT
						pr.id_product as productID,
						pr.from_quantity as `from`,
						IF(reduction_type='percentage', a.price*(1-reduction),a.price-reduction) as `net_price`,
						'$price_group' as pricegroup
					FROM {$this->quoteTable('specific_price', 'pr')}

					LEFT JOIN {$this->quoteTable('product', 'a')}
					ON a.id_product=pr.id_product

					WHERE `id_group`=$price_group || `id_group`=0
					ORDER BY id_product, `from`
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
        // prestashop generates the image path from the "id_image" ID by splitting it after
        // each char and concatening it with slashes. This behaviour is somewhat hard
        // to reproduce with sql
        // perhaps this should rather be done via php
        $replaceSql = "
        CONCAT(
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
            REPLACE(REPLACE(REPLACE(REPLACE(
                id_image,
            0, '0/'), 1, '1/') , 2, '2/') , 3, '3/') , 4, '4/'), 5, '5/'),
            6, '6/'), 7, '7/'), 8, '8/'), 9, '9/'),
        id_image,
        '.jpg'
        ) as image";

		return "
				SELECT `id_product` as productID, $replaceSql, cover as main, position as position
				FROM {$this->quoteTable('image')}
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
				d.id_product 					as productID,
				d.id_lang 			    		as languageID,
				d.name 		        	    	as name,
				d.description 			        as description_long,
				d.description_short 	        as description,
				'' 			                    as tags,
				d.meta_title			        as meta_title,
				d.meta_description 	            as meta_description,
				d.meta_keywords		            as keywords
			FROM {$this->quoteTable('product_lang', 'd')}
			WHERE `id_lang`!={$this->Db()->quote($this->getDefaultLanguage())}
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
   	 * Returns the sql statement to select the shop system customer
   	 * @return string {String} | sql for the customer data
   	 */
	public function getCustomerSelect()
	{
		return "
			SELECT
				u.id_customer 										as customerID,
				u.id_customer 										as customernumber,
				u.id_shop                                           as subshopID,

				IF(g.type=0, 'mr', 'ms')		                    as billing_salutation,
				u.firstname                                         as billing_firstname,
				u.lastname       	 								as billing_lastname,
				u.company   		 								as billing_company,
				'' 													as billing_department,
				a.address1          	 							as billing_street,
				'' 													as billing_streetnumber,
				a.postcode       									as billing_zipcode,
				a.city	 								        	as billing_city,
				c2.iso_code           								as billing_countryiso,

				IF(g.type=0, 'mr', 'ms')                    	    as shipping_salutation,
				u.firstname 						        		as shipping_firstname,
				u.lastname 							            	as shipping_lastname,
				u.company 							        		as shipping_company,
				'' 													as shipping_department,
				a.address1  						            	as shipping_street,
				'' 													as shipping_streetnumber,
				a.postcode            								as shipping_zipcode,
				a.city  								        	as shipping_city,
				c2.iso_code                   						as shipping_countryiso,

				a.phone 					            			as phone,
				''               									as fax,
				u.email                 							as email,
				DATE(u.birthday)				    				as birthday,
				a.vat_number     									as ustid,
				u.newsletter        								as newsletter,

				u.passwd 								            as md5_password,

				u.id_default_group									as customergroupID,

				u.date_add           								as firstlogin,
				u.date_upd	                                        as lastlogin,
				u.active                							as active

			FROM {$this->quoteTable('customer', 'u')}

			LEFT JOIN {$this->quoteTable('address', 'a')}
			ON a.id_customer=u.id_customer
			AND a.id_customer=u.id_customer

			LEFT JOIN {$this->quoteTable('gender', 'g')}
			ON g.id_gender=u.id_gender

			LEFT JOIN {$this->quoteTable('country_lang', 'c')}
			ON c.id_country=a.id_country

			LEFT JOIN {$this->quoteTable('country', 'c2')}
            ON c2.id_country=a.id_country

			WHERE c.id_lang={$this->Db()->quote($this->getDefaultLanguage())}
		";
	}

    /**
   	 * Returns the sql statement to select the shop system article category allocation
   	 * @return string {String} | sql for the article category allocation
   	 */
	public function getProductCategorySelect()
	{
		return "
			SELECT `id_product` as productID, `id_category` as categoryID
			FROM {$this->quoteTable('category_product')}
			ORDER BY `id_product`
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
				c.id_category as categoryID,
				IF(c.id_parent=1, '', c.id_parent) as parentID,
				cl.id_lang as languageID,
				cl.name as description,
				c.position as position,
				cl.meta_keywords as metakeywords,
				cl.meta_description as metadescription,
				c.active as active

			FROM  {$this->quoteTable('category', 'c')}

            LEFT JOIN {$this->quoteTable('category_lang', 'cl')}
            ON cl.id_category=c.id_category

            WHERE c.id_category>1

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
			    r.`id_product` as `productID`,
				r.`id_customer` as `customerID`,
				r.`customer_name` as `name`,
				IFNULL(c.`email`, '') as `email`,
				r.`grade` as `rating`,
				r.`date_add` as `date`,
				1 as `active`,
				`content` as `comment`,
				r.title as `title`
			FROM {$this->quoteTable('product_comment', 'r')}

			LEFT JOIN {$this->quoteTable('customer', 'c')}
			ON r.id_customer=c.id_customer
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
				o.`id_order`									as orderID,
				o.`reference`									as ordernumber,
				u.`id_customer`								as customerID,
				aBilling.`vat_number`							as ustid,
				o.id_shop                                       as subshopID,


				IF(g.type=0, 'mr', 'ms')		                as billing_salutation,
				u.firstname                                         as billing_firstname,
				u.lastname       	 								as billing_lastname,
				u.company   		 								as billing_company,
				'' 													as billing_department,
				aBilling.address1          	 						as billing_street,
				'' 													as billing_streetnumber,
				aBilling.postcode       							as billing_zipcode,
				aBilling.city	 								    as billing_city,
				cBilling.iso_code           						as billing_countryiso,


				IF(g.type=0, 'mr', 'ms')		                    as shipping_salutation,
				u.firstname                                         as shipping_firstname,
				u.lastname       	 								as shipping_lastname,
				u.company   		 								as shipping_company,
				'' 													as shipping_department,
				aShipping.address1          	 					as shipping_street,
				'' 													as shipping_streetnumber,
				aShipping.postcode       							as shipping_zipcode,
				aShipping.city	 								    as shipping_city,
				cShipping.iso_code           						as shipping_countryiso,

				aBilling.`phone`							    as phone,
				`module`									    as paymentID,
				`id_carrier`								    as dispatchID,
				c.`iso_code`										    as currency,
				o.`conversion_rate`								as currency_factor,
				o.`id_lang`								        as languageID,
				GROUP_CONCAT(cm.`message`)                      as customercomment,
				o.`date_add`								        as date,
				`current_state`									as status,
				-- `orders_date_finished`,
				-- IF(o.`allow_tax`=1,0,1)						as tax_free,
				-- o.`customers_ip`								as remote_addr,

				o.total_shipping_tax_incl                       as invoice_shipping,
				o.total_shipping                                as invoice_shipping_net,
				o.total_paid_tax_incl   						as invoice_amount,
				o.total_paid_tax_excl							as invoice_amount_net

			FROM {$this->quoteTable('orders', 'o')}

			LEFT JOIN {$this->quoteTable('customer', 'u')}
			ON u.id_customer=o.id_customer

			LEFT JOIN {$this->quoteTable('gender', 'g')}
			ON g.id_gender=u.id_gender

			LEFT JOIN {$this->quoteTable('address', 'aShipping')}
			ON aShipping.id_address=o.id_address_delivery

			LEFT JOIN {$this->quoteTable('address', 'aBilling')}
			ON aBilling.id_address=o.id_address_invoice

			LEFT JOIN {$this->quoteTable('country', 'cBilling')}
            ON cBilling.id_country=aBilling.id_country

			LEFT JOIN {$this->quoteTable('country', 'cShipping')}
            ON cShipping.id_country=aShipping.id_country

            LEFT JOIN {$this->quoteTable('currency', 'c')}
            ON c.id_currency=o.id_currency


            LEFT JOIN {$this->quoteTable('customer_thread', 'ct')}
            ON ct.id_order=o.id_order

            LEFT JOIN {$this->quoteTable('customer_message', 'cm')}
            ON cm.id_customer_thread=ct.id_customer_thread

            GROUP BY o.id_order

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
				od.`id_order` as orderID,
				od.`product_id` as productID,
				o.`reference`	as ordernumber,
                if(p.reference='', CONCAT('sw', p.id_product), p.reference) as article_ordernumber,
				od.`product_name` as name,
				od.`product_price` as price,
				od.`product_quantity` as quantity,
				odt.id_tax as tax


			FROM {$this->quoteTable('order_detail', 'od')}

            LEFT JOIN {$this->quoteTable('orders', 'o')}
            ON o.id_order=od.id_order

            LEFT JOIN {$this->quoteTable('product', 'p')}
            ON p.id_product=od.product_id

            LEFT JOIN {$this->quoteTable('order_detail_tax', 'odt')}
            ON odt.id_order_detail=od.id_order_detail


		";
	}
}