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
 * Shopware SwagMigration Components - Profile
 *
 * @category  Shopware
 * @package Shopware\Plugins\SwagMigration\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
abstract class Shopware_Components_Migration_Profile extends Enlight_Class
{
    /**
     * Global variable for the database object
     * @var #M#C\Enlight_Components_Db.factory|?
     */
	protected $db;

    /**
     * Prefix of each database table in the profile
     * @var
     */
	protected $db_prefix;

    /**
     * Database adapter type
     * @var string
     */
	protected $db_adapter = 'PDO_MYSQL';

    /**
     * Array of the configuration
     * @var
     */
	protected $config;

    /**
     * Default language of shopware
     * @var
     */
    protected $default_language;

    /**
     * Class constructor to open the database connection
     * @param $config
     */
	public function __construct($config)
	{
		$this->db = Enlight_Components_Db::factory($this->db_adapter, $config);
    	$this->db->getConnection();
    	if(isset($config['prefix'])) {
    		$this->db_prefix = $config['prefix'];
    	}    	
	}

    /**
     * This function add the profile database prefix to the given table
     * @param $table
     * @param null $alias
     * @return string
     */
	public function quoteTable($table, $alias = null)
	{
		if(!empty($this->db_prefix)) {
			$table = $this->db_prefix.$table;
		}
		return $this->db->quoteTableAs($table, $alias);
	}

    /**
     * This function returns the database information
     * @return array
     */
	public function getDatabases()
	{
		$databases = $this->db->fetchCol('SHOW DATABASES');
    	foreach ($databases as $key => $database) {
    		if($database=='information_schema') {
    			unset($databases[$key]);
    		}
    	}
    	return $databases;
	}

    /**
     * Returns the database object
     * @return Zend_Db_Adapter_Abstract
     */
	public function Db()
	{
		return $this->db;
	}

    /**
     * This function returns the configuration array
     * @return mixed
     */
	public function Config()
	{
		if(!isset($this->config)) {
			$config = array();
			$sql = $this->getConfigSelect();
			$rows = $this->db->fetchAll($sql);
			foreach ($rows as $row) {
				if(!empty($row['type'])) {
					switch ($row['type']) {
						case 'bool':
							if($row['value']=='false') {
								$row['value'] = false;
							} else {
								$row['value'] = (bool) $row['value'];
							}
							break;
						case 'aarr':
							$row['value'] = unserialize($row['value']);
							break;
						case 'str':
						default:
							break;
					}
				}
				$config[$row['name']] = $row['value'];
			}
			$this->config = new ArrayObject($config, ArrayObject::ARRAY_AS_PROPS);
		}
		return $this->config;
	}

    /**
     * This function executes an sql statement with the given parameters
     * @param $sql
     * @param int $count
     * @param int $offset
     * @return string
     */
	public function limit($sql, $count=0, $offset=0)
	{
		$count = intval($count);
        if ($count <= 0) {
        	$count = 2147483647;
        }
        return $this->db->limit($sql, $count, $offset);
	}

    /**
     * This function returns the customer group select statement of the current profile
     * @return mixed
     */
	public function getPriceGroupSelect()
	{
		return $this->getCustomerGroupSelect();
	}

    /**
     * This function returns the profile sub shops
     * @return array
     */
	public function getShops()
	{
		if(!method_exists($this, 'getShopSelect')) {
			return;
		}
		return $this->db->fetchPairs($this->getShopSelect());
	}

    /**
     * This function returns the profile languages
     * @return array
     */
	public function getLanguages()
	{
		if(!method_exists($this, 'getLanguageSelect')) {
			return;
		}
		return $this->db->fetchPairs($this->getLanguageSelect());
	}



	/**
     * This function returns the profile default language
     * @return mixed
     */
	public function getDefaultLanguage()
	{
		if($this->default_language===null && method_exists($this, 'getDefaultLanguageSelect')) {
			$this->default_language = $this->db->fetchOne($this->getDefaultLanguageSelect());
		}
		return $this->default_language;
	}

    /**
     * This function sets the profile default language
     * @param $language
     */
	public function setDefaultLanguage($language)
	{
		$this->default_language = $language;
	}

    /**
     * Returns the customer groups, selected by the profile  sql
     * @return array
     */
	public function getCustomerGroups()
	{
		if(!method_exists($this, 'getCustomerGroupSelect')) {
			return;
		}
		return $this->db->fetchPairs($this->getCustomerGroupSelect());
	}

    /**
     * Returns the price groups, selected by the profile sql
     * @return array
     */
	public function getPriceGroups()
	{
		if(!method_exists($this, 'getPriceGroupSelect')) {
			return;
		}
		return $this->db->fetchPairs($this->getPriceGroupSelect());
	}

    /**
     * Returns the payment, selected by the profile  sql
     * @return array
     */
	public function getPaymentMeans()
	{
		if(!method_exists($this, 'getPaymentMeanSelect')) {
			return;
		}
		return $this->db->fetchPairs($this->getPaymentMeanSelect());
	}

    /**
     * Returns the order states, selected by the profile sql
     * @return array
     */
	public function getOrderStatus()
	{
		if(!method_exists($this, 'getOrderStatusSelect')) {
			return;
		}
		return $this->db->fetchPairs($this->getOrderStatusSelect());
	}

    /**
     * Returns the article attributes, selected by the profile sql
     * @return array
     */
	public function getAttributes()
	{
		if(!method_exists($this, 'getAttributeSelect')) {
			return;
		}
		return $this->db->fetchPairs($this->getAttributeSelect());
	}

    /**
     * Returns the tax rates, selected by the profile sql
     * @return array
     */
	public function getTaxRates()
	{
		if(!method_exists($this, 'getTaxRateSelect')) {
			return;
		}
		return $this->db->fetchPairs($this->getTaxRateSelect());
	}

    /**
     * Returns the supplier, selected by the profile sql
     * @return array
     */
	public function getSuppliers()
	{
		if(!method_exists($this, 'getSupplierSelect')) {
			return;
		}
		return $this->db->fetchPairs($this->getSupplierSelect());
	}

    /**
     * Returns the categories, selected by the profile sql
     * @return array
     */
	public function getCategories()
	{
		if(!method_exists($this, 'getCategorySelect')) {
			return;
		}
		return $this->db->fetchAll($this->getCategorySelect());
	}

    /**
     * Executes the profile category select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryCategories($offset=0)
	{
		$sql = $this->getCategorySelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}

    /**
     * Executes the profile product category allocation select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryProductCategories($offset=0)
	{
		$sql = $this->getProductCategorySelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}

    /**
     * Executes the profile product select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryProducts($offset=0)
	{
		$sql = $this->getProductSelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}

    /**
     * Executes the profile product price select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryProductPrices($offset=0)
	{
		$sql = $this->getProductPriceSelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}

    /**
     * Executes the profile customer select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryCustomers($offset=0)
	{
		$sql = $this->getCustomerSelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}

    /**
     * Executes the profile product image select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryProductImages($offset=0)
	{
		$sql = $this->getProductImageSelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}

    /**
     * Executes the profile product translation select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryProductTranslations($offset=0)
	{
		$sql = $this->getProductTranslationSelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}

    /**
     * Executes the profile product rating select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryProductRatings($offset=0)
	{
		$sql = $this->getProductRatingSelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}

    /**
     * Executes the profile order select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryOrders($offset=0)
	{
		$sql = $this->getOrderSelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}

    /**
     * Executes the profile order detail select statement with the given offset
     * @param int $offset
     * @return Zend_Db_Statement_Interface
     */
	public function queryOrderDetails($offset=0)
	{
		$sql = $this->getOrderDetailSelect();
		if(!empty($offset)) {
			$sql = $this->limit($sql, null, $offset);
		}
		return $this->db->query($sql);
	}
}