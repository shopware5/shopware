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
 * Shopware Trusted Shops Excellence Plugin - Bootstrap
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagTrustedShopsExcellence
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Frontend_SwagTrustedShopsExcellence_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

	####################################################################################################################
	# Plugins install methods ##########################################################################################
	####################################################################################################################

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
            'version'     => $this->getVersion(),
            'label'       => $this->getLabel(),
            'link'        => 'http://www.shopware.de/',
            'description' => file_get_contents($this->Path() . 'info.txt')
        );
    }

    /**
     * Returns the version of the plugin as a string
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.1.2';
    }

    /**
     * Returns the well-formatted name of the plugin
     * as a sting
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Trusted Shops Excellence';
    }


	/**
	 * Plugin install method to subscribe all required events.
	 *
	 * @access public
	 * @return bool success
	 */
	public function install()
	{
		$this->createEvents();

		$this->createCronJobs();
		
		$this->createForm();

		$this->createDatabaseTables();

		return true;
	}

    /**
     * Plugin update method to handle the update process
     *
     * @param string $oldVersion
     * @return bool|void
     */
    public function update($oldVersion)
    {
        $this->createForm();

        //get the latest trusted shops items
        $this->updateTrustedShopsProtectionItems();

        return true;
    }
	/**
	 * This function subscribes all required events
	 * @return void
	 */
	private function createEvents()
	{
		$this->subscribeEvent('Enlight_Controller_Action_PostDispatch', 'onPostDispatch');

		//Creates the Frontend-controller TrustedShops
		$this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_TrustedShops', 'onGetControllerPath_Frontend_TrustedShops');

		//Creates the Frontend-controller TrustedShops
		$this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_TrustedShops', 'onGetControllerPath_Backend_TrustedShops');


        $this->subscribeEvent('sOrder::sSaveOrder::after','onSaveOrder');

	}

	/**
	 * This function creates all required cron jobs
	 * @return void
	 */
	private function createCronJobs()
	{
        //Creates the TrustedShops Cronjob to get the latest trusted shop products
        $this->subscribeEvent('Shopware_CronJob_TSGetLatestProtectionItems', 'onRunTSGetLatestProtectionItems');
        $this->createCronJob("TSGetLatestProtectionItems", "TSGetLatestProtectionItems");

		//Creates the TrustedShops Cronjob to check the order state of the pending orders
        $this->subscribeEvent('Shopware_CronJob_TSCheckOrderState', 'onRunTSCheckOrderState');
		$this->createCronJob("TSCheckOrderState", "TSCheckOrderState", 3600);

		//Creates the TrustedShops event to get the latest trusted shops products
        $this->subscribeEvent('Shopware_CronJob_TSGetRatingImage', 'onRunTSGetRatingImage');
		$this->createCronJob("TSGetRatingImage", "TSGetRatingImage");
	}

	/**
	 * This function creates the database table for the buyer protection articles
	 * @return void
	 */
	private function createDatabaseTables()
	{
		//creates new database table for the trusted shops orders this table should not be deleted on uninstall
		$sql = "CREATE TABLE IF NOT EXISTS `s_plugin_swag_trusted_shops_excellence_orders` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`ordernumber` varchar(30) NOT NULL,
					`ts_applicationId` varchar(30) NOT NULL,
					`status` int(1) DEFAULT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";
		Shopware()->Db()->query($sql);

		$sql = "SELECT MAX(id) FROM s_core_states";
		$id = Shopware()->Db()->fetchOne($sql);

		$sql = "INSERT INTO s_core_states (`id`, `description`, `position`, `group`, `mail`) VALUES (?,?,?,?,?)";
		Shopware()->Db()->query($sql, array($id + 1, "TS - Antrag in Bearbeitung", 100, "state", 0));
		Shopware()->Db()->query($sql, array($id + 2, "TS - Antrag erfolgreich", 100, "state", 0));
		Shopware()->Db()->query($sql, array($id + 3, "TS - Antrag fehlgeschlagen", 100, "state", 0));
		
	}

	/**
	 * This function creates the plugin form for the required parameters.
	 * @return void
	 */
	private function createForm()
	{
		//creates the standard plugin form
		$form = $this->Form();
		$form->setElement('textarea', 'tsSeal', array('label' => 'Trusted Shop Siegel', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->setElement('text', 'tsSealBlock', array('label' => 'Smarty Block f&uuml;r das Trusted Shop Siegel', 'value' => 'frontend_index_left_menu', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->setElement('text', 'tsEID', array('label' => 'Trusted Shop ID', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));

		//trusted shop connection settings
		$form->setElement('text', 'tsWebServiceUser', array('label' => 'Trusted User', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->setElement('text', 'tsWebServicePassword', array('label' => 'Trusted User Password', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));

		//activate or deactivates the test or live system call to trusted shops
		$form->setElement('checkbox', 'testSystemActive', array('label' => 'Test System aktivieren', 'value' => '0'));

		//activate or deactivates the rating widget of trusted shops
		$form->setElement('checkbox', 'ratingActive', array('label' => 'Trusted Shop Rating aktivieren', 'value' => '1'));

        $form->setElement('button', 'Test Connection', array('label'=>'Verbindung, Login und Trusted Shop Zertifikat testen',
            'handler'=>'function (){
                Ext.Ajax.request({
                   scope:this,
                   url: window.location.pathname+"TrustedShops/testConnection",
                   success: function(result,request) {
                         var jsonResponse = Ext.JSON.decode(result.responseText);
                        Shopware.Notification.createGrowlMessage(\'\',jsonResponse.message, \'SwagTrustedShopsExcellence\');
                   },
                   failure: function() {
                        Ext.MessageBox.alert(\'Ups\',\'Url not reachable\');
                   }
                });
             }'
        ));

		//controller button to import the buyer protection articles
        $form->setElement('button', 'Import Action', array('label'=>'Trusted Shop Artikel und Rating Image importieren (Trusted Shop ID, Web Service User und Passwort ben&ouml;tigt)',
            'handler'=>'function (){
                Ext.Ajax.request({
                   scope:this,
                   url: window.location.pathname+"TrustedShops/importBuyerProtectionItems",
                   success: function(result,request) {
                         var jsonResponse = Ext.JSON.decode(result.responseText);
                        Shopware.Notification.createGrowlMessage(\'\',jsonResponse.message, \'SwagTrustedShopsExcellence\');
                   },
                   failure: function() {
                        Ext.MessageBox.alert(\'Ups\',\'Url not reachable\');
                   }
                });
             }'
        ));
	}

	/**
	 * Standard uninstall method which removes the trusted shops db tables
	 * @return boolean
	 */
	public function uninstall()
	{
		$sql = "DELETE FROM s_core_states WHERE description LIKE 'TS - Antrag%'";
		Shopware()->Db()->query($sql);

		return parent::uninstall();
	}


	####################################################################################################################
	# Plugins controller methods########################################################################################
	####################################################################################################################

	/**

	 * Returns the controller path of the frontend controller.
	 * @param Enlight_Event_EventArgs $args
	 * @return string
	 */
	public function onGetControllerPath_Frontend_TrustedShops(Enlight_Event_EventArgs $args)
	{
		return dirname(__FILE__) . '/Controllers/Frontend/TrustedShops.php';
	}

	/**
	 * Returns the controller path of the backend controller.
     *
	 * @param Enlight_Event_EventArgs $args
	 * @return string
	 */
	public function onGetControllerPath_Backend_TrustedShops(Enlight_Event_EventArgs $args)
	{
		return dirname(__FILE__) . '/Controllers/Backend/TrustedShops.php';
	}

    public function getDataModel()
    {
        static $model;
        if(!isset($model)) {
            require_once($this->Path() . 'Models/TrustedShopsDataModel.php');
            $model = new TrustedShopsDataModel();
        }
        return $model;
    }

	####################################################################################################################
	# Plugins event and hook methods####################################################################################
	####################################################################################################################
	/**
	 * If the user confirm the order, this function sends the buyer protection request
     *
	 * @param Enlight_Hook_HookArgs $args
	 * @return void
	 */
	public function onSaveOrder(Enlight_Hook_HookArgs $args)
	{
		set_time_limit(1000);

		$orderSubject = $args->getSubject();
		$article = $this->isTsArticleInOrder($orderSubject);
		if(!empty($article)) {
			$config = $this->getTrustedShopBasicConfig();

			$tsDataModel = $this->getDataModel();
			$returnValue = $tsDataModel->sendBuyerProtectionRequest($orderSubject,$article["ordernumber"]);

			if(is_int($returnValue) && $returnValue > 0) {
				/*
				 * Inserts the order to the trusted shops order table
				 * The Status will be updated in the cronjob onRunTSCheckOrderState
				 * Status Description:
				 * Status 0 Pending
				 * Status 1 Success
				 * Status 3 Error
				 */

				$sql= "INSERT INTO `s_plugin_swag_trusted_shops_excellence_orders`
						(`ordernumber`, `ts_applicationId`, `status`)
						VALUES (?,?,0)";
				Shopware()->Db()->query($sql, array($orderSubject->sOrderNumber, $returnValue));

				$comment = $config["stateWaiting"]["description"];
				$status = $config["stateWaiting"]["id"];

			} else {
				//failed
				$comment = $config["stateError"]["description"];
				$status = $config["stateError"]["id"];
			}

			$sql = "UPDATE s_order SET internalcomment = ?, status = ? WHERE ordernumber = ?";
			Shopware()->Db()->query($sql, array($comment, $status, $orderSubject->sOrderNumber));
		}
	}

    /**
     * Global post dispatch limited to the frontend module.
     * 
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
	public function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		/** @var $subject Enlight_Controller_Action */
		$subject = $args->getSubject();
		$view = $subject->View();
		$request = $subject->Request();
		$response = $subject->Response();
		$validControllers = array("index", "checkout", "listing");

		if(!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || !in_array(strtolower($request->getControllerName()), $validControllers)) {
			return;
		}

        //get basic config of trusted shop for the seal template and trusted shop id
		$config = $this->getTrustedShopBasicConfig();

        $view->isEmotionTemplate = Shopware()->Shop()->get('esi');
		//if the current controller is the checkout controller extend the config for the basket
		if(strtolower($request->getControllerName()) == "checkout") {
			if(strtolower($request->getActionName()) != "finish") {
				$this->controlBasketTsArticle($subject, $request);
			}
			$basketConfig = $this->getTrustedShopBasketConfig($subject);
			$config = array_merge($basketConfig, $config);
		}

        $basket = $view->sBasket;
        foreach($basket['content'] as &$article){
            $explode = explode('_',$article['ordernumber']);
            if(count($explode) == 4 && substr($explode[0],0,2) == 'TS') {
                $article['trustedShopArticle']= true;
            }
        }
        $view->sBasket = $basket;

		//extend template
		$view->sTrustedShop = $config;
		$view->addTemplateDir(dirname(__FILE__) . '/Views/');

		$view->extendsTemplate('frontend/plugins/swag_trusted_shops_excellence/index/index.tpl');
		$view->extendsBlock($config["block"], $config["seal"], 'prepend');
		$view->extendsTemplate('frontend/plugins/swag_trusted_shops_excellence/checkout/cart.tpl');
		if(strtolower($request->getActionName()) == "finish") {
			$view->extendsTemplate('frontend/plugins/swag_trusted_shops_excellence/checkout/finish.tpl');
		}
	}



	####################################################################################################################
	# Trusted Shops Cronjobs ###########################################################################################
	####################################################################################################################
	/**
	 * Imports an Update the latest trusted Shops ProtectionsItems
	 *
	 * @param \Shopware_Components_Cron_CronJob $job
	 * @return void
	 */
	public function onRunTSGetLatestProtectionItems(Shopware_Components_Cron_CronJob $job)
	{
		$this->prepareCronJob($job);
		
		//updates the TS Shop ProtectionItems
		$this->updateTrustedShopsProtectionItems();
		return true;
	}

	/**
	 * Checks the status of the trusted shops orders
	 *
	 * Status Description:
	 * Status 0 Pending
	 * Status 1 Success
	 * Status 3 Error
	 *
	 * @param \Shopware_Components_Cron_CronJob $job
	 * @return void
	 */
	public function onRunTSCheckOrderState(Shopware_Components_Cron_CronJob $job)
	{
		$this->prepareCronJob($job);
		$sql= "SELECT * FROM `s_plugin_swag_trusted_shops_excellence_orders` WHERE `status` = 0";
		$trustedShopOrders = Shopware()->Db()->fetchAll($sql);

		if(empty($trustedShopOrders)) {
			return true;
		}

		//get plugin basic config
		$config = $this->getTrustedShopBasicConfig();
		$tsDataModel = $this->getDataModel();

		//iterate the open trusted shop orders
		foreach($trustedShopOrders as $order) {
			$returnValue = $tsDataModel->getRequestState(array($config["id"], $order["ts_applicationId"]));
			switch(true) {
				case ($returnValue==0):
					$comment = $config["stateWaiting"]["description"];
					$status = $config["stateWaiting"]["id"];
					break;
				case ($returnValue>0):
					$comment = $config["stateSuccess"]["description"] . ' / Garantie-Nr.: ' . $returnValue;
					$status = $config["stateSuccess"]["id"];
					break;
				default:
					$comment = $config["stateError"]["description"];
					$status = $config["stateError"]["id"];
					break;
			}
			echo '<br>' . $order["ordernumber"] . ':  ' . $comment . '<br>';
			
			$sql = "UPDATE s_order SET status = ?, internalcomment = ? WHERE ordernumber = ?";
			Shopware()->Db()->query($sql, array($status, $comment, $order["ordernumber"]));
			
			$sql = "UPDATE s_plugin_swag_trusted_shops_excellence_orders SET status = ? WHERE id = ?";
			Shopware()->Db()->query($sql, array($returnValue, $order["id"]));
		}
		return true;

	}

	/**
	 * Imports an Update the trusted shop rating image
	 *
	 * @param \Shopware_Components_Cron_CronJob $job
	 * @return void
	 */
	public function onRunTSGetRatingImage(Shopware_Components_Cron_CronJob $job)
	{
		$this->prepareCronJob($job);
		$success = $this->importTsRatingImage();

		if(empty($success)) {
			echo 'Das Trusted Shop Rating Image konnte nicht importiert werden!<br><br>';
		} else {
			echo 'Das Trusted Shop Rating Image wurde importiert!<br><br>';
		}
		return true;
	}



	####################################################################################################################
	# Plugin helper functions #########################################################################################
	####################################################################################################################

	/**
	 * This function updates the cron job times. Through this the cron job will always be active
	 * @param Shopware_Components_Cron_CronJob $job
	 * @return void
	 */
	protected function prepareCronJob($job)
	{
		set_time_limit(1000);
		echo '<br>';
		$end = new Zend_Date($job->get("end"));
		$start = new Zend_Date($job->get("start"));
		$next = new Zend_Date($job->get("next"));
		$sql= "UPDATE s_crontab SET `end` = ?, `start` = ?, `next` = ? WHERE id = ?";
		Shopware()->Db()->query($sql, array($end, $start, $next, $job->get("id")));
	}

    /**
     * This function updates the buyer protection article in the database
     * @throws Exception
     * @return Array | protection items
     */
	public function updateTrustedShopsProtectionItems()
	{
		$tsDataModel = $this->getDataModel();
		$TsProducts = $tsDataModel->getProtectionItems();
		foreach($TsProducts->item as $product) {
            $articleData = array(
                'name' => 'Käuferschutz',
                'active' => true,
                'tax' => 19,
                'supplier' => 'Trusted Shops',
                'mainDetail' => array(
                    'number' => $product->tsProductID,
                    'active' => true,
                    'instock' => 0,
                    'attribute' => array(
                        'attr19' => $product->protectedAmountDecimal,
                        'attr20' => $product->protectionDurationInt
                    ),
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => $product->grossFee,
                        ),
                    )
                )
            );
            $articleResource = \Shopware\Components\Api\Manager::getResource('article');
            try {
                $articleDetailRepostiory = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');
                $articleDetailModel = $articleDetailRepostiory->findOneBy(array('number' => $articleData['mainDetail']['number']));
                if ($articleDetailModel) {
                    $articleModel = $articleDetailModel->getArticle();
                }
                if ($articleModel) {
                    $articleResource->update($articleModel->getId(), $articleData);
                } else  {
                    $articleResource->create($articleData);
                }
            } catch (\Shopware\Components\Api\Exception\ValidationException $ve) {
                $errors = array();
                /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
                foreach ($ve->getViolations() as $violation) {
                    $errors[] = sprintf(
                        '%s: %s',
                        $violation->getPropertyPath(),
                        $violation->getMessage()
                    );
                }
                throw new \Exception(implode(', ', $errors));
            }
		}

		return $TsProducts->item;
	}

	/**
	 * This function maps the shopware payments with the trusted shops payments
	 * @param $payment
	 * @return string
	 */
	public function getTsPaymentCode($payment)
	{
		switch(strtolower($payment)) {
			case "cash":
				return "CASH_ON_DELIVERY";

			case "clickandbuy":
				return "CLICKANDBUY";

			case "debit":
			case "sofortlastschrift_multipay":
			case "lastschriftbysofort_multipay":
			case "heidelpay_dd":
			case "heidelpay_sue":
			case "heidelpay_gir":
				return "DIRECT_DEBIT";

			case "invoice":
			case "sofortrechnung_multipay":
			case "billsafe_invoice":
			case "PaymorrowInvoice":
			case "heidelpay_iv":
			case "KlarnaInvoice":
				return "INVOICE";

			case "ipayment":
			case "skrill":
			case "BuiswPaymentPayone":
			case "heidelpay_cc":
			case "heidelpay_dc":
			case "heidelpay_ide":
				return "CREDIT_CARD";

			case "moneybookers":
				return "OTHER";

			case "paypalexpress":
			case "heidelpay_pay":
				return "PAYPAL";

			case "prepayment":
			case "vorkassebysofort_multipay":
			case "heidelpay_pp":
				return "PREPAYMENT";

			case "saferpay":
				return "CREDIT_CARD";

			case "sofortueberweisung":
			case "sofortueberweisung_multipay":
				return "DIRECT_E_BANKING";

			default:
				return "OTHER";
		}
	}

    /**
     * This function adds the given buyer protection article into the basket
     * @param array $article
     * @internal param $ {array} $article
     * @return void
     */
	public function addBuyerProtection(Array $article)
	{
		if($this->isTsArticleInBasket()) {
			return;
		}
		$user = Shopware()->Modules()->Admin()->sGetUserData();
		$userID = $user["billingaddress"]["userID"];
		if(!$userID) {
			$userID = 0;
		}
		$sql = "INSERT INTO s_order_basket (sessionID, userID, articlename, articleID, ordernumber, shippingfree, quantity, price, netprice, modus) VALUES(?,?,?,?,?,?,?,?,?,4)";
		Shopware()->Db()->query($sql, array(Shopware()->SessionID(), $userID, "Käuferschutz", $article["id"], $article["tsProductID"], 0, 1, $article["grossFee"], $article["netFee"]));
	}

	/**
	 * Checks if the user has already the trusted shop article in basket
	 * @return bool
	 */
	public function isTsArticleInBasket()
	{
		$sql = "SELECT * FROM s_order_basket WHERE SUBSTR(ordernumber,1,2) = 'TS' AND SUBSTR(ordernumber, LENGTH(ordernumber)-2) = ? AND sessionID = ?";

		$exist = Shopware()->Db()->fetchRow($sql, array(Shopware()->System()->sCurrency["currency"], Shopware()->SessionID()));
		return $exist;
	}

	/**
	 * This function updated and download the trusted shop rating image.
	 * @return string | image data
	 */
	public function importTsRatingImage()
	{
		$config = $this->getTrustedShopBasicConfig();

		$tsDataModel = $this->getDataModel();
		$params = array("tsId" => $config["id"], "activation" => 1, "wsUser" => $config["user"], "wsPassword" => $config["pw"], "partnerPackage" => "");
		$tsDataModel->updateRatingWidgetState($params);

		$imagePath = Shopware()->DocPath() . 'images/ts_rating.gif';
		$image = file_get_contents("https://www.trustedshops.com/bewertung/widget/widgets/" . $config["id"] . ".gif");
		file_put_contents($imagePath, $image);
		return $image;
	}

	/**
	 * Returns the article data for the trusted shop article calculated by the current basket amount
	 * @param $amount
	 * @return string
	 */
	public function getTsArticleByAmount($amount)
	{
        if(empty($amount)){
            return array();
        }
        $currency = Shopware()->System()->sCurrency["currency"];
        $sql = "
                SELECT a.id as id
                FROM s_articles a
                INNER JOIN s_articles_attributes aa ON a.id = aa.articleID
                INNER JOIN s_articles_details ad ON a.id = ad.articleID

                WHERE attr19+0 >= ? AND ordernumber LIKE ? ?
        ";
        $ids = Shopware()->Db()->fetchCol($sql, array($amount, '%', $currency));
        $idString=implode(',', $ids);

        $sql = "
            SELECT a.id as id,
            REPLACE(ROUND(ap.price*(100+t.tax)/100,2),'.',',') as grossFee,
            ROUND(ap.price,2) as netFee,
            attr19 as protectedAmountDecimal,
            attr20 as protectionDurationInt,
            ordernumber as tsProductID

            FROM s_articles a
            INNER JOIN s_articles_attributes aa ON a.id = aa.articleID
            INNER JOIN s_articles_details ad ON a.id = ad.articleID
            INNER JOIN s_core_tax t ON t.id=a.taxID
            INNER JOIN s_articles_prices ap ON a.id = ap.articleID
                AND ap.pricegroup='EK' AND `from`=1

            WHERE a.id IN(?)
            ORDER BY attr19+0 ASC LIMIT 1
        ";
        $nextProtectionItem = Shopware()->Db()->fetchRow($sql, array($idString));
        $nextProtectionItem['currency'] = substr($nextProtectionItem['tsProductID'], strlen($nextProtectionItem['tsProductID'])-3);

        return $nextProtectionItem;
	}

	/**
	 * Helper to get the plugin config
	 * @return object | plugin config
	 */
	protected function pluginConfig(){
		return Shopware()->Plugins()->Frontend()->SwagTrustedShopsExcellence()->Config();
	}

	/**
	 * This function returns the rating link for trusted shops.
	 * @param $tsID
	 * @return string
	 */
	protected function getRatingLink($tsID)
	{
        $isoCode = Shopware()->Locale()->toString();
		switch($isoCode) {
			case "de_DE":
				return "https://www.trustedshops.de/bewertung/info_". $tsID . ".html";
			case "fr_FR":
				return "https://www.trustedshops.fr/evaluation/info_". $tsID . ".html";
			case "pl_PL":
				return "https://www.trustedshops.pl/opinia/info_". $tsID . ".html";
			default:
				return "https://www.trustedshops.com/buyerrating/info_". $tsID . ".html";
		}
	}

    /**
     * This function controls the buyer protection item in the basket.
     * @param $controller
     * @param $request
     * @return void
     */
	protected function controlBasketTsArticle(&$controller, $request)
	{
		//get total basket amount
		$amount = Shopware()->Modules()->sBasket()->sGetAmount();
		$shippingCost = $controller->getShippingCosts();
		$amount = $amount["totalAmount"] + $shippingCost["value"];
		$basketArticle = $this->isTsArticleInBasket();
        //Always use the brutto-value
        if($controller->View()->sAmountWithTax){
            $amount = $controller->View()->sAmountWithTax;
        }
		if(empty($basketArticle)) {
			return;
		}
		if($amount > 0) {
            //get trusted shop article data
            $toAddArticle = $this->getTsArticleByAmount($amount);
            if($toAddArticle["tsProductID"] == $basketArticle["ordernumber"]) {
                return;
            }
        }

		$sql = "DELETE FROM s_order_basket WHERE id = ? AND sessionID = ?";
		Shopware()->Db()->query($sql, array($basketArticle["id"], Shopware()->SessionID()));
		$controller->View()->sTsArticleRemoved = true;
		$controller->forward($request->getActionName());
	}

	/**
	 * Returns the trusted shop id and the seal template
	 * @return array
	 */
	protected function getTrustedShopBasicConfig()
	{
		$config = $this->pluginConfig();

		$sql = "SELECT id, description FROM s_core_states WHERE description LIKE 'TS - Antrag%' ORDER BY id";
		$states = Shopware()->Db()->fetchAll($sql);

		//set trusted shop parameters
		$trustedShop = array("seal" => $config->tsSeal,
							 "block" => $config->tsSealBlock,
							 "id" => $config->tsEID,
							 "rating_active" => $config->ratingActive,
							 "rating_link" => $this->getRatingLink($config->tsEID),
							 "user" => $config->tsWebServiceUser,
							 "pw" => $config->tsWebServicePassword,
							 "stateWaiting" => $states[0],
							 "stateSuccess" => $states[1],
							 "stateError" => $states[2],
							);

		return $trustedShop;
	}

	/**
	 * Returns the trusted shop article data for the checkout controller.
	 * @param $checkoutController
	 * @return array
	 */
	protected function getTrustedShopBasketConfig($checkoutController)
	{
		//get total basket amount
		$amount = Shopware()->Modules()->sBasket()->sGetAmount();
		$shippingCost = $checkoutController->getShippingCosts();
		$amount = $amount["totalAmount"] + $shippingCost["value"];

        if($checkoutController->View()->sAmountWithTax){
            $amount = $checkoutController->View()->sAmountWithTax;
        }

		//get trusted shop article data
		$article = $this->getTsArticleByAmount($amount);

		//set trusted shop parameters
		$trustedShop = array("displayProtectionBox" => !$this->isTsArticleInBasket(), "article" => $article);
		return $trustedShop;
	}

	/**
	 * This function checks if the order has a buyer protection article
	 * @param $order
	 * @return array | empty or article data
	 */
	protected function isTsArticleInOrder($order)
	{
		$basket = $order->sBasketData;
		foreach($basket["content"] as $article) {
            $explode = explode('_',$article['ordernumber']);
            if(count($explode) == 4 && substr($explode[0],0,2) == 'TS') {
                return $article;
            }
		}
		return array();
	}

}



