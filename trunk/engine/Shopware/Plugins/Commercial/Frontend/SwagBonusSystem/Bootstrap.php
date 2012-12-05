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
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

require_once(dirname(__FILE__) . '/Models/BonusSystemDataModel.php');

class Shopware_Plugins_Frontend_SwagBonusSystem_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Returns the current version of the plugin.
     * @return string
     */
    public function getVersion()
    {
        return '1.1.0';
    }

    /**
     * Get (nice) name for plugin manager list
     * @return string
     */
    public function getLabel()
    {
        return 'Bonussystem (SwagBonusSystem)';
    }

    /**
     * Standard plugin install method to register all required components.
     * @throws \Exception
     * @return bool success
     */
    public function install()
    {
        // Check if shopware version matches
        if (!$this->assertVersionGreaterThen('4.0.3')) {
            throw new \Exception('This plugin requires Shopware 4.0.3 or a later version');
        }

        // Check license
        $this->checkLicense(true);

        $this->Application()->Models()->addAttribute(
            's_order_details_attributes',
            'swag',
            'bonus',
            'tinyint(1)',
            true,
            0
        );

        $this->Application()->Models()->addAttribute(
            's_order_basket_attributes',
            'swag',
            'bonus',
            'tinyint(1)',
            true,
            0
        );

        $this->createMenu();
        $this->subscribeEvents();
        $this->subscribeHooks();
        $this->subscribeCronJobs();
        $this->createDatabaseTables();

        return true;
    }

    /**
     * @param   bool $throwException
     * @throws  Exception
     * @return  bool
     */
    public function checkLicense($throwException = true)
    {
        static $r, $m = 'SwagBonussystem';
        if (!isset($r)) {
            $s = base64_decode('AdQCP0vWgBmANATlr735koGvZZI=');
            $c = base64_decode('xWYS0K0mn+QbbImlbWzbOl6qjdw=');
            $r = sha1(uniqid('', true), true);
            /** @var $l Shopware_Components_License */
            $l = $this->Application()->License();
            $i = $l->getLicense($m, $r);
            $t = $l->getCoreLicense();
            $u = strlen($t) === 20 ? sha1($t . $s . $t, true) : 0;
            $r = $i === sha1($c. $u . $r, true);
        }
        if (!$r && $throwException) {
            throw new Exception('License check for module "' . $m . '" has failed.');
        }
        return $r;
    }

    /**
     * Registers all necessary events and hooks.
     */
    private function subscribeEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_BonusSystem',
            'onGetBackendControllerPath'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_BonusSystem',
            'onGetFrontendControllerPath'
        );

        //Post dispatch of all controllers to display the user score and basket point score in the header
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatchFrontend'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Widgets_Checkout',
            'onPostDispatchWidgetsCheckout'
        );

        //Pre dispatch of the checkout controller to control the basket and user points
        $this->subscribeEvent(
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout',
            'onPreDispatchCheckout'
        );

        $this->subscribeEvent(
            'Shopware_Modules_Basket_UpdateArticle_Start',
            'onUpdateArticle'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_Frontend_Checkout_AddArticle',
            'onAddArticle'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_Frontend_Checkout_AddVoucher',
            'onAddVoucher'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Account',
            'onPostDispatchAccount'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_Frontend_Checkout_AjaxAmount',
            'onAjaxAmount'
        );

        $this->subscribeEvent(
            'Shopware_Modules_Order_SaveOrder_ProcessDetails',
            'onOrderSaveOrderProcessDetails'
        );
    }

    /**
     * This function subscribe the required hooks on the shopware controllers and shopware classes
     */
    private function subscribeHooks()
    {
        $this->subscribeEvent(
            'Shopware_Components_Document::assignValues::after',
            'onBeforeRenderDocument'
        );

        // Hooks for the shopware basket class
        $this->subscribeEvent(
            'sBasket::sGetBasket::after',
            'afterGetBasket'
        );

        // Hook on the shopware order class to update the user point score
        $this->subscribeEvent(
            'sOrder::sSaveOrder::after',
            'onSaveOrder'
        );
    }

    /**
     * Subscribe the bonus system cron job to unlock the bonus points of the open orders
     * @return void
     */
    private function subscribeCronJobs()
    {
        $this->subscribeEvent(
            'Shopware_CronJob_SwagBonusSystemCron',
            'onRunUnlockBonusPoints'
        );

        $this->createCronJob(
            "SwagBonusSystemCron",
            "SwagBonusSystemCron",
            3600,
            true
        );
    }

    /**
     * This function creates the menu entry to allow access on the backend module.
     * @return void
     */
    private function createMenu()
    {
        $this->createMenuItem(array(
            'label' => 'Bonus-System',
            'controller' => 'BonusSystem',
            'class' => 'sprite-point',
            'action' => 'Index',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy('label', 'Marketing')
        ));
    }

    /**
     * This function creates all database tables of the bonus system.
     * @return void
     */
    private function createDatabaseTables()
    {
        $this->Application()->Db()->query("
            CREATE TABLE IF NOT EXISTS `s_core_plugins_bonus_order` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `orderID` int(11) NOT NULL,
                `userID` int(11) NOT NULL,
                `points` int(11) NOT NULL,
                `approval` tinyint(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `orderID` (`orderID`),
                KEY `approval` (`approval`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
        ");

        $this->Application()->Db()->query("
            CREATE TABLE IF NOT EXISTS `s_core_plugins_bonus_articles` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `shopID` int(11) NOT NULL,
              `articleID` int(11) NOT NULL,
              `articleName` varchar(255) NOT NULL,
              `ordernumber` varchar(255) NOT NULL,
              `required_points` int(11) NOT NULL,
              `position` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `articleID` (`articleID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
        ");

        $this->Application()->Db()->query("CREATE TABLE IF NOT EXISTS `s_core_plugins_bonus_user_points` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `userID` int NOT NULL,
                `points` int NOT NULL,
                PRIMARY KEY (`id`),
                KEY `user` (`userID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
        ");

        $this->Application()->Db()->query("CREATE TABLE IF NOT EXISTS `s_core_plugins_bonus_settings` (
              `shopID` int(11) NOT NULL,
              `bonus_maintenance_mode` int(11) NOT NULL,
              `bonus_articles_active` int(11) NOT NULL,
              `bonus_voucher_active` int(11) NOT NULL,
              `bonus_point_conversion_factor` varchar(20) NOT NULL,
              `bonus_voucher_conversion_factor` varchar(20) NOT NULL,
              `bonus_voucher_limitation_type` varchar(20) NOT NULL,
              `bonus_voucher_limitation_value` varchar(20) NOT NULL,
              `bonus_point_unlock_type` varchar(20) NOT NULL,
              `bonus_point_unlock_day` int(11) NOT NULL,
              `bonus_listing_text` text NOT NULL,
              `bonus_listing_banner` text NOT NULL,
              `display_banner` int(11) NOT NULL,
              `display_accordion` int(11) NOT NULL,
              `display_article_slider` int(11) NOT NULL,
              UNIQUE KEY `shop` (`shopID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
        ");
    }

    /**
     * This function is responsible to resolve the backend controller path.
     * @param  Enlight_Event_EventArgs $arguments
     * @return string
     */
    public function onGetBackendControllerPath(Enlight_Event_EventArgs $arguments)
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );

        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/',
            'swag_bonus_system'
        );

        return $this->Path() . 'Controllers/Backend/BonusSystem.php';
    }

    /**
     * This function is responsible to resolve the frontend controller path.
     *
     * @param  Enlight_Event_EventArgs $arguments
     * @return string
     */
    public function onGetFrontendControllerPath(Enlight_Event_EventArgs $arguments)
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );

        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/',
            'swag_bonus_system'
        );

        return $this->Path() . 'Controllers/Frontend/BonusSystem.php';
    }

    /**
     * Pre dispatch of the checkout controller finish action to control the basket and user points
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPreDispatchCheckout(Enlight_Event_EventArgs $args)
    {
        $subject  = $args->getSubject();
        $request  = $subject->Request();
        $response = $subject->Response();
        $view     = $subject->View();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || strtolower($request->getControllerName()) != "checkout" ) {
            return;
        }

        $model = new BonusSystemDataModel($view->sBasket);

        if (!$model->isBonusSystemActive()) {
            return;
        }

        //control the basket and user points and get the removed items
        $view->sRemovedItems = $model->controlBasket();

        //if the user try to finish the order with not enough points, forward the user back to the confirm page and display error
        if (!empty($view->sRemovedItems)) {
            $subject->forward('index');

            return;
        }
    }

    /**
     * Post dispatch of the shopware account controller to set the bonus item flags in the order positions.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchAccount(Enlight_Event_EventArgs $args)
    {
        $subject  = $args->getSubject();
        $request  = $subject->Request();
        $response = $subject->Response();
        $view     = $subject->View();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend') {
            return;
        }

        if ($request->getControllerName() != 'account' && $request->getActionName() != 'orders' ) {
            return;
        }

        if (!$view->hasTemplate()) {
            return;
        }

        $orders = $view->sOpenOrders;
        $model  = new BonusSystemDataModel();

        $sql = "SELECT swag_bonus FROM s_order_details_attributes WHERE detailID = ?";

        foreach ($orders as &$order) {
            foreach ($order['details'] as $key => $orderDetails) {
                $order['details'][$key]['swag_bonus'] = $this->Application()->Db()->fetchOne($sql, array($orderDetails['id']));
            }
            $order['details'] = $model->setBasketContentFlags($order["details"]);
        }

        $view->sOpenOrders = $orders;
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

        if (!$view->hasTemplate()) {
            return;
        }

        $model = new BonusSystemDataModel($view->sBasket);
        if (!$model->isBonusSystemActive()) {
            return;
        }

        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() > 1;
        if ($isEmotion) {
            $template = '_emotion/';
        } else {
            $template = '_default/';
        }


        $data = $model->getBonusSystemData();

        // on checkout finish page we need to reset spending points in the view
        $isFinish = $view->getAssign('isFinish');
        if ($isFinish) {
            $data['points']['remaining'] = $data['points']['user'];
        }

        $view->sBonusSystem = $data;

        $view->addTemplateDir(dirname(__FILE__) . '/Views/' . $template, 'swag_bonus_system');
        $view->extendsTemplate("frontend/plugins/swag_bonus_system/index/index.tpl");
    }

    /**
     * Global post dispatch of the frontend controller to load the css and jquery
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchFrontend(Enlight_Event_EventArgs $args)
    {
        $subject  = $args->getSubject();
        $request  = $subject->Request();
        $response = $subject->Response();
        $view     = $subject->View();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend') {
            return;
        }

        if (!$view->hasTemplate()) {
            return;
        }

        if (!$this->checkLicense(false)) {
            return;
        }

        $model = new BonusSystemDataModel($view->sBasket);
        if (!$model->isBonusSystemActive()) {
            return;
        }

        $this->extendTemplate($subject, $model);
    }

    /**
     * Helper function to extend the template for the different controllers
     *
     * @param $subject
     * @param \BonusSystemDataModel $model
     */
    private function extendTemplate($subject, \BonusSystemDataModel $model)
    {
        $request = $subject->Request();
        $view    = $subject->View();

        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() > 1;
        if ($isEmotion) {
            $template = '_emotion/';
        } else {
            $template = '_default/';
        }

        //set bonus data into template
        $view->sBonusSystem = $model->getBonusSystemData();
        $settings = $model->getBonusSystemSettings();

        $view->addTemplateDir(dirname(__FILE__) . '/Views/' . $template, 'swag_bonus_system');

        //load css and jquery
        $view->extendsTemplate("frontend/plugins/swag_bonus_system/index/header.tpl");
        $view->extendsTemplate("frontend/plugins/swag_bonus_system/index/index.tpl");

        //case between the different controllers
        switch (strtolower($request->getControllerName())) {
            //in listing and on home controller display the accordion on the right side
            case "index":
                if ($isEmotion) {
                    $view->BonusSliderPerPage = 4;
                } else {
                    $view->BonusSliderPerPage = 3;
                }

                $view->extendsTemplate("frontend/plugins/swag_bonus_system/index/right.tpl");
                $view->extendsTemplate("frontend/plugins/swag_bonus_system/home/index.tpl");
                break;
            case "listing":
                $view->extendsTemplate("frontend/plugins/swag_bonus_system/listing/right.tpl");
                break;
            case "custom":
                if (strpos($view->sContent, "sBonusSystem")> 0) {
                    $view->sContent = $view->fetch('string:{eval var=$sContent}');
                }
                break;

            //in the account and bonus points order listing of the user extend the right account navigation
            case "account";
            case "bonussystem";
            case "bonus_system":
                $view->extendsTemplate("frontend/plugins/swag_bonus_system/account/content_right.tpl");
                $view->extendsTemplate("frontend/plugins/swag_bonus_system/account/orders.tpl");
                break;

            //in the checkout controller the slider jquery must be loaded and the different template must be extended
            case "checkout":
                $view->BonusSliderPerPage = 4;

                //if the user logged in and bonus voucher active display the slider row
                if ($this->Application()->Session()->sUserId > 0 && $settings["bonus_voucher_active"] == 1) {
                    //load slider jquery and css
                    $view->extendsTemplate("frontend/plugins/swag_bonus_system/basket/slider.tpl");
                }

                //extend the cart items
                $view->extendsTemplate("frontend/plugins/swag_bonus_system/basket/cart.tpl");

                //case for the action name
                switch (strtolower($request->getActionName())) {
                    case "confirm":
                        //extend the confirm items
                        $view->extendsTemplate("frontend/plugins/swag_bonus_system/basket/confirm.tpl");
                        break;
                    case "finish":
                        $view->assign('isFinish', true);
                        $view->extendsTemplate("frontend/plugins/swag_bonus_system/basket/finish.tpl");
                        break;
                }
                break;

            case "detail":
                //check if the displayed article is defined as bonus article
                $article = $view->sArticle;

                $price = str_replace(",", ".", $article["price"]);
                $current = $price / $settings["bonus_point_conversion_factor"];

                if ($current > 1) {
                    $current = round($current);
                } else {
                    $current = 0;
                }

                $article["earning_points_current"]  = $current;
                $article["earning_points_per_unit"] = $price / $settings["bonus_point_conversion_factor"];

                $article["required_points"]         = $model->getRequiredPointsOfArticle($article["articleID"]);

                //if true display the radio group to buy the article for euro or bonus points
                $view->sDisplayRadios = ($model->points["remaining"] >= $article["required_points"]) && ($article["required_points"]);

                //return the modified article
                $view->sArticle = $article;

                //extend template to display the radio groups
                $view->extendsTemplate("frontend/plugins/swag_bonus_system/detail/buy.tpl");
        }
    }

    /**
     * BeforeRender Event on the document class to modify the order positions display.
     * For each order position the bonus system flags will be set and the own template will be loaded.
     *
     * @param  Enlight_Hook_HookArgs $args
     * @return void
     */
    public function onBeforeRenderDocument(Enlight_Hook_HookArgs $args)
    {
        $document  = $args->getSubject();
        $view      = $document->_view;
        $pages     = $view->getTemplateVars('Pages');
        $orderData = $view->getTemplateVars('Order');
        $content   = array();

        foreach ($pages as $page) {
            foreach ($page as $position) {
                $content[] = $position;
            }
        }

        $order = array(
            "content" => $content,
            "Amount" => $orderData["_order"]["invoice_amount"] - $orderData["_order"]["invoice_shipping"]
        );

        $sql = "SELECT * FROM s_core_plugins_bonus_order WHERE orderID = ?";
        $bonusOrder = $this->Application()->Db()->fetchRow($sql, array($orderData["_order"]["id"]));

        if (empty($bonusOrder)) {
            return;
        }

        foreach ($order['content'] as &$item) {
            if (isset($item['attributes']['swag_bonus'])) {
                $item['swag_bonus'] = $item['attributes']['swag_bonus'];
            }
        }

        $model = new BonusSystemDataModel($order);
        $order["content"] = $model->setBasketContentFlags($order["content"]);
        $model->setBasket($order);

        foreach ($pages as $pageIndex => $page) {
            foreach ($page as $positionIndex => $position) {
                if (isset($position['attributes']['swag_bonus'])) {
                    $pages[$pageIndex][$positionIndex]['tax'] = floatval($position['tax']);
                    $pages[$pageIndex][$positionIndex]['swag_bonus'] = $position['attributes']['swag_bonus'];
                }
                $pages[$pageIndex] = $model->setBasketContentFlags($pages[$pageIndex]);
            }
        }

        $view->assign('Points', $model->getPoints());
        $view->assign('Pages', $pages);
        $dir = $document->_template->getTemplateDir();
        array_unshift($dir, dirname(__FILE__) . '/Views/');
        $document->_template->setTemplateDir($dir);
    }

    /**
     * If the user select the option to buy the article for bonus points execute the internal function, otherwise call the parent function
     *
     * @param Enlight_Event_EventArgs $args
     * @return null
     */
    public function onAddArticle(Enlight_Event_EventArgs $args)
    {
        $subject     = $args->getSubject();
        $buyFor      = $subject->Request()->getParam('buy_for');
        $orderNumber = $subject->Request()->getParam('sAdd');
        $quantity    = $subject->Request()->getParam('sQuantity');

        if (!$quantity) {
            $quantity = 1;
        }

        // No bonus article. Proceed normal;
        if (empty($buyFor)) {
            return;
        }

        $basket = $this->isBonusArticleInBasket($orderNumber);

        // buy as normal article, further checks required
        if ($buyFor == "euro") {
            if (empty($basket)) {
                // Bonus artikle or not in basket. Proceed normal;
                return;
            } else {
                // delete bonus artikel is in basket
                $sql = "DELETE b, ba FROM s_order_basket b LEFT JOIN s_order_basket_attributes ba ON ba.basketID = b.id WHERE b.id = ? AND b.sessionID = ?";
                $this->Application()->Db()->query($sql, array($basket['id'], $this->Application()->SessionID()));
                return;
            }
        }

        // buy for bonus points
        if (!empty($basket)) {
            // if bonus artikel is in basket delete and increse basket
            $sql = "DELETE b, ba FROM s_order_basket b LEFT JOIN s_order_basket_attributes ba ON ba.basketID = b.id WHERE b.id = ? AND b.sessionID = ?";
            $this->Application()->Db()->query($sql, array($basket['id'], $this->Application()->SessionID()));

            $quantity += $basket['quantity'];
        }

        // delete normal artikel is in basket
        $sql = "DELETE b, ba FROM s_order_basket b LEFT JOIN s_order_basket_attributes ba ON ba.basketID = b.id WHERE b.ordernumber = ? AND b.sessionID = ?";
        $this->Application()->Db()->query($sql, array($orderNumber, $this->Application()->SessionID()));

        $model   = new BonusSystemDataModel();
        $article = $model->addBonusArticleToBasket($orderNumber, $quantity);

        //set article data
        $subject->View()->sArticle     = $article;
        $subject->View()->sArticleName = $article["articleName"];
        $subject->View()->sSum         = $article["required_points"];
        $subject->View()->sQuantity    = $quantity;
        $subject->View()->sBasketInfo  = $subject->getInstockInfo($orderNumber, $quantity);

        //load Template
        Enlight()->Plugins()->Controller()->Json()->setPadding();

        $subject->View()->loadTemplate("frontend/plugins/swag_bonus_system/basket/ajax_add_article.tpl");
        return true;
    }

    /**
     * Checks if the article is already for bonus points in the basket
     *
     * @param $ordernumber
     * @return mixed
     */
    private function isBonusArticleInBasket($ordernumber)
    {
        $sql = "SELECT basket.*, attributes.swag_bonus
        FROM s_order_basket basket
        LEFT JOIN s_order_basket_attributes attributes ON attributes.basketID = basket.id
        WHERE basket.ordernumber = ? AND basket.sessionID = ? AND attributes.swag_bonus = 1";

        $article = $this->Application()->Db()->fetchRow($sql, array($ordernumber, $this->Application()->SessionID()));

        return $article;
    }

    /**
     * sAddVoucher function of the shopware checkout controller
     * which called when the user manuel inserts a voucher code and click on add.
     * If the user tries to add a bonus voucher cancel the action.
     *
     * @param  Enlight_Event_EventArgs $args
     * @return bool
     */
    public function onAddVoucher(Enlight_Event_EventArgs $args)
    {
        $subject = $args->getSubject();
        $voucher = $subject->Request()->get("sVoucher");

        if (substr($voucher, 0, 12) == 'bonusvoucher') {
            $subject->forward($subject->Request()->getParam('sTargetAction', 'index'));

            return false;
        }
    }

    /**
     * Overwrite the ajax amount action to load own template.
     *
     * @param  Enlight_Event_EventArgs $args
     * @return bool
     */
    public function onAjaxAmount(Enlight_Event_EventArgs $args)
    {
        $view = $args->getSubject()->View();

        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() > 1;
        if ($isEmotion) {
            $template = '_emotion';
        } else {
            $template = '_default';
        }

        $view->loadTemplate(dirname(__FILE__) .  "/Views/{$template}/frontend/plugins/swag_bonus_system/basket/ajax_amount.tpl");
    }

    /**
     * sUpdateArticle function of the shopware basket class. For bonus articles this function shouldn't be executed.
     * @param  Enlight_Event_EventArgs $args
     * @return bool
     */
    public function onUpdateArticle(Enlight_Event_EventArgs $args)
    {
        $id       = $args->get('id');
        $quantity = $args->get('quantity');

        $sql = "SELECT attributes.swag_bonus
        FROM s_order_basket basket
        LEFT JOIN s_order_basket_attributes attributes ON attributes.basketID = basket.id
        WHERE basket.id = ?";

        $isBonusArticle = $this->Application()->Db()->fetchOne($sql, array($id));

        if (!empty($isBonusArticle) && $isBonusArticle == 1) {
            //update the point attributes which can be set in the plugin config
            $sql = "UPDATE s_order_basket
                    SET quantity = ?
                    WHERE id = ?";

            $this->Application()->Db()->query($sql, array($quantity, $id));

            $sql = "UPDATE s_order_basket_attributes
                    SET swag_bonus = 1
                    WHERE basketID = ?";

            $this->Application()->Db()->query($sql, array($id));

            return true;
        }
    }

    /**
     * Extends the standard function "sBasket->sGetBasket" to consider bonus articles and bonus vouchers
     *
     * @param  Enlight_Hook_HookArgs $args
     * @return void
     */
    public function afterGetBasket(Enlight_Hook_HookArgs $args)
    {
        $basket = $args->getReturn();

        $sql = "SELECT swag_bonus FROM s_order_basket_attributes WHERE basketID = ?";

        foreach ($basket['content'] as &$item) {
            $bonus = $this->Application()->Db()->fetchOne($sql, array($item['id']));
            $item['swag_bonus'] = $bonus;
        }

        $model = new BonusSystemDataModel();
        $basket["content"] = $model->setBasketContentFlags($basket["content"]);

        $args->setReturn($basket);
    }

    /**
     * @param  Enlight_Event_EventArgs $args
     * @return bool
     */
    public  function onOrderSaveOrderProcessDetails(Enlight_Event_EventArgs $args)
    {
        $basketContent = $args->getDetails();
        $order = $args->getSubject();
        $orderNumber = $order->sOrderNumber;

        foreach ($basketContent as $article) {
            if ($article['swag_bonus']) {
                $articleOrderNumber = $article['ordernumber'];

                $sql = "SELECT id
                        FROM `s_order_details`
                        WHERE `ordernumber` LIKE ?
                        AND `articleordernumber` LIKE ?";

                $orderDetailId = $this->Application()->Db()->fetchOne($sql, array($orderNumber, $articleOrderNumber));

                $sql = "UPDATE `s_order_details_attributes` set `swag_bonus` = 1 WHERE `detailID` = ?";
                $this->Application()->Db()->query($sql, array($orderDetailId));
            }
        }
    }

    /**
     * This function update the user bonus points score
     *
     * @param  Enlight_Hook_HookArgs $args
     * @return void
     */
    public function onSaveOrder(Enlight_Hook_HookArgs $args)
    {
        $orderNumber = $args->getReturn();

        $model = new BonusSystemDataModel();
        $model->finishOrder($orderNumber);
    }

    /**
     * Cron job to iterate all orders with bonus points.
     * For each order the payment status will checked if its payed and unlock the bonus points of the order.
     *
     * @param  \Shopware_Components_Cron_CronJob $job
     * @return bool
     */
    public function onRunUnlockBonusPoints(\Shopware_Components_Cron_CronJob $job)
    {
        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $query = $repository->getBaseListQuery();
        $shops = $query->getArrayResult();

        foreach ($shops as $shop) {
            $shopObject = $repository->find($shop['id']);
            $shopObject->registerResources(Shopware()->Bootstrap());

            $model    = new BonusSystemDataModel();
            $settings = $model->getBonusSystemSettings();

            if (empty($settings)) {
                continue;
            }

            $orders = array();

            switch ($settings["bonus_point_unlock_type"]) {
                case "paid":
                    //select all orders which are payed
                    $sql = "SELECT s_order.id,
                                   s_core_plugins_bonus_order.userID,
                                   s_core_plugins_bonus_order.points,
                                   s_core_plugins_bonus_order.id as bonusId
                        FROM s_core_plugins_bonus_order, s_order
                        WHERE s_core_plugins_bonus_order.orderID = s_order.id
                        AND s_order.subshopID = ?
                        AND s_order.cleared = 12
                        AND s_core_plugins_bonus_order.approval = 0";
                    $orders = $this->Application()->Db()->fetchAll($sql, array($shop['id']));

                    break;

                case "day":
                    $sql = "SELECT s_order.id,
                                   s_core_plugins_bonus_order.userID,
                                   s_core_plugins_bonus_order.points,
                                   now(),
                                   s_core_plugins_bonus_order.id as bonusId
                        FROM s_core_plugins_bonus_order,
                             s_order
                        WHERE s_core_plugins_bonus_order.orderID = s_order.id
                        AND s_order.subshopID = ?
                        AND s_core_plugins_bonus_order.approval = 0
                        AND DATEDIFF(DATE(now()), DATE(s_order.ordertime)) >= ?";
                    $orders = $this->Application()->Db()->fetchAll($sql, array($shop['id'], $settings["bonus_point_unlock_day"]));
                    break;
            }

            // iterate the order
            foreach ($orders as $order) {
                $model->unlockBonusPoints($order["id"], $order['points'], $order["userID"]);
            }

            echo "<br>ShopId " . $shop['id'] . ": Es wurden bei " . count($orders) . " Bestellungen die Bonuspunkte freigeschaltet.<br><br>";
        }

        return true;
    }
}
