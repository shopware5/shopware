<?php
/**
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
 * Shopware frontend controller of the SwagLiveShopping plugin.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagLiveShopping\Controllers\Frontend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_LiveShopping extends Enlight_Controller_Action
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
     * Repository of the SwagLiveShopping plugin.
     *
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project
     * to get access on a model repository.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $liveShoppingRepository = null;

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
     * View renderer of enlight
     *
     * Used to disable the automatically template loading for ajax request.
     *
     * @var Enlight_Controller_Plugins_ViewRenderer_Bootstrap
     */
    protected $viewRendererPlugin = null;

    /**
     * Getter of the viewRendererPlugin property.
     *
     * Used to disable the automatically template loading for ajax request.
     *
     * @return null
     */
    public function getViewRendererPlugin()
    {
        if ($this->viewRendererPlugin === null) {
            $this->viewRendererPlugin = Shopware()->Plugins()->Controller()->ViewRenderer();
        }
        return $this->viewRendererPlugin;
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
            $this->liveShoppingComponent = Shopware()->LiveShopping();
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
    		$this->entityManager = Shopware()->Models();
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
    		$this->shopwareBoostrap = Shopware()->Bootstrap();
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
    		$this->snippetNamespace = Shopware()->Snippets()->getNamespace('frontend/live_shopping');
    	}
    	return $this->snippetNamespace;
    }

    /**
     * Getter of the liveShoppingRepository property.
     *
     * The getLiveShoppingRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     *
     * @return null|Shopware\Components\Model\ModelRepository
     */
    public function getLiveShoppingRepository()
    {
        if ($this->liveShoppingRepository === null) {
            $this->liveShoppingRepository = Shopware()->Models()->getRepository('Shopware\CustomModels\LiveShopping\LiveShopping');
        }
        return $this->liveShoppingRepository;
    }


    /**
     * Enlight controller action function.
     *
     * This function is used to refresh the live shopping data on the
     * article detail page.
     * The function expects the live shopping id of the displayed live shopping
     * article.
     */
    public function getLiveShoppingDataAction()
    {
        $this->getViewRendererPlugin()->setNoRender();

        $liveShoppingId = (int) $this->Request()->getParam('liveShoppingId', null);

        if ($liveShoppingId === null) {
            return array();
        }

        $liveShopping = $this->getLiveShoppingComponent()->getActiveLiveShoppingById($liveShoppingId);

        $data = $this->getLiveShoppingComponent()->getLiveShoppingArrayData($liveShopping);

        echo Zend_Json::encode(array(
            'success'=> !empty($data),
            'data' => $data
        ));
    }
}

