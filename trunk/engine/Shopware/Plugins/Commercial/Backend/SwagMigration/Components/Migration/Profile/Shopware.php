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
 * Shopware SwagMigration Components - Shopware
 *
 * @category  Shopware
 * @package Shopware\Plugins\SwagMigration\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Migration_Profile_Shopware extends Shopware_Components_Migration_Profile
{
    /**
     * Prefix of each shopware database table.
     * @var string
     */
    protected $db_prefix = 's_';

    /**
   	 * Returns the sql statement to select default shopware language
   	 * @return string {String} | sql for default language
   	 */
    public function getDefaultLanguageSelect()
    {
        return 'SELECT `isocode` FROM `s_core_multilanguage` WHERE `default` =1';
    }

    /**
   	 * Returns the sql statement to select the shop system sub shops
   	 * @return string {String} | sql for sub shops
   	 */
    public function getShopSelect()
    {
        return "
			SELECT `id`, `name`, `domainaliase` as domain
			FROM {$this->quoteTable('core_multilanguage')}
		";
    }

    /**
   	 * Returns the sql statement to select the shop system languages
   	 * @return string {String} | sql for languages
   	 */
    public function getLanguageSelect()
    {
        return "
			SELECT `isocode` as `id`, `name`
			FROM {$this->quoteTable('core_multilanguage')}
			WHERE `default`=1
			
			UNION ALL
			
			SELECT `isocode` as `id`, `name`
			FROM {$this->quoteTable('core_multilanguage')}
			WHERE `skipbackend`=0
		";
    }

    /**
   	 * Returns the sql statement to select the shop system customer groups
   	 * @return string {String} | sql for customer groups
   	 */
    public function getCustomerGroupSelect()
    {
        return "
			SELECT `groupkey`, `description` as `name`
			FROM {$this->quoteTable('core_customergroups')}
		";
    }

    /**
   	 * Returns the sql statement to select the shop system price groups
   	 * @return string {String} | sql for price groups
   	 */
    public function getPriceGroupSelect()
    {
        return "
			SELECT `groupkey`, `description` as `name`
			FROM {$this->quoteTable('core_customergroups')}
		";
    }

    /**
   	 * Returns the sql statement to select the shop system payments
   	 * @return string {String} | sql for the payments
   	 */
    public function getPaymentMeanSelect()
    {
        return "
			SELECT `id`, `description` as `name`
			FROM {$this->quoteTable('core_paymentmeans')}
			ORDER BY `description` ASC
		";
    }

    /**
   	 * Returns the sql statement to select the shop system order states
   	 * @return string {String} | sql for the order states
   	 */
    public function getOrderStatusSelect()
    {
        return "
			SELECT `id` , `description` as name
			FROM {$this->quoteTable('core_states', 's')}
			WHERE `group`='state'
			ORDER BY `position` ASC
		";
    }

    /**
   	 * Returns the sql statement to select the shop system tax rates
   	 * @return string {String} | sql for the tax rates
   	 */
    public function getTaxRateSelect()
    {
        return "
			SELECT `id`, `description` as name
			FROM {$this->quoteTable('core_tax')}
		";
    }

    /**
   	 * Returns the sql statement to select the shop system article attributes
   	 * @return string {String} | sql for the article attributes
   	 */
    public function getAttributeSelect()
    {
        return "
			SELECT `name` as id, `label` as name
			FROM {$this->quoteTable('core_engine_elements')}
			WHERE `name` LIKE '%attr%%'
			UNION ALL
			SELECT 'ean' as id, 'EAN' as name
		";
    }

    /**
   	 * Returns the sql statement to select the shop system suppliers
   	 * @return string {String} | sql for the suppliers
   	 */
    public function getSupplierSelect()
    {
        return "
			SELECT `id`, `name`
			FROM {$this->quoteTable('articles_supplier')}
			ORDER BY `name`
		";
    }

    /**
   	 * Returns the sql statement to select the shop system categories
   	 * @return string {String} | sql for the categories
   	 */
    public function getCategorySelect()
    {
        return "
			SELECT *
			FROM {$this->quoteTable('categories')}
		";
    }

    /**
   	 * Returns the sql statement to select the shop system articles
   	 * @return string {String} | sql for the articles
   	 */
    public function getProductSelect()
    {
        return "
			SELECT *
			FROM {$this->quoteTable('articles')}
		";
    }

    /**
   	 * Returns the sql statement to select the shop system customer
   	 * @return string {String} | sql for the customer data
   	 */
    public function getOrderSelect()
    {
        return "
			SELECT *
			FROM {$this->quoteTable('order')}
		";
    }

    /**
   	 * Returns the sql statement to select the shop system customer
   	 * @return string {String} | sql for the customer data
   	 */
    public function getCustomerSelect()
    {
        return "
			SELECT *
			FROM {$this->quoteTable('user')}
		";
    }

    /**
   	 * Returns the sql statement to select the shop system article image allocation
   	 * @return string {String} | sql for the article image allocation
   	 */
    public function getProductImageSelect()
    {
        return "
            SELECT *
            FROM {$this->quoteTable('articles_img')}
        ";
    }

    /**
   	 * Returns the sql statement to select the shop system article ratings
   	 * @return string {String} | sql for the article ratings
   	 */
    public function getProductRatingSelect()
    {
        return "
            SELECT *
            FROM {$this->quoteTable('articles_vote')}
        ";
    }

    /**
   	 * Returns the sql statement to select the shop system article prices
   	 * @return string {String} | sql for the article prices
   	 */
    public function getProductPriceSelect()
    {
        return "
                SELECT *
                FROM {$this->quoteTable('articles_prices')}
            ";
    }
}