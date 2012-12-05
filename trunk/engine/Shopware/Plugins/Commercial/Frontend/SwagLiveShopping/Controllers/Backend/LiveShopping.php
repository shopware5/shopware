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
 * Backend Controller of the SwagLiveShopping Plugin.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagLiveShopping\Controllers\Backend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_LiveShopping extends Shopware_Controllers_Backend_ExtJs
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
     * Repository of the SwagLiveShopping plugin.
     *
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project
     * to get access on a model repository.
     *
     * @var \Shopware\CustomModels\LiveShopping\Repository
     */
    protected $liveShoppingRepository = null;

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
     * Getter of the liveShoppingRepository property.
     *
     * The getLiveShoppingRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     *
     * @return \Shopware\CustomModels\LiveShopping\Repository
     */
    public function getLiveShoppingRepository()
    {
        if ($this->liveShoppingRepository === null) {
            $this->liveShoppingRepository = Shopware()->Models()->getRepository('Shopware\CustomModels\LiveShopping\LiveShopping');
        }
        return $this->liveShoppingRepository;
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
     * Pre dispatch event of the bundle backend module.
     */
    public function preDispatch()
    {
        if(!in_array($this->Request()->getActionName(), array('index', 'load', 'validateNumber'))) {
            $this->Front()->Plugins()->Json()->setRenderer();
        }
    }

    /**
     * Global interface to create a new LiveShopping.
     *
     * Creates a new LiveShopping record. The function can handles only one data set.
     * The function expects the new record data directly in the request object:
     * <pre>
     *
     * </pre>
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @return boolean The function assigns the result to the controller view.
     */
    public function createLiveShoppingAction()
    {
        $this->View()->assign(
            $this->saveLiveShopping(
                $this->Request()->getParams()
            )
        );
        return true;
    }

    /**
     * Global interface to update an existing LiveShopping record.
     *
     * Updates an existing LiveShopping record. The function can handles only one data set.
     * The function expects the updated record data directly in the request object:
     * <pre>
     *
     * </pre>
     * Property which aren't passed in the request object, won't be updated.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * @return boolean The function assigns the result to the controller view.
     */
    public function updateLiveShoppingAction()
    {
        $this->View()->assign(
            $this->saveLiveShopping(
                $this->Request()->getParams()
            )
        );
        return true;
    }

    /**
     * Global interface to delete an existing LiveShopping record.
     *
     * Removes an existing LiveShopping record from the shopware database.
     * The function can handles multiple data set.
     * In case that the function has to remove only one record, the function expects the following
     * request parameter structure:
     * <pre>
     *
     * </pre>
     *
     * In case that the function has to remove multiple record, the function expects the following
     * request parameter structure:
     * <pre>
     *
     * </pre>
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @return boolean The function assigns the result to the controller view.
     */
    public function deleteLiveShoppingAction()
    {
        $this->View()->assign(
            $this->deleteLiveShopping(
                $this->Request()->getParam('id')
            )
        );
        return true;
    }

    /**
     * Global interface to get an offset of defined LiveShopping records.
     *
     * The getListAction expects the standard listing parameters directly in the request parameters
     * start, limit, filter and sort.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @return boolean The function assigns the result to the controller view.
     */
    public function getListAction()
    {
        $this->View()->assign(
            $this->getList(
                $this->Request()->getParam('articleId', null),
                $this->Request()->getParam('filter', array()),
                $this->Request()->getParam('sort', array()),
                $this->Request()->getParam('start', null),
                $this->Request()->getParam('limit', null)
            )
        );
        return true;
    }

    /**
     * Global interface to get the whole data for a single LiveShopping record.
     *
     * The getDetailAction expects the LiveShopping id in the request parameters.
     * This function can handles only one data set.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @return bool
     */
    public function getDetailAction()
    {
        $this->View()->assign(
            $this->getDetail(
                $this->Request()->getParam('id', null)
            )
        );
        return true;
    }

    /**
     * Global interface which used for the bundle backend extension of the article module.
     * Returns an offset of article variants for the passed article id.
     * @return bool
     */
    public function getVariantsAction() {
        $this->View()->assign(
            $this->getVariants(
                $this->Request()->getParam('articleId'),
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20)
            )
        );
        return true;
    }

    /**
     * Internal helper function which returns an offset of variants for the passed article id.
     *
     * @param $articleId int
     * @param $offset int
     * @param $limit int
     *
     * @return array
     */
    protected function getVariants($articleId, $offset, $limit)
    {
        $builder = $this->getLiveShoppingRepository()->getArticleVariantsQueryBuilder(
            $articleId, $offset, $limit
        );

        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        return array(
            'success' => true,
            'total' => $paginator->count(),
            'data' => $paginator->getIterator()->getArrayCopy(),
        );
    }

    /**
     * Internal helper function to get an offset of defined LiveShopping records.
     *
     * The getListAction expects the standard listing parameters directly in the request parameters start, limit, filter and sort.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @param       $articleId
     * @param array $filter     An array of listing filters to filter the result set
     * @param array $sort       An array of listing order by condition to sort the result set
     * @param int   $offset     An offset for a paginated listing.
     * @param int   $limit      An limit for a paginated listing.
     * @param int   $hydrationMode
     *
     * @return array Result of the listing query or the exception code and message
     */
    protected function getList($articleId, $filter, $sort, $offset, $limit, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        try {
            /**@var $query \Doctrine\ORM\Query*/
            $builder = $this->getLiveShoppingRepository()->getListQueryBuilder($articleId, $filter, $sort, $offset, $limit);

            $query = $builder->getQuery();

            $query->setHydrationMode($hydrationMode);

            $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

            return array(
                'success' => true,
                'total' => $paginator->count(),
                'data' => $paginator->getIterator()->getArrayCopy()
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Internal function to save a single LiveShopping record.
     *
     * Used from the createLiveShoppingAction and updateLiveShoppingAction interface.
     * Contains the whole source code logic to save a single LiveShopping record.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @param  array $data The whole liveShopping data as array
     * @return array       Result of the delete process
     */
    protected function saveLiveShopping($data)
    {
        try {
            /**@var $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping*/
            if (empty($data['id'])) {
                $model = new \Shopware\CustomModels\LiveShopping\LiveShopping;
            } else {
                $model = Shopware()->Models()->find('Shopware\CustomModels\LiveShopping\LiveShopping', $data['id']);
            }

            if (!$model instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
                return array('success' => false, 'message' => "LiveShopping record can't created or find");
            }

            $data = $this->prepareLiveShoppingData($data, $model);
            $model->fromArray($data);

            Shopware()->Models()->persist($model);
            Shopware()->Models()->flush();

            $data = $this->getDetail($model->getId());

            return array(
                'success' => true,
                'data' => $data['data']
            );

        } catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Internal helper function to prepare the associated data of a single liveShopping resource.
     *
     * @param array $data
     * @param       $model
     *
     * @return array $data
     */
    protected function prepareLiveShoppingData($data, $model)
    {
        /**@var $article \Shopware\Models\Article\Article*/
        $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $data['articleId']);
        $data['article'] = $article;

        $data = $this->prepareLiveShoppingTimeFields($data, $model);
        $data['customerGroups'] = $this->prepareLiveShoppingCustomerGroups($data, $model);
        $data['limitedVariants'] = $this->prepareLiveShoppingLimitedVariants($data, $model);
        $data['prices'] = $this->prepareLiveShoppingPrices($data, $model, $article);
        $data['shops'] = $this->prepareLiveShoppingShops($data, $model);

        return $data;
    }

    /**
     * Helper function to prepare the live shopping time fields.
     *
     * This function is used to convert the passed ExtJs dates into valid doctrine
     * date values.
     *
     * @param $data
     * @param $model
     *
     * @return array
     */
    protected function prepareLiveShoppingTimeFields($data, $model)
    {
        if (!empty($data['id'])) {
            unset($data['created']);
        } else {
            $data['created'] = new \DateTime();
        }

        if (!empty($data['validFrom'])) {
            $validFrom = new \DateTime($data['validFrom']);
            $validFromTime = explode(':', $data['validFromTime']);
            $validFrom->setTime($validFromTime[0], $validFromTime[1], 0);
            $data['validFrom'] = $validFrom;
        }

        if (!empty($data['validTo'])) {
            $validTo = new \DateTime($data['validTo']);
            $validToTime = explode(':', $data['validToTime']);
            $validTo->setTime($validToTime[0], $validToTime[1], 0);
            $data['validTo'] = $validTo;
        }
        return $data;
    }

    /**
     * Helper function to prepare the live shopping customer groups.
     *
     * This function is used to convert the passed ExtJs customer group data into valid
     * customer group models for doctrine.
     *
     * @param $data
     * @param $model
     * @return array
     */
    protected function prepareLiveShoppingCustomerGroups($data, $model)
    {
        $customerGroups = array();
        foreach($data['customerGroups'] as $customerGroupData) {
            if (empty($customerGroupData['id'])) {
                continue;
            }
            $customerGroup = Shopware()->Models()->find('Shopware\Models\Customer\Group', $customerGroupData['id']);
            if (!($customerGroup instanceof \Shopware\Models\Customer\Group)) {
                continue;
            }
            $customerGroups[] = $customerGroup;
        }
        return $customerGroups;
    }

    /**
     * Helper function to prepare the live shopping shops
     *
     * This function is used to convert the passed ExtJs shop data into valid
     * models for doctrine.
     *
     * @param $data
     * @param $model
     * @return array
     */
    protected function prepareLiveShoppingShops($data, $model)
    {
        $shops = array();
        foreach($data['shops'] as $shopData) {
            if (empty($shopData['id'])) {
                continue;
            }
            $shop = Shopware()->Models()->find('Shopware\Models\Shop\Shop', $shopData['id']);
            if (!($shop instanceof \Shopware\Models\Shop\Shop)) {
                continue;
            }
            $shops[] = $shop;
        }
        return $shops;
    }

    /**
     * Helper function to prepare the live shopping limited details
     *
     * This function is used to convert the passed ExtJs article details data into valid
     * models for doctrine.
     *
     * @param $data
     * @param $model
     * @return array
     */
    protected function prepareLiveShoppingLimitedVariants($data, $model)
    {
        $limitedVariants = array();
        foreach($data['limitedVariants'] as $limitedVariantData) {
            if (empty($limitedVariantData['id'])) {
                continue;
            }
            $limitedVariant = Shopware()->Models()->find('Shopware\Models\Article\Detail', $limitedVariantData['id']);
            if (!($limitedVariant instanceof \Shopware\Models\Article\Detail)) {
                continue;
            }
            $limitedVariants[] = $limitedVariant;
        }
        return $limitedVariants;
    }

    /**
     * Helper function to prepare the live shopping prices.
     *
     * This function is used to calculate the inserted gross and net prices.
     *
     * @param $data
     * @param $model
     * @param $article
     */
    protected function prepareLiveShoppingPrices($data, $model, $article)
    {
        foreach($data['prices'] as &$priceData) {
            /**@var $customerGroup \Shopware\Models\Customer\Group*/
            $customerGroup = Shopware()->Models()->find('Shopware\Models\Customer\Group', $priceData['customerGroup'][0]['id']);
            if (!($customerGroup instanceof \Shopware\Models\Customer\Group)) {
                continue;
            }

            $priceData['customerGroup'] = $customerGroup;
            if ($customerGroup->getTaxInput()) {
                $priceData['price'] = $priceData['price'] / (100 + $article->getTax()->getTax()) * 100;
                $priceData['endPrice'] = $priceData['endPrice'] / (100 + $article->getTax()->getTax()) * 100;
            }
        }
        return $data['prices'];
    }

    /**
     * Internal function to delete a LiveShopping record.
     *
     * Used from the deleteLiveShoppingAction interface.
     * Contains the whole source code logic to delete a single LiveShopping record.
     *
     * @param  int   $id Unique identifier for the LiveShopping record.
     * @return array     Result of the delete process
     */
    protected function deleteLiveShopping($id)
    {
        try {
            /**@var $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping*/
            $model = Shopware()->Models()->find('Shopware\CustomModels\LiveShopping\LiveShopping', (int) $id);

            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();

            return array(
                'success' => true,
                'data' => array('id' => $id)
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Internal function to get the whole data for a single LiveShopping record.
     * The LiveShopping record will be identified over the
     * passed id parameter. The second parameter "$hydrationMode" can be use to control the result data type.
     *
     * @param     $id
     * @param int $hydrationMode
     *
     * @return array
     */
    protected function getDetail($id, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        try {
            $builder = $this->getLiveShoppingRepository()
                            ->getDetailQueryBuilder($id);

            $query = $builder->getQuery();

            $query->setHydrationMode($hydrationMode);

            $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

            $records = $paginator->getIterator()->getArrayCopy();

            $liveShopping = $records[0];

            if ($hydrationMode === \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY) {
                $liveShopping['customerGroups'] = $this->getLiveShoppingCustomerGroups($id);
                $liveShopping['shops'] = $this->getLiveShoppingShops($id);
                $liveShopping['validFromTime'] = $this->getTimeOfDateTime($liveShopping['validFrom']);
                $liveShopping['validToTime'] = $this->getTimeOfDateTime($liveShopping['validTo']);
                $liveShopping['prices'] = $this->formatPricesIntoGross($liveShopping['prices'], $liveShopping['article']['tax']);
            }

            return array(
                'success' => true,
                'data' => $liveShopping
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Remove validation.
     *
     * This function is used to validate the inserted live shopping order number
     * The number has to be unique in the whole system.
     */
    public function validateNumberAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        echo $this->validateNumber(
            $this->Request()->getParam('value'),
            $this->Request()->getParam('param')
        );
    }

    /**
     * Internal function which validates the passed order number for live shoppings.
     * Each live shopping order number can only be defined one time.
     * Returns true if the passed number is unique.
     *
     * @param string $number Number to validate
     * @param int $liveShoppingId Optional live shopping id, sent to exclude an existing live shopping
     * @return boolean
     */
    protected function validateNumber($number, $liveShoppingId = null)
    {
        $parameters = array('number' => $number);

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('liveShopping'))
                ->from('Shopware\CustomModels\LiveShopping\LiveShopping', 'liveShopping')
                ->where('liveShopping.number = :number');

        if ($liveShoppingId !== null) {
            $builder->andWhere('liveShopping.id != :liveShoppingId');
            $parameters['liveShoppingId'] = $liveShoppingId;
        }
        $builder->setParameters($parameters);
        $result = $builder->getQuery()->getArrayResult();

        return empty($result);
    }

    /**
     * Helper function to get the live shopping assigned customer groups
     * @param $liveShoppingId
     *
     * @return array
     */
    protected function getLiveShoppingCustomerGroups($liveShoppingId)
    {
        $customerGroups = $this->getLiveShoppingRepository()
                ->getCustomerGroupsQueryBuilder($liveShoppingId)
                ->getQuery()
                ->getArrayResult();

        if (!empty($customerGroups[0]['customerGroups'])) {
            return $customerGroups[0]['customerGroups'];
        } else {
            return array();
        }
    }

    /**
     * Helper function to get the assigned live shopping shops.
     *
     * @param $liveShoppingId
     * @return array
     */
    protected function getLiveShoppingShops($liveShoppingId)
    {
        $shops = $this->getLiveShoppingRepository()
                ->getShopsQueryBuilder($liveShoppingId)
                ->getQuery()
                ->getArrayResult();

        if (!empty($shops[0]['shops'])) {
            return $shops[0]['shops'];
        } else {
            return array();
        }
    }

    /**
     * Helper function to get the hour and minute value of the passed
     * date time object.
     *
     * @param DateTime $dateTime
     *
     * @return string
     */
    protected function getTimeOfDateTime($dateTime) {
        if ($dateTime instanceof \DateTime) {
            return $dateTime->format('H:i');
        } else {
            return '00:00';
        }
    }

    /**
     * Formats the prices for the bundle detail page.
     * The prices has to format from net prices (from the database)
     * to gross prices (for the view).
     *
     * @param $prices
     * @param $tax
     *
     * @return mixed
     */
    public function formatPricesIntoGross($prices, $tax) {
        foreach($prices as &$price) {
            if ($price['customerGroup']['taxInput']) {
                $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                $price['endPrice'] = $price['endPrice'] / 100 * (100 + $tax['tax']);
            }
        }
        return $prices;
    }
}