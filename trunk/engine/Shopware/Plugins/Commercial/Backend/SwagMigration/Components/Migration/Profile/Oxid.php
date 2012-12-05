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
 * Shopware SwagMigration Components - Oxid
 *
 * @category  Shopware
 * @package Shopware\Plugins\SwagMigration\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Migration_Profile_Oxid extends Shopware_Components_Migration_Profile
{
	protected $db_prefix = 'ox';

    /**
   	 * Returns the directory of the article images.
   	 * @return string {String} | image path
   	 */
	public function getProductImagePath()
	{
		return 'out/pictures/master/product/';
	}

    /**
   	 * Returns the sql statement to select the config base path
   	 * @return string {String} | sql for the config base path
   	 */
	public function getConfigSelect()
	{
		return "
			SELECT `OXVARNAME` as name, DECODE(`OXVARVALUE`, 'fq45QS09_fqyx09239QQ') as value, `OXVARTYPE` as type
			FROM {$this->quoteTable('config')}
		";
	}

    /**
   	 * Returns the shop system languages
   	 * @return array {Array} | languages
   	 */
	public function getLanguages()
	{
		return $this->Config()->aLanguages;
	}

    /**
     * Returns the keys of the shop system languages
     * @return array
     */
	public function getLanguageKeys()
	{
		$keys = array();
		$params = $this->Config()->aLanguageParams;
		foreach ($params as $id => $param) {
			$keys[$param['baseId']] = $id;
		}
		return $keys;
	}

    /**
   	 * Returns the sql statement to select the shop system sub shops
   	 * @return string {String} | sql for sub shops
   	 */
	public function getShopSelect()
	{
		return "
			SELECT `OXID` as id, `OXNAME` as name, `OXURL` as url
			FROM {$this->quoteTable('shops')}
		";
	}

    /**
   	 * Returns the sql statement to select the shop system price groups
   	 * @return string {String} | sql for price groups
   	 */
	public function getPriceGroupSelect()
	{
		return "
			SELECT `OXID` as id, `OXTITLE` as name
			FROM {$this->quoteTable('groups')}
			WHERE `OXID` LIKE 'oxidprice%'
		";
	}

    /**
   	 * Returns the sql statement to select the shop system payments
   	 * @return string {String} | sql for the payments
   	 */
	public function getPaymentMeanSelect()
	{
		return "
			SELECT `OXID` , `OXDESC`
			FROM {$this->quoteTable('payments')}
		";
	}

    /**
   	 * Returns an array of the order states mapping, with keys and descriptions
   	 * @return array {Array} | order states: key - description
   	 */
	public function getOrderStatus()
	{
		$status = array();
		$keys = array_keys($this->Config()->aOrderfolder);
		$values = array(
			'ORDERFOLDER_NEW'                               => 'Neu',
			'ORDERFOLDER_FINISHED'                          => 'Bearbeitet',
			'ORDERFOLDER_PROBLEMS'                          => 'Probleme'
		);
		foreach ($keys as $key) {
			$status[$key] = isset($values[$key]) ? $values[$key] : $key;
		}
		return $status;
	}

    /**
   	 * Returns the sql statement to select the shop system article attributes
   	 * @return string {String} | sql for the article attributes
   	 */
	public function getAttributeSelect()
	{
		return "
				SELECT 'length' as id, 'Länge' as name
			UNION
				SELECT 'width' as id, 'Weite' as name
			UNION
				SELECT 'height' as id, 'Höhe' as name
			UNION
				SELECT 'tags' as id, 'Stichworte' as name
			UNION
				SELECT 'ean' as id, 'EAN' as name
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
				u.OXID										as customerID,
				u.OXCUSTNR 									as customernumber,
				
				u.OXCOMPANY 								as billing_company,
				'' 											as billing_department,
				IF(u.OXSAL IN ('m','Herr','MR'), 'mr', 'ms') 	as billing_salutation,
				u.OXFNAME 									as billing_firstname,
				u.OXLNAME 									as billing_lastname,
				u.OXSTREET 									as billing_street,
				u.OXSTREETNR 								as billing_streetnumber,
				u.OXZIP 									as billing_zipcode,
				u.OXCITY 									as billing_city,
				bc.OXISOALPHA2								as billing_countryiso,
				u.OXADDINFO 								as billing_text1,
				
				IF(u.OXFON='', u.OXMOBFON, u.OXFON) 		as phone,
				u.OXFAX 									as fax,
				u.OXUSERNAME 								as email,
				u.OXBIRTHDATE 								as birthday,
				u.OXUSTID 									as ustid,
				
				IF(OXDELSAL IN ('m','Herr','MR'), 'mr', 'ms')	as shipping_salutation,
				OXDELFNAME									as shipping_firstname,
				OXDELLNAME 									as shipping_lastname,
				OXDELCOMPANY 								as shipping_company,
				'' 											as shipping_department,
				OXDELSTREET 								as shipping_street,
				OXDELSTREETNR 								as shipping_streetnumber,
				OXDELZIP									as shipping_zipcode,
				OXDELCITY									as shipping_city,
				sc.OXISOALPHA2								as shipping_countryiso,
				OXDELADDINFO								as shipping_text1,
				
				u.OXPASSWORD								as password,
				u.OXCREATE									as firstlogin,
				IFNULL(o.OXORDERDATE, u.OXCREATE)			as lastlogin,
				u.OXSHOPID									as subshopID,
				
				IF(gb.OXID, 0, IF(u.OXACTIVE,1,0))			as active,
				IF(n.OXID, IF(gb.OXID, 0, IF(u.OXACTIVE,1,0)), 0)	as newsletter
				
			FROM {$this->quoteTable('user', 'u')}
			LEFT JOIN {$this->quoteTable('order', 'o')} ON o.OXID=(SELECT OXID FROM {$this->quoteTable('order')} WHERE OXUSERID=u.OXID ORDER BY OXORDERDATE DESC LIMIT 1)

			LEFT JOIN {$this->quoteTable('object2group', 'n')} ON n.OXOBJECTID=u.OXID AND n.OXGROUPSID='oxidnewsletter'
			-- LEFT JOIN oxobject2group gd ON gd.OXOBJECTID=u.OXID AND gd.OXGROUPSID='oxiddealer'
			LEFT JOIN {$this->quoteTable('object2group', 'gb')} ON gb.OXOBJECTID=u.OXID AND gb.OXGROUPSID='oxidblacklist'
			LEFT JOIN {$this->quoteTable('object2group', 'gb2')} ON gb2.OXOBJECTID=u.OXID AND gb2.OXGROUPSID='oxidblocked'
			
			LEFT JOIN {$this->quoteTable('country', 'bc')} ON bc.OXID=u.OXCOUNTRYID
			LEFT JOIN {$this->quoteTable('country', 'sc')} ON sc.OXID=o.OXDELCOUNTRYID
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
				a.OXID 				as `productID`,
				a.OXPARENTID 		as `parentID`,
				a.OXARTNUM 			as ordernumber,
				a.OXACTIVE 			as active,
				a.OXTITLE 			as name,
				a.OXVARSELECT 		as additionaltext,
			    COALESCE(a2.OXVARNAME, '') as variant_group_names,
				a.OXSHORTDESC 		as description,
				a.OXSEARCHKEYS 		as keywords,
				a.OXWEIGHT 			as weight,
				a.OXDELIVERY 		as releasedate,
				a.OXSTOCK 			as instock,
				a.OXREMINDAMOUNT	as minstock,
				a.OXMPN				as suppliernumber,
				a.OXFREESHIPPING	as shippingfree,
				CONCAT(
					IF(a.OXDELTIMEUNIT='WEEK', a.OXMINDELTIME*7, a.OXMINDELTIME),
					IF(a.OXMAXDELTIME!=0, CONCAT('-', IF(a.OXDELTIMEUNIT='WEEK', a.OXMAXDELTIME*7, a.OXMAXDELTIME)), '')
				)					as shippingtime,
				a.OXVAT				as tax,
				a.OXTPRICE 			as pseudoprice,
				a.OXBPRICE 			as baseprice,
				a.OXPRICE 			as price,

				-- a.OXPRICEA 		as price_A,
				-- a.OXPRICEB 		as price_B,
				-- a.OXPRICEC 		as price_C,

				s.OXTITLE 			as supplier,
				e.OXLONGDESC 		as description_long,
				e.OXTAGS 			as tags,

				a.OXEXTURL 			as link,
				a.OXURLDESC			as link_description,
				a.OXLENGTH 			as length,
				a.OXWIDTH 			as width,
				a.OXHEIGHT 			as height,

				a.OXUNITNAME			as packunit,
				a.OXUNITQUANTITY		as purchaseunit

			FROM {$this->quoteTable('articles', 'a')}

			LEFT JOIN {$this->quoteTable('manufacturers', 's')}
			ON s.OXID=a.OXMANUFACTURERID

			LEFT JOIN {$this->quoteTable('articles', 'a2')}
			ON a2.OXID=a.OXPARENTID

			LEFT JOIN {$this->quoteTable('artextends', 'e')}
			ON e.OXID=a.OXID

			ORDER BY `parentID`, a.OXSORT
        ";
	}

    /**
   	 * Returns the sql statement to select the shop system article prices
   	 * @return string {String} | sql for the article prices
   	 */
	public function getProductPriceSelect()
	{
		return " (
			SELECT 
				`OXARTID` as productID,
				`OXAMOUNT` as `from`,
				`OXADDABS` as `price`,
				`OXADDPERC` as `percent`,
				'' as pricegroup
			FROM {$this->quoteTable('price2article')}
			ORDER BY productID, `from`
		) UNION ALL (
			SELECT 
				`OXID` as productID,
				0 as `from`,
				`OXPRICEA` as `price`,
				0 as `percent`,
				'oxidpricea' as pricegroup
			FROM {$this->quoteTable('articles')}
			WHERE `OXPRICEA`!=0
		) UNION ALL (
			SELECT 
				`OXID` as productID,
				0 as `from`,
				`OXPRICEB` as `price`,
				0 as `percent`,
				'oxidpriceb' as pricegroup
			FROM {$this->quoteTable('articles')}
			WHERE `OXPRICEB`!=0
		) UNION ALL (
			SELECT 
				`OXID` as productID,
				0 as `from`,
				`OXPRICEC` as `price`,
				0 as `percent`,
				'oxidpricec' as pricegroup
			FROM {$this->quoteTable('articles')}
			WHERE `OXPRICEC`!=0
		)";		
	}

    /**
   	 * Returns the sql statement to select the shop system article image allocation
   	 * @return string {String} | sql for the article image allocation
   	 */
	public function getProductImageSelect()
	{
		$sql = array();
		for ($i=1;$i<=12;$i++) {
			$sql[] = "
				SELECT OXID as `productID`, CONCAT('$i/', OXPIC$i) as `image`, $i as `position`, IF($i=1, 1, 0) as `main`
				FROM {$this->quoteTable('articles', 'a')}
				WHERE OXPIC$i NOT IN ('', 'nopic.jpg')
				AND OXPARENTID=''
			";
		}
		return implode('UNION ALL', $sql);
	}

    /**
   	 * Returns the sql statement to select the shop system article translations
   	 * @return string {String} | sql for the article translations
   	 */
	public function getProductTranslationSelect()
	{
		$keys = $this->getLanguageKeys();
		foreach ($keys as $key=>$languageID) {
			if(empty($key)) {
				continue;
			}
			$sql[] = "
				SELECT
					a.OXID 					as productID,
					{$this->Db()->quote($languageID)} as languageID,
					a.OXTITLE_$key 			as name,
					-- a.OXVARNAME_$key        as configuratorgroup,
					a.OXVARSELECT_$key 		as additionaltext,
					a.OXSHORTDESC_$key 		as description,
					a.OXSEARCHKEYS_$key 	as keywords,
					e.OXLONGDESC_$key 		as description_long,
					e.OXTAGS_$key 			as tags
				
				FROM {$this->quoteTable('articles', 'a')}
				
				LEFT JOIN {$this->quoteTable('artextends', 'e')}
				ON e.OXID=a.OXID
			";
		}
		return '('.implode(') UNION ALL (', $sql).')';
	}

    /**
   	 * Returns the sql statement to select the shop system article category allocation
   	 * @return string {String} | sql for the article category allocation
   	 */
	public function getProductCategorySelect()
	{
		return "
			SELECT DISTINCT a.OXOBJECTID AS productID, a.OXCATNID AS categoryID
			FROM {$this->quoteTable('object2category', 'a')}
			INNER JOIN {$this->quoteTable('categories', 'c')} ON c.OXID = a.OXCATNID
			AND OXRIGHT-OXLEFT=1
			ORDER BY OXPOS
		";
	}

    /**
   	 * Returns the sql statement to select the shop system categories.
   	 * If the shop system have more than one sub shop the sql statements will join with "UNION ALL".
   	 * @return string {String} | sql for the categories
   	 */
	public function getCategorySelect()
	{
		$keys = $this->getLanguageKeys();
		$sql = array("
			SELECT 
				OXID as categoryID,
				(CASE WHEN OXPARENTID = 'oxrootid' THEN '' ELSE OXPARENTID END) as parentID,
				{$this->Db()->quote($keys[0])} as languageID,
				-- OXSHOPID as shopID,
				OXTITLE as description,
				OXDESC as cmsheadline,
				OXLONGDESC as cmstext,
				OXACTIVE as active,
				OXHIDDEN as hidetop,
				OXSORT as position,
				OXRIGHT-OXLEFT as diff
			FROM {$this->quoteTable('categories')}
			WHERE OXSHOPID='oxbaseshop'
		");
		foreach ($keys as $key=>$languageID) {
			if(empty($key)) {
				continue;
			}
			$sql[] = "
				SELECT 
					OXID as categoryID,
					(CASE WHEN OXPARENTID = 'oxrootid' THEN '' ELSE OXPARENTID END) as parentID,
					{$this->Db()->quote($languageID)} as languageID,
					-- OXSHOPID as shopID,
					IF(OXTITLE_$key='', OXTITLE, OXTITLE_$key) as description,
					IF(OXDESC_$key='', OXDESC, OXDESC_$key) as cmsheadline,
					IF(OXLONGDESC_$key='', OXLONGDESC, OXLONGDESC_$key) as cmstext,
					IF(OXACTIVE_$key='', OXACTIVE, OXACTIVE_$key) as active,
					OXHIDDEN as hidetop,
					OXSORT as position,
					OXRIGHT-OXLEFT as diff
				FROM {$this->quoteTable('categories')}
				WHERE OXSHOPID='oxbaseshop'
			";
		}
		return '('.implode(') UNION ALL (', $sql).') ORDER BY languageID, diff DESC';
	}

    /**
   	 * Returns the sql statement to select the shop system article ratings
   	 * @return string {String} | sql for the article ratings
   	 */
	public function getProductRatingSelect()
	{
		return "
			SELECT
				r.`OXOBJECTID` as `productID`,
				r.`OXUSERID` as `customerID`,
				u.`OXFNAME` as `name`,
				u.`OXUSERNAME` as `email`,
				r.`OXRATING` as `rating`,
				r.`OXCREATE` as `date`,
				r.`OXACTIVE` as `active`,
				r.`OXTEXT` as `comment`,
				'' as `title`
			FROM {$this->quoteTable('reviews', 'r')}, {$this->quoteTable('user', 'u')}
			WHERE r.OXUSERID=u.OXID
			AND `OXTYPE`='oxarticle'
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
				o.`OXID`									as orderID,
				`OXSHOPID`									as subshopID,
				`OXUSERID`									as customerID,
				`OXPAYMENTTYPE`								as paymentID,
				`OXORDERDATE`								as date,
				`OXORDERNR`									as ordernumber,
				`OXBILLUSTID`								as ustid,
				`OXBILLFON`									as phone,
				`OXBILLFAX`									as fax,
				
				`OXBILLCOMPANY`								as billing_company,
				`OXBILLFNAME`								as billing_firstname,
				`OXBILLLNAME`								as billing_lastname,
				`OXBILLSTREET`								as billing_street,
				`OXBILLSTREETNR` 							as billing_streetnumber,
				`OXBILLADDINFO`								as billing_text1,
				`OXBILLCITY` 								as billing_city,
				bc.OXISOALPHA2								as billing_countryiso,
				`OXBILLZIP`									as billing_zipcode,
				IF(`OXBILLSAL` IN ('m', 'Herr', 'MR'), 'mr', 'ms')
															as billing_salutation,
				
				`OXDELCOMPANY`								as shipping_company,
				`OXDELFNAME`								as shipping_firstname,
				`OXDELLNAME` 								as shipping_lastname,
				`OXDELSTREET` 								as shipping_street,
				`OXDELSTREETNR` 							as shipping_streetnumber,
				`OXDELADDINFO`								as shipping_text1,
				`OXDELCITY`									as shipping_city,
				sc.OXISOALPHA2								as shipping_countryiso,
				`OXDELZIP`									as shipping_zipcode,
				IF(`OXDELSAL` IN ('m', 'Herr', 'MR'), 'mr', 'ms')
															as shipping_salutation,
				
				`OXTOTALNETSUM`								as invoice_amount_net,
				`OXTOTALORDERSUM`							as invoice_amount,
				`OXDELCOST`+`OXPAYCOST`						as invoice_shipping,
				`OXDELCOST`+`OXPAYCOST`						as invoice_shipping_net,
				-- (`OXDELCOST`+`OXPAYCOST`)
				-- 	- (`OXDELVAT`+`OXPAYVAT`)				as invoice_shipping_net,
					
				-- `OXARTVAT1`,
				-- `OXARTVATPRICE1`,
				-- `OXARTVAT2`,
				-- `OXARTVATPRICE2`,					
				-- `OXWRAPCOST`,
				-- `OXWRAPVAT`,
				-- `OXVOUCHERDISCOUNT`,	
				-- `OXDISCOUNT`,
				
				`OXTRACKCODE`								as trackingID,
				`OXREMARK`									as customercomment,
				`OXCURRENCY`								as currency,
				`OXCURRATE`									as currency_factor,
				`OXFOLDER`									as statusID,
				`OXTRANSID`									as transactionID,
				`OXPAID`									as cleared_date,
				`OXIP` 										as remote_addr,
				-- `OXLANG`									as languageID,
				`OXDELTYPE`									as dispatchID
				
			FROM {$this->quoteTable('order', 'o')}
			
			LEFT JOIN {$this->quoteTable('country', 'bc')} ON bc.OXID=o.OXBILLCOUNTRYID
			LEFT JOIN {$this->quoteTable('country', 'sc')} ON sc.OXID=o.OXDELCOUNTRYID
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
			
				OXORDERID as orderID,
				OXARTID  as productID,
				
				OXARTNUM as article_ordernumber,
				OXTITLE as name,
				OXPRICE as price,
				OXAMOUNT as quantity,
				OXVAT as tax,
				IF(OXSUBCLASS='oxarticle', 0, IF(OXPRICE>0, 4, 3)) as modus
				
			FROM {$this->quoteTable('orderarticles')}
		";
	}
}