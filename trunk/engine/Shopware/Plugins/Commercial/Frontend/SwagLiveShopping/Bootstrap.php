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
 * Plugin bootstraping class.
 *
 * The Shopware_Plugins_Frontend_SwagLiveShopping_Bootstrap class is the bootstrap class
 * of the live shopping plugin. This class contains all functions to bootstrap the live shopping
 * extension in the shopware system.
 *
 * @category Shopware
 * @package Shopware\Plugin\SwagLiveShopping
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Frontend_SwagLiveShopping_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Shopware Entity Manager
     *
     * Used for all shopware model access. The entity manager of shopware is an extension
     * of the doctrine entity manager. It supports some helper function to extends the database
     * or generate source code for custom models.
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $entityManager = null;

    /**
     * Schema manager of the shopware database.
     *
     * Used to validate the current database version of shopware.
     *
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $schemaManager = null;

    /**
     * Shopware snippet namespace for this plugin.
     *
     * The snippetNamespace of this plugin is used for
     * all translation in this plugin.
     *
     * @var Enlight_Components_Snippet_Namespace
     */
    protected $snippetNamespace = null;

    /**
     * Shopware database connection.
     *
     * The database connection is used for all plain database accesses in this plugin.
     * It support some helper function to select or update data.
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $database = null;

    /**
     * Shopware application.
     *
     * The shopware application bootstrap is used for the communication between this plugin
     * and the shopware application. This property can be used for unit tests to resolve
     * the dependencies to shopware.
     *
     * @var Shopware
     */
    protected $shopwareBoostrap = null;

    /**
     * Current shop object.
     *
     * The shop property contains the current selected frontend shop model.
     * This property is used for all shop specified actions in the frontend.
     *
     * @var \Shopware\Models\Shop\Shop
     */
    protected $shop = null;

    /**
     * Front controller of Enlight.
     *
     * The Enlight front controller used for the dispatch process in the shopware frontend.
     * This property is used for redirections or redispatchs.
     *
     * @var Enlight_Controller_Front
     */
    protected $frontController = null;

    /**
     * Current frontend session.
     *
     * The session property contains the frontend session of shopware.
     * The session object is used for different data process in the store front.
     * It contains different data which sets from shopware like the sUserData.
     *
     * @var Enlight_Components_Session_Namespace
     */
    protected $session = null;

    /**
     * Global session id.
     *
     * The sessionId property contains the session id of the current frontend session.
     * It is used for different identifications for the current frontend user.
     * Theh sessionId has to used, if the user isn't already logged in.
     *
     * @var int
     */
    protected $sessionId = null;

    /**
     * Shopware configuration.
     *
     * The shpowareConfigu property contains an instance of the Shopware_Components_Config
     * which can be used to get access on the global defined shopware configuration.
     *
     * @var Shopware_Components_Config
     */
    protected $shopwareConfig = null;

    /**
     * Contains the Enlight_Event_EventManager.
     *
     * The Enlight event manager is used to fire custom application events
     * or register dynamic events in your application.
     *
     * @var Enlight_Event_EventManager
     */
    protected $eventManager = null;

    /**
     * Admin class of shopware.
     *
     * The shopware admin class is used for all user specified actions like user registration,
     * vat id validations or log in/out processes.
     *
     * @var sAdmin
     */
    protected $adminModule = null;

    /**
     * Article class of shopware.
     *
     * The Article class of shopware is used for all article specified processes like price calculation
     * or to get global defined data results for article data.
     *
     * @var sArticles
     */
    protected $articleModule = null;

    /**
     * Basket class of shopware.
     *
     * The Basket class of shopware is used for all basket specified processes within shopware.
     * This class allows to add or remove items to the frontend basket or to get the current
     * basket content or amount.
     *
     * @var sBasket
     */
    protected $basketModule = null;

    /**
     * Category class of shopware.
     *
     * The Category class of shopware is used for all category specified processes within shopware.
     * This class allows to get articles identified over a category id, or to get an offset of articles
     * for category listings.
     *
     * @var sCategories
     */
    protected $categoryModule = null;

    /**
     * Configurator class of shopware.
     *
     * The Configurator class of shopware is used for the different processes for configurator articles.
     * This class allows you to get the configurator configuration of a single article or translations
     * for single configurator groups or options.
     *
     * @var sConfigurator
     */
    protected $configuratorModule = null;

    /**
     * Marketing class of shopware.
     *
     * The marketing class of shopware is used for all marketing specified processes within shopware.
     * The class allows you to get similar shown articles for a single article, also bought articles for a single article,
     * banners for categories, to build tag clouds, etc.
     *
     * @var sMarketing
     */
    protected $marketingModule = null;

    /**
     * Order class of shopware.
     *
     * The order class of shopware is used for the order processes within shopware.
     * This class supports different data actions about shopware orders.
     * It allows you to add new orders in shopware or to calculate current orders
     * of the different current frontend customers.
     *
     * @var sOrder
     */
    protected $orderModule = null;

    /**
     * Live shopping component of this plugin.
     *
     * The live shopping component of shopware is used for all live shopping
     * processes within shopware.
     * The component calculates the current article price and validates live shopping
     * articles for the current frontend session.
     *
     * @var Shopware_Components_LiveShopping
     */
    protected $liveShoppingComponent = null;

    /**
     * Basket component of the live shopping plugin.
     *
     * This property is used to create a new basket row if the
     * customer want to add a live shopping article to the basket.
     *
     * @var Shopware_Components_LiveShoppingBasket
     */
    protected $liveShoppingBasketComponent = null;

    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\CustomModels\LiveShopping\Repository
     */
    protected $liveShoppingRepository = null;

    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $articleDetailRepository = null;


    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $customerGroupRepository = null;

    /**
     * Helper property to get the current system instance of shopware.
     * @var null
     */
    protected $system = null;

    /**
     * Returns the current system instance of shopware.
     *
     * In case that the internal property $system is null, the property
     * will be set as default Showpare()->System().
     * This property is used to calculate the prices including the currency factor.
     * @return null
     */
    public function getSystem()
    {
        if ($this->system === null) {
            $this->system = $this->Application()->System();
        }
        return $this->system;
    }

    /**
     * Returns the current system instance of shopware.
     *
     * In case that the internal property $system is null, the property
     * will be set as default Showpare()->System().
     * This property is used to calculate the prices including the currency factor.
     *
     * @param $system
     */
    public function setSystem($system)
    {
        $this->system = $system;
    }

    /**
     * The getCustomerGroupRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getCustomerGroupRepository()
    {
    	if ($this->customerGroupRepository === null) {
    		$this->customerGroupRepository = $this->getEntityManager()->getRepository('Shopware\Models\Customer\Group');
    	}
    	return $this->customerGroupRepository;
    }

    /**
     * The getArticleDetailRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getArticleDetailRepository()
    {
    	if ($this->articleDetailRepository === null) {
    		$this->articleDetailRepository = $this->getEntityManager()->getRepository('Shopware\Models\Article\Detail');
    	}
    	return $this->articleDetailRepository;
    }

    /**
     * The getArticleRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getArticleRepository()
    {
    	if ($this->articleRepository === null) {
    		$this->articleRepository = $this->getEntityManager()->getRepository('Shopware\Models\Article\Article');
    	}
    	return $this->articleRepository;
    }

    /**
     * The getLiveShoppingRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return \Shopware\CustomModels\LiveShopping\Repository
     */
    public function getLiveShoppingRepository()
    {
        if ($this->liveShoppingRepository === null) {
            $this->liveShoppingRepository = $this->getEntityManager()->getRepository('Shopware\CustomModels\LiveShopping\LiveShopping');
        }
        return $this->liveShoppingRepository;
    }

    /**
     * Getter of the liveShoppingBasketComponent property.
     *
     * This property is used to create a new basket row if the
     * customer want to add a live shopping article to the basket.
     *
     * @return Shopware_Components_LiveShoppingBasket
     */
    protected function getLiveShoppingBasketComponent()
    {
        if ($this->liveShoppingBasketComponent === null) {
            $this->liveShoppingBasketComponent = $this->Application()->LiveShoppingBasket();
        }
        return $this->liveShoppingBasketComponent;
    }

    /**
     * Getter of the liveShoppingComponent property
     *
     * The getter function of the liveShoppingComponent property of this class returns the current
     * instance of the Shopware_Components_LiveShopping class. If the class property is set to null,
     * the getter function loads the component over Shopware()->LiveShopping().
     * @return Shopware_Components_LiveShopping
     */
    protected function getLiveShoppingComponent()
    {
        if ($this->liveShoppingComponent === null) {
            $this->liveShoppingComponent = $this->Application()->LiveShopping();
        }
        return $this->liveShoppingComponent;
    }

    /**
     * Getter of the entity manager property.
     *
     * The getter function of the entityManager property returns the current instance
     * of the Shopware\Components\Model\ModelManager which used for all shopware model
     * access.
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    protected function getEntityManager()
    {
        if ($this->entityManager === null) {
            $this->entityManager = $this->Application()->Models();
        }
        return $this->entityManager;
    }

    /**
     * Shopware application bootstrap class.
     *
     * Used to register plugin components.
     *
     * @return Enlight_Bootstrap
     */
    public function getShopwareBoostrap()
    {
        if ($this->shopwareBoostrap === null) {
            $this->shopwareBoostrap = $this->Application()->Bootstrap();
        }
        return $this->shopwareBoostrap;
    }

    /**
     * Getter of the schema manager property.
     *
     * Helper function to create an own database schema manager to remove
     * all dependencies to the existing shopware models and meta data caches.
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected function getSchemaManager()
    {
        if ($this->schemaManager === null) {
            /**@var $connection \Doctrine\DBAL\Connection*/
            $connection = \Doctrine\DBAL\DriverManager::getConnection(
                array('pdo' => $this->getDatabase()->getConnection())
            );

            $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

            $this->schemaManager = $connection->getSchemaManager();
        }

        return $this->schemaManager;
    }

    /**
     * Getter of the shop property.
     *
     * The shop property contains the current selected frontend shop model.
     * This property is used for all shop specified actions in the frontend.
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        if ($this->shop === null) {
            $this->shop = Shopware()->Shop();
        }
        return $this->shop;
    }

    /**
     * Getter of the adminModule property.
     *
     * The shopware admin class is used for all user specified actions like user registration,
     * vat id validations or log in/out processes.
     *
     * @return \sAdmin
     */
    public function getAdminModule()
    {
        if ($this->adminModule === null) {
            $this->adminModule = Shopware()->Modules()->Admin();
        }
        return $this->adminModule;
    }

    /**
     * Getter of the articleModule property.
     *
     * The Article class of shopware is used for all article specified processes like price calculation
     * or to get global defined data results for article data.
     *
     * @return \sArticles
     */
    public function getArticleModule()
    {
        if ($this->articleModule === null) {
            $this->articleModule = Shopware()->Modules()->Articles();
        }
        return $this->articleModule;
    }

    /**
     * Getter of the basketModule property.
     *
     * The Basket class of shopware is used for all basket specified processes within shopware.
     * This class allows to add or remove items to the frontend basket or to get the current
     * basket content or amount.
     *
     * @return \sBasket
     */
    public function getBasketModule()
    {
        if ($this->basketModule === null) {
            $this->basketModule = Shopware()->Modules()->Basket();
        }
        return $this->basketModule;
    }

    /**
     * Getter of the categoryModule property.
     *
     * The Category class of shopware is used for all category specified processes within shopware.
     * This class allows to get articles identified over a category id, or to get an offset of articles
     * for category listings.
     *
     * @return \sCategories
     */
    public function getCategoryModule()
    {
        if ($this->categoryModule === null) {
            $this->categoryModule = Shopware()->Modules()->Categories();
        }
        return $this->categoryModule;
    }

    /**
     * Getter of the configuratorModule property.
     *
     * The Configurator class of shopware is used for the different processes for configurator articles.
     * This class allows you to get the configurator configuration of a single article or translations
     * for single configurator groups or options.
     *
     * @return \sConfigurator
     */
    public function getConfiguratorModule()
    {
        if ($this->configuratorModule === null) {
            $this->configuratorModule = Shopware()->Modules()->Configurator();
        }
        return $this->configuratorModule;
    }

    /**
     * Getter of the database property.
     *
     * The database connection is used for all plain database accesses in this plugin.
     * It support some helper function to select or update data.
     *
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function getDatabase()
    {
        if ($this->database === null) {
            $this->database = Shopware()->Db();
        }
        return $this->database;
    }

    /**
     * Getter of the eventManager property.
     *
     * The Enlight event manager is used to fire custom application events
     * or register dynamic events in your application.
     *
     * @return \Enlight_Event_EventManager
     */
    public function getEventManager()
    {
        if ($this->eventManager === null) {
            $this->eventManager = Enlight()->Events();
        }
        return $this->eventManager;
    }

    /**
     * Getter of the frontController property.
     *
     * The Enlight front controller used for the dispatch process in the shopware frontend.
     * This property is used for redirections or redispatchs.
     *
     * @return \Enlight_Controller_Front
     */
    public function getFrontController()
    {
        if ($this->frontController === null) {
            $this->frontController = Shopware()->Front();
        }
        return $this->frontController;
    }

    /**
     * Getter of the marketingModule property.
     *
     * The marketing class of shopware is used for all marketing specified processes within shopware.
     * The class allows you to get similar shown articles for a single article, also bought articles for a single article,
     * banners for categories, to build tag clouds, etc.
     *
     * @return \sMarketing
     */
    public function getMarketingModule()
    {
        if ($this->marketingModule === null) {
            $this->marketingModule = Shopware()->Modules()->Marketing();
        }
        return $this->marketingModule;
    }

    /**
     * Getter of the orderModule property.
     *
     * The order class of shopware is used for the order processes within shopware.
     * This class supports different data actions about shopware orders.
     * It allows you to add new orders in shopware or to calculate current orders
     * of the different current frontend customers.
     *
     * @return \sOrder
     */
    public function getOrderModule()
    {
        if ($this->orderModule === null) {
            $this->orderModule = Shopware()->Modules()->Order();
        }
        return $this->orderModule;
    }

    /**
     * Getter of the session property.
     *
     * The session property contains the frontend session of shopware.
     * The session object is used for different data process in the store front.
     * It contains different data which sets from shopware like the sUserData.
     *
     * @return \Enlight_Components_Session_Namespace
     */
    public function getSession()
    {
        if ($this->session === null) {
            $this->session = Shopware()->Session();
        }
        return $this->session;
    }

    /**
     * Getter of the sessionId property.
     *
     * The sessionId property contains the session id of the current frontend session.
     * It is used for different identifications for the current frontend user.
     * Theh sessionId has to used, if the user isn't already logged in.
     *
     * @return int
     */
    public function getSessionId()
    {
        if ($this->sessionId === null) {
            $this->sessionId = Shopware()->SessionID();
        }
        return $this->sessionId;
    }

    /**
     * Getter of the shopwareConfig property.
     *
     * The shpowareConfigu property contains an instance of the Shopware_Components_Config
     * which can be used to get access on the global defined shopware configuration.
     *
     * @return \Shopware_Components_Config
     */
    public function getShopwareConfig()
    {
        if ($this->shopwareConfig === null) {
            $this->shopwareConfig = Shopware()->Config();
        }
        return $this->shopwareConfig;
    }

    /**
     * Getter of the snippetNamespace property.
     *
     * The snippetNamespace of this plugin is used for
     * all translation in this plugin.
     *
     * @return \Enlight_Components_Snippet_Namespace
     */
    public function getSnippetNamespace()
    {
        if ($this->snippetNamespace === null) {
            $this->snippetNamespace = Shopware()->Snippets()->getNamespace('backend/live_shopping');
        }
        return $this->snippetNamespace;
    }

    /**
     * Global live shopping lizenz check.
     *
     * Helper function to check the plugin lizenz.
     *
     * @param   bool $throwException
     * @throws  Exception
     * @return  bool
     */
    public function checkLicense($throwException = true)
    {
        static $r, $m = 'SwagLiveshopping';
        if(!isset($r)) {
            $s = base64_decode('e7FERAJGxQHNpYx0b3Nbgnu4ycQ=');
            $c = base64_decode('EUtmej9CZbp6zkJlpYXPDeTPoeE=');
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

    /**
     * Returns the meta information about the plugin
     * as an array.
     * Keep in mind that the plugin description located
     * in the info.txt.
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
            'label' => $this->getLabel(),
            'version' => $this->getVersion(),
            'link' => 'http://www.shopware.de/',
            'description' => file_get_contents($this->Path() . 'info.txt')
        );
    }

    /**
     * Plugin configuration function.
     *
     * Returns the displayed name for this plugin.
     * Used in the plugin manager and plugin information.
     *
     * @return string|void
     */
    public function getLabel()
    {
        return 'Liveshopping';
    }

    /**
     * Plugin version getter.
     *
     * Returns the current version of the plugin.
     *
     * @return string
     */
    public function getVersion()
    {
        return "2.0.0";
    }

    /**
     * After init event of the bootstrap class.
     *
     * The afterInit function registers the custom plugin models.
     */
    public function afterInit()
    {
        $this->registerCustomModels();
    }

    /**
     * Install function of the plugin bootstrap.
     *
     * Registers all necessary components and dependencies.
     *
     * @return bool
     */
    public function install()
    {
        if (!$this->checkLicense(true)) {
            return false;
        }

        try {
            $result = $this->updateDatabase();

            if ($result === false) {
                return array('success' => false, 'message' => 'Could not update live shopping database tables. Please check your database for inconsistence data and structure');
            }

            $this->createAttributes();

            $this->registerComponents();

            $this->subscribeControllerEvents();

            $this->subscribeFrontendDispatchEvents();

            $this->subscribeBackendDispatchEvents();

            $this->subscribeHooks();

//            $this->createMenu();

            return array(
                'success' => true,
                'invalidateCache' => array('backend','proxy','frontend')
            );
        } catch (Exception $e) {
            $this->removeAttributes();

            $this->restoreBackup();

            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Uninstall function of the plugin.
     *
     * Removes all plugin custom resources like database tables, etc.
     *
     * @return bool|void
     */
    public function uninstall()
    {
        $this->removeAttributes();

        return array(
            'success' => true,
            'invalidateCache' => array('backend','proxy','frontend')
        );

    }

    /**
     * Creates the LiveShopping backend menu item.
     *
     * The LiveShopping menu item opens the listing for the SwagLiveShopping plugin.
     */
    public function createMenu()
    {
        $this->createMenuItem(array(
            'label' => $this->getLabel(),
            'controller' => 'LiveShopping',
            'class' => 'sprite-alarm-clock',
            'action' => 'Index',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy('label', 'Artikel')
        ));
    }

    /**
     * Removes the plugin specified shopware attributes.
     *
     * This function is used to remove the added shopware attribute to add plugin specified data to the different
     * shopware resources.
     */
    private function removeAttributes()
    {
        $this->getEntityManager()->removeAttribute(
            's_order_basket_attributes', 'swag', 'live_shopping_timestamp'
        );

        $this->getEntityManager()->removeAttribute(
            's_order_basket_attributes', 'swag', 'live_shopping_id'
        );

        $this->getEntityManager()->generateAttributeModels(array(
            's_order_basket_attributes'
        ));
    }

    /**
     * Creates/Updates the shopware database with the plugin
     * specified database tables, data sets.
     *
     * @throws Exception In case that an database action fails
     */
    public function updateDatabase()
    {
        $oldStructure = $this->tableExist('s_articles_live');

        $this->createBackupTables($oldStructure);

        try {
            $this->createNewTables();

            if ($oldStructure) {
                $this->migrateData();
            } else {
                $this->importData();
            }

            $this->removeBackup();
            return true;
        } catch (Exception $e) {
            $this->restoreBackup();
            return false;
        }
    }

    public function migrateData()
    {
        $this->migrateLiveShoppings();
        $this->migratePrices();
        $this->migrateLimitedShops();
        $this->migrateLimitedVariants();
    }

    public function restoreBackup()
    {
        $this->dropTable('s_articles_live');
        $this->dropTable('s_articles_lives');
        $this->dropTable('s_articles_live_prices');
        $this->dropTable('s_articles_live_shoprelations');
        $this->dropTable('s_articles_live_stint');
        $this->dropTable('s_articles_live_customer_groups');

        $this->renameTable('s_articles_live_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_lives_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_live_prices_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_live_shoprelations_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_live_stint_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_live_customer_groups_sw_backup', 's_articles_live');
    }

    public function removeBackup()
    {
        $this->dropTable('s_articles_live_sw_backup');
        $this->dropTable('s_articles_lives_sw_backup');
        $this->dropTable('s_articles_live_prices_sw_backup');
        $this->dropTable('s_articles_live_shoprelations_sw_backup');
        $this->dropTable('s_articles_live_stint_sw_backup');
        $this->dropTable('s_articles_live_customer_groups_sw_backup');
    }


    /**
     * Helper function to migrate 3.5 shopware live shopping articles into the shopware 4 structure
     */
    public function migrateLiveShoppings()
    {
        $sql = "SELECT * FROM s_articles_live_sw_backup";
        $result = $this->getDatabase()->fetchAll($sql);

        foreach($result as $liveShoppingData) {
            if (!empty($liveShoppingData['customergroups'])) {
                $customerGroups = explode(',', $liveShoppingData['customergroups']);
                foreach($customerGroups as $key) {
                    $customerGroup = $this->getCustomerGroupRepository()->findOneBy(array('key' => $key));
                    if (!($customerGroup instanceof \Shopware\Models\Customer\Group)) {
                        continue;
                    }
                    $sql = "INSERT INTO s_articles_live_customer_groups (live_shopping_id, customer_group_id) VALUES (?, ?)";
                    $this->getDatabase()->query($sql, array($liveShoppingData['id'], $customerGroup->getId()));
                }
            }

            $sql = "INSERT INTO s_articles_lives (id, article_id, type, name, active, order_number, max_quantity_enable, max_quantity, valid_from, valid_to, datum, sells)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $this->getDatabase()->query($sql, array(
                $liveShoppingData['id'],
                $liveShoppingData['articleID'],
                $liveShoppingData['typeID'],
                $liveShoppingData['name'],
                $liveShoppingData['active'],
                $liveShoppingData['ordernumber'],
                $liveShoppingData['max_quantity_enable'],
                $liveShoppingData['max_quantity'],
                $liveShoppingData['valid_from'],
                $liveShoppingData['valid_to'],
                $liveShoppingData['datum'],
                $liveShoppingData['sells']
            ));
        }
    }


    public function migratePrices()
    {
        $sql = "SELECT s_articles_live_prices_sw_backup.*, s_articles_live_sw_backup.articleID
                FROM s_articles_live_prices_sw_backup
                INNER JOIN s_articles_live_sw_backup
                    ON s_articles_live_prices_sw_backup.liveshoppingID = s_articles_live_sw_backup.id";
        $result = $this->getDatabase()->fetchAll($sql);

        foreach($result as $priceData) {
            if (empty($priceData) || empty($priceData['customergroup'])) {
                continue;
            }

            $customerGroup = $this->getCustomerGroupRepository()->findOneBy(array('key' => $priceData['customergroup']));
            if (!($customerGroup instanceof \Shopware\Models\Customer\Group)) {
                continue;
            }

            if ($customerGroup->getTaxInput() && !empty($priceData['articleID'])) {
                $article = $this->getEntityManager()->find('Shopware\Models\Article\Article', $priceData['articleID']);
                if ($article instanceof \Shopware\Models\Article\Article) {
                    if (!empty($priceData['price'])) {
                        $priceData['price'] = $priceData['price'] / (100 + $article->getTax()->getTax()) * 100;
                    }
                    if (!empty($priceData['endprice'])) {
                        $priceData['endprice'] = $priceData['endprice'] / (100 + $article->getTax()->getTax()) * 100;
                    }
                }
            }

            $sql = "INSERT INTO s_articles_live_prices (id, live_shopping_id, customer_group_id, price, endprice)
                    VALUES (?, ?, ?, ?, ?)";
            $this->getDatabase()->query($sql, array(
                $priceData['id'],
                $priceData['liveshoppingID'],
                $customerGroup->getId(),
                $priceData['price'],
                $priceData['endprice']
            ));
        }
    }

    public function migrateLimitedShops()
    {
        $this->importTable('s_articles_live_shoprelations', 's_articles_live_shoprelations_sw_backup', 'id, liveshoppingID as live_shopping_id, subshopID as shop_id');
    }

    public function migrateLimitedVariants()
    {
        $sql = "SELECT * FROM s_articles_live_stint_sw_backup";
        $result = $this->getDatabase()->fetchAll($sql);

        foreach($result as $variantData) {
            if (empty($variantData) || empty($variantData['ordernumber'])) {
                continue;
            }
            $variant = $this->getArticleDetailRepository()->findOneBy(
                array('number' => $variantData['ordernumber'])
            );
            if (!($variant instanceof \Shopware\Models\Article\Detail)) {
                continue;
            }
            $sql = "INSERT INTO s_articles_live_stint (live_shopping_id, article_detail_id)
                    VALUES (?,?)";
            $this->getDatabase()->query($sql, array($variantData['liveshoppingID'], $variant->getId()));
        }
    }

    public function createNewTables()
    {
        $this->createLiveShoppingTable();
        $this->createCustomerGroupTable();
        $this->createLimitedVariantTable();
        $this->createPriceTable();
        $this->createSubShopTable();
    }


    public function importData()
    {
        if ($this->tableExist('s_articles_lives_sw_backup')) {
            $this->importTable('s_articles_lives', 's_articles_lives_sw_backup');
        }
        if ($this->tableExist('s_articles_live_customer_groups_sw_backup')) {
            $this->importTable('s_articles_live_customer_groups', 's_articles_live_customer_groups_sw_backup');
        }
        if ($this->tableExist('s_articles_live_prices_sw_backup')) {
            $this->importTable('s_articles_live_prices', 's_articles_live_prices_sw_backup');
        }
        if ($this->tableExist('s_articles_live_shoprelations_sw_backup')) {
            $this->importTable('s_articles_live_shoprelations', 's_articles_live_shoprelations_sw_backup');
        }
        if ($this->tableExist('s_articles_live_stint_sw_backup')) {
            $this->importTable('s_articles_live_stint', 's_articles_live_stint_sw_backup');
        }
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
     *
     */
    public function createBackupTables($oldStructure)
    {
        if ($oldStructure) {
            $this->createBackupTable('s_articles_live');
            $this->createBackupTable('s_articles_live_prices');
            $this->createBackupTable('s_articles_live_shoprelations');
            $this->createBackupTable('s_articles_live_stint');
        } else {
            $this->createBackupTable('s_articles_lives');
            $this->createBackupTable('s_articles_live_customer_groups');
            $this->createBackupTable('s_articles_live_prices');
            $this->createBackupTable('s_articles_live_shoprelations');
            $this->createBackupTable('s_articles_live_stint');
        }
    }

    /**
     * Helper function to create a new backup for the passed table
     * @param $name
     */
    private function createBackupTable($name)
    {
        if (!$this->tableExist($name)) {
            return;
        }
        if ($this->tableExist($name . "_sw_backup")) {
            $this->dropTable($name . "_sw_backup");
        }
        $this->renameTable($name, $name . "_sw_backup");
    }

    /**
     * Helper function to create the s_articles_lives table.
     *
     * The s_articles_lives table contains all defined live shopping articles.
     */
    private function createLiveShoppingTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_lives` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `article_id` int(11) unsigned DEFAULT NULL,
              `type` int(1) DEFAULT NULL,
              `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `active` int(1) unsigned NOT NULL,
              `order_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `max_quantity_enable` int(1) unsigned NOT NULL,
              `max_quantity` int(11) unsigned NOT NULL,
              `valid_from` datetime NULL DEFAULT NULL,
              `valid_to` datetime NULL DEFAULT NULL,
              `datum` datetime NULL DEFAULT NULL,
              `sells` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `article_id` (`article_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        $this->getDatabase()->exec($sql);
    }


    /**
     * Helper function to create the s_articles_live_customer_groups table.
     *
     * The s_articles_live_customer_groups table contains the definition which customer groups
     * can buy/see the defined live shopping article.
     */
    private function createCustomerGroupTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_live_customer_groups` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `live_shopping_id` int(11) unsigned DEFAULT NULL,
              `customer_group_id` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `live_shopping_id` (`live_shopping_id`,`customer_group_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        $this->getDatabase()->exec($sql);
    }

    /**
     * Helper function to create the s_articles_live_stint table.
     *
     * The s_articles_live_stint table contains the definition of a limited variant definition for
     * each live shopping article. The stint table allows the user to define an offset of article variants
     * on which the live shopping article will be displayed.
     */
    private function createLimitedVariantTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_live_stint` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `live_shopping_id` int(11) unsigned DEFAULT NULL,
              `article_detail_id` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `live_shopping_id` (`live_shopping_id`,`article_detail_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        $this->getDatabase()->exec($sql);
    }

    /**
     * Helper function to create the s_articles_live_prices table.
     *
     * The s_articles_live_prices table contains the definition of the different prices
     * of a single live shopping article for each customer group.
     */
    private function createPriceTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_live_prices` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `live_shopping_id` int(11) unsigned DEFAULT NULL,
              `customer_group_id` int(11) DEFAULT NULL,
              `price` double NOT NULL,
              `endprice` double NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        $this->getDatabase()->exec($sql);
    }


    /**
     * Helper function to create the s_articles_shoprelations table.
     *
     * The s_articles_shoprelations contains the definition in which shops each defined
     * live shopping article is visible.
     */
    private function createSubShopTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_live_shoprelations` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `live_shopping_id` int(11) unsigned DEFAULT NULL,
              `shop_id` int(11) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `live_shopping_id` (`live_shopping_id`,`shop_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        $this->getDatabase()->exec($sql);
    }

    /**
     * Creates the plugin specified shopware attributes.
     *
     * This function is used to add new shopware attribute to add plugin specified data to the different
     * shopware resources.
     */
    public function createAttributes()
    {
        $this->getEntityManager()->addAttribute(
            's_order_basket_attributes', 'swag', 'live_shopping_timestamp', 'DATETIME', true, NULL
        );
        $this->getEntityManager()->addAttribute(
            's_order_basket_attributes', 'swag', 'live_shopping_id', 'INT(11)', true, NULL
        );

        $this->getEntityManager()->generateAttributeModels(array(
            's_order_basket_attributes'
        ));
    }

    /**
     * Registers an own resource for this plugin.
     *
     * The resource can be called over "Shopware()->LiveShopping()".
     */
    public function registerComponents()
    {
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_LiveShopping',
            'onInitLiveShoppingResource'
        );

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_LiveShoppingBasket',
            'onInitLiveShoppingBasketResource'
        );
    }

    /**
     * Registers the different events for the plugin controllers.
     *
     * <pre>
     * For example:
     *   - Event listeners for an own plugin backend controller.
     *   - Event listeners for an own plugin frontend controller.
     *   - Event listeners for an own plugin widget controller.
     *   - Event listeners for an own plugin api controller.
     * </pre>
     */
    public function subscribeControllerEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_LiveShopping',
            'onGetFrontendController'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_LiveShopping',
            'onGetBackendController'
        );
    }

    /**
     * Subscribes the frontend events.
     *
     * Registers the different frontend post/pre dispatch events.
     * <pre>
     * For example:
     *   - Event listener for the post/pre dispatch event of the frontend Shopware_Controllers_Frontend_Account controller.
     *   - Event listener for the post/pre dispatch event of the frontend Shopware_Controllers_Frontend_Checkout controller.
     *   - Event listener for the post/pre dispatch event of the frontend Shopware_Controllers_Frontend_Listing controller.
     *   - ...
     * </pre>
     */
    public function subscribeFrontendDispatchEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout', 'onFrontendCheckoutPostDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout', 'onCheckoutPreDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Listing', 'onFrontendPostDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Detail', 'onFrontendDetailPostDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch', 'onFrontendPostDispatch', -100
        );

        $this->subscribeEvent(
            'Shopware_Modules_Articles_sGetArticlesByCategory_FilterResult',
            'onGetArticlesByCategory'
        );
    }

    /**
     * Subscribes the backend events.
     *
     * Registers the different backend post/pre dispatch events.
     * <pre>
     * For example:
     *   - Event listener for the post/pre dispatch event of the frontend Shopware_Controllers_Backend_Article controller.
     *   - Event listener for the post/pre dispatch event of the frontend Shopware_Controllers_Backend_Order controller.
     *   - Event listener for the post/pre dispatch event of the frontend Shopware_Controllers_Backend_Customer controller.
     *   - ...
     * </pre>
     */
    public function subscribeBackendDispatchEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Article', 'onBackendArticlePostDispatch'
        );
    }

    /**
     * Susbcribe all required hooks.
     *
     * Registers the required plugin hooks.
     * <pre>
     * For example:
     *   - Before hook for the sBasket class, sAddArticle function.
     *   - Replace hook for the sBasket class, sAddArticle function.
     *   - After hook for the sBasket class, sAddArticle function.
     *   - ...
     * </pre>
     */
    public function subscribeHooks()
    {
        $this->subscribeEvent('sBasket::sAddArticle::before', 'onBeforeAddArticle');
        $this->subscribeEvent('sBasket::sAddArticle::after', 'onAfterAddArticle');
        $this->subscribeEvent('sBasket::sUpdateArticle::before', 'onUpdateArticle');
        $this->subscribeEvent('sArticles::sGetPromotionById::after', 'onGetPromotion');

        $this->subscribeEvent(
            'Shopware_Controllers_Frontend_Checkout::saveOrder::after',
            'onSaveOrder'
        );

        //hook for the new basket component. The shouldAddAsNewPosition is used to identify if the article should add as new basket position.
        $this->subscribeEvent(
            'Shopware_Components_LiveShoppingBasket::shouldAddAsNewPosition::after',
            'onShouldAddAsNewPosition'
        );

        $this->subscribeEvent(
            'Shopware_Components_LiveShoppingBasket::getAttributeCreateData::after',
            'onGetBasketAttribute'
        );

        $this->subscribeEvent(
            'Shopware_Components_LiveShoppingBasket::getVariantCreateData::after',
            'onGetVariantCreateData'
        );
    }


    /*************************************************/
    /*Controller and component register listeners*/

    /**
     * Event listener function of the Enlight_Bootstrap_InitResource_LiveShopping event.
     * Fired if the shopware source code call Shopware()->LiveShopping();
     *
     * @return Shopware_Components_LiveShopping
     */
    public function onInitLiveShoppingResource()
    {
        if (!$this->checkLicense(false)) {
            return null;
        }

        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
                $this->Path() . 'Components/'
        );
        $liveShopping = Enlight_Class::Instance('Shopware_Components_LiveShopping');
        $this->getShopwareBoostrap()->registerResource('LiveShopping', $liveShopping);

        return $liveShopping;
    }

    /**
     * Event listener function of the Enlight_Bootstrap_InitResource_LiveShoppingBasket event.
     * Fired if the shopware source code call Shopware()->LiveShoppingBasket();
     *
     * @return Shopware_Components_LiveShoppingBasket
     */
    public function onInitLiveShoppingBasketResource()
    {
        if (!$this->checkLicense(false)) {
            return null;
        }

        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );
        $liveShoppingBasket = Enlight_Class::Instance('Shopware_Components_LiveShoppingBasket');
        $this->getShopwareBoostrap()->registerResource('LiveShoppingBasket', $liveShoppingBasket);

        return $liveShoppingBasket;
    }

    /**
     * Returns the path to the controller.
     *
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Frontend_LiveShopping
     * event.
     * Fired if an request will be root to the own LiveShopping frontend controller.
     *
     * @return string
     */
    public function onGetFrontendController()
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
        return $this->Path(). 'Controllers/Frontend/LiveShopping.php';
    }

    /**
     * Returns the path to the controller.
     *
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_LiveShopping
     * event.
     * Fired if an request will be root to the own LiveShopping backend controller.
     *
     * @return string
     */
    public function onGetBackendController()
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
        return $this->Path(). 'Controllers/Backend/LiveShopping.php';
    }



    /************************************************/
    /*Backend post dispatches to extend the tempalte*/

    /**
     * Post dispatch event of the article backend module.
     *
     * Event listener function of the Shopware_Controllers_Backend_Article post dispatch event.
     * This listener is used to extend the article backend module with the live shopping tab.
     * In case that the current request action equals "load" the function lodas all extJs overrides.
     * In case that the current request action equals "index" the function loads the live shopping application.
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onBackendArticlePostDispatch(Enlight_Event_EventArgs $arguments)
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
                'backend/article/view/detail/live_shopping_window.js'
            );
        }

        //if the controller action name equals "index" we have to extend the backend article application
        if ($arguments->getRequest()->getActionName() === 'index') {
            $arguments->getSubject()->View()->extendsTemplate(
                'backend/article/live_shopping_app.js'
            );
        }
    }


    /*************************************************/
    /*Frontend post dispatches to extend the tempalte*/

    /**
     * Frontend article detail page.
     *
     * Event listener function of the Shopware_Controllers_Frontend_Detail post dispatch event.
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return null
     */
    public function onFrontendDetailPostDispatch(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return null;
        }

        /**@var $subject Enlight_Controller_Action*/
        $subject = $arguments->getSubject();

        /**@var $request Enlight_Controller_Request_RequestHttp*/
        $request = $subject->Request();

        /**@var $response Enlight_Controller_Response_ResponseHttp*/
        $response = $subject->Response();

        /**@var $view Enlight_View_Default*/
        $view = $subject->View();

        if (!$request->isDispatched()
                || $response->isException()
                || !$view->hasTemplate()) {
            return;
        }

        $articleId = (int) $request->getParam('sArticle');

        /**@var $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping*/
        $liveShopping = $this->getLiveShoppingComponent()->getActiveLiveShoppingForArticle($articleId);

        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return;
        }

        /**@var $article \Shopware\Models\Article\Article*/
        $article = $this->getArticleRepository()->find($articleId);

        $selectedVariant = $this->getSelectedVariantForArticle($article, $request->getParam('group'));

        if (!$this->getLiveShoppingComponent()->isVariantAllowed($liveShopping, $selectedVariant)) {
            return;
        }

        $data = $this->getLiveShoppingComponent()->getLiveShoppingArrayData($liveShopping);

        if (empty($data)) {
            return;
        }

        $view->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('frontend/index/live_shopping_header.tpl');
        $view->extendsTemplate('frontend/plugins/live_shopping/index.tpl');
        $view->assign('liveShopping', $data);

        //fix to hide the shopware standard prices.
        $article = $view->sArticle;
        $article['liveshoppingData']['valid_to_ts'] = 'hide';
        $view->sArticle = $article;
    }

    /**
     * Event listener of the frontend post dispatch.
     *
     * Fired in the global post dispatch event and in the listing event.
     * This listener is used to override all article templates which an included price.
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return null
     */
    public function onFrontendPostDispatch(Enlight_Event_EventArgs $arguments)
    {
        /**@var $subject Enlight_Controller_Action*/
        $subject = $arguments->getSubject();

        /**@var $request Enlight_Controller_Request_RequestHttp*/
        $request = $subject->Request();

        $response = $subject->Response();

        /**@var $view Enlight_View_Default*/
        $view = $subject->View();

        if ($request->isDispatched()
            && !$response->isException()
            && $view->hasTemplate()
            && ($request->getModuleName() === 'frontend' || $request->getModuleName() === 'widgets' && $request->getControllerName() === 'emotion')) {

                if (!$this->checkLicense(false)) {
                    return null;
                }

                $view->addTemplateDir($this->Path() . 'Views/');

                $view->extendsTemplate('frontend/index/live_shopping_header.tpl');

                if ($this->getShop()->getTemplate()->getVersion() > 1) {
                    $view->extendsTemplate('frontend/listing/live_shopping_box.tpl');
                }

        }

    }


    /*********************************************/
    /*Article hooks to override the default price*/

    /**
     * Enlight hook of the sArticles::sGetArticlesByCategory function.
     *
     * This function is used to manipulate the category listing article prices.
     * As return value we will get an array with data of articles.
     * The function iterates this data and checks if, for the current frontend session,
     * the array contains active live shopping articles and refresh their prices.
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return mixed
     */
    public function onGetArticlesByCategory(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return $arguments->getReturn();
        }

        $returnValue = $arguments->getReturn();
        $articles = $returnValue['sArticles'];

        foreach($articles as &$article) {
            $articleId = $article['articleID'];

            $liveShopping = $this->getLiveShoppingComponent()->getActiveLiveShoppingForArticle($articleId);

            if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
                continue;
            }

            $article['liveShopping'] = $this->getLiveShoppingComponent()->getLiveShoppingArrayData($liveShopping);
            $article['price'] = $this->getArticleModule()->sFormatPrice($liveShopping->getCurrentPrice());
        }

        $returnValue['sArticles'] = $articles;

        $arguments->setReturn($returnValue);
        return $arguments->getReturn();
    }

    /**
     * Enlight hook listener of the sArticles::sGetPromotionById function
     *
     * This function is used to manipulate the article prices which not displayed
     * in listings or on the article detail page.
     * As return value the enlight hook arguments returns an article id.
     * The function checks if, for the current frontend session,
     * the passed article has an active live shopping and refresh the prices.
     *
     * @param Enlight_Hook_HookArgs $arguments
     *
     * @return mixed
     */
    public function onGetPromotion(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return $arguments->getReturn();
        }

        $returnValue = $arguments->getReturn();
        $articleId = $returnValue['articleID'];

        $liveShopping = $this->getLiveShoppingComponent()->getActiveLiveShoppingForArticle($articleId);

        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return;
        }

        $returnValue['liveShopping'] = $this->getLiveShoppingComponent()->getLiveShoppingArrayData($liveShopping);
        $returnValue['price'] = $this->getArticleModule()->sFormatPrice($liveShopping->getCurrentPrice());
        $returnValue['priceStartingFrom'] = $returnValue['price'];

        $arguments->setReturn($returnValue);

        return $arguments->getReturn();
    }




    /****************/
    /* Basket Hooks */

    /**
     * Enlight hook of the sBasket::sAddArticle function.
     *
     * This function is used to return the inserted basket id on adding a live shopping article.
     *
     * @param Enlight_Hook_HookArgs $arguments
     * @return int
     */
    public function onAfterAddArticle(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $orderNumber = $arguments->get('id');
        $builder = $this->getLiveShoppingRepository()->getLastBasketLiveShoppingQueryBuilder(
            $this->getSessionId(),
            $orderNumber
        );

        /**@var $lastRow \Shopware\Models\Order\Basket*/
        $lastRow = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if (!empty($lastRow) && empty($orderNumber)) {
            return $lastRow->getId();
        }
    }

    /**
     * Enlight event listener function of the sBasket()->sAddArticle() function.
     * The event is subscribed as replace event.
     * If no case of the bundle module occurred, the default function will be executed.
     */
    public function onBeforeAddArticle(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return $arguments->getReturn();
        }

        $orderNumber = $arguments->get('id');
        $quantity = $arguments->get('quantity');

        $variant = $this->getArticleDetailRepository()->findOneBy(array('number' => $orderNumber));

        if (!$variant instanceof \Shopware\Models\Article\Detail) {
            return $arguments->getReturn();
        }

        $liveShopping = $this->getLiveShoppingComponent()->getActiveLiveShoppingForArticle(
            $variant->getArticle()->getId()
        );

        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return $arguments->getReturn();
        }

        if (!$this->getLiveShoppingComponent()->isVariantAllowed($liveShopping, $variant)) {
            return;
        }

        $arguments->set('id', '');
        $data = $this->getLiveShoppingBasketComponent()->addArticle(
            $orderNumber,
            $quantity,
            array('forceNewPosition' => true, 'liveShopping' => $liveShopping)
        );
    }

    /**
     * Basket hook after.
     *
     * This function is a hook listener function of the Shopware_Components_LiveShoppingBasket::shouldAddAsNewPosition function.
     * The original function is used to check if the current article should add as new basket row or not.
     *
     * @param Enlight_Hook_HookArgs $arguments
     * @return bool|mixed
     */
    public function onShouldAddAsNewPosition(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return $arguments->getReturn();
        }

        $parameter = $arguments->getArgs();
        $additional = $parameter[2];

        //check if the current process would add a bundle article
        if ($additional['liveShopping'] || $additional['forceNewPosition']) {
            $arguments->setReturn(true);
        }

        return $arguments->getReturn();
    }

    /**
     * Basket hook after.
     *
     * This functioni is a hook listener function of the Shopware_Components_LiveShoppingBasket::getAttributeCreateData
     * function.
     * The original function is used to get the basket attributes for the passed article.
     *
     * @param Enlight_Hook_HookArgs $arguments
     * @return mixed
     */
    public function onGetBasketAttribute(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return $arguments->getReturn();
        }

        $returnValue = $arguments->getReturn();
        $parameters = $arguments->getArgs();
        $additional = $parameters[2];

        if (array_key_exists('liveShopping', $additional)) {
            /**@var $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping*/
            $liveShopping = $additional['liveShopping'];
            $now = new DateTime();
            $returnValue['swagLiveShoppingId'] = $liveShopping->getId();
            $returnValue['swagLiveShoppingTimestamp'] = $now->format('Y-m-d h:i:s');
            $arguments->setReturn($returnValue);
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
            SELECT s_order_details.articleordernumber, s_articles_details.articleID, s_articles_details.id as variantId
            FROM s_order_details
                INNER JOIN s_articles_details
                    ON s_order_details.articleordernumber = s_articles_details.ordernumber
            WHERE s_order_details.ordernumber = ?
        ";
        $articles = Shopware()->Db()->fetchCol($sql, array($orderNumber));
        foreach($articles as $articleData) {

            if (!empty($articleData['articleID'])) {
                $liveShopping = $this->getLiveShoppingComponent()->getActiveLiveShoppingForArticle(
                    $articleData['articleID']
                );

                if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
                    continue;
                }

                if (!empty($articleData['variantId'])) {
                    $variant = $this->getEntityManager()->find('Shopware\Models\Article\Detail', $articleData['variantId']);
                    if ($variant instanceof \Shopware\Models\Article\Detail && !($this->getLiveShoppingComponent()->isVariantAllowed($liveShopping, $variant))) {
                        continue;
                    }
                }

                try {
                    $this->getLiveShoppingComponent()->decreaseLiveShoppingStock($liveShopping);
                } catch (Exception $e) {
                }
            }
        }
    }

    /**
     * Enlight hook listener of the Shopware_Components_LiveShoppingBasket::getVariantCreateData function.
     *
     * This function is used to manipluate the article prices when adding the article to the shopware basket.
     *
     * @param Enlight_Hook_HookArgs $arguments
     * @return mixed
     */
    public function onGetVariantCreateData(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return $arguments->getReturn();
        }

        $returnValue = $arguments->getReturn();
        $parameters = $arguments->getArgs();
        $additional = $parameters[2];
        /**@var $variant \Shopware\Models\Article\Detail*/
        $variant = $parameters[0];

        if (array_key_exists('liveShopping', $additional)) {
            /**@var $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping*/
            $liveShopping = $additional['liveShopping'];

            //the current price is set to gross price if the current customer group is defined as "display gross price in frontend"
            $price = $liveShopping->getCurrentPrice();
            $netPrice = $price;

            $taxRate = $this->getLiveShoppingComponent()->getCurrentTaxRate(
                $variant->getArticle()
            );

            if (!$this->getLiveShoppingComponent()->displayNetPrices()) {
                //in this case "$price" is a gross price.
                $netPrice = $price / (100 + $taxRate) * 100;
            }

            $returnValue['netPrice'] = $netPrice;
            $returnValue['price'] = $price;
            $returnValue['taxRate'] = $taxRate;

            $arguments->setReturn($returnValue);
        }
    }

    /**
     * Basket hook after.
     *
     * This function is a hook listener function of the sBasket::sUpdateArticle function.
     * The original function updates the basket item data. We have to prevent the default
     * process if the current basket item is an live shopping article.
     * The function would set the original article price and that is not correct.
     *
     * @param Enlight_Hook_HookArgs $arguments
     * @return mixed
     */
    public function onUpdateArticle(Enlight_Hook_HookArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return $arguments->getReturn();
        }

        $id = $arguments->get('id');

        /**@var $basketItem \Shopware\Models\Order\Basket*/
        $basketItem = $this->getLiveShoppingBasketComponent()->getItem(
            $id, \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
        );

        if (!$basketItem instanceof \Shopware\Models\Order\Basket) {
            return;
        }

        if (!$basketItem->getAttribute() instanceof \Shopware\Models\Attribute\OrderBasket) {
            return;
        }

        $articleNumber = $basketItem->getOrderNumber();
        $variant = $this->getArticleDetailRepository()->findOneBy(
            array('number' => $articleNumber)
        );

        if (!$variant instanceof \Shopware\Models\Article\Detail) {
            return;
        }

        $article = $variant->getArticle();
        if (!$article instanceof \Shopware\Models\Article\Article) {
            return;
        }

        $liveShopping = $this->getLiveShoppingComponent()->getActiveLiveShoppingForArticle(
            $article->getId()
        );

        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            $this->removeLiveShoppingBasketFlag($basketItem);
            return;
        }

        if (!$this->getLiveShoppingComponent()->isVariantAllowed($liveShopping, $variant)) {
            $this->removeLiveShoppingBasketFlag($basketItem);
            return;
        }

        //the current price is set to gross price if the current customer group is defined as "display gross price in frontend"
        $price = $liveShopping->getCurrentPrice();
        $netPrice = $price;

        $taxRate = $this->getLiveShoppingComponent()->getCurrentTaxRate(
            $article
        );

        if (!$this->getLiveShoppingComponent()->displayNetPrices()) {
            //in this case "$price" is a gross price.
            $netPrice = $price / (100 + $taxRate) * 100;
        }

        $arguments->set('id', null);

        $this->getEntityManager()->clear();

        /**@var $basket \Shopware\Models\Order\Basket*/
        $basket = $this->getEntityManager()->find('Shopware\Models\Order\Basket', $basketItem->getId());

        /**@var $attribute \Shopware\Models\Attribute\OrderBasket*/
        $attribute = $this->getEntityManager()->find('Shopware\Models\Attribute\OrderBasket', $basketItem->getAttribute()->getId());
        if ($attribute->getSwagLiveShoppingId() === null) {
            $attribute->setSwagLiveShoppingId($liveShopping->getId());
            $attribute->setSwagLiveShoppingTimestamp(new \DateTime());
        }

        $basket->setCurrencyFactor(
            $this->getLiveShoppingComponent()->getCurrentCurrencyFactor()
        );

        $basket->setTaxRate($taxRate);

        if ($this->getLiveShoppingComponent()->useNetPriceInBasket()) {
            $basket->setPrice($netPrice);
        } else {
            $basket->setPrice($price);
        }

        $basket->setNetPrice($netPrice);
        $this->getEntityManager()->flush();
    }

    /**
     * Helper function to remove the live shopping flags from the passed basket item.
     * @param $basket \Shopware\Models\Order\Basket
     */
    protected function removeLiveShoppingBasketFlag($basket)
    {
        if (!$basket instanceof \Shopware\Models\Order\Basket) {
            return;
        }
        if (!$basket->getAttribute() instanceof \Shopware\Models\Attribute\OrderBasket) {
            return;
        }

        $this->getEntityManager()->clear();

        /**@var $attribute \Shopware\Models\Attribute\OrderBasket*/
        $attribute = $this->getEntityManager()->find('Shopware\Models\Attribute\OrderBasket', $basket->getAttribute()->getId());

        $attribute->setSwagLiveShoppingId(null);
        $attribute->setSwagLiveShoppingTimestamp(null);

        $this->getEntityManager()->flush();
    }

    /**
     * Enlight event listener function of Shopware_Controllers_Frontend_Checkout::postDispatch function.
     *
     * Used to extends the checkout tempalte to add the live shopping flag in the basket rows.
     * @param Enlight_Event_EventArgs $arguments
     *
     * @return mixed
     */
    public function onFrontendCheckoutPostDispatch(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return true;
        }

        /**@var $subject Enlight_Controller_Action*/
        $subject = $arguments->getSubject();

        /**@var $request Enlight_Controller_Request_RequestHttp*/
        $request = $subject->Request();

        /**@var $response Enlight_Controller_Response_ResponseHttp*/
        $response = $subject->Response();

        /**@var $view Enlight_View_Default*/
        $view = $subject->View();

        if (!$request->isDispatched()
                || $response->isException()
                || !$view->hasTemplate()) {
            return;
        }

        $basket = $view->getAssign('sBasket');

        if (!empty($basket['content'])) {
            foreach($basket['content'] as &$item) {
                if (empty($item['id'])) {
                    continue;
                }
                $model = $this->getLiveShoppingBasketComponent()->getItem(
                    $item['id'],
                    \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
                );

                /**@var $model \Shopware\Models\Order\Basket*/
                if ($model instanceof \Shopware\Models\Order\Basket && ($model->getAttribute() instanceof \Shopware\Models\Attribute\OrderBasket)) {
                    $item['swagLiveShoppingId'] = $model->getAttribute()->getSwagLiveShoppingId();
                }
            }
        }

        $view->assign('sBasket', $basket);

        if ($request->getActionName() === 'cart') {
            $view->extendsTemplate('frontend/checkout/live_shopping_cart_item.tpl');
        } else if ($request->getActionName() === 'confirm') {
            $view->extendsTemplate('frontend/checkout/live_shopping_confirm_item.tpl');
        } else if ($request->getActionName() === 'finish') {
            $view->extendsTemplate('frontend/checkout/live_shopping_finish_item.tpl');
        }
    }

    /**
     * Enlight event listener function.
     * Fired when the customer enters the checkout section.
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return mixed
     */
    public function onCheckoutPreDispatch(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->checkLicense(false)) {
            return $arguments->getReturn();
        }

        /**@var $subject Enlight_Controller_Action*/
        $subject = $arguments->getSubject();

        /**@var $request Enlight_Controller_Request_RequestHttp*/
        $request = $subject->Request();

        /**@var $view Enlight_View_Default*/
        $view = $subject->View();

        $liveShoppings = $this->getLiveShoppingComponent()->getBasketLiveShoppingArticles();

        if (empty($liveShoppings)) {
            return;
        }

        $validations = array();

        /**@var $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping*/
        foreach($liveShoppings as $basketId => $liveShopping) {
            $validation = $this->getLiveShoppingComponent()->validateLiveShopping($liveShopping);

            $timeValidation = $this->getLiveShoppingComponent()->isLiveShoppingDateActive($liveShopping);

            if ($validation === true && $timeValidation === true) {
                continue;
            }

            $validation['basketId'] = $basketId;
            if (!empty($validation)) {
                $validations[] = $validation;
            } else if ($timeValidation === false) {
                $validations[] = array(
                    'outOfDate' => true,
                    'basketId' => $basketId,
                    'article' => $this->getLiveShoppingComponent()->getLiveShoppingArticleName($liveShopping)
                );
            }
        }

        if (empty($validations)) {
            return;
        }
        if ($request->getActionName() === 'finish') {
            $subject->forward('confirm', 'checkout', 'frontend', array('sLiveShoppingValidation' => $validations) );
        } else {
            $view->extendsTemplate('frontend/checkout/live_shopping_errors.tpl');
            $view->assign('sLiveShoppingValidation', $validations);
        }
    }



    /********************/
    /* HELPER FUNCTIONS */

    /**
     * Renames a table.
     *
     * Internal helper function to rename safety
     *
     * @param $from
     * @param $to
     * @return array
     */
    private function renameTable($from, $to)
    {
        if ($this->tableExist($from))  {
            try {
                $this->getSchemaManager()->renameTable($from, $to);
                return array('success' => true);
            } catch (Exception $e) {
                return array('success' => false, 'message' => $e->getMessage());
            }
        }
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
        try {
            return $this->getSchemaManager()->tablesExist($tableName);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Checks if a table column exists.
     *
     * Returns true if the table column exist.
     *
     * @param $tableName
     * @param $columnName
     *
     * @return bool
     */
    private function columnExist($tableName, $columnName)
    {
        try {
            $columns = $this->getSchemaManager()->listTableColumns($tableName);
            return array_key_exists($columnName, $columns);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Removes a database table.
     *
     * Internal helper function to remove a table safety.
     *
     * @param $name
     */
    private function dropTable($name)
    {
        if (!$this->tableExist($name)) {
            return;
        }
        $this->getSchemaManager()->dropTable($name);
    }

    /**
     * Helper function to get the selected variant for the passed article
     * and configuration array.
     *
     * @param $article \Shopware\Models\Article\Article
     * @param $configuration
     * @return \Shopware\Models\Article\Detail
     */
    protected function getSelectedVariantForArticle($article, $configuration)
    {
        if (empty($configuration)) {
            return $article->getMainDetail();
        }

        $builder = $this->getLiveShoppingRepository()->getVariantForArticleConfigurationQueryBuilder($article, $configuration);
        $variants = $builder->getQuery()->getResult();

        if (empty($variants) || !($variants[0] instanceof \Shopware\Models\Article\Detail) ) {
            return $article->getMainDetail();
        } else {
            return $variants[0];
        }
    }

}

