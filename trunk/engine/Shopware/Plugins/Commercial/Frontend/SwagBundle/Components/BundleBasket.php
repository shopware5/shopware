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
 * The Shopware_Components_Basket class is the basis for the new Shopware shopping basket.
 * All functions in this class are based on the new Shopware 4 technologies.
 * The different return values of the function are available through the class documentation.
 * The Shopware_Components_Basket class includes any processes that control or affect the shopping basket processes.
 * Any functions of this class are held atomic in order to achieve a high level of reusability.
 * Functions as the addArticle function should not be overwritten as a whole,
 * for this includes the shopping basket logic of Shopware. In order to be able to intervene
 * in the process of adding an article, the function addArticle makes use of many helper functions
 * which are also defined in this class. To (for example) be able to save additional attributes
 * in the shopping basket process, you can, for instance, overwrite/ hook the function getAttributeData,
 * which is responsible for the generation of the attribute data of a single shopping basket position.
 * To intercept certain scenarios, the transfer parameters (as for instance addArticle) are forwarded
 * to the helper functions. Furthermore, the additional transfer parameter "parameter" has been
 * attached to the Shopware standard functions, it is available for plugins in order to transfer
 * further data to the shopping cart process. The values ??of this transfer parameter are not
 * considered by the Shopware standard and do not serve the individualization of the shopping
 * basket process for plugins.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagBundle\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @Shopware\noEncryption
 */
class Shopware_Components_BundleBasket extends Enlight_Class implements Enlight_Hook
{
    /**
     * Constant for the exception case that
     * no valid order number passed to the add article function
     */
    const FAILURE_NO_VALID_ORDER_NUMBER = 1;

    /**
     * Constant for the exception case that
     * the current session identified as bot session.
     */
    const FAILURE_BOT_SESSION = 2;

    /**
     * Constant for the exception case that
     * the notify until event prevent the process.
     */
    const FAILURE_ADD_ARTICLE_START_EVENT = 3;

    /**
     * Constant for the exception case that
     * one of the articles has not enough stock.
     */
    const FAILURE_NOT_ENOUGH_STOCK = 4;

    /**
     * Session id of the user.
     * @var string Unique identifier for the basket item.
     */
    protected $sessionId = null;

    /**
     * Current customer session.
     * Contains the customer data and other helper objects/data.
     * @var \Enlight_Components_Session_Namespace
     */
    protected $session = null;

    /**
     * Contains the current sub shop of shopware.
     * If the class property contains null, the getter function of
     * this property loads the current shop over "Shopware()->Shop()"
     * @var \Shopware\Models\Shop\Shop
     */
    protected $shop = null;

    /**
     * Contains the entity manager of shopware.
     * Used for the model access.
     * If the class property contains null, the getter function loads
     * the entity manager over "Shopware()->Models()".
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $entityManager = null;

    /**
     * Contains the basket snippet namespace object which is used to get the translation
     * for the different basket notices and errors.
     * @var Enlight_Components_Snippet_Namespace
     */
    protected $snippetNamespace = null;

    /**
     * Contains the Doctrine repository of the \Shopware\Models\Customer\Group
     * model. Used for all data access of the customer group resource.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $customerGroupRepository = null;

    /**
     * Contains the sArticles core class.
     * @var sArticles
     */
    protected $articleModule = null;

    /**
     * Connection to the shopware database.
     * If this property is set to null, the getter function
     * of this property loads the default class over "Shopware()->Db()"
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $database = null;

    /**
     * Contains the Enlight_Event_EventManager.
     * If this property is set to null, the getter function
     * of this property loads the default class over "Enlight()->Events()".
     * Used for all application events in this class.
     * @var Enlight_Event_EventManager
     */
    protected $eventManager = null;

    /**
     * Contains the old basket module.
     * Is used for the sUpdateArticle function to recalculate the article prices.
     * After the whole basket was converted to the new structure, this property can be remove.
     * @var sBasket
     */
    protected $basketModule = null;

    /**
     * This property is only used for unit tests.
     * @var null
     */
    protected $newBasketItem = null;

    /**
     * Getter function of the newBasketItem property.
     * This property is only used for php unit tests.
     *
     * @return null|Shopware\Models\Order\Basket
     */
    public function getNewBasketItem()
    {
        if ($this->newBasketItem === null) {
            return new \Shopware\Models\Order\Basket();
        } else {
            return $this->newBasketItem;
        }
    }

    /**
     * Setter function of the newBasketItem property.
     * This property is only used for php unit tests.
     *
     * @param $newBasketItem
     */
    public function setNewBasketItem($newBasketItem)
    {
        $this->newBasketItem = $newBasketItem;
    }

    /**
     * Getter function for the $basketModule property of this class.
     * Returns the shopware 3.* basket core class.
     * If this property contains null, the getter function
     * returns as default Shopware()->Modules()->Basket()
     * @return sBasket
     */
    public function getBasketModule()
    {
        if ($this->basketModule === null) {
            $this->basketModule = Shopware()->Modules()->Basket();
        }
        return $this->basketModule;
    }

    /**
     * Setter function for the basketModule property of this class.
     * @param $basketModule sBasket
     */
    public function setBasketModule($basketModule)
    {
        $this->basketModule = $basketModule;
    }

    /**
     * Getter function for the eventManager property of this class.
     * Used for all application events in this class.
     * Returns the Enlight_Event_EventManager.
     * If this property is set to null, this getter function
     * loads the default class over "Enlight()->Events()".
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
     * Setter function for the eventManager property of this class.
     * Used for all application events in this class.
     * Returns the Enlight_Event_EventManager.
     * If this property is set to null, the getter function of this property
     * loads the default class over "Enlight()->Events()".
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Getter function for the session id property of this class.
     * Used for the customer identification.
     * If the class property contains null, the function loads the
     * session id over "Shopware()->SessionID()".
     * @return string
     */
    public function getSessionId()
    {
        if ($this->sessionId === null) {
            $this->sessionId = Shopware()->SessionID();
        }
        return $this->sessionId;
    }

    /**
     * Setter function for the session id property of this class.
     * Used for customer identification.
     * @param $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Getter function for the session property of this class.
     * Contains the current customer session.
     * The session contains for example the unique session id
     * which is used as basket identification for the current customer.
     * If the session property contains null, this function loads the
     * standard shopware session over "Shopware()->Session()".
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
     * Setter function for the session property of this class.
     * Contains the current customer session.
     * The session contains for example the unique session id
     * which is used as basket identification for the current customer.
     *
     * @param  $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * Getter function for the shop property of this class.
     * Contains the current sub shop model of shopware.
     * If the class property contains null, the getter function loads
     * the active sub shop over "Shopware()->Shop()".
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
     * Setter function for the shop property of this class.
     * Contains the current sub shop model of shopware.
     * If the class property contains null, the getter function loads
     * the active sub shop over "Shopware()->Shop()".
     *
     * @param \Shopware\Models\Shop\Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * Getter function for the snippet namespace property.
     * The snippet namespace is used for to get the translation
     * for the different basket notices and errors.
     * If the class property contains null, this function loads automatically the default snippet namespace
     * "Shopware()->Snippets()->getNamespace('frontend/checkout')".
     * @return Enlight_Components_Snippet_Namespace
     */
    public function getSnippetNamespace()
    {
        if ($this->snippetNamespace === null) {
            $this->snippetNamespace = Shopware()->Snippets()->getNamespace('frontend/checkout');
        }
        return $this->snippetNamespace;
    }

    /**
     * Setter function for the snippet namespace property.
     * The snippet namespace is used for to get the translation
     * for the different basket notices and errors.
     * If the class property contains null, the getter function loads automatically the default snippet namespace
     * "Shopware()->Snippets()->getNamespace('frontend/checkout')".
     *
     * @param $snippetNamespace Enlight_Components_Snippet_Namespace
     */
    public function setSnippetNamespace($snippetNamespace)
    {
        $this->snippetNamespace = $snippetNamespace;
    }

    /**
     * Getter function for the entityManager property.
     * Contains the entity manager of shopware.
     * Used for the model access.
     * If the class property contains null, the function loads
     * the entity manager over "Shopware()->Models()".
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    public function getEntityManager()
    {
        if ($this->entityManager === null) {
            $this->entityManager = Shopware()->Models();
        }
        return $this->entityManager;
    }

    /**
     * Setter function for the entity manager property of this class.
     * @param \Shopware\Components\Model\ModelManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * The getCustomerGroupRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getCustomerGroupRepository()
    {
        if ($this->customerGroupRepository === null) {
            $this->customerGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group');
        }
        return $this->customerGroupRepository;
    }

    /**
     * Setter function for the customer group repository property of this class.
     * Used for all data accesses to the customer group resource.
     *
     * @param $customerGroupRepository
     */
    public function setCustomerGroupRepository($customerGroupRepository)
    {
        $this->customerGroupRepository = $customerGroupRepository;
    }


    /**
     * Getter function for the article module property of this class.
     * Used for the price calculation. If this property is set to null,
     * the function loads the default class "Shopware()->Modules()->Articles()"
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
     * Setter function for the article module property of this class.
     * Used for the price calculation.
     *
     * @param \sArticles $articleModule
     */
    public function setArticleModule($articleModule)
    {
        $this->articleModule = $articleModule;
    }

    /**
     * Getter function of the database property of this class.
     * If the database property is set to null, this getter
     * function loads the default connection over "Shopware()->Db()".
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
     * Setter function of the database property in this class.
     * Used for all directly database action in this class.
     * If the database property is set to null, the getter
     * function loads the default connection over "Shopware()->Db()".
     *
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }


    /**
     * Global interface to add a single article to the basket.
     * The passed order number used as identifier for the article.
     * The passed quantity is used to identify how many times the customer want to
     * buy the article.
     *
     * <pre>
     * To add an article to the shopware basket, shopware checks the following conditions:
     * 1. The passed order number has to be a valid order number which defined over the s_articles_details table.
     * 2. The article and the variant of the passed order number has to been activated (active column in the s_articles and s_articles_details).
     * 3. The current customer group must be enabled for the selected article.
     * 4. The Shopware_Modules_Basket_AddArticle_Start notifyUntil event should not return TRUE
     * 5. The variant stock has to be greater or equal than the sum of the quantity of the basket for the current customer session
     * and the passed quantity for the passed article.
     * 6. The article must have defined a price.
     * </pre>
     *
     * @param string  $orderNumber The order number of the article variant.
     * @param integer $quantity    How many unit of the variant has to been added.
     * @param array   $parameter   An optional array of process parameters which can be handled from plugins.
     * The Shopware standard process don't considers this property.
     *
     * @return array
     * Result of the add article to basket process.
     * <br>
     * If the process <b>was successfully</b> the array contains the following data:
     * <pre>
     *   'success' => true
     *   'data'    => array(
     *       'id' => 2,
     *       'sessionId' => '5711bfc8c3ebd0161366d02d4f9f364649c342ca',
     *       'customerId' => 0,
     *       'articleId' => 178,
     *       'partnerId' => '',
     *       'articleName' => 'Strandtuch "Ibiza"',
     *       'orderNumber' => 'SW10178',
     *       'shippingFree' => 0,
     *       'quantity' => 1,
     *       'price' => 19.95,
     *       'netPrice' => 16.764705882353,
     *       'date' => DateTime::__set_state(array(
     *           'date' => '2012-10-08 13:45:48',
     *           'timezone_type' => 3,
     *           'timezone' => 'Europe/Berlin',
     *       )),
     *       'mode' => 0,
     *       'esdArticle' => 0,
     *       'config' => '',
     *       'currencyFactor' => 1,
     *       'attribute' => array (
     *           'id' => 92,
     *           'attribute1' => '',
     *           'attribute2' => NULL,
     *           'attribute3' => NULL,
     *           'attribute4' => NULL,
     *           'attribute5' => NULL,
     *           'attribute6' => NULL,
     *           'orderBasketId' => 2,
     *       ),
     *    )
     * </pre>
     * <br>
     * If the process <b>wasn't successfully</b> the array contains the following data:
     * <pre>
     *   'success' => false,
     *   'error'   => array(
     *       'code' => 123
     *       'message' => '....'
     *   )
     * </pre>
     */
    public function addArticle($orderNumber, $quantity = 1, $parameter = array())
    {
        //make sure that the used quantity is an integer value.
        $quantity = (empty($quantity)||!is_numeric($quantity)) ? 1 : (int) $quantity;

        //first we have to get the \Shopware\Models\Article\Detail model for the passed order number
        $variant = $this->getVariantByOrderNumber($orderNumber);

        //if no \Shopware\Models\Article\Detail found return an failure result
        if (!$variant instanceof \Shopware\Models\Article\Detail) {
            return $this->getNoValidOrderNumberFailure();
        }

        //validate the order number and quantity.
        $validation = $this->validateArticle($variant, $quantity, $parameter);

        //not allowed to add the article?
        if ($validation['success'] === false) {
            return $validation;
        }

        //the shouldAddAsNewPosition is a helper function to validate if the passed variant has to be created
        //as new basket position.
        $id = $this->shouldAddAsNewPosition($variant, $quantity, $parameter);

        if ($id === true) {

            //if the shouldAddAsNewPosition function returns true, the variant will be added as new position
            $id = $this->createItem(
                $this->getVariantCreateData($variant, $quantity, $parameter),
                $variant,
                $quantity,
                $parameter
            );

        } else {

            //in the other case, the shouldAddAsNewPosition returns the id of the basket position which
            //has to be updated.
            $data = $this->getVariantUpdateData($variant, $quantity, $parameter);

            //the quantity could be changed by a hook
            if (isset($data['quantity'])) {
                $quantity = $data['quantity'];
            }

            $this->updateItem(
                $id,
                $data,
                $variant,
                $quantity,
                $parameter
            );
        }

        //we have to execute the sUpdateArticle function to update the basket prices.
        $this->getBasketModule()->sUpdateArticle($id, $quantity);

        return array(
            'success' => true,
            'data' => $this->getItem($id)
        );
    }

    /**
     * Helper function to get the variant data for the new
     * basket position. To add additional information you can hook
     * this function and change the return value by an after hook.
     *
     * @param $variant \Shopware\Models\Article\Detail
     * @param $quantity
     * @param $parameter
     *
     * @return array
     */
    public function getVariantCreateData($variant, $quantity, $parameter = array())
    {
        $price = $this->getVariantPrice($variant, $quantity, $parameter);

        return array(
            'sessionId' => (string) $this->getSessionId(),
            'customerId' => (string) $this->getUserId(),
            'articleName' => $this->getVariantName($variant, $quantity, $parameter),
            'articleId' => $this->getArticleId($variant, $quantity, $parameter),
            'orderNumber' => $this->getNumber($variant, $quantity, $parameter),
            'shippingFree' => $this->getShippingFree($variant, $quantity, $parameter),
            'quantity' => $quantity,
            'price' => $price['gross'],
            'netPrice' => $price['net'],
            'date' => 'now',
            'esdArticle' => $this->getEsdFlag($variant, $quantity, $parameter),
            'partnerId' => (string) $this->getSession()->sPartner,
            'attribute' => $this->getAttributeCreateData($variant, $quantity, $parameter)
        );
    }

    /**
     * Helper function of the addArticle function.
     * Generates the default shopware attribute data,
     * based on the passed variant, for the new basket row.
     * To add additional basket attributes, use an Enlight_Hook_After
     * event to modify the return value.
     *
     * @param \Shopware\Models\Article\Detail      $variant
     * @param integer                              $quantity
     * @param array                                $parameter
     *
     * @return array
     */
    public function getAttributeCreateData($variant, $quantity, $parameter = array())
    {
        return array(
            'attribute1' => null,
            'attribute2' => null,
            'attribute3' => null,
            'attribute4' => null,
            'attribute5' => null,
            'attribute6' => null
        );
    }

    /**
     * Helper function to get the variant data for the updated
     * basket position. To add additional information you can hook
     * this function and change the return value by an after hook.
     *
     * @param       $variant \Shopware\Models\Article\Detail
     * @param       $quantity
     * @param array $parameter
     *
     * @return array
     */
    public function getVariantUpdateData($variant, $quantity, $parameter = array())
    {
        $summarizedQuantity = $this->getSummarizedQuantityOfVariant($variant, $quantity, $parameter);

        $attribute = $this->getAttributeUpdateData($variant, $quantity, $parameter);

        $data = array(
            'quantity' => $summarizedQuantity + $quantity
        );
        if (!empty($attribute)) {
            $data['attribute'] = $attribute;
        }

        return $data;
    }

    /**
     * Helper function of the addArticle function.
     * Generates the default shopware attribute data,
     * based on the passed variant, for the updated basket row.
     * To add additional basket attributes, use an Enlight_Hook_After
     * event to modify the return value.
     *
     * @param \Shopware\Models\Article\Detail      $variant
     * @param integer                              $quantity
     * @param array                                $parameter
     *
     * @return array
     */
    public function getAttributeUpdateData($variant, $quantity, $parameter = array())
    {
        return array();
    }

    /**
     * Helper function to update an existing basket item.
     * The function expects an array with basket data.
     * All parameters of the addArticle function are also available here.
     *
     * @param                                 $id
     * @param array                           $data
     * @param \Shopware\Models\Article\Detail $variant
     * @param integer                         $quantity
     * @param array                           $parameter
     *
     * @return array The inserted data.
     */
    public function updateItem($id, $data, $variant, $quantity, $parameter)
    {
        $basket = $this->getEntityManager()->find('Shopware\Models\Order\Basket', $id);
        if (!$basket instanceof \Shopware\Models\Order\Basket) {
            $basket = $this->getNewBasketItem();
        }
        if (empty($data)) {
            return;
        }

        $basket->fromArray($data);

        $this->getEntityManager()->clear();
        $this->getEntityManager()->persist($basket);
        $this->getEntityManager()->flush();
    }

    /**
     * Returns the basket data for the passed basket row id.
     * The result set data type can be handled over the hydration mode parameter.
     * The hydration mode default is set to \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY,
     * you can pass \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT to get an instance of
     * \Shopware\Models\Order\Basket
     *
     * @param     $id
     * @param int $hydrationMode
     *
     * @return array|null
     */
    public function getItem($id, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('basket', 'attribute'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->leftJoin('basket.attribute', 'attribute')
                ->where('basket.id = :id')
                ->setFirstResult(0)
                ->setMaxResults(1);

        $builder->setParameters(array(
            'id' => $id
        ));

        return $builder->getQuery()->getOneOrNullResult(
            $hydrationMode
        );
    }


    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the userID column
     * of the s_order_basket.
     *
     * @return null|integer
     */
    public function getUserId()
    {
        return $this->getSession()->sUserId;
    }

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the articleName column
     * of the s_order_basket.
     * Override this function to control an own article name
     * handling in the basket section.
     *
     * @param \Shopware\Models\Article\Detail      $variant
     * @param integer                              $quantity
     * @param array                                $parameter
     * @return string
     */
    public function getVariantName($variant, $quantity, $parameter = array())
    {
        $translation = $this->getArticleModule()->sGetArticleNameByOrderNumber($variant->getNumber(), true);
        if (!empty($translation['articleName'])) {
            return $translation['articleName'];
        } else {
            return $variant->getArticle()->getName();
        }
    }

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the articleID column
     * of the s_order_basket.
     * Override this function to control an own article id handling of the
     * basket section.
     *
     * @param \Shopware\Models\Article\Detail      $variant
     * @param integer                              $quantity
     * @param array                                $parameter
     * @return string
     */
    public function getArticleId($variant, $quantity, $parameter = array())
    {
        return $variant->getArticle()->getId();
    }

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the order number column
     * of the s_order_basket.
     * Override this function to control an own order number handling
     * in the basket section.
     *
     * @param \Shopware\Models\Article\Detail      $variant
     * @param integer                              $quantity
     * @param array                                $parameter
     * @return string
     */
    public function getNumber($variant, $quantity, $parameter = array())
    {
        return $variant->getNumber();
    }

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the shipping free column
     * of the s_order_basket.
     * Override this function to control an own shipping free handling
     * in the basket section.
     *
     * @param \Shopware\Models\Article\Detail      $variant
     * @param integer                              $quantity
     * @param array                                $parameter
     * @return string
     */
    public function getShippingFree($variant, $quantity, $parameter = array())
    {
        if ($variant->getShippingFree()) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the price column
     * of the s_order_basket.
     * Override this function to control an own price handling
     * in the basket section.
     *
     * @param \Shopware\Models\Article\Detail      $variant
     * @param integer                              $quantity
     * @param array                                $parameter
     * @return string
     */
    public function getVariantPrice($variant, $quantity, $parameter = array())
    {
        $prices = $this->getPricesForCustomerGroup(
            $variant,
            $this->getCurrentCustomerGroup()->getKey(),
            $this->getShop()->getCustomerGroup()->getKey(),
            \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT,
            $parameter
        );

        if ($prices === null) {
            return false;
        }

        $price = $this->getPriceForQuantity($prices, $quantity, $variant, $parameter);

        if ($price === null) {
            return false;
        }

        return $this->getNetAndGrossPriceForVariantPrice($price, $variant, $parameter);
    }

    /**
     * Helper function for the getVariantData function.
     * Used to check the current shop session if the customer price
     * will be displayed as gross or net prices.
     * Override this function to control an own net and gross price handling
     * in the basket section.
     *
     * @param \Shopware\Models\Article\Price $price
     * @param \Shopware\Models\Article\Detail $variant
     * @param array $parameter
     *
     * @return array
     */
    public function getNetAndGrossPriceForVariantPrice($price, $variant, $parameter)
    {
        $gross = $this->getArticleModule()->sCalculatingPriceNum(
            $price->getPrice(),
            $variant->getArticle()->getTax()->getTax(),
            false,
            false,
            $variant->getArticle()->getTax()->getId(),
            false,
            $variant
        );

        return array(
            'gross' => $gross,
            'net' => $price->getPrice()
        );
    }

    /**
     * Helper function to select all prices for the passed variant and the passed
     * customer group. If the result set of the query is empty, the function
     * resume the query with the passed fallback customer group key.
     * To control the result data type you can use the $hydrationMode parameter.
     * The default is set to "\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY".
     * Set the parameter to "\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT" to get the result
     * set as \Shopware\Models\Article\Price instances.
     *
     * @param \Shopware\Models\Article\Detail $variant
     * @param string                          $customerGroupKey Contains the group key for the customer group
     * @param string                          $fallbackKey      Contains an fallback group key for the customer group
     * @param int                             $hydrationMode    The hydration mode parameter control the result data type.
     * @param array                           $parameter
     *
     * @return array
     */
    public function getPricesForCustomerGroup($variant, $customerGroupKey, $fallbackKey, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY, $parameter = array())
    {
        //no group key passed?
        if (empty($customerGroup)) {
            $customerGroupKey = $fallbackKey;
        }

        $builder = $this->getPriceQueryBuilder();
        $builder->setParameters(array(
            'articleDetailId' => $variant->getId(),
            'customerGroupKey' => $customerGroupKey
        ));

        $prices = $builder->getQuery()->getResult($hydrationMode);

        if (empty($prices) && $customerGroupKey !== $fallbackKey) {
            return $this->getPricesForCustomerGroup($variant, $fallbackKey, $fallbackKey, $hydrationMode);
        } else if (empty($prices)) {
            return null;
        } else {
            return $prices;
        }
    }

    /**
     * Helper function to get an query builder object which creates an select
     * on the article price table with an article detail id and customer group key
     * condition.
     * The result will be sorted by the from value of the prices.
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getPriceQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('prices'))
                ->from('Shopware\Models\Article\Price', 'prices')
                ->where('prices.articleDetailsId = :articleDetailId')
                ->andWhere('prices.customerGroupKey = :customerGroupKey')
                ->orderBy('prices.from', 'ASC');
    }

    /**
     * Helper function to get the current customer group of the logged in customer.
     * If the customer isn't logged in now, the function returns the default customer
     * group of the current sub shop.
     * @return \Shopware\Models\Customer\Group
     */
    public function getCurrentCustomerGroup()
    {
        $customerGroupData = $this->getSession()->sUserGroupData;

        $customerGroup = null;

        /**@var $customerGroup \Shopware\Models\Customer\Group*/
        //check if the customer logged in and get the customer group model for the logged in customer
        if (!empty($customerGroupData['id'])) {
            $customerGroup = $this->getCustomerGroupRepository()->find($customerGroupData['id']);
        }

        //if no customer group given, get the default customer group.
        if (!$customerGroup instanceof Shopware\Models\Customer\Group) {
            $customerGroup = $this->getShop()->getCustomerGroup();
        }

        return $customerGroup;
    }

    /**
     * Helper function for the getVariantData function.
     * Used to get the stack price of the passed quantity.
     * The passed prices are already filtered by the customer group.
     * The first price with the corresponding "from" and "to" value
     * will be returned.
     *
     * @param array                                $prices
     * @param integer                              $quantity
     * @param \Shopware\Models\Article\Detail      $variant
     * @param array                                $parameter
     *
     * @return \Shopware\Models\Article\Price
     */
    public function getPriceForQuantity($prices, $quantity, $variant, $parameter = array())
    {
        $currentPrice = null;

        /**@var $price \Shopware\Models\Article\Price*/
        foreach($prices as $price) {
            if (!is_numeric($price->getTo())) {
                $currentPrice = $price;
                break;
            } else if ($quantity >= $price->getFrom() && $quantity <= $price->getTo()) {
                $currentPrice = $price;
                break;
            }
        }
        return $currentPrice;
    }

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the ordernumber column
     * of the s_order_basket.
     * Override this function to control an own esd handling
     * in the basket section.
     *
     * @param \Shopware\Models\Article\Detail      $variant
     * @param integer                              $quantity
     * @param array                                $parameter
     * @return string
     */
    public function getEsdFlag($variant, $quantity, $parameter = array())
    {
        if ($variant->getEsd() instanceof \Shopware\Models\Article\Esd) {
            return 1;
        } else {
            return 0;
        }
    }


    /**
     * Search an article variant (\Shopware\Models\Article\Detail) with the passed
     * article order number and returns it.
     *
     * @param $orderNumber
     * @return null|\Shopware\Models\Article\Detail
     */
    public function getVariantByOrderNumber($orderNumber)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('variant', 'article'))
                ->from('Shopware\Models\Article\Detail', 'variant')
                ->innerJoin('variant.article', 'article')
                ->where('variant.number = :orderNumber')
                ->andWhere('variant.active = :active')
                ->andWhere('article.active = :active')
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->setParameters(array('orderNumber' => $orderNumber, 'active' => true));

        return $builder->getQuery()->getOneOrNullResult(
            \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
        );
    }

    /**
     * Helper function to validate the passed article variant and the passed quantity.
     * Checks if the passed variant fulfill all requirements to add the article
     * in the current session to the basket.
     *
     * @param         $variant
     * @param integer $quantity
     * @param array   $parameter
     *
     * @return array
     */
    public function validateArticle($variant, $quantity, $parameter = array())
    {
        //check if the current shop customer group can see/buy the passed article variant.
        if (!$this->isCustomerGroupAllowed($variant, $this->getCurrentCustomerGroup(), $parameter)) {
            return $this->getNoValidOrderNumberFailure();
        }

        //check if the current session is a bot session.
        if ($this->isBotSession()) {
            return $this->getBotSessionFailure();
        }

        //check if the standard shopware notify event returns true.
        if ($this->fireNotifyUntilAddArticleStart($variant, $quantity, $parameter)){
            return $this->getAddArticleStartFailure();
        }

        //check if the variant is in stock and the last stock flag is set to true.
        if (!$this->isVariantInStock($variant, $quantity, $parameter)) {
            return $this->getInStockFailure();
        }

        return array('success' => true);
    }

    /**
     * Helper function to check if the passed variant has enough stock.
     * Returns false if the lastStock flag is set to true and
     * the passed quantity is greater than the stock value of the variant.
     * <br>
     * Notice: This function sums the already added quantity of the same variant in the basket.
     *
     * @param \Shopware\Models\Article\Detail      $variant
     * @param integer                              $quantity
     * @param array                                $parameter
     * @return boolean
     */
    public function isVariantInStock($variant, $quantity, $parameter = array())
    {
        $basketQuantity = $this->getSummarizedQuantityOfVariant($variant, $quantity, $parameter);

        $totalQuantity = $basketQuantity + $quantity;

        if ($variant->getArticle()->getLastStock() && $totalQuantity > $variant->getInStock()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Helper function to get the summarized quantity of the basket for the passed variant.
     *
     * @param \Shopware\Models\Article\Detail $variant
     * @param integer                         $quantity
     * @param array                           $parameter
     *
     * @return integer Returns the summarized value of the quantity column of the s_order_basket.
     * If the variant isn't in basket, the function return the numeric value 0.
     */
    public function getSummarizedQuantityOfVariant($variant, $quantity, $parameter = array())
    {
        $sql= "
            SELECT SUM(basket.quantity)
            FROM s_order_basket basket
            WHERE basket.ordernumber = ?
            AND sessionID = ?
            GROUP BY basket.ordernumber
        ";
        $basketQuantity = $this->getDatabase()->fetchOne(
            $sql,
            array($variant->getNumber(), $this->getSessionId())
        );

        if (!is_numeric($basketQuantity)) {
            $basketQuantity = 0;
        }
        return $basketQuantity;
    }

    /**
     * Helper function to fire the notify until event for "Shopware_Modules_Basket_AddArticle_Start".
     * If the event has an event listener in some plugins which returns true, the add article
     * process will be canceled.
     *
     * @param       $variant \Shopware\Models\Article\Detail
     * @param       $quantity
     * @param array $parameter
     * @return boolean Result of the Shopware_Modules_Basket_AddArticle_Start NotifyUntil event.
     */
    public function fireNotifyUntilAddArticleStart($variant, $quantity, $parameter = array())
    {
        return $this->getEventManager()->notifyUntil(
            'Shopware_Modules_Basket_AddArticle_Start',
            array(
                 'subject' => $this,
                 'id' => $variant->getId(),
                 'quantity' => $quantity,
                 'parameter' => $parameter
            )
        );
    }

    /**
     * Helper function to check if the current session is a bot session.
     * @return boolean
     */
    private function isBotSession()
    {
        return $this->getSession()->Bot;
    }

    /**
     * Helper function to check if the passed customer group
     * can see the passed article variant.
     *
     * @param $variant \Shopware\Models\Article\Detail
     * @param $customerGroup \Shopware\Models\Customer\Group
     * @param $parameter
     *
     * @return bool
     */
    public function isCustomerGroupAllowed($variant, $customerGroup, $parameter)
    {
        if ($variant->getArticle()->getCustomerGroups()->contains($customerGroup)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Helper function to check if the passed variant with the additional parameters
     * has to be add as new row or update an existing row.
     * The shopware standard checks only the order number of the passed variant.
     * If this number is already in the basket, the basket id will be returned
     * and the basket row will be updated with the new quantity and the new variant data.
     * To implement an handling for this logic, you can create an event listener
     * for this function with an Enlight_Hook_After event to modify the return value.
     * All parameters of the addArticle function are also available here.
     * To control that an existing row has to been updated, return the id of the
     * basket row.
     *
     * @param \Shopware\Models\Article\Detail $variant
     * @param $quantity
     * @param $parameter
     *
     * @return bool
     */
    public function shouldAddAsNewPosition($variant, $quantity, $parameter)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('basket', 'attribute'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->leftJoin('basket.attribute', 'attribute')
                ->where('basket.sessionId = :sessionId')
                ->andWhere('basket.orderNumber = :orderNumber')
                ->setFirstResult(0)
                ->setMaxResults(1);

        $builder->setParameters(array(
            'sessionId' => $this->getSessionId(),
            'orderNumber' => $variant->getNumber())
        );

        $result = $builder->getQuery()->getOneOrNullResult(
            \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
        );

        if ($result instanceof \Shopware\Models\Order\Basket) {
            return $result->getId();
        } else {
            return true;
        }
    }

    /**
     * Helper function to create a new basket item.
     * The function expects an array with basket data.
     * All parameters of the addArticle function are also available here.
     *
     * @param array $data
     * @param \Shopware\Models\Article\Detail $variant
     * @param integer $quantity
     * @param array $parameter
     *
     * @return int|null The inserted data.
     */
    public function createItem($data, $variant, $quantity, $parameter)
    {
        $basket = $this->getNewBasketItem();
        $basket->fromArray($data);

        $this->getEntityManager()->clear();
        $this->getEntityManager()->persist($basket);
        $this->getEntityManager()->flush();
        if ($basket instanceof \Shopware\Models\Order\Basket) {
            return $basket->getId();
        } else {
            return null;
        }
    }

    /**
     * Helper function to create an array result with success false and
     * the error "no valid order number passed".
     * @return array
     */
    private function getNoValidOrderNumberFailure() {
        return array(
            'success' => false,
            'error' => array(
                'code' => self::FAILURE_NO_VALID_ORDER_NUMBER,
                'message' => $this->getSnippetNamespace()->get(
                    'no_valid_order_number',
                    'The order number is not valid'
                )
            )
        );
    }

    /**
     * Helper function to create an array result with success false and
     * the error "You are identified as bot!".
     * @return array
     */
    private function getBotSessionFailure() {
        return array(
            'success' => false,
            'error' => array(
                'code' => self::FAILURE_BOT_SESSION,
                'message' => $this->getSnippetNamespace()->get(
                    'bot_session',
                    'You are identified as bot!'
                )
            )
        );
    }

    /**
     * Helper function to create an array result with success false and
     * the error "The add article process aborted over the Shopware_Modules_Basket_AddArticle_Start event.".
     * @return array
     */
    private function getAddArticleStartFailure() {
        return array(
            'success' => false,
            'error' => array(
                'code' => self::FAILURE_ADD_ARTICLE_START_EVENT,
                'message' => $this->getSnippetNamespace()->get(
                    'notify_until_add_article_start',
                    'The add article process aborted over the Shopware_Modules_Basket_AddArticle_Start event'
                )
            )
        );
    }

    /**
     * Helper function to create an array result with success false and
     * the error "The add article process aborted over the Shopware_Modules_Basket_AddArticle_Start event.".
     * @return array
     */
    private function getInStockFailure() {
        return array(
            'success' => false,
            'error' => array(
                'code' => self::FAILURE_NOT_ENOUGH_STOCK,
                'message' => $this->getSnippetNamespace()->get(
                    'not_enough_stock',
                    'Not enough article stock!'
                )
            )
        );
    }
}