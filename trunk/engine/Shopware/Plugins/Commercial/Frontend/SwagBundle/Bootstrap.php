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
 * The Shopware_Plugins_Frontend_SwagBundle_Bootstrap class is the bootstrap class
 * of the bundle plugin. This class contains all function to bootstrap the bundle extension
 * in the shopware system.
 *
 * @category Shopware
 * @package Shopware\Plugin\SwagBundle
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Frontend_SwagBundle_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @param   bool $throwException
     * @throws  Exception
     * @return  bool
     */
    public function checkLicense($throwException = true)
    {
        try {
            static $r, $m = 'SwagBundle';
            if(!isset($r)) {
                $s = base64_decode('Va7L06J0OlXLk1SFDsrC40h6MDw=');
                $c = base64_decode('ShgbEOkhjE7k9MtL+Cdexo1l968=');
                $r = sha1(uniqid('', true), true);
                /** @var $l Shopware_Components_License */
                $l = $this->Application()->License();
                $i = $l->getLicense($m, $r);
                $t = $l->getCoreLicense();
                $u = strlen($t) === 20 ? sha1($t . $s . $t, true) : 0;
                $r = $i === sha1($c. $u . $r, true);
            }
            if(!$r && $throwException) {
                throw new Exception('License check for module "' . $m . '" has failed.');
            }
            return $r;
        }
        catch (Exception $e) {
            if($throwException) {
                throw new Exception('License check for module "' . $m . '" has failed.');
            }
        }
    }

    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $bundleRepository = null;

    /**
     * The getBundleRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return \Shopware\CustomModels\Bundle\Repository
     */
    public function getBundleRepository()
    {
    	if ($this->bundleRepository === null) {
    		$this->bundleRepository = Shopware()->Models()->getRepository('Shopware\CustomModels\Bundle\Bundle');
    	}
    	return $this->bundleRepository;
    }

    /**
     * Returns an array with the capabilities of the bundle plugin.
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'install' => true,
            'enable' => true,
            'update' => true
        );
    }

    public function getInfo() {
        return array(
            'version' => $this->getVersion(),
            'copyright' => 'Copyright © 2012, shopware AG',
            'label' => $this->getLabel(),
            'description' => file_get_contents($this->Path() . 'info.txt'),
            'support' => 'http://forum.shopware.de',
            'link' => 'http://www.shopware.de',
            'changes' => array(
                '2.0.0'=>array('releasedate'=>'2012-11-09', 'lines' => array(
                    'Erstes Release'
                )),
                '2.0.1'=>array('releasedate'=>'2012-11-10', 'lines' => array(
                    'Optimierung des Preisimports von 3.5.* Bundles.'
                )),
                '2.0.2'=>array('releasedate'=>'2012-11-12', 'lines' => array(
                    'Optimierung der Bruttopreisberechnung. Korrektur der Absoluten Rabatte im Frontend'
                ))
            ),
            'revision' => '1'
        );
    }

    /**
     * Plugin meta data function.
     *
     * Returns the plugin label.
     *
     * @return string|void
     */
    public function getLabel()
    {
        return 'Produktbundle';
    }

    /**
     * Returns the current version of the bundle plugin.
     * @return string
     */
    public function getVersion()
    {
        return "2.0.3";
    }

    /**
     * Update function of the bundle plugin
     *
     * @param string $version
     *
     * @return bool|void
     */
    public function update($version)
    {
        try {
            $sql = "ALTER TABLE `s_articles_bundles` CHANGE  `max_quantity`  `max_quantity` INT( 11 ) NOT NULL";
            Shopware()->Db()->query($sql);
        } catch (Exception $e) {
        }
        return true;
    }


    /**
     * Install function of the plugin bootstrap.
     * Registers all necessary components and dependencies.
     * @return bool
     */
    public function install()
	{
        // Check license
        $this->checkLicense(true);

        $this->subscribeEvents();

        $result = $this->updateDatabase();

        try {
            $this->createBasketAttribute();
        }
        catch (Exception $e) {
        }

        Shopware()->Models()->generateAttributeModels(array('s_order_basket_attributes'));

        return $result;
    }

    /**
     * Uninstall function of the plugin bootstrap.
     * Removes all custom components.
     * @return bool
     */
    public function uninstall()
    {
        try {
            $this->removeBasketAttribute();
            Shopware()->Models()->generateAttributeModels(array('s_order_basket_attributes'));
        }
        catch (Exception $e) {
        }

        return true;
    }

    /**
     * Registers all necessary events and hooks.
     */
    private function subscribeEvents()
    {
        //event listener for the backend controller of the bundle module.
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Bundle',
            'onGetBackendController'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout',
            'onCheckoutPostDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout',
            'onCheckoutPreDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onFrontendPostDispatch'
        );

        //event listener function for the article detail page. Used to display all bundle of the current article.
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
            'onArticleDetailPage'
        );

        $this->subscribeEvent(
            'Shopware_Controllers_Frontend_Checkout::saveOrder::after',
            'onSaveOrder'
        );

        $this->subscribeEvent(
            'sBasket::sGetBasket::before',
            'onGetBasket'
        );

        //hook for the new basket component. If the customer add an article that is already as bundle article in the basket,
        //the hook calls the new basket addArticle function
        $this->subscribeEvent(
            'sBasket::sAddArticle::before',
            'onAddArticle'
        );

        $this->subscribeEvent(
            'sBasket::sAddArticle::after',
            'onAfterAddArticle'
        );

        $this->subscribeEvent(
            'sBasket::sDeleteArticle::before',
            'onDeleteArticle'
        );

        //hook for the new basket component. The shouldAddAsNewPosition is used to identify if the article should add as new basket position.
        $this->subscribeEvent(
            'Shopware_Components_BundleBasket::shouldAddAsNewPosition::after',
            'onShouldAddAsNewPosition'
        );

        //hook for the new basket component. The shouldAddAsNewPosition is used to identify if the article should add as new basket position.
        $this->subscribeEvent(
            'Shopware_Components_BundleBasket::getVariantUpdateData::after',
            'onGetVariantUpdateData'
        );

        //hook for the new basket component. The getAttributeData is used to generate the new s_order_basket_attributes data
        $this->subscribeEvent(
            'Shopware_Components_BundleBasket::getAttributeCreateData::after',
            'onGetBasketAttribute'
        );

        //event listener for the bundle resource. Called if the developer use Shopware()->Bundle()
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_Bundle',
            'onInitBundleResource'
        );

        //event listener for the new basket resource. Called if the developer use Shopware()->BundleBasket()
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_BundleBasket',
            'onInitBasketResource'
        );

        //event listener function for the new bundle frontend controller.
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Bundle',
            'onGetFrontendController'
        );

        //event listener function for the post dispatch event of the article backend module. Used to add the new bundle tab
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Article',
            'loadBackendModule'
        );

        //creates the menu item under the menu "Product" to add an bundle listing module.
        $this->createMenuItem(array(
            'label' => 'Produktbundle Übersicht',
            'controller' => 'Bundle',
            'class' => 'sprite-box-zipper',
            'action' => 'Index',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy('label', 'Artikel')
        ));
    }

    /**
     * Updates the database tables.
     */
    private function updateDatabase()
    {
        try {
            $this->createBackupTables();

            $this->createNewTables();

            $migrations = $this->importData();

            $this->removeBackupTables();

            $this->createSnippets();

            return true;
        }
        catch (Exception $e) {

            $this->removeNewTables();

            $this->restoreBackupTables();
            
            return array('success' => false, 'code' => $e->getCode(), 'message' => $e->getMessage());
        }
    }

    private function createSnippets()
    {
        $sql= "
            INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleHeader', 'Sparen Sie jetzt mit unseren Bundle-Angeboten', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleActionAdd', 'In den Warenkorb', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleInfoPriceForAll', 'Preis für alle', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleInfoPriceInstead', 'Statt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleInfoPercent', '% Rabatt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
            (NULL, 'frontend/detail/bundle', 1, 1, 'DetailBundleHeaeder', 'Sparen Sie jetzt mit unserem Bundle-Angeboten:', '2012-11-08 16:07:09', '2012-11-08 16:14:19'),
            (NULL, 'frontend/detail/bundle', 1, 1, 'DetailBundleInstead', 'Statt', '2012-11-08 16:07:09', '2012-11-08 16:14:30'),
            (NULL, 'frontend/detail/bundle', 1, 1, 'DetailBundleDiscount', 'Rabatt', '2012-11-08 16:07:09', '2012-11-08 16:14:37'),
            (NULL, 'frontend/detail/bundle', 1, 1, 'DetailBundle', 'Preis für alle', '2012-11-08 16:07:09', '2012-11-08 16:14:52'),
            (NULL, 'frontend/detail/bundle', 1, 1, 'sConfigurationTake', 'Konfiguration übernehmen', '2012-11-08 16:07:09', '2012-11-08 16:15:08'),
            (NULL, 'frontend/detail/bundle/box_related', 1, 1, 'BundleHeader', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
            (NULL, 'frontend/detail/bundle/box_related', 1, 1, 'BundleActionAdd', 'In den Warenkorb', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
            (NULL, 'frontend/detail/bundle/box_related', 1, 1, 'BundleInfoPriceForAll', 'Preis für alle', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
            (NULL, 'frontend/detail/bundle/box_related', 1, 2, 'BundleActionAdd', 'Add to shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
            (NULL, 'frontend/detail/bundle/box_related', 1, 2, 'BundleInfoPriceForAll', 'Prices for all', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleInfoPercent', '% discount', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
            (NULL, 'frontend/detail/bundle/box_related', 1, 2, 'BundleHeader', 'Buy this product bundled with ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
            (NULL, 'frontend/detail/bundle', 1, 2, 'DetailBundleHeaeder', 'Save money with our bundle offer:', '2012-11-08 16:10:18', '2012-11-08 16:10:18'),
            (NULL, 'frontend/detail/bundle', 1, 2, 'DetailBundleInstead', 'Instead of', '2012-11-08 16:10:28', '2012-11-08 16:10:28'),
            (NULL, 'frontend/detail/bundle', 1, 2, 'DetailBundleDiscount', 'discount', '2012-11-08 16:10:34', '2012-11-08 16:10:37'),
            (NULL, 'frontend/detail/bundle', 1, 2, 'DetailBundle', 'Price for all:', '2012-11-08 16:11:33', '2012-11-08 16:11:33'),
            (NULL, 'frontend/detail/bundle', 1, 2, 'sConfigurationTake', 'Accept configuration', '2012-11-08 16:11:40', '2012-11-08 16:11:40'),
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleHeader', 'Save good money with our bundle offerings', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleActionAdd', 'Add to shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleInfoPriceForAll', 'Price for all', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
            (NULL, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleInfoPriceInstead', 'instead of ', '2012-08-22 15:57:47', '2012-08-22 15:57:47');
        ";
        Shopware()->Db()->query($sql);
    }

    /**
     * Internal helper function which creates new tables for the bundle plugin.
     */
    private function createNewTables()
    {
        $this->createBundlesTable();
        $this->createBundleArticlesTable();
        $this->createBundlePricesTable();
        $this->createBundleStintTable();
        $this->createBundleCustomerGroupsTable();
    }

    /**
     * Internal helper funct
     */
    private function createBasketAttribute()
    {
        $sql= "
            ALTER TABLE s_order_basket_attributes
            ADD COLUMN bundle_id INT NULL DEFAULT NULL;
        ";
        Shopware()->Db()->query($sql, array());
    }

    /**
     * Internal helper function to create the basket attribute model.
     */
    private function removeBasketAttribute()
    {
        $sql= "
            ALTER TABLE s_order_basket_attributes
            DROP COLUMN bundle_id;
        ";
        Shopware()->Db()->query($sql, array());

    }


    /**
     * Internal helper function which removes the backup tables of the plugin.
     */
    private function removeBackupTables()
    {
        $this->dropTable('s_articles_bundles_sw_backup');
        $this->dropTable('s_articles_bundles_articles_sw_backup');
        $this->dropTable('s_articles_bundles_prices_sw_backup');
        $this->dropTable('s_articles_bundles_customergroups_sw_backup');
        $this->dropTable('s_articles_bundles_stint_sw_backup');
    }

    /**
     * Internal helper function which starts an custom import of the bundle data.
     */
    private function importBundles()
    {
        $columns = array('id, articleID, name, active, rab_type, taxID, ordernumber, max_quantity_enable, max_quantity, valid_from, valid_to, datum, sells');
        if ($this->columnExist('s_articles_bundles_sw_backup', 'bundle_type')) {
            $columns[] = 'bundle_type';
        } else {
            $columns[] = "'1' as bundle_type";
        }
        $this->importTable('s_articles_bundles', 's_articles_bundles_sw_backup', implode(',', $columns));

        $sql= "UPDATE s_articles_bundles SET valid_from = NULL WHERE valid_from = '0000-00-00 00:00:00'";
        Shopware()->Db()->query($sql, array());

        $sql= "UPDATE s_articles_bundles SET valid_to = NULL WHERE valid_to = '0000-00-00 00:00:00'";
        Shopware()->Db()->query($sql, array());
    }

    /**
     * Internal helper function which imports the bundle data from the old bundle tables into the just created
     * new bundle.
     */
    private function importData()
    {
        $migrations = array();

        if ($this->tableExist('s_articles_bundles_sw_backup')) {
            $this->importBundles();
        }

        if ($this->tableExist('s_articles_bundles_articles_sw_backup')) {
            if ($this->columnExist('s_articles_bundles_articles_sw_backup', 'article_detail_id')) {
                $this->importTable('s_articles_bundles_articles', 's_articles_bundles_articles_sw_backup');
            } else {
                $migrations[] = 'articles';
                $this->migrateBundleArticles();
            }
        }

        if ($this->tableExist('s_articles_bundles_prices_sw_backup')) {
            if ($this->columnExist('s_articles_bundles_prices_sw_backup', 'customer_group_id')) {
                $this->importTable('s_articles_bundles_prices', 's_articles_bundles_prices_sw_backup');
            } else {
                $migrations[] = 'prices';
                $this->migrateBundlePrices();
            }
        }

        if ($this->tableExist('s_articles_bundles_customergroups_sw_backup')) {
            $this->importTable('s_articles_bundles_customergroups', 's_articles_bundles_customergroups_sw_backup');
        } else if ($this->tableExist('s_articles_bundles_sw_backup')) {
            $migrations[] = 'customergroups';
            $this->migrateBundleCustomerGroups();
        }

        if ($this->tableExist('s_articles_bundles_stint_sw_backup')) {
            if ($this->columnExist('s_articles_bundles_stint_sw_backup', 'article_detail_id')) {
                $this->importTable('s_articles_bundles_stint', 's_articles_bundles_stint_sw_backup');
            } else {
                $migrations[] = 'stints';
                $this->migrateBundleStints();
            }
        }
        return $migrations;
    }

    /**
     * Internal helper function to check if a database table exists.
     *
     * @param $tableName
     *
     * @return bool
     */
    private function tableExist($tableName)
    {
        $sql = "SHOW TABLES LIKE '" . $tableName . "'";
        $result = Shopware()->Db()->fetchRow($sql);
        return !empty($result);
    }

    /**
     * Internal helper function to check if a database table column exist.
     *
     * @param $tableName
     * @param $columnName
     *
     * @return bool
     */
    private function columnExist($tableName, $columnName)
    {
        $sql= "SHOW COLUMNS FROM " . $tableName . " LIKE '" . $columnName . "'";
        $result = Shopware()->Db()->fetchRow($sql);
        return !empty($result);
    }

    /**
     * Internal helper function to create a new table for the article bundles.
     */
    private function createBundlesTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_bundles` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `articleID` int(11) unsigned NOT NULL,
                `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `active` int(1) unsigned NOT NULL,
                `rab_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `taxID` int(11) unsigned DEFAULT NULL,
                `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `max_quantity_enable` int(11) unsigned NOT NULL,
                `max_quantity` int(11) NOT NULL,
                `valid_from` datetime DEFAULT NULL,
                `valid_to` datetime DEFAULT NULL,
                `datum` datetime NOT NULL,
                `sells` int(11) unsigned NOT NULL,
                `bundle_type` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `articleID` (`articleID`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        Shopware()->Db()->query($sql);


    }

    /**
     * Internal helper function which creates a new table for the bundle customer groups
     */
    private function createBundleCustomerGroupsTable()
    {
        $sql= "
            CREATE TABLE IF NOT EXISTS `s_articles_bundles_customergroups` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `bundle_id` int(10) unsigned DEFAULT NULL,
                `customer_group_id` int(10) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `bundle_id` (`bundle_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        Shopware()->Db()->query($sql);
    }

    /**
     * Internal helper function which creates a new table for the bundle articles.
     */
    private function createBundleArticlesTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_bundles_articles` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `bundle_id` int(11) unsigned NOT NULL,
                `article_detail_id`  int(11) unsigned NOT NULL,
                `quantity` int(11) NOT NULL DEFAULT '1',
                `configurable` int(1) NOT NULL DEFAULT '0',
                `bundle_group_id` INT unsigned NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `bundle_id` (`bundle_id`,`article_detail_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        Shopware()->Db()->query($sql);
    }

    /**
     * Internal helper function to create a new table for the bundle prices.
     */
    private function createBundlePricesTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_bundles_prices` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `bundle_id` int(11) unsigned NOT NULL,
                `customer_group_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `price` double NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `bundle_id` (`bundle_id`,`customer_group_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        Shopware()->Db()->query($sql);
    }

    /**
     * Internal helper function to create a new table for the bundle stints.
     */
    private function createBundleStintTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_bundles_stint` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `bundle_id` int(11) unsigned NOT NULL,
                `article_detail_id` int(11) unsigned NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `bundle_id` (`bundle_id`,`article_detail_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        Shopware()->Db()->query($sql);
    }

    /**
     * Internal helper function which imports the table data from the "$from" table into the "$into" table.
     *
     * @param $into
     * @param $from
     * @param $columns
     *
     * @return void
     */
    private function importTable($into, $from, $columns = '*')
    {
        $sql= "INSERT INTO " . $into .  " (SELECT " . $columns . " FROM " . $from . ")";
        Shopware()->Db()->query($sql);
    }

    /**
     * Converts the old customer groups definition from the s_articles_bundles table into the new
     * s_articles_bundles_customergroups table.
     */
    private function migrateBundleCustomerGroups()
    {
        $sql= "SELECT id, customergroups FROM s_articles_bundles_sw_backup WHERE customergroups != ''";
        $bundles = Shopware()->Db()->fetchAll($sql);
        foreach($bundles as $bundle) {
            if (!empty($bundle['customergroups'])) {
                if (strpos($bundle['customergroups'], ',')) {
                    $customerGroups = explode(',', $bundle['customergroups']);
                } else {
                    $customerGroups = array($bundle['customergroups']);
                }
                foreach($customerGroups as $group) {
                    $sql= "SELECT id FROM s_core_customergroups WHERE groupkey = ?";
                    $customerGroupId = Shopware()->Db()->fetchOne($sql, array(trim($group)));
                    if (!empty($customerGroupId)) {
                        $sql= "INSERT INTO s_articles_bundles_customergroups (bundle_id, customer_group_id) VALUES (?, ?)";
                        Shopware()->Db()->query($sql, array($bundle['id'], $customerGroupId));
                    }
                }
            }
        }
    }

    /**
     * Converts the old bundle article data from shopware 3 to shopware 4.
     */
    private function migrateBundleArticles()
    {
        $sql= "
            SELECT bundle.bundleID as bundleId, detail.id as articleDetailId
            FROM s_articles_bundles_articles_sw_backup bundle
             INNER JOIN s_articles_details detail
              ON bundle.ordernumber = detail.ordernumber
        ";
        $articles = Shopware()->Db()->fetchAll($sql);
        foreach($articles as $article) {
            if (!empty($article) && !empty($article['articleDetailId'])) {
                $sql= "INSERT INTO s_articles_bundles_articles (bundle_id, article_detail_id) VALUES (?, ?)";
                Shopware()->Db()->query($sql, array($article['bundleId'], $article['articleDetailId']));
            }
        }
    }

    /**
     * Converts the old bundle stint data from shopware 3 to shopware 4.
     */
    private function migrateBundleStints()
    {
        $sql= "
            SELECT bundle.bundleID as bundleId, detail.id as articleDetailId
            FROM s_articles_bundles_stint_sw_backup bundle
             INNER JOIN s_articles_details detail
              ON bundle.ordernumber = detail.ordernumber
        ";
        $articles = Shopware()->Db()->fetchAll($sql);
        foreach($articles as $article) {
            if (!empty($article) && !empty($article['articleDetailId'])) {
                $sql= "INSERT INTO s_articles_bundles_stint (bundle_id, article_detail_id) VALUES (?, ?)";
                Shopware()->Db()->query($sql, array($article['bundleId'], $article['articleDetailId']));
            }
        }
    }

    /**
     * Converts the old bundle price data from shopware 3 to shopware 4.
     */
    public function migrateBundlePrices()
    {
        $sql= "
            SELECT bundle.bundleID as bundleId, customerGroups.id as customerGroupId, bundle.price, customerGroups.taxinput, tax.tax, main.rab_type
            FROM s_articles_bundles_prices_sw_backup bundle
             INNER JOIN s_core_customergroups customerGroups
              ON bundle.customergroup = customerGroups.groupkey
             INNER JOIN s_articles_bundles_sw_backup main
              ON main.id = bundle.bundleID
             LEFT JOIN s_core_tax tax
              ON customerGroups.tax = tax.id
        ";

        $prices = Shopware()->Db()->fetchAll($sql);
        foreach($prices as $price) {
            if (!empty($price) && !empty($price['customerGroupId'])) {
                if ($price['taxinput'] == 1 && $price['rab_type'] == 'abs' && !empty($price['tax'])) {
                    $price['price'] = ($price['price'] / (100 + $price['tax'])) * 100;
                }

                $sql= "INSERT INTO s_articles_bundles_prices (bundle_id, customer_group_id, price) VALUES (?, ?, ?)";
                Shopware()->Db()->query($sql, array($price['bundleId'], $price['customerGroupId'], $price['price']));
            }
        }
    }

    /**
     * Creates backup tables for all bundle database tables.
     */
    private function createBackupTables()
    {
        $this->createBackupTable('s_articles_bundles');
        $this->createBackupTable('s_articles_bundles_articles');
        $this->createBackupTable('s_articles_bundles_prices');
        $this->createBackupTable('s_articles_bundles_customergroups');
        $this->createBackupTable('s_articles_bundles_stint');
    }

    /**
     * Helper function to create a new backup for the passed table
     * @param $name
     */
    private function createBackupTable($name)
    {
        if ($this->tableExist($name)) {
            $this->dropTable($name . "_sw_backup");
            $this->renameTable($name, $name . "_sw_backup");
        }
    }

    /**
     * Internal helper function to rename
     * @param $from
     * @param $to
     */
    private function renameTable($from, $to)
    {
        if ($this->tableExist($from))  {
            $sql = "RENAME TABLE " . $from . " TO " . $to;
            Shopware()->Db()->query($sql);
        }
    }

    /**
     * Internal helper function to remove a table safety.
     * @param $name
     */
    private function dropTable($name)
    {
        $sql= "DROP TABLE IF EXISTS " . $name;
        Shopware()->Db()->query($sql);
    }

    /**
     * Restores the created backup tables.
     * Called if an exception occurred while updating the database in the install function.
     */
    private function restoreBackupTables()
    {
        $this->renameTable('s_articles_bundles_sw_backup', 's_articles_bundles');
        $this->renameTable('s_articles_bundles_articles_sw_backup', 's_articles_bundles_articles');
        $this->renameTable('s_articles_bundles_prices_sw_backup', 's_articles_bundles_prices');
        $this->renameTable('s_articles_bundles_customergroups_sw_backup', 's_articles_bundles_customergroups');
        $this->renameTable('s_articles_bundles_stint_sw_backup', 's_articles_bundles_stint');
    }
 
    /**
     * Internal helper function to remove the new tables.
     * Called if an exception occurred while updating the database in the install function.
     */
    private function removeNewTables()
    {
        if ($this->tableExist('s_articles_bundles_sw_backup')) {
            $this->dropTable('s_articles_bundles');
        }

        if ($this->tableExist('s_articles_bundles_articles_sw_backup')) {
            $this->dropTable('s_articles_bundles_articles');
        }

        if ($this->tableExist('s_articles_bundles_prices_sw_backup')) {
            $this->dropTable('s_articles_bundles_prices');
        }

        if ($this->tableExist('s_articles_bundles_customergroups_sw_backup')) {
            $this->dropTable('s_articles_bundles_customergroups');
        }

        if ($this->tableExist('s_articles_bundles_stint_sw_backup')) {
            $this->dropTable('s_articles_bundles_stint');
        }
    }


    /**
     * The onGetPluginController function is responsible to resolve the backend controller path
     * of the bundle plugin.
     * @param Enlight_Event_EventArgs $arguments
     * @return string
     */
    public function onGetBackendController(Enlight_Event_EventArgs $arguments)
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );
        return $this->Path(). 'Controllers/Backend/Bundle.php';
    }

    /**
     * The onGetFrontendController function is responsible to resolve the frontend controller path
     * of the bundle plugin.
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return string
     */
    public function onGetFrontendController(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return null;
        }

        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );
        return $this->Path(). 'Controllers/Frontend/Bundle.php';
    }

    /**
     * The afterInit function registers the custom plugin models.
     */
    public function afterInit()
    {
        $this->registerCustomModels();
    }

    /**
     * The loadBackendModule function is an event listener function which is responsible to
     * load the bundle backend module extension for the article module.
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function loadBackendModule(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );

        $arguments->getSubject()->View()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        //if the controller action name equals "load" we have to load all application components.
        if ($arguments->getRequest()->getActionName() === 'load') {
            $arguments->getSubject()->View()->extendsTemplate(
                'backend/article/view/detail/bundle_window.js'
            );
        }

        //if the controller action name equals "index" we have to extend the backend article application
        if ($arguments->getRequest()->getActionName() === 'index') {
            $arguments->getSubject()->View()->extendsTemplate(
                'backend/article/bundle_app.js'
            );
        }
    }

    /**
     * Enlight event listener function which registers the bundle resource. The bundle
     * resource contains some global function which used in the frontend controller and
     * in the plugin bootstrap.
     *
     * @param Enlight_Event_EventArgs $arguments
     *
     * @return Shopware_Components_Bundle
     */
    public function onInitBundleResource(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return null;
        }

        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );
        $bundle = new Shopware_Components_Bundle();
        Shopware()->Bootstrap()->registerResource('Bundle', $bundle);
        return $bundle;
    }

    /**
     * Enlight event listener function which registers the bundle resource. The bundle
     * resource contains some global function which used in the frontend controller and
     * in the plugin bootstrap.
     *
     * @param Enlight_Event_EventArgs $arguments
     *
     * @return Shopware_Components_Bundle
     */
    public function onInitBasketResource(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return null;
        }

        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );
        $basket = Enlight_Class::Instance('Shopware_Components_BundleBasket');
        Shopware()->Bootstrap()->registerResource('BundleBasket', $basket);

        return $basket;
    }

    /**
     * Event listener function of the article detail page. Fired before the article detail page will
     * be dispatched (preDispatch).
     * Reads out the bundles for the article id of the request object parameter "sArticle".
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onArticleDetailPage(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        /**@var $subject Enlight_Controller_Action*/
        $subject = $arguments->getSubject();

        /**@var $request Enlight_Controller_Request_RequestHttp*/
        $request = $subject->Request();

        /**@var $view Enlight_View_Default*/
        $view = $subject->View();

        $view->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('frontend/detail/bundle_index.tpl');

        $articleId = (int) $request->getParam('sArticle');

        //converts the configurator selection to the bundle structure.
        Shopware()->Bundle()->convertMainArticleConfiguration(
            $articleId,
            $request->getParam('group')
        );

        $bundles = Shopware()->Bundle()->getBundlesForDetailPage($articleId);

        $view->assign('sBundles', $bundles);
    }

    /**
     * Enlight event listener function of the sBasket()->sAddArticle() function.
     * The event is subscribed as replace event.
     * If no case of the bundle module occurred, the default function will be executed.
     */
    public function onAddArticle(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $orderNumber = $arguments->get('id');
        $quantity = $arguments->get('quantity');

        $normalPosition = Shopware()->Bundle()->isVariantAsNormalInBasket($orderNumber);
        $bundlePosition = Shopware()->Bundle()->isVariantAsBundleInBasket($orderNumber);

        if ($bundlePosition !== null && $normalPosition === null) {
            //bundle position exist? and no normal position exist? Force new basket position
            $data = Shopware()->BundleBasket()->addArticle($orderNumber, $quantity, array('forceNewPosition' => true));

            $arguments->set('id', '');
        } else if ($bundlePosition !== null && $normalPosition !== null) {

            //bundle position exist? and a normal position exist? Update the normal position
            $data = Shopware()->BundleBasket()->addArticle($orderNumber, $quantity, array('updatePosition' => $normalPosition));

            $arguments->set('id', '');
        }
    }

    /**
     * @param Enlight_Hook_HookArgs $arguments
     */
    public function onAfterAddArticle(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $orderNumber = $arguments->get('id');
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('basket', 'attribute'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->innerJoin('basket.attribute', 'attribute')
                ->where('basket.sessionId = :sessionId')
                ->andWhere('attribute.bundleId > :bundleId')
                ->setParameters(array('sessionId' => Shopware()->SessionID(), 'bundleId' => 0))
                ->orderBy('basket.id', 'DESC')
                ->setFirstResult(0)
                ->setMaxResults(1);

        /**@var $lastRow \Shopware\Models\Order\Basket*/
        $lastRow = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
        if (!empty($lastRow) && empty($orderNumber)) {
            $bundle = Shopware()->Models()->find('Shopware\CustomModels\Bundle\Bundle', $lastRow->getAttribute()->getBundleId());

            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->select(array('basket', 'attribute'))
                    ->from('Shopware\Models\Order\Basket', 'basket')
                    ->innerJoin('basket.attribute', 'attribute')
                    ->where('basket.sessionId = :sessionId')
                    ->andWhere('basket.articleId = :articleId')
                    ->setParameters(array('sessionId' => Shopware()->SessionID(), 'articleId' => $bundle->getArticle()->getId()))
                    ->setFirstResult(0)
                    ->setMaxResults(1);

            $basketRow = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);;
            if (!empty($basketRow)) {
                $arguments->setReturn($basketRow->getId());
            }
        }
    }

    /**
     * Enlight event listener function of the Shopware_Components_Basket()->shouldAddAsNewPosition() function.
     *
     * @param Enlight_Hook_HookArgs $arguments
     *
     * @return bool
     */
    public function onShouldAddAsNewPosition(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return true;
        }

        $parameter = $arguments->getArgs();
        $additional = $parameter[2];

        //check if the current process would add a bundle article
        if ($additional['bundleId'] || $additional['forceNewPosition']) {
            $arguments->setReturn(true);
            return true;
        }

        //the update position parameter is passed in the following szenario:
        //The customer added a bundle with the article "SW-2000"
        //and the customer added the article "SW-2000" as normal article,
        //the $additional['updatePosition'] property contains now the id of the normal position
        //this position has to been updated.
        if ($additional['updatePosition']) {
            $arguments->setReturn($additional['updatePosition']);
        }

        return $arguments->getReturn();
    }

    /**
     * Enlight event listener for the Shopware_Components_Basket hook.
     * This event listener function is an hook after event on the "getAttributeData" function.
     * The function is used to generate the new basket attribute data.
     *
     * @param Enlight_Hook_HookArgs $arguments
     */
    public function onGetBasketAttribute(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $returnValue = $arguments->getReturn();
        $parameters = $arguments->getArgs();
        $additional = $parameters[2];

        if (array_key_exists('bundleId', $additional)) {
            $returnValue['bundleId'] = $additional['bundleId'];
            $arguments->setReturn($returnValue);
        }
    }
    /**
     * Enlight event listener function of the global frontend post dispatch.
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onFrontendPostDispatch(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        /**@var $subject Enlight_Controller_Action*/
        $subject = $arguments->getSubject();

        /**@var $request Enlight_Controller_Request_RequestHttp*/
        $request = $subject->Request();

        /**@var $response Enlight_Controller_Response_ResponseHttp*/
        $response = $subject->Response();

        /**@var $view Enlight_View_Default*/
        $view = $subject->View();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || !$view->hasTemplate()) {
            return;
        }

        $view->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('frontend/index/bundle_header.tpl');
    }

    /**
     * Enlight event listener function.
     * Fired on the checkout section in the shop frontend.
     * Used to extend the cart_item.tpl template.
     */
    public function onCheckoutPostDispatch(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        /**@var $subject Enlight_Controller_Action*/
        $subject = $arguments->getSubject();

        /**@var $request Enlight_Controller_Request_RequestHttp*/
        $request = $subject->Request();

        /**@var $response Enlight_Controller_Response_ResponseHttp*/
        $response = $subject->Response();

        /**@var $view Enlight_View_Default*/
        $view = $subject->View();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || !$view->hasTemplate()) {
            return;
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('attribute'))
                ->from('Shopware\Models\Attribute\OrderBasket', 'attribute', 'attribute.orderBasketId')
                ->innerJoin('attribute.orderBasket', 'basket')
                ->where('basket.sessionId = :sessionId')
                ->setParameters(array('sessionId' => Shopware()->SessionID()));

        $attributes = $builder->getQuery()->getArrayResult();

        $basket = $view->getAssign('sBasket');
        if (empty($basket) || empty($attributes)) {
            return;
        }

        foreach($basket['content'] as &$row) {
            if (array_key_exists($row['id'], $attributes)) {
                $row['attribute'] = $attributes[$row['id']];
            }
        }

        $view->assign('sBasket', $basket);
        $view->addTemplateDir($this->Path() . 'Views/');

        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() > 1;

        if ($isEmotion) {
            $template = 'grid_10';
        } else {
            $template = 'grid_6';
        }
        $view->assign('checkoutBundleTemplate', $template);

        if ($request->has('sBundleValidation')) {
            $view->assign('sBundleValidation', $request->getParam('sBundleValidation'));
        }

        if ($request->getActionName() === 'cart') {
            $view->extendsTemplate('frontend/checkout/bundle_cart_item.tpl');
        } else if ($request->getActionName() === 'confirm') {
            $view->extendsTemplate('frontend/checkout/bundle_confirm_item.tpl');
        } else if ($request->getActionName() === 'finish') {
            $view->extendsTemplate('frontend/checkout/bundle_finish_item.tpl');
        }

        $view->extendsTemplate('frontend/checkout/bundle_errors.tpl');
    }

    /**
     * Enlight event listener function.
     * Fired when the customer enters the checkout section.
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onCheckoutPreDispatch(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        /**@var $subject Enlight_Controller_Action*/
        $subject = $arguments->getSubject();

        /**@var $request Enlight_Controller_Request_RequestHttp*/
        $request = $subject->Request();

        /**@var $view Enlight_View_Default*/
        $view = $subject->View();

        $validation = Shopware()->Bundle()->validateBundleDiscounts();

        if ($validation === true) {
            return;
        }

        $view->assign('sBundleValidation', $validation);

        if ($request->getActionName() === 'finish') {
            $subject->forward('confirm', 'checkout', 'frontend', array('sBundleValidation' => $validation));
        }
    }

    /**
     * Enlight event listener function of the sBasket::sDeleteArticle function.
     * Fired when the customer removes an article position or the already added
     * position fulfills no more the conditions to stay in the basket.
     * @param Enlight_Hook_HookArgs $arguments
     */
    public function onDeleteArticle(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $parameters = $arguments->getArgs();
        $id = $parameters[0];
        if (!is_numeric($id)) {
            return;
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('attribute'))
                ->from('Shopware\Models\Attribute\OrderBasket', 'attribute')
                ->where('attribute.orderBasketId = :basketId')
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->setParameters(array('basketId' => $id));

        $basketRow = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (empty($basketRow)) {
            return;
        }

        if ($basketRow['bundleId'] > 0) {
            /**@var $bundle \Shopware\CustomModels\Bundle\Bundle*/
            $bundle = Shopware()->Models()->find('Shopware\CustomModels\Bundle\Bundle', $basketRow['bundleId']);
            Shopware()->Bundle()->removeBasketBundle($bundle);
        }
    }

    /**
     * Enlight hook for the Shopware_Controllers_Frontend_Checkout saveOrder function.
     * The hook fired after the saveOrder function passed.
     * The saveOrder function returns the new order number
     */
    public function onSaveOrder(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $orderNumber = $arguments->getReturn();

        if (!strlen($orderNumber) > 0) {
            return;
        }

        $sql= "
            SELECT articleordernumber
            FROM s_order_details
            WHERE ordernumber = ?
            AND modus = 10
        ";
        $bundles = Shopware()->Db()->fetchCol($sql, array($orderNumber));

        foreach($bundles as $bundleNumber) {
            if (!empty($bundleNumber)) {
                $bundle = $this->getBundleRepository()->findOneBy(array('number' => $bundleNumber));
                if ($bundle instanceof \Shopware\CustomModels\Bundle\Bundle) {
                    Shopware()->Bundle()->decreaseBundleStock($bundle);
                }
            }
        }
    }

    /**
     * Helper function to get the variant data for the updated
     * basket position. To add additional information you can hook
     * this function and change the return value by an after hoook.
     *
     * @param Enlight_Hook_HookArgs $arguments
     *
     * @return array
     */
    public function onGetVariantUpdateData(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $parameters = $arguments->getArgs();
        $returnValue = $arguments->getReturn();

        if (!array_key_exists('quantity', $returnValue)) {
            return $returnValue;
        }

        /**@var $variant \Shopware\Models\Article\Detail*/
        $variant = $parameters[0];

        $bundlePosition = Shopware()->Bundle()->isVariantAsBundleInBasket($variant->getNumber());

        $basketItem = Shopware()->BundleBasket()->getItem($bundlePosition);

        $returnValue['quantity'] = $returnValue['quantity'] - $basketItem['quantity'];

        $arguments->setReturn($returnValue);

        return $returnValue;
    }

    /**
     * @param Enlight_Hook_HookArgs $arguments
     */
    public function onGetBasket(Enlight_Hook_HookArgs $arguments)
    {
        Shopware()->Bundle()->updateBundleBasketDiscount();
    }


}
