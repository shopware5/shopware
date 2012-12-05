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

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * Global Shopware LiveShopping component
 *
 * Can be called over Shopware()->LiveShopping()
 * Used for all LiveShopping resource specified processes.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagLiveShopping\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_LiveShopping extends Enlight_Class
{
    /**
     * Constant for the live shopping type "standard"
     *
     * The live shopping article contains a fix price definition. The article is selled for
     * the fix defined price while the live shopping article is active and in the date range.
     */
    const NORMAL_TYPE = 1;

    /**
     * Constant for the live shopping type "discount per minute"
     *
     * The live shopping article contains a definition for the start and end price.
     * Based on the valid from and valid to date, the article price will be descrease per minute.
     */
    const DISCOUNT_TYPE = 2;

    /**
     * Constant for the live shopping type "surcharge per minute"
     *
     * The live shopping article contains a definition for the start and end price.
     * Based on the valid from and valid to date, the article price will be increase per minute.
     */
    const SURCHARGE_TYPE = 3;

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
     * Repository of the customergroup model.
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $customerGroupRepository = null;

    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\CustomModels\LiveShopping\Repository
     */
    protected $liveShoppingRepository = null;

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
            $this->system = Shopware()->System();
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
     * The getLiveShoppingRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
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
     * Getter function of the customerGroupRepository property.
     *
     * The getCustomerGroupRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getCustomerGroupRepository()
    {
        if ($this->customerGroupRepository === null) {
            $this->customerGroupRepository = $this->getEntityManager()->getRepository(
                'Shopware\Models\Customer\Group'
            );
        }
        return $this->customerGroupRepository;
    }

    /**
     * Internal helper function to get the current customer group for the customer
     * or the default customer group of the current shop.
     * @return \Shopware\Models\Customer\Group
     */
    public function getCurrentCustomerGroup()
    {
        $customerGroupData = $this->getSession()->sUserGroupData;

        $customerGroup = null;

        /**@var $customerGroup \Shopware\Models\Customer\Group*/
        //check if the customer logged in and get the customer group model for the logged in customer
        if (!empty($customerGroupData['groupkey'])) {
            $customerGroup = $this->getCustomerGroupRepository()->findOneBy(array(
                'key' => $customerGroupData['groupkey']
            ));
        }

        //if no customer group given, get the default customer group.
        if (!$customerGroup instanceof Shopware\Models\Customer\Group) {
            $customerGroup = $this->getShop()->getCustomerGroup();
        }

        return $customerGroup;
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
            $this->snippetNamespace = Shopware()->Snippets()->getNamespace('backend/live_shopping');
        }
        return $this->snippetNamespace;
    }

    /**
     * Returns all active live shopping articles
     *
     * This function is used for the frontend listing and article detail page.
     * The function returns all defined live shopping articles for the passed article id.
     * The returned live shopping definitions contains already the current price for the
     * current frontend session.
     *
     * @param $articleId
     *
     * @return \Shopware\CustomModels\LiveShopping\LiveShopping
     */
    public function getActiveLiveShoppingForArticle($articleId)
    {
        $builder = $this->getLiveShoppingRepository()->getActiveLiveShoppingForArticleQueryBuilder(
            $articleId,
            $this->getCurrentCustomerGroup(),
            $this->getShop()
        );

        $builder->setFirstResult(0)
                ->setMaxResults(1);

        $query = $builder->getQuery();
        
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        $liveShopping = $paginator->getIterator()->current();

        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return array();
        }

        $now = new \DateTime();

        /**@var $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping*/
        $liveShopping = $this->getLiveShoppingWithGrossPrices($liveShopping);

        $currentPrice = $this->getLiveShoppingPriceForDate($liveShopping, $now);

        if ($currentPrice === false) {
            return false;
        }

        $liveShopping->setCurrentPrice(
            $currentPrice
        );


        return $liveShopping;
    }

    /**
     * Returns a single active live shopping article.
     *
     * This function is used to refresh the live shopping data on the article
     * detail page if one minute remained.
     *
     * @param $liveShoppingId
     *
     * @return bool|Shopware\CustomModels\LiveShopping\LiveShopping
     */
    public function getActiveLiveShoppingById($liveShoppingId)
    {
        $builder = $this->getLiveShoppingRepository()->getActiveLiveShoppingByIdQueryBuilder(
            (int) $liveShoppingId,
            $this->getCurrentCustomerGroup(),
            $this->getShop()
        );

        $builder->setFirstResult(0)
                ->setMaxResults(1);

        $liveShopping = $builder->getQuery()->getOneOrNullResult(
            \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
        );

        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return false;
        }

        $now = new \DateTime();

        /**@var $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping*/
        $liveShopping = $this->getLiveShoppingWithGrossPrices($liveShopping);

        $currentPrice = $this->getLiveShoppingPriceForDate($liveShopping, $now);

        if ($currentPrice === false) {
            return false;
        }

        $liveShopping->setCurrentPrice(
            $currentPrice
        );


        return $liveShopping;
    }

    /**
     * Array mapping function.
     *
     * This function is used to convert the passed LiveShopping model into array data.
     *
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     * @return array
     */
    public function getLiveShoppingArrayData($liveShopping)
    {
        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return array();
        }

        /**@var $price \Shopware\CustomModels\LiveShopping\Price*/
        $price = $liveShopping->getUpdatedPrices()->first();

        return array(
            'id' => $liveShopping->getId(),
            'name' => $liveShopping->getName(),
            'type' => $liveShopping->getType(),
            'number' => $liveShopping->getNumber(),
            'remaining' => $this->getDateInvertalArrayData($liveShopping->getRemainingDateInterval()),
            'expired' => $this->getDateInvertalArrayData($liveShopping->getExpiredDateInterval()),
            'startPrice' => $price->getPrice(),
            'endPrice' => $price->getEndPrice(),
            'currentPrice' => $liveShopping->getCurrentPrice(),
            'percentage' => 100 - $liveShopping->getCurrentPrice() * 100 / $price->getPrice(),
            'perMinute' => $liveShopping->getPerMinuteValue(),
            'limited' => $liveShopping->getLimited(),
            'quantity' => $liveShopping->getQuantity(),
            'sells' => $liveShopping->getSells(),
            'validTo' => $liveShopping->getValidTo()->getTimestamp()
        );
    }

    /**
     * Array mapping function.
     *
     * Returns the passed date interval object as array data.
     *
     * @param $dateInterval DateInterval
     * @return array
     */
    public function getDateInvertalArrayData($dateInterval)
    {
        return array(
            'days' => sprintf("%02d",$dateInterval->days),
            'hours' => sprintf("%02d",$dateInterval->h),
            'minutes' => sprintf("%02d",$dateInterval->i),
            'seconds' => sprintf("%02d",$dateInterval->s)
        );
    }

    /**
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     * @param $date \DateTime
     *
     * @return mixed
     */
    public function getLiveShoppingPriceForDate($liveShopping, $date)
    {
        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return false;
        }

        if (!$date instanceof \DateTime) {
            return false;
        }

        if (!$liveShopping->getUpdatedPrices()->first() instanceof \Shopware\CustomModels\LiveShopping\Price) {
            return false;
        }

        /**@var $price \Shopware\CustomModels\LiveShopping\Price*/
        $price = $liveShopping->getUpdatedPrices()->first();

        $perMinute = $liveShopping->getPerMinuteValue();

        $expiredMinutes = $liveShopping->getTotalMinutesOfDateInterval(
            $liveShopping->getExpiredDateInterval()
        );

        $expiredAmount = $expiredMinutes * $perMinute;

        switch($liveShopping->getType()) {
            case self::NORMAL_TYPE:
                $price = $price->getEndPrice();
                break;
            case self::DISCOUNT_TYPE:
                $price = $price->getPrice() - $expiredAmount;
                break;
            case self::SURCHARGE_TYPE:
                $price = $price->getPrice() + $expiredAmount;
                break;
            default:
                $price = $price->getEndPrice();
                break;
        }

        return $price;
    }

    /**
     * Formats the live shopping prices.
     *
     * This function iterates the prices of the passed live shopping
     * object and sets the right values for gross and net prices.
     *
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     * @return \Shopware\CustomModels\LiveShopping\LiveShopping
     */
    public function getLiveShoppingWithGrossPrices($liveShopping)
    {
        $prices = array();

        $taxRate = $this->getCurrentTaxRate(
            $liveShopping->getArticle()
        );

        $currencyFactor = $this->getCurrentCurrencyFactor();

        /**@var $price \Shopware\CustomModels\LiveShopping\Price*/
        foreach($liveShopping->getPrices() as $price) {
            if (!$price->getCustomerGroup() instanceof \Shopware\Models\Customer\Group) {
                continue;
            }
            $price->setPrice(
                floatval($price->getPrice()) * floatval($currencyFactor)
            );

            if (!$this->displayNetPrices()) {
                $price->setPrice(
                    $price->getPrice() / 100 * (100 + $taxRate)
                );
                $price->setEndPrice(
                    $price->getEndPrice() / 100 * (100 + $taxRate)
                );
            }
            $prices[] = $price;
        }

        $liveShopping->getUpdatedPrices()->clear();
        foreach($prices as $price) {
            $liveShopping->getUpdatedPrices()->add($price);
        }
        $this->getEntityManager()->clear();

        return $liveShopping;
    }

    /**
     * Helper function to get the current tax rate.
     * @param $article \Shopware\Models\Article\Article
     *
     * @return mixed
     */
    public function getCurrentTaxRate($article)
    {
        $taxRate = $this->getArticleModule()->getTaxRateByConditions(
            $article->getTax()->getId()
        );
        if ($taxRate === false || empty($taxRate)) {
            $taxRate = $article->getTax()->getTax();
        }
        return $taxRate;
    }

    /**
     * Helper function to get the current currency factor for the store front.
     * @return int
     */
    public function getCurrentCurrencyFactor()
    {
        $currencyFactor = $this->getSystem()->sCurrency["factor"];
        if (empty($currencyFactor)) {
            $currencyFactor = 1;
        }
        return $currencyFactor;
    }


    /**
     * Helper function to check if the current customer should see net prices for articles.
     *
     * @return bool
     */
    public function displayNetPrices()
    {
        return $this->isCustomerGroupNet();
    }

    /**
     * Helper function to check if the shopware basket should use gross or net prices
     * for the current logged in customer.
     * @return bool
     */
    public function useNetPriceInBasket()
    {
        if (!$this->isCustomerGroupNet() && !$this->isShippingCountryNet()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Helper function to check if the selected country would be delivered with net prices.
     *
     * @return bool|int
     */
    public function isShippingCountryNet()
    {
        if (empty($this->getSession()->sUserGroupData['id'])) {
            return false;
        }
        
        if (empty($this->getSession()->sCountry)) {
            return false;
        }

        /**@var $country \Shopware\Models\Country\Country */
        $country = $this->getEntityManager()->find('Shopware\Models\Country\Country', $this->getSession()->sCountry);

        return (bool) $country->getTaxFree();
    }

    /**
     * Helper function to check if the current customer would see net or gross prices.
     *
     * @return bool
     */
    public function isCustomerGroupNet()
    {
        return !$this->getCurrentCustomerGroup()->getTax();
    }

    /**
     * Global interface to validate a single live shopping article.
     *
     * This function is used to validate live shopping articles for
     * the shopware basket.
     *
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     * @return array|bool
     */
    public function validateLiveShopping($liveShopping)
    {
        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return array('noLiveShoppingDetected' => true);
        }

        if (!$liveShopping->getActive()) {
            return array('noMoreActive' => true, 'article' => $this->getLiveShoppingArticleName($liveShopping));
        }

        if (!$this->isCustomerGroupAllowed($liveShopping, $this->getCurrentCustomerGroup())) {
            return array('notForCurrentCustomerGroup' => true, 'article' => $this->getLiveShoppingArticleName($liveShopping));
        }

        if (!$this->hasLiveShoppingPriceForCustomerGroup($liveShopping, $this->getCurrentCustomerGroup())) {
            return array('notForCurrentCustomerGroup' => true, 'article' => $this->getLiveShoppingArticleName($liveShopping));
        }

        if ($liveShopping->getLimited() && $liveShopping->getQuantity() <= 0) {
            return array('noStock' => true, 'article' => $this->getLiveShoppingArticleName($liveShopping));
        }

        if (!$this->isShopAllowed($liveShopping, $this->getShop())) {
            return array('notForShop' => true, 'article' => $this->getLiveShoppingArticleName($liveShopping));
        }

        return true;
    }

    /**
     * Helper function to check if the passed customer group has a defined price.
     *
     * This function is used to validate the basket live shopping articles for the current customer group.
     * It returns false if the passed live shopping article has no price for the passed customer group.
     *
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     * @param $customerGroup \Shopware\Models\Customer\Group
     * @return bool
     */
    public function hasLiveShoppingPriceForCustomerGroup($liveShopping, $customerGroup)
    {
        /**@var $price \Shopware\CustomModels\LiveShopping\Price*/
        foreach($liveShopping->getPrices() as $price) {
            if ($price->getCustomerGroup()->getId() === $customerGroup->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper function to validate shop limitations.
     *
     * This function is used to check if the passed live shopping article
     * contains the passed shop object.
     *
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     * @param $shop \Shopware\Models\Shop\Shop
     * @return bool
     */
    protected function isShopAllowed($liveShopping, $shop)
    {
        foreach($liveShopping->getShops() as $liveShoppingShop) {
            if ($liveShoppingShop->getId() === $shop->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     * @return string
     */
    public function getLiveShoppingArticleName($liveShopping)
    {
        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return '';
        }

        $article = $liveShopping->getArticle();

        $translation = $this->getArticleModule()->sGetArticleNameByOrderNumber($article->getId(), true);

        if (!empty($translation['articleName'])) {
            return $translation['articleName'];
        } else {
            return $article->getName();
        }
    }

    /**
     * @param $liveShopping
     * @return bool
     */
    public function isLiveShoppingDateActive($liveShopping)
    {
        if (!$liveShopping instanceof \Shopware\CustomModels\LiveShopping\LiveShopping) {
            return false;
        }

        $now = new \DateTime();


        if ($liveShopping->getValidFrom() > $now) {
            return false;
        }

        if ($liveShopping->getValidTo() < $now) {
            return false;
        }

        return true;
    }

    /**
     * Returns all basket live shoppings.
     *
     * This function is used to get all live shopping articles
     * which placed in the shopware basket for the current
     * frontend session.
     *
     * @return array
     */
    public function getBasketLiveShoppingArticles()
    {
        $builder = $this->getLiveShoppingRepository()->getBasketAttribtuesWithLiveShoppingFlagQueryBuilder(
            $this->getSession()
        );

        $basket = $builder->getQuery()->getArrayResult();

        $liveShoppings = array();
        foreach($basket as $item) {
            if (!empty($item['swagLiveShoppingId'])) {
                $liveShoppings[$item['id']] = $this->getLiveShoppingRepository()->find($item['swagLiveShoppingId']);
            }
        }

        return $liveShoppings;
    }

    /**
     * Helper function to descrease the stock value of the passed live shopping article.
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     */
    public function decreaseLiveShoppingStock($liveShopping)
    {
        $sql= "
            UPDATE s_articles_lives
            SET max_quantity = max_quantity - 1
            WHERE s_articles_lives.id = ?
        ";
        $this->getDatabase()->query($sql, array($liveShopping->getId()));
    }

    /**
     * Helper function to check if the passed customer group is allowed for the passed live shopping article.
     *
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     * @param $customerGroup \Shopware\Models\Customer\Group
     *
     * @return bool
     */
    protected function isCustomerGroupAllowed($liveShopping, $customerGroup)
    {
        /**@var $customerGroupBundle \Shopware\Models\Customer\Group*/
        foreach($liveShopping->getCustomerGroups() as $customerGroupBundle) {
            if ($customerGroup->getKey() === $customerGroupBundle->getKey()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper function to check if the passed article variant is allowed for the passed live shopping article.
     *
     * @param $liveShopping \Shopware\CustomModels\LiveShopping\LiveShopping
     * @param $variant \Shopware\Models\Article\Detail
     * @return bool
     */
    public function isVariantAllowed($liveShopping, $variant)
    {
        if ($liveShopping->getLimitedVariants()->count() === 0) {
            return true;
        }

        foreach($liveShopping->getLimitedVariants() as $limitedVariant) {
            if (!($limitedVariant instanceof \Shopware\Models\Article\Detail)) {
                continue;
            }
            if ($limitedVariant->getId() === $variant->getId()) {
                return true;
            }
        }
        return false;
    }


}
