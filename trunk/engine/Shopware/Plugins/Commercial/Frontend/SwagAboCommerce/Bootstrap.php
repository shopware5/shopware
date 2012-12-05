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
 * Shopware SwagAboCommerce Plugin - Bootstrap
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagAboCommerce
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Frontend_SwagAboCommerce_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Getter of the entity manager property.
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    protected function getEntityManager()
    {
        return Shopware()->Models();
    }

    /**
     * Getter of the database property.
     *
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function getDatabase()
    {
        return $this->Application()->Db();
    }

    /**
     * Getter of the aboCommerceRepository property.
     *
     * @return \Shopware\CustomModels\SwagAboCommerce\Repository
     */
    public function getAboCommerceRepository()
    {
        return $this->getEntityManager()->getRepository('Shopware\CustomModels\SwagAboCommerce\Article');
    }

    /**
     * Getter of the aboCommerceBasketComponent property.
     *
     * @return Shopware_Components_AboCommerceBasket
     */
    protected function getAboCommerceBasketComponent()
    {
        return $this->Application()->AboCommerceBasket();
    }

    /**
     * Getter of the aboCommerceBasketComponent property.
     *
     * @return Shopware_Components_AboCommerce
     */
    protected function getAboCommerceComponent()
    {
        return $this->Application()->AboCommerce();
    }

    /**
     * Getter of the articleRepostiroy property.
     *
     * @return \Shopware\Models\Article\Repository
     */
    public function getArticleRepository()
    {
        return $this->getEntityManager()->getRepository('Shopware\Models\Article\Article');
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
        return $this->Application()->Bootstrap();
    }

    /**
     * Helper function to check the plugin lizenz.
     *
     * @param bool $throwException
     *
     * @throws Exception
     * @return bool
     */
    public function checkLicense($throwException = true)
    {
        // todo@bc add license
        return true;
    }

    /**
     * Returns the current version of the plugin.
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * Returns the well-formatted name of the plugin
     * as a sting
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Abo-Commerce';
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
     * Registers all necessary components and dependencies.
     *
     * @throws Exception
     * @return bool
     */
    public function install()
    {
        // Check if shopware version matches
        if (!$this->assertVersionGreaterThen('4.0.5')) {
            throw new Exception('This plugin requires Shopware 4.0.5 or a later version');
        }

        $this->checkLicense();

        try {
            $this->createTables();
            $this->createMenu();
            $this->subscribeEvents();

            $this->Application()->Models()->addAttribute(
                's_order_basket_attributes',
                'swag',
                'abo_commerce_id',
                'int(11)',
                true,
                null
            );

            $this->Application()->Models()->addAttribute(
                's_order_basket_attributes',
                'swag',
                'abo_commerce_delivery_interval',
                'int(11)',
                true,
                null
            );

            $this->Application()->Models()->addAttribute(
                's_order_basket_attributes',
                'swag',
                'abo_commerce_duration',
                'int(11)',
                true,
                null
            );

            $this->getEntityManager()->generateAttributeModels(array('s_order_basket_attributes'));

            return array(
                'success' => true,
                'invalidateCache' => array('backend')
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Creates the AboCommerce backend menu item.
     *
     * The AboCommerce menu item opens the listing for the SwagAboCommerce plugin.
     */
    public function createMenu()
    {
        $this->createMenuItem(array(
            'label'      => 'Abonnements',
            'controller' => 'AboCommerce',
            'class'      => 'sprite-metronome',
            'action'     => 'Index',
            'active'     => 1,
            'parent'     => $this->Menu()->findOneBy('label', 'Marketing')
        ));
    }

    /**
     * Create tables
     */
    public function createTables()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_plugin_swag_abo_commerce_articles` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `article_id` int(11) unsigned NOT NULL,
            `active` int(1) unsigned NOT NULL,
            `exclusive` int(1) unsigned NOT NULL,
            `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `min_duration` int(11) unsigned NOT NULL,
            `max_duration` int(11) unsigned NOT NULL,
            `duration_unit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `min_delivery_interval` int(11) unsigned NOT NULL,
            `max_delivery_interval` int(11) unsigned NOT NULL,
            `delivery_interval_unit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `limited` int(1) unsigned NOT NULL,
            `max_units_per_week` int(11) unsigned DEFAULT NULL,
            `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `article_id` (`article_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        $this->getDatabase()->query($sql);

        $sql = "
            CREATE TABLE IF NOT EXISTS  `s_plugin_swag_abo_commerce_prices` (
             `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
             `customer_group_id` int(11) unsigned NOT NULL,
             `abo_article_id` int(11) unsigned NOT NULL,
             `duration_from` int(11) NOT NULL,
             `discount_absolute` double DEFAULT NULL,
             `discount_percent` double DEFAULT NULL,
             PRIMARY KEY (`id`),
             KEY `abo_article_id` (`abo_article_id`),
             KEY `customer_group_id` (`customer_group_id`),
             CONSTRAINT `s_plugin_swag_abo_commerce_prices_ibfk_1`
             FOREIGN KEY (`abo_article_id`)
             REFERENCES `s_plugin_swag_abo_commerce_articles` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ";
        $this->getDatabase()->query($sql);

        $sql = "
            CREATE TABLE IF NOT EXISTS `s_plugin_swag_abo_commerce_orders` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `order_id` int(11) unsigned NOT NULL,
            `article_order_detail_id` int(11) unsigned NOT NULL,
            `discount_order_detail_id` int(11) unsigned NOT NULL,
            `last_order_id` int(11) unsigned NOT NULL,
            `duration` int(11) NOT NULL,
            `duration_unit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `delivery_interval` int(11) NOT NULL,
            `delivery_interval_unit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`),
            KEY `order_id` (`order_id`),
            KEY `article_order_detail_id` (`article_order_detail_id`),
            KEY `discount_order_detail_id` (`discount_order_detail_id`),
            KEY `last_order_id`(`last_order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ";
        $this->getDatabase()->query($sql);
    }

    /**
     * Registers the different events
     */
    public function subscribeEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_AboCommerce',
            'onGetFrontendController'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_AboCommerce',
            'onGetBackendController'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Ipayment',
            'onGetBackendControllerIpayment'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Article',
            'onBackendArticlePostDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
            'onPostDispatchFrontendDetail'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout',
            'onPostDispatchFrontendCheckout'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_Frontend_Checkout_AddArticle',
            'onAddArticle'
        );

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_AboCommerce',
            'onInitAboCommerceResource'
        );

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_AboCommerceBasket',
            'onInitAboCommerceBasketResource'
        );

        $this->subscribeEvent(
            'Shopware_Components_AboCommerceBasket::shouldAddAsNewPosition::after',
            'onShouldAddAsNewPosition'
        );

        $this->subscribeEvent(
            'Shopware_Components_AboCommerceBasket::getAttributeCreateData::after',
            'onGetBasketAttribute'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Widgets_Checkout',
            'onPostDispatchWidgetsCheckout'
        );

        $this->subscribeEvent(
            'sBasket::sGetBasket::before',
            'onBeforeGetBasket'
        );

        $this->subscribeEvent(
            'sBasket::sGetBasket::after',
            'onAfterGetBasket'
        );

        $this->subscribeEvent(
            'Shopware_Modules_Order_SaveOrder_ProcessDetails',
            'onOrderSaveOrderProcessDetails'
        );

        // Inject the header globally in the store
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onFrontendPostDispatch'
        );
    }

    /**
     * Returns the path to the controller.
     *
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Frontend_AboCommerce
     * event.
     * Fired if an request will be root to the own AboCommerce frontend controller.
     *
     * @return string
     */
    public function onGetFrontendController()
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );

        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        return $this->Path(). 'Controllers/Frontend/AboCommerce.php';
    }

    /**
     * Returns the path to the controller.
     *
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_AboCommerce
     * event.
     * Fired if an request will be root to the own AboCommerce backend controller.
     *
     * @return string
     */
    public function onGetBackendController()
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );

        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        return $this->Path(). 'Controllers/Backend/AboCommerce.php';
    }

    /**
     * Returns the path to the controller.
     *
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_AboCommerce
     * event.
     * Fired if an request will be root to the own AboCommerce backend controller.
     *
     * @return string
     */
    public function onGetBackendControllerIpayment()
    {
        include_once $this->Path(). 'Controllers/Backend/Payment.php';

        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );

        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        return $this->Path(). 'Controllers/Backend/Ipayment.php';
    }

    /**
     * Event listener function of the Shopware_Controllers_Backend_Article post dispatch event.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onBackendArticlePostDispatch(Enlight_Event_EventArgs $args)
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );

        $args->getSubject()->View()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        //if the controller action name equals "load" we have to load all application components.
        if ($args->getRequest()->getActionName() === 'load') {
            $args->getSubject()->View()->extendsTemplate(
                'backend/article/view/detail/abo_commerce_window.js'
            );
        }

        //if the controller action name equals "index" we have to extend the backend article application
        if ($args->getRequest()->getActionName() === 'index') {
            $args->getSubject()->View()->extendsTemplate(
                'backend/article/abo_commerce.js'
            );
        }
    }

    /**
     * Event listener function of the Enlight_Bootstrap_InitResource_AboCommerce event.
     * Fired if the shopware source code call $this->Application()->AboCommerce();
     *
     * @return Shopware_Components_AboCommerce
     */
    public function onInitAboCommerceResource()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );
        $aboCommerce = Enlight_Class::Instance('Shopware_Components_AboCommerce');
        $this->getShopwareBoostrap()->registerResource('AboCommerce', $aboCommerce);

        return $aboCommerce;
    }

    /**
     * Event listener function of the Enlight_Bootstrap_InitResource_AboCommerceBasket event.
     * Fired if the shopware source code call $this->Application()->AboCommerceBasket();
     *
     * @return Shopware_Components_AboCommerceBasket
     */
    public function onInitAboCommerceBasketResource()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );

        $aboCommerceBasket = Enlight_Class::Instance('Shopware_Components_AboCommerceBasket');
        $this->getShopwareBoostrap()->registerResource('AboCommerceBasket', $aboCommerceBasket);

        return $aboCommerceBasket;
    }

    /**
     * If the user select the option to buy the article for bonus points
     * execute the internal function, otherwise call the parent function
     *
     * @param Enlight_Event_EventArgs $args
     * @throws Exception
     * @return null
     */
    public function onAddArticle(Enlight_Event_EventArgs $args)
    {
        /** @var $subject Enlight_Controller_Action */
        $subject = $args->getSubject();

        $deliveryInterval = $subject->Request()->getParam('sDeliveryInterval');
        $duration         = $subject->Request()->getParam('sDurationInterval');
        $quantity         = $subject->Request()->getParam('sQuantity');
        $orderNumber      = $subject->Request()->getParam('sAdd');

        if (!$quantity) {
            $quantity = 1;
        }

        // No abo-commerce-article. Proceed normal;
        if (empty($deliveryInterval) || empty($duration)) {
            return;
        }

        $articleId = $this->Application()->Modules()->Articles()->sGetArticleIdByOrderNumber($orderNumber);
        $variant = $this->getAboCommerceComponent()->getVariantByOrderNumber($orderNumber);

        $aboArticle = $this->getAboCommerceRepository()
                ->getDetailQueryBuilder($articleId)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (empty($aboArticle)) {
            throw new \Exception('Invalid AboArticle');
        }

        $aboCommercePosition = $this->getAboCommerceComponent()
                                    ->isAboCommerceConfgigurationInBasket($orderNumber, $duration, $deliveryInterval);

        if ($aboCommercePosition === null) {
            $basketItem = $this->getAboCommerceBasketComponent()->addArticle(
                $orderNumber,
                $quantity,
                array(
                     'forceNewPosition' => true,
                     'isAboCommerce'    => true,
                     'duration'         => $duration,
                     'deliveryInterval' => $deliveryInterval,
                )
            );

            $this->getAboCommerceComponent()->getDiscountForArticle(
                $variant,
                $aboArticle,
                $basketItem,
                $quantity,
                $duration,
                $deliveryInterval
            );
        }
    }

    /**
     * Basket hook after.
     *
     * This functioni is a hook listener function of the Shopware_Components_LiveShoppingBasket::getAttributeCreateData
     * function.
     * The original function is used to get the basket attributes for the passed article.
     *
     * @param Enlight_Hook_HookArgs $arguments
     */
    public function onGetBasketAttribute(Enlight_Hook_HookArgs $arguments)
    {
        $returnValue = $arguments->getReturn();
        $parameters = $arguments->getArgs();
        $additional = $parameters[2];

        if (isset($additional['isAboCommerce'])) {
            $returnValue['swagAboCommerceDuration']         = $additional['duration'];
            $returnValue['swagAboCommerceDeliveryInterval'] = $additional['deliveryInterval'];

            $arguments->setReturn($returnValue);
        }
    }

    /**
     * @param Enlight_Hook_HookArgs $arguments
     */
    public function onBeforeGetBasket(Enlight_Hook_HookArgs $arguments)
    {
        $this->getAboCommerceComponent()->updateBasketDiscount();
    }

    /**
     * Basket hook after.
     *
     * This function is a hook listener function of the
     * Shopware_Components_AboCommerceBasket::shouldAddAsNewPosition function.
     * The original function is used to check if the current article should add as new basket row or not.
     *
     * @param  Enlight_Hook_HookArgs $arguments
     * @return bool|mixed
     */
    public function onShouldAddAsNewPosition(Enlight_Hook_HookArgs $arguments)
    {
        $parameter = $arguments->getArgs();
        $additional = $parameter[2];

        //check if the current process would add a bundle article
        if ($additional['isAboCommerce'] && $additional['forceNewPosition']) {
            $arguments->setReturn(true);
        }

        return $arguments->getReturn();
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchWidgetsCheckout(Enlight_Event_EventArgs $args)
    {
        $subject  = $args->getSubject();
        $request  = $subject->Request();
        $response = $subject->Response();
        $view     = $subject->View();

        if (!$request->isDispatched() || $response->isException()) {
            return;
        }

        $view->addTemplateDir($this->Path() . 'Views/', 'swag_abocommerce');
        $view->extendsTemplate("frontend/plugins/swag_abocommerce/menu.tpl");
    }

    /**
     * Global post dispatch of the frontend controller to load the css and jquery
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchFrontendDetail(Enlight_Event_EventArgs $args)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        /** @var $subject Enlight_Controller_Action */
        $subject = $args->getSubject();

        /** @var $request Enlight_Controller_Request_RequestHttp */
        $request = $subject->Request();

        /** @var $view Enlight_View_Default*/
        $view = $subject->View();

        if (!$this->checkLicense(false)) {
            return;
        }

        $articleId       = (int) $request->getParam('sArticle');
        $aboCommerceData = $this->getAboCommerceComponent()->getAboCommerceDataByIdArticleId($articleId);

        if (!empty($aboCommerceData)) {
            $view->assign('aboCommerce', $aboCommerceData);
        }

        // todo@bc implement
        $view->assign('aboCommerceOrderLists', array(
            array(
                'id'   => 1,
                'name' => 'Meine erste Bestellliste'
            ),
            array(
                'id' => 2,
                'name' => 'Tiernahrung'
            ),
        ));

        $isAboCommerceArticleInBasket = $this->getAboCommerceComponent()->isAboCommerceArticleInBasket();
        $isStandardArticleInBasket    = $this->getAboCommerceComponent()->isStandardArticleInBasket();

        $view->assign('aboCommerceOrderListsActive', true);
        $view->assign('aboCommerceArticleInBasket', $isAboCommerceArticleInBasket);
        $view->assign('aboCommerceStandardArticleInBasket', $isStandardArticleInBasket);

        $view->addTemplateDir($this->Path() . 'Views/');
        $view->loadTemplate('frontend/plugins/swag_abocommerce/detail.tpl');
    }

    /**
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onPostDispatchFrontendCheckout(Enlight_Event_EventArgs $arguments)
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

        if ($request->getActionName() === 'finish') {
            $view->addTemplateDir($this->Path() . 'Views/');
            $view->extendsTemplate('frontend/checkout/abo_commerce_finish_item.tpl');

            return;
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('attribute'))
                ->from('Shopware\Models\Attribute\OrderBasket', 'attribute', 'attribute.orderBasketId')
                ->innerJoin('attribute.orderBasket', 'basket')
                ->where('basket.sessionId = :sessionId')
                ->setParameters(array('sessionId' => $this->Application()->SessionID()));

        $attributes = $builder->getQuery()->getArrayResult();

        $basket = $view->getAssign('sBasket');
        $isAboCommerceArticleInBasket = $this->getAboCommerceComponent()->isAboCommerceArticleInBasket();

        if (empty($basket) || empty($attributes)) {
            return;
        }

        foreach ($basket['content'] as &$row) {
            $aboCommerceData = $this->getAboCommerceComponent()->getAboCommerceDataByIdArticleId($row['articleID']);

            if (!empty($aboCommerceData)) {
                $row['aboCommerce'] = $aboCommerceData;
            }

            if (array_key_exists($row['id'], $attributes)) {
                $row['attribute'] = $attributes[$row['id']];
            }
        }
        $view->assign('sBasket', $basket);

        $view->addTemplateDir($this->Path() . 'Views/');

        if ($request->getActionName() === 'cart') {
            $view->extendsTemplate('frontend/checkout/abo_commerce_cart_item.tpl');
        } elseif ($request->getActionName() === 'confirm') {
            $view->extendsTemplate('frontend/checkout/abo_commerce_confirm_item.tpl');
        }

        $view->assign('aboCommerceArticleInBasket', $isAboCommerceArticleInBasket);
    }

    /**
     * Extends the standard function "sBasket->sGetBasket" to consider bonus articles and bonus vouchers
     *
     * @param  Enlight_Hook_HookArgs $args
     * @return void
     */
    public function onAfterGetBasket(Enlight_Hook_HookArgs $args)
    {
        $basket = $args->getReturn();

        $sql = "SELECT * FROM s_order_basket_attributes WHERE basketID = ?";

        foreach ($basket['content'] as &$item) {
            $attributes = $this->Application()->Db()->fetchRow($sql, array($item['id']));
            $item['abo_attributes'] = $attributes;
        }

        $args->setReturn($basket);
    }

    /**
     * @param  Enlight_Event_EventArgs $args
     * @throws Exception
     * @return bool
     */
    public function onOrderSaveOrderProcessDetails(Enlight_Event_EventArgs $args)
    {
        $basketContent = $args->getDetails();
        $order         = $args->getSubject();
        $orderNumber   = $order->sOrderNumber;

        $indexedBasket = array();
        $aboDiscounts  = array();

        foreach ($basketContent as $basketItem) {
            $indexedBasket[$basketItem['id']] = $basketItem;

            if (!empty($basketItem['abo_attributes']['swag_abo_commerce_id'])) {
                $aboDiscounts[] = $basketItem;
            }
        }

        foreach ($aboDiscounts as $aboDiscount) {
            $basketItemId = $aboDiscount['abo_attributes']['swag_abo_commerce_id'];

            if (!isset($indexedBasket[$basketItemId])) {
                throw new \Exception(sprintf('Could not find matching basketItem to discount with basketItemId %s', $basketItemId));
            }

            $aboArticle = $indexedBasket[$basketItemId];

            $orderId = $this->getDatabase()->fetchOne("SELECT id FROM s_order WHERE ordernumber LIKE ?", array($orderNumber));
            if (!$orderId) {
                throw new Exception("Could not find orderId");
            }

            $this->getDatabase()->insert('s_plugin_swag_abo_commerce_orders', array(
                'order_id'                  => $orderId,
                'article_order_detail_id'   => $aboArticle['orderDetailId'],
                'discount_order_detail_id'  => $aboDiscount['orderDetailId'],
                'last_order_id'             => $orderId,
                'duration'                  => $aboArticle['abo_attributes']['swag_abo_commerce_duration'],
                'duration_unit'             => 'weeks', // todo@bc do it
                'delivery_interval'         => $aboArticle['abo_attributes']['swag_abo_commerce_delivery_interval'],
                'delivery_interval_unit'    => 'weeks', // todo@bc do it
            ));
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
        $view->extendsTemplate('frontend/index/abocommerce_header.tpl');
    }
}
