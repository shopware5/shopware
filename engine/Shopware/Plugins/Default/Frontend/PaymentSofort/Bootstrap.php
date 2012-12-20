<?php
/**
 * Bootstrap for sofort
 *
 * $Date: 2012-07-23 10:46:50 +0200 (Mon, 23 Jul 2012) $
 * @version sofort 1.0  $Id: Bootstrap.php 4870 2012-07-23 08:46:50Z dehn $
 * @author SOFORT AG http://www.sofort.com (f.dehn@sofort.com)
 * @package Shopware 4, sofort.com
 *
 */
class Shopware_Plugins_Frontend_PaymentSofort_Bootstrap extends Shopware_Components_Plugin_Bootstrap {

	private $snippets = null;
	
	/**
	 * Products of Payment Network
	 * consisting of name, description, action, template and additionaldescription
	 * @var Array
	 */
	private $products = array();
	
	
	/**
	 *
	 * Initiate this class
	 */
	public function init() {
		$sql = 'SELECT count(*) FROM `s_core_snippets` WHERE namespace LIKE "sofort_multipay%"';
		$snippetCount = Shopware()->Db()->FetchOne($sql);
		$lang = '';
		
		if ($snippetCount == 0) {
			$lang = $this->insertGermanLanguage();
		}
		if (is_null($this->snippets)) {
			$this->snippets = Shopware()->Snippets();
		}
		
		$this->initProducts();
	}
	
	
	/**
	 * Common install routine
	 * uninstall is called first to make sure everything's fine before installing
	 */
	public function install() {
		if (is_null($this->snippets)) {
			$this->snippets = Shopware()->Snippets();
		}
		
		$this->uninstall();
		$this->createPayments();
		$this->createLanguages();
		$this->createEvents();
		$this->createHooks();
		$this->createOrdersTable();
		$this->createProductTable();
		$this->createStatusTable();
		$this->createTemporaryOrdersTable();
		$this->createSettingsTable();
		$this->createCoreConfigTable();
		$this->createBackendMenu();
		return true;
	}
	
	
	public function update($oldVersion) {
		switch ($oldVersion) {
			case '1.0.0':

			break;
		}

        return true;
	}
	
	
	/**
	 * Common uninstall routine
	 */
	public function uninstall() {
		$this->removePaymentMeans();
		//$this->removeOrdersTable(); //must not be removed in case of an update!
		//$this->removeStatusTable();
		//$this->removeProductTable();
		$this->removeSettingsTable();
		$this->removeLanguageSnippets();
		return true;
	}
	
	
	/**
	 * initiate product array
	 */
	private function initProducts() {
		if (is_null($this->snippets)) {
			$this->snippets = Shopware()->Snippets();
		}
		
		$sueLandingUrl = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_landing_url');
		$sueBanner = '';
		$sueLogo = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_title_img');
		
		$svLandingUrl = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_landing_url');
		$svBanner = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_banner_img');
		$svLogo = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_title_img');
		
		$srLandingUrl = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sr_landing_url');
		$srBanner = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sr_banner_img');
		$srLogo = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sr_title_img');
		
		$slLandingUrl = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sl_landing_url');
		$slBanner = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sl_banner_img');
		$slLogo = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sl_title_img');
		
		$lsLandingUrl = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_ls_landing_url');
		$lsBanner = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_ls_banner_img');
		$lsLogo = $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_ls_title_img');
		
		$this->products = array(
		array(
				'name' => 'sofortueberweisung_multipay',
				'description' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_public_title'),
				'action' => 'sofort',
				'esdactive' => 1,
				'template' => '/Templates/Frontend/payment_methods/sofortueberberweisung.tpl',
				'additionaldescription' => '
				<a href="'.$sueLandingUrl.'" target="_blank">
				'.$sueBanner.'
				</a>
				',
				'logo' => base64_encode(file_get_contents($sueLogo)),
		),
		array(
				'name' => 'vorkassebysofort_multipay',
				'description' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_public_title'),
				'action' => 'sofort',
				'esdactive' => 0,
				'template' => 'Templates/Frontend/payment_methods/vorkassebysofort.tpl',
				'additionaldescription' => '
						'.$this->snippets->getSnippet("sofort_multipay_bootstrap")->get("sofort_multipay_sv_public_title").'
					###SV_REASON_HINT###
					###SV_ACCOUNT_DATA###
				',
				'logo' => base64_encode(file_get_contents($svLogo)),
		),
		array(
				'name' => 'sofortrechnung_multipay',
				'description' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sr_public_title'),
				'action' => 'sofort',
				'esdactive' => 0,
				'template' => 'Templates/Frontend/payment_methods/sofortrechnung.tpl',
				'additionaldescription' => '
				<a href="'.$srLandingUrl.'" target="_blank">
				'.$srBanner.'
				</a>
				',
				'logo' => base64_encode(file_get_contents($srLogo)),
		),
		array(
				'name' => 'sofortlastschrift_multipay',
				'description' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sl_public_title'),
				'action' => 'sofort',
				'esdactive' => 0,
				'template' => 'Templates/Frontend/payment_methods/sofortlastschrift.tpl',
				'additionaldescription' => '
				<a href="'.$slLandingUrl.'" target="_blank">
				'.$slBanner.'
				</a>
				',
				'logo' => base64_encode(file_get_contents($slLogo)),
		),
		array(
				'name' => 'lastschriftbysofort_multipay',
				'description' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_ls_public_title'),
				'action' => 'sofort',
				'esdactive' => 0,
				'template' => 'Templates/Frontend/lastschriftbysofort.tpl',
				'additionaldescription' => '
				<a href="'.$lsLandingUrl.'" target="_blank">
				'.$lsBanner.'
				</a>
				',
				'logo' => base64_encode(file_get_contents($lsLogo)),
		),
		);
	}
	
	
	/**
	 * In future versions: include all supported languages
	 */
	private function createLanguages() {
		$this->insertGermanLanguage();
		$this->insertEnglishLanguage();
		//$this->insertItalianLanguage();
		$this->insertDutchLanguage();
		$this->insertPolishLanguage();
		$this->insertItalianLanguage();
		$this->insertFrenchLanguage();
	}
	
	
	/**
	 *
	 * Insert English language snippets
	 * @throws Exception
	 */
	private function insertEnglishLanguage() {
		$langFile = dirname(__FILE__).'/build/shopware_en_utf8.sql';
		if (file_exists($langFile)) {
			$sql = file_get_contents($langFile);
			Shopware()->Db()->exec($sql);
		} else {
			throw new Exception('English language data not found');
		}
	}
	
	
	/**
	 *
	 * Insert German language snippets
	 * @throws Exception
	 */
	private function insertGermanLanguage() {
		$langFile = dirname(__FILE__).'/build/shopware_de_utf8.sql';
		if (file_exists($langFile)) {
			$sql = file_get_contents($langFile);
			Shopware()->Db()->exec($sql);
			return true;
		} else {
			throw new Exception('German language data not found');
			return false;
		}
	}
	
	
	/**
	 *
	 * Insert Dutch language snippets
	 * @throws Exception
	 */
	private function insertDutchLanguage() {
		$langFile = dirname(__FILE__).'/build/shopware_nl_utf8.sql';
		if (file_exists($langFile)) {
			$sql = file_get_contents($langFile);
			Shopware()->Db()->exec($sql);
			return true;
		} else {
			throw new Exception('Dutch language data not found');
			return false;
		}
	}
	
	
	/**
	 *
	 * Insert Polish language snippets
	 * @throws Exception
	 */
	private function insertPolishLanguage() {
		$langFile = dirname(__FILE__).'/build/shopware_pl_utf8.sql';
		if (file_exists($langFile)) {
			$sql = file_get_contents($langFile);
			Shopware()->Db()->exec($sql);
			return true;
		} else {
			throw new Exception('Polish language data not found');
			return false;
		}
	}
	
	
	/**
	 *
	 * Insert Italian language snippets
	 * @throws Exception
	 */
	private function insertItalianLanguage() {
		$langFile = dirname(__FILE__).'/build/shopware_it_utf8.sql';
		
		if (file_exists($langFile)) {
			$sql = file_get_contents($langFile);
			Shopware()->Db()->exec($sql);
			return true;
		} else {
			throw new Exception('Italian language data not found');
			return false;
		}
	}
	
	
	/**
	 *
	 * Insert French language snippets
	 * @throws Exception
	 */
	private function insertFrenchLanguage() {
		$langFile = dirname(__FILE__).'/build/shopware_fr_utf8.sql';
		if (file_exists($langFile)) {
			$sql = file_get_contents($langFile);
			Shopware()->Db()->exec($sql);
			return true;
		} else {
			throw new Exception('French language data not found');
			return false;
		}
	}
	
	
	/**
	 *
	 * Insert Turkish language snippets
	 * @throws Exception
	 */
	private function insertTurkishLanguage() {
		$langFile = dirname(__FILE__).'/build/shopware_tr_utf8.sql';
		if (file_exists($langFile)) {
			$sql = file_get_contents($langFile);
			Shopware()->Db()->exec($sql);
			return true;
		} else {
			throw new Exception('Turkish language data not found');
			return false;
		}
	}
	
	
	/**
	 * Remove any language snippets
	 */
	private function removeLanguageSnippets() {
		$sql = 'DELETE FROM `s_core_snippets` WHERE `namespace` LIKE "sofort_multipay%";';
		Shopware()->Db()->exec($sql);
		return true;
	}
	
	
	/**
	 *
	 * Used a hook for Shopware_Controllers_Frontend_Account
	 * (or let's say: created a proxy object for the above mentioned controller, implemented an own paymentAction
	 * to validate some custom fields when choosing payment)
	 * http://wiki.shopware.de/Einfuehrung-Hook-System_detail_615.html
	 */
	private function createHooks() {
		$this->subscribeEvent('Shopware_Controllers_Frontend_Account::savePaymentAction::before', 'sofortPaymentAction');
		return true;
	}
	
	
	/**
	 * The custom payment action to validate user's credentials for SOFORT AG
	 * @param Enlight_Hook_HookArgs $args
	 * @return bool
	 */
static function sofortPaymentAction(Enlight_Hook_HookArgs $args) {
		$doc = $args->getSubject();
		$params = $args->getArgs();
		// the chosen payment method
		$chosenPayment = $doc->Request()->register[payment];
		// fetch the paymentMeans from s_core_paymentmeans
		$p = self::getActiveSofortPaymentMeans();
		
		// take the chosen payment method into account
		switch($p[$chosenPayment]['name']) {
			case('vorkassebysofort_multipay'):
				if($doc->Request()->vorkassebysofort_dhw != 'on') {
					$url = $doc->Front()->Router()->assemble(array(
							'controller' => 'account',
							'action' => 'payment',
							'sofort_error' => 'vorkassebysofort_dhw_not_accepted'
							));
							header('location: '.$url);
							die;
				}
				self::saveSofortSettings($args);
				break;
			case('sofortrechnung_multipay'):
				if($doc->Request()->sofortrechnung_dhw != 'on') {
					$url = $doc->Front()->Router()->assemble(array(
							'controller' => 'account',
							'action' => 'payment',
							'sofort_error' => 'sofortrechnung_dhw_not_accepted'
							));
							header('location: '.$url);
							die;
				}
				self::saveSofortSettings($args);
				break;
			case('sofortueberweisung_multipay'):
				// nothing to be done here
				break;
			case('vorkassebysofort_multipay'):
				// nothing to be done here
				break;
			case('sofortlastschrift_multipay'):
				// nothing to be done here
				break;
			case('lastschriftbysofort_multipay'):
				$validationErrors = array();
				
				if($doc->Request()->lastschriftbysofort_account_number == '') {
					$validationErrors[] = 'lastschriftbysofort_account_number';
				}
				if($doc->Request()->lastschriftbysofort_bank_code == '') {
					$validationErrors[] = 'lastschriftbysofort_bank_code';
				}
				if($doc->Request()->lastschriftbysofort_holder == '') {
					$validationErrors[] = 'lastschriftbysofort_holder';
				}
				if($doc->Request()->lastschriftbysofort_dhw != 'on') {
					$validationErrors[] = 'lastschriftbysofort_dhw_not_accepted';
				}
				
				$i = 0;
				$errorString = '';
				foreach($validationErrors as $error) {
					($i == 0) ? $errorString = $error : $errorString .= '|'.$error;
					$i++;
				}
				
				if(!empty($validationErrors)) {
					$url = $doc->Front()->Router()->assemble(array(
						'controller' => 'account',
						'action' => 'payment',
						'sofort_error' => $errorString,
					));
					header('location: '.$url);
					die;
				}
				self::saveSofortSettings($args);
				break;
				
			default: return;	// allow other payment methods to be chosen. if no return here, there wouldn't be any chance to select any other
		}
		
		return true;
	}
	
	
	/**
	 *
	 * Create all the events necessary for handling both the frontend and the backend ...
	 */
	private function createEvents() {
		/**
		 *
		 * Event is being triggered when page is loaded
		 * Used to show Payment Network Logo on specific parts of the template
		 */
		$this->subscribeEvent(
			'Enlight_Controller_Action_PostDispatch_Frontend_Index', 'onLoadFrontpage'
			);
			
			/**
			 *
			 * Frontend Controller
			 */
			$this->subscribeEvent(
			'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Sofort', 'onSofortPaymentCall'
			);
			
			/**
			 *
			 * Frontend Controller used for notifications
			 */
			$this->subscribeEvent(
			'Enlight_Controller_Dispatcher_ControllerPath_Frontend_SofortNotification', 'onNotificationCall'
			);
			/**
			 *
			 * Backend Controller
			 */
			$this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_SofortSettings', 'onBackendSofortSettings');
			
			/**
			 *
			 * Backend Controller
			 */
			$this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_SofortOrders', 'onBackendSofortOrders');
			
			/**
			 *
			 * Frontend Account Controller
			 */
			$this->subscribeEvent( 'Enlight_Controller_Action_PostDispatch_Frontend_Account', 'onPostDispatchAccount');
			
			/**
			 *
			 * Frontend Controller
			 */
			$this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Checkout', 'onPostDispatchCheckout');
			
			$this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Register', 'onPostDispatchRegister');
			
			$this->subscribeEvent(
				'Shopware_Modules_Order_SendMail_BeforeSend', 'onSendMailForVorkasse');
			
			return true;
	}
	
	
	/**
	 * Creates the backend menu
	 */
	private function createBackendMenu() {
		$parent = $this->Menu()->findOneBy('label', 'Zahlungen');
		$item = $this->createMenuItem(array(
			'label' => 'sofort.com',
			'onclick' => 'openAction(\'SofortOrders\');',
			'class' => 'ico2 date2',
			'active' => 1,
			'parent' => $parent,
			'style' => 'background-position: 5px 5px;'
			));
			$this->Menu()->addItem($item);
			$this->Menu()->save();
	}
	
	
	/**
	 *
	 * create settings
	 * @return true
	 */
	private function createCoreConfigTable() {
		require_once(dirname(__FILE__).'/library/sofortLib_sofortueberweisung_classic.php');
		// get Form instance
		/** @var $form Shopware\Models\Config\Form */
		$form = $this->Form();
		
		// Config key field
		$form->setElement('text', 'sofort_api_key', array(
			'label' => $this->snippets->getSnippet('sofort_multipay')->get('sofort_multipay_api_key'),
			'required' => true,
			'supportText' => '',
			'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
            "uniqueId" => 'sofort_api_key'
            )
        );
					
        // Flag if API Key is valid
        $form->setElement('checkbox', 'sofort_api_key_valid', array(
			'label' => 'Valid API Key',
			'required' => false,
			'supportText' => '',
			'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
            "uniqueId" => 'sofort_api_key_valid'
        ));

        // Check config key
        $form->setElement('button', 'sofort_multipay_test_api_key', array(
			'label' => $this->snippets->getSnippet('sofort_multipay')->get('sofort_multipay_test_api_key'),
			'required' => false,
			'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
			'action' => 'testApiKey',
			'handler' => 'function(){
						var apiKeyInput = this.up(\'form\').down(\'textfield[uniqueId=sofort_api_key]\'),
						apiKeyValid = this.up(\'form\').down(\'checkbox[uniqueId=sofort_api_key_valid]\'),
						url = window.location.pathname.split(\'/backend\')[0]
						+ \'/backend/sofort_orders/testApi\',
						box = Ext.MessageBox.wait(\''.$this->snippets->getSnippet('sofort_multipay_backend')->get('please_wait').'\', \''.$this->snippets->getSnippet('sofort_multipay_backend')->get('test_connection').'\');
						if (!apiKeyInput.getValue()){
						Ext.MessageBox.alert(\'\',\''.$this->snippets->getSnippet('sofort_multipay_backend')->get('enter_credentials').'\');
						//eidTextfieldObject.getEl().setStyle(\'background\',\'red\');
						apiKeyValid.setValue(0);
						box.hide();
						return;
					}
					Ext.Ajax.request({
						scope   : this,
						url     : url,
						success : function(response,request) {
						box.hide();
						if(response.responseText == 1) {
							Ext.MessageBox.alert(\''.$this->snippets->getSnippet('sofort_multipay_backend')->get('success').'\',\''.$this->snippets->getSnippet('sofort_multipay_backend')->get('connection_ok').'\');
							apiKeyInput.setFieldStyle(\'background\',\'green\');
							apiKeyValid.setValue(1);
						} else {
							Ext.MessageBox.alert(\''.$this->snippets->getSnippet('sofort_multipay_backend')->get('wrong_credentials').'\', \''.$this->snippets->getSnippet('sofort_multipay_backend')->get('connection_failed').'\');
							//apiKeyInput.setStyle(\'background\',\'red\');
							apiKeyValid.setValue(0);
						}
					},
					failure: function() {
						box.hide();
						//configTextfieldObject.getEl().setStyle(\'background\',\'red\');
						Ext.MessageBox.alert(\''.$this->snippets->getSnippet('inactive_module')->get('wrong_credentials').'\',\''.$this->snippets->getSnippet('inactive_module')->get('shop.shopware.activate_module').'\');
					},
					// Pass all needed parameters
					params: { apiKey: apiKeyInput.getValue() }
					});
				}'
            ));
					
        // Payment Secret
        $paymentSecret = SofortLib_SofortueberweisungClassic::generatePassword();
        $form->setElement('text', 'paymentSecret', array(
			'label' => $this->snippets->getSnippet('sofort_multipay_backend')->get('payment_secret'),
			'required' => true,
			'value' => $paymentSecret,
			'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
					
        // Payment Reason - Default shop name
        $paymentReason = '-TRANSACTION-';
        $form->setElement('text', 'paymentReason', array(
			'label' => $this->snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_reason_1'),
			'required' => true,
			'value' => $paymentReason,
			'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
					
					
        // Payment Reason - Default shop name
        $paymentReason2 = Shopware()->Config()->shopname;
        $form->setElement('text', 'paymentReason2', array(
			'label' => $this->snippets->getSnippet('sofort_multipay_finish')->get('sofort_multipay_reason_2'),
			'required' => true,
			'value' => $paymentReason2,
			'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
					
					
        /** PAYMENT STATES **/
        // open | pending
        $form->setElement('combo', 'sofort_pending_state', array(
			'label' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('payment_state_open'),
			'value' => 17,
			'store' => 'base.PaymentStatus',
			'displayField' => 'description',
			'valueField' => 'id',
			'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        // Confirmed
        $form->setElement('combo', 'sofort_confirmed_state', array(
			'label' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('payment_state_confirmed'),
			'value' => 12,
			'store' => 'base.PaymentStatus',
			'displayField' => 'description',
			'valueField' => 'id',
			'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
					
        // canceled
        $form->setElement('combo', 'sofort_canceled_state', array(
			'label' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('payment_state_canceled'),
			'value' => 35,
			'store' => 'base.PaymentStatus',
			'displayField' => 'description',
			'valueField' => 'id',
			'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
					
        /** Protection Services **/
        // sofort ueberweisung customer protection
        $form->setElement('checkbox', 'sofort_ueberweisung_customer_protection', array(
			'label' => $this->snippets->getSnippet('sofort_multipay')->get('customer_protection_su'),
        ));

        $form->setElement('combo', 'sofort_su_display', array(
            'label' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_banner_or_text'),
            'value' => $this->snippets->getSnippet('sofort_multipay')->get('banner_su_desc'),
            'store' => array(
                array(1, $this->snippets->getSnippet('sofort_multipay')->get('banner_su_desc')),
                array(2, $this->snippets->getSnippet('sofort_multipay')->get('text_su_desc'))
            ),
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement('combo', 'sofort_sl_display', array(
            'label' => $this->snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_banner_or_text'),
            'value' => $this->snippets->getSnippet('sofort_multipay')->get('banner_sl_desc'),
            'store' => array(
                array(1, $this->snippets->getSnippet('sofort_multipay')->get('banner_sl_desc')),
                array(2, $this->snippets->getSnippet('sofort_multipay')->get('text_sl_desc'))
            ),
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
	    return true;
	}
	
	
	/**
	 * Remove payment means
	 * @return true
	 */
	private function removePaymentMeans() {
		foreach($this->products as $product) {
			$sql = 'DELETE FROM s_core_paymentmeans WHERE `name` = ?';
			$fields = array(
			$product['name'],
			);
			Shopware()->Db()->query($sql, $fields);
		}
		
		return true;
	}
	
	
	/**
	 * Create and save payments
	 * @return true
	 */
	protected function createPayments()
	{
		$i = 1;
		foreach($this->products as $product) {
			$this->createPayment(
				array(
					'name' => $product['name'],
					'description' => $product['description'],
					'position' => $i,
					'action' => $product['action'],
					'active' => 0,
					'esdactive' => $product['esdactive'],
					'pluginID' => $this->getId(),
					'template' => $product['template'],
					'additionaldescription' => $product['additionaldescription'],
				)
			);
			$i++;
		}
		return true;
	}
	
	
	/**
	 * Create the orders table (if none exist)
	 * @return true
	 */
	private function createOrdersTable() {
		Shopware()->Db()->exec(
			'CREATE TABLE IF NOT EXISTS `sofort_orders` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`paymentMethod` VARCHAR(32),
			`paymentDescription` VARCHAR(64),
			`transactionId` VARCHAR(32) NOT NULL,
			`secret` VARCHAR(32) NOT NULL,
			`paymentStatus` VARCHAR(32),
			`dateTime` TIMESTAMP NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			KEY `paymentMethod` (`paymentMethod`),
			KEY `paymentDescription` (`paymentDescription`),
			KEY `transactionId` (`transactionId`),
			KEY `secret` (`secret`),
			KEY `paymentStatus` (`paymentStatus`)
		)'
		);
		return true;
	}
	
	
	/**
	 * Create the invoice table (if none exist)
	 */
	private function createProductTable() {
		Shopware()->Db()->exec(
			'CREATE TABLE IF NOT EXISTS `sofort_products` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`order_id` int(11) unsigned NOT NULL,
			`transactionId` varchar(32) NOT NULL,
			`amount` float DEFAULT NULL,
			`comment` text,
			`date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		)'
		);
		return true;
	}
	
	
	/**
	 * Create the inovice status table (if none exist)
	 */
	private function createStatusTable() {
		Shopware()->Db()->exec(
			'CREATE TABLE IF NOT EXISTS `sofort_status` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`sofort_product_id` int(11) unsigned NOT NULL,
			`status_id` int(11) unsigned NOT NULL,
			`status` varchar(32) NOT NULL,
			`status_reason` varchar(32) NOT NULL,
			`invoice_status` varchar(32) DEFAULT NULL,
			`invoice_objection` varchar(45) DEFAULT NULL,
			`items` text,
			`date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		)'
		);
		
		// update db schema detectioin
		$databaseConfig = Shopware()->Db()->getConfig();
		$itemColumnExists = Shopware()->Db()->fetchOne('SELECT count(COLUMN_NAME) as count FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA = "'.$databaseConfig['dbname'].'" AND TABLE_NAME = "sofort_status" AND COLUMN_NAME = "items"'
				);
        $commentColumnExists = Shopware()->Db()->fetchOne('SELECT count(COLUMN_NAME) as count FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA = "'.$databaseConfig['dbname'].'" AND TABLE_NAME = "sofort_status" AND COLUMN_NAME = "comment"'
        );
        // update db if necessary
        if($commentColumnExists == 0) {
            Shopware()->Db()->exec('ALTER TABLE `sofort_status` ADD `comment` TEXT NOT NULL AFTER `invoice_objection`');
        }
        if($itemColumnExists == 0) {
            Shopware()->Db()->exec('ALTER TABLE `sofort_status` ADD `items` TEXT NOT NULL AFTER `invoice_objection`');
        }
				
        return true;
	}
	
	
	/**
	 * 
	 * Create temporary orders table
	 */
	private function createTemporaryOrdersTable() {
		Shopware()->Db()->exec(
			'CREATE TABLE IF NOT EXISTS `sofort_temp_orders` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`ordernumber` int(11) unsigned NOT NULL,
			`secret` varchar(32) NOT NULL,
			`cart_content` text NOT NULL,
			`user_data` text NOT NULL,
			`customer_comment` text NOT NULL,
			`comment` text NOT NULL,
			`order_time` datetime NOT NULL DEFAULT "0000-00-00 00:00:00",
			PRIMARY KEY (`id`)
		)'
		);
		return true;
	}
	
	
	/**
	 *
	 * Remove the orders table when plugin is uninstalled ...
	 */
	private function removeOrdersTable() {
		Shopware()->Db()->exec(
			'DROP TABLE IF EXISTS `sofort_orders`'
			);
			return true;
	}


	/**
	 *
	 * Remove sofort_product table
	 */
	private function removeProductTable() {
		Shopware()->Db()->exec(
			'DROP TABLE IF EXISTS `sofort_product`'
			);
			return true;
	}


	/**
	 * remove sofort_status table
	 * Enter description here ...
	 */
	private function removeStatusTable() {
		Shopware()->Db()->exec(
			'DROP TABLE IF EXISTS `sofort_status`'
			);
			return true;
	}


	/**
	 * Create the orders table
	 * @return true
	 */
	private function createSettingsTable() {
		Shopware()->Db()->exec(
			'CREATE TABLE IF NOT EXISTS `sofort_user_settings` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`userId` int(11) NOT NULL,
			`ls_account_number` VARCHAR(32) NOT NULL,
			`ls_bank_code` VARCHAR(32) NOT NULL,
			`ls_holder` VARCHAR(64) NOT NULL,
			`sv_customer_protection` TINYINT(1) DEFAULT 0,
			`su_customer_protection` TINYINT(1) DEFAULT 0,
			`sv_dhw_accepted`  TINYINT(1) DEFAULT 0,
			`sr_dhw_accepted`  TINYINT(1) DEFAULT 0,
			`sl_dhw_accepted`  TINYINT(1) DEFAULT 0
		)'
		);
		return true;
	}
	
	
	/**
	 *
	 * Remove the settings table when plugin is uninstalled ...
	 */
	private function removeSettingsTable() {
		Shopware()->Db()->exec(
			'DROP TABLE IF EXISTS `sofort_user_settings`'
			);
			return true;
	}
	
	
	/**
	 *
	 * Fetch all active payments and return an array containing information about
	 * @return Array payments
	 */
	private function getActiveSofortPaymentMeans() {
		$sql = 'SELECT `id`, `active`, `name`, `description` FROM `s_core_paymentmeans` WHERE active = 1 ORDER BY position ASC';
		$paymentMeans = Shopware()->Db()->fetchAll($sql);
		
		$sofortMeans = array();
		foreach($paymentMeans as $mean) {
			$sofortMeans[$mean['id']] = array(
				'id' => $mean['id'],
				'name' => $mean['name'],
				'description' => $mean['description'],
			);
		}
		return $sofortMeans;
	}
	
	
	/**
	 *
	 * Payment Network Frontend Controller
	 * @param Enlight_Event_EventArgs $args
	 * @return string
	 */
	public static function onSofortPaymentCall(Enlight_Event_EventArgs $args) {
		Shopware()->Template()->addTemplateDir(dirname(__FILE__).'/Templates/Frontend/');
		return dirname(__FILE__).'/Controllers/Frontend/Sofort.php';
	}
	
	
	public static function onNotificationCall(Enlight_Event_EventArgs $args) {
		Shopware()->Template()->addTemplateDir(dirname(__FILE__).'/Templates/Frontend/');
		return dirname(__FILE__).'/Controllers/Frontend/SofortNotification.php';
	}
	
	
	/**
	 *
	 * Payment Network Backend Orders Controller
	 * @param Enlight_Event_EventArgs $args
	 * @return string
	 */
	public static function onBackendSofortOrders(Enlight_Event_EventArgs $args) {
		Shopware()->Template()->addTemplateDir(dirname(__FILE__).'/Templates/Backend/');
		return dirname(__FILE__).'/Controllers/Backend/SofortOrders.php';
	}
	
	
	/**
	 *
	 * Fetch bank account for lastschriftbysofort
	 * @param Enlight_Event_EventArgs $args
	 * @return Array
	 */
	private function getBankAccount($args) {
		$view = $args->getSubject()->View();
		$userId = $view->sUserData['billingaddress']['id'];
		
		if(!empty($userId)) {
			$sql = 'SELECT `ls_account_number`, `ls_bank_code`, `ls_holder` FROM `sofort_user_settings` WHERE userID = ?';
			$fields = array($userId);
			$bankAccount = Shopware()->Db()->fetchAll($sql, $fields);
				
			if(!empty($bankAccount)) {
				return $bankAccount[0];
			}
		}
		
		return array();
	}
	
	
	/**
	 *
	 * Fetch user settings
	 * @param $userId
	 * @internal param \Enlight_Event_EventArgs $args
	 * @return Array
	 */
	private function getUserSettingsByUserId($userId) {
		if(!empty($userId)) {
			$sql = 'SELECT * FROM `sofort_user_settings` WHERE userID = ?';
			$settings = Shopware()->Db()->fetchAll($sql,array($userId));
				
			if(!empty($settings)) {
				return $settings[0];
			}
		}
		return array();
	}
	
	
	/**
	 *
	 * Fetch user settings
	 * @param $userId
	 * @internal param \Enlight_Event_EventArgs $args
	 * @return Array
	 */
	private function getChosenPaymentMethod($userId) {
		if(!empty($userId)) {
			$sql = 'SELECT paymentID FROM `s_user` WHERE id = ?';
			$chosenPaymentMethod = Shopware()->Db()->fetchOne($sql,array($userId));
			return $chosenPaymentMethod;
		}
		return 0;
	}
	
	
	/**
	 *
	 * Insert/Update payment settings for the current user
	 * @param Enlight_Event_EventArgs $args
	 * @return bool
	 */
	static function saveSofortSettings(Enlight_Event_EventArgs $args) {
		$param = $args->getSubject()->Request()->getParams();
		$view   = $args->getSubject()->View();
		$userId = $view->sUserData['billingaddress']['id'];
		
		if($param['controller'] === 'account' && $param['action'] === 'savePayment') {
				
			if(!self::sofortUserSettingsExist($userId) && !empty($userId)) {
				// initiate table
				$sql = 'INSERT INTO `sofort_user_settings` (`userId`, `ls_account_number`, `ls_bank_code`, `ls_holder`) VALUES (?, ?, ?, ?);';
				Shopware()->Db()->query($sql, array($userId, '', '', ''));
			}
				
			$accountNumber = (!empty($param['lastschriftbysofort_account_number'])) ? $param['lastschriftbysofort_account_number'] : '';
			$bankCode = (!empty($param['lastschriftbysofort_bank_code'])) ? $param['lastschriftbysofort_bank_code'] : '';
			$holder = (!empty($param['lastschriftbysofort_holder'])) ? $param['lastschriftbysofort_holder'] : '';
				
			// just in case we haven't created a user yet, return here
			if (empty($userId)) {
				return false;
			}
				
			$sql = array();
			$sql[] = 'UPDATE `sofort_user_settings` SET `su_customer_protection` = "'.(($param['sofortueberweisung_cp'] == 'on') ? 1 : 0).'" WHERE `userId` = ?;';
			$sql[] = 'UPDATE `sofort_user_settings` SET `sv_customer_protection` = "'.(($param['vorkassebysofort_cp'] == 'on') ? 1 : 0).'" WHERE `userId` = ?;';
			$sql[] = 'UPDATE `sofort_user_settings` SET `sv_dhw_accepted` = "'.(($param['vorkassebysofort_dhw'] == 'on') ? 1 : 0).'" WHERE `userId` = ?;';
			$sql[] = 'UPDATE `sofort_user_settings` SET `sr_dhw_accepted` = "'.(($param['sofortrechnung_dhw'] == 'on') ? 1 : 0).'" WHERE `userId` = ?;';
			$sql[] = 'UPDATE `sofort_user_settings` SET `sl_dhw_accepted` = "'.(($param['lastschriftbysofort_dhw'] == 'on') ? 1 : 0).'" WHERE `userId` = ?;';
			$fields = array(
			$userId,
			);
				
			// update all settings accordingly
			foreach($sql as $query) {
				$result = Shopware()->Db()->query($query, $fields);
			}
				
			$bankAccount = self::getBankAccount($args);
				
			if (empty($bankAccount) && !empty($accountNumber) && !empty($bankCode) && !empty($holder)) {
				$sql = 'INSERT INTO `sofort_user_settings` (`userId`, `ls_account_number`, `ls_bank_code`, `ls_holder`) VALUES (?, ?, ?, ?);';
				$fields = array(
				$userId,
				$accountNumber,
				$bankCode,
				$holder,
				);
				Shopware()->Db()->query($sql, $fields);
				return true;
			} elseif(!empty($bankAccount) && !empty($accountNumber) && !empty($bankCode) && !empty($holder)) {
				$view = $args->getSubject()->View();
				$userId = $view->sUserData['billingaddress']['id'];
				$sql = 'UPDATE `sofort_user_settings` SET `ls_account_number` = ?, `ls_bank_code` = ?, `ls_holder` = ? WHERE `userId` = ?;';
				$fields = array(
				$accountNumber,
				$bankCode,
				$holder,
				$userId,
				);
				Shopware()->Db()->query($sql, $fields);
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * 
	 * Do user settings exist for this user
	 * @param int $userId
	 */
	static function sofortUserSettingsExist($userId) {
		$sql = 'SELECT COUNT(ID) FROM sofort_user_settings WHERE userid = ?';
		$userSettingsExist = Shopware()->Db()->fetchOne($sql, array($userId));
		return ($userSettingsExist == 1) ? true : false;
	}


	/**
	 *
	 * When sites are loaded (whatever site it is) do something...
	 * This event is being used to display an image on front page (frontend_index_left_menu)
	 * @param Enlight_Event_EventArgs $args
	 */
	static function onLoadFrontpage(Enlight_Event_EventArgs $args) {
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		
		if (!$request->isDispatched()
		|| $response->isException()
		|| $request->getModuleName() != 'frontend'
		|| !$view->hasTemplate()
		) {
			return;
		}
		
		// the logo will only be displayed in non emotion templates
		$view->addTemplateDir(dirname(__FILE__).'/Templates/Frontend/');
		$view->extendsTemplate('sofort_banner.tpl');
	}
	
	
	/**
	 * 
	 * Assembles an overview for payments
	 * @param Enlight_Event_EventArgs $args
	 * @param string $template
	 */
	public static function sofortPaymentOverview(Enlight_Event_EventArgs $args, $template) {
		// get the params (e.g. error messages set by invalid fields)
		$param = $args->getSubject()->Request()->getParams();
		
		// fetch the view
		/** @var $view Enlight_View_Default */
		$view = $args->getSubject()->View();
		if(!$view->hasTemplate()) {
			return ;
		}
		
		Shopware()->Template()->addTemplateDir(dirname(__FILE__).'/Templates/Frontend/');
		Shopware()->Template()->addTemplateDir(dirname(__FILE__).'/Templates/Frontend/payment_methods');
		
		$view->extendsTemplate($template);
		
		if(!empty($param['sofort_error'])) {
			$errorArray = explode('|', $param['sofort_error']);
			$view->errors = $errorArray;
			$view->sErrorMessages = ' ';
		}
		// fetch the active payment methods and pass them to the view
		$payments = self::getActiveSofortPaymentMeans();
		$view->sofortPaymentMeans = $payments;
		// fetch data for "Datenschutzhinweise" from remote server
		$svDhwUrl = Shopware()->Snippets()->getSnippet('sofort_multipay_checkout')->get('dhw_sv_conditions');
		$cache = self::getCache();
		$sv_dhw = self::getCachedData($cache, $svDhwUrl); //uf8
		$matches = array();
		preg_match("/<\!-- content -->.*<\!-- \/content -->/s", $sv_dhw, $matches);
		$view->vorkassebysofort_dhw = $matches[0];
		$srDhwUrl = Shopware()->Snippets()->getSnippet('sofort_multipay_checkout')->get('dhw_sr_conditions');
		$sr_dhw = self::getCachedData($cache, $srDhwUrl);//utf8
		$matches  = array();
		preg_match("/<\!-- content -->.*<\!-- \/content -->/s", $sr_dhw, $matches);
		$view->sofortrechnung_dhw = $matches[0];
		$view->dhwNoticeSR = self::makeConditionHint('sr');
		$view->dhwNoticeSV = self::makeConditionHint('sv');
		$view->dhwNoticeLS = self::makeConditionHint('ls');
		$lsDhwUrl = Shopware()->Snippets()->getSnippet('sofort_multipay_checkout')->get('dhw_ls_conditions');
		$ls_dhw = self::getCachedData($cache, $lsDhwUrl);//utf8
		$matches  = array();
		preg_match("/<\!-- content -->.*<\!-- \/content -->/s", $ls_dhw, $matches);
		$view->lastschriftbysofort_dhw = $matches[0];
		// customer protection in table 's_core_configuration' set?
		$suCustomerProtection = Shopware()->Plugins()->Frontend()->PaymentSofort()->Config()->sofort_ueberweisung_customer_protection;
		$svCustomerProtection = Shopware()->Plugins()->Frontend()->PaymentSofort()->Config()->vorkassebysofort_customer_protection;
		// set the variables for usage in template
		$view->suCustomerProtection = $suCustomerProtection;
		$view->svCustomerProtection = $svCustomerProtection;
		$view->bankAccount = self::getBankAccount($args);
		$userSettings = self::getUserSettingsByUserId($view->sUserData['additional']['user']['id']);
		$chosenPaymentMethod = self::getChosenPaymentMethod($view->sUserData['additional']['user']['id']);
		$view->chosenPaymentMethod = $chosenPaymentMethod;
		
		// set the "Datenschutzhinweis akzeptiert" according to user's settings
		if($userSettings['sr_dhw_accepted'] == 1) {
			$view->sofortrechnung_dhw_checked = 'checked';
		}
		
		if($userSettings['sv_dhw_accepted'] == 1) {
			$view->vorkassebysofort_dhw_checked = 'checked';
		}
		
		if($userSettings['sl_dhw_accepted'] == 1) {
			$view->lastschriftbysofort_dhw_checked = 'checked';
		}
		
		if($userSettings['su_customer_protection'] == 1) {
			$view->su_customer_protection = 'checked';
		}
		
		if($userSettings['sv_customer_protection'] == 1) {
			$view->sv_customer_protection = 'checked';
		}
		
		// fetch the settings for logos and text
		$suBannerOrText = Shopware()->Plugins()->Frontend()->PaymentSofort()->Config()->sofort_su_display;
		$view->suBannerOrText = $suBannerOrText;
		$slBannerOrText = Shopware()->Plugins()->Frontend()->PaymentSofort()->Config()->sofort_sl_display;
		$view->slBannerOrText = $slBannerOrText;
		
		self::saveSofortSettings($args);
		return true;
	}
	
	
	/**
	 * @return Zend_Cache_Core
	 */
	private function getCache() {
		// return $this->Application()->Cache();
	}
	
	
	/**
	 * 
	 * Get the cached data
	 * @param object $cache
	 * @param string $url
	 */
	private function getCachedData($cache=null, $url) {
		// if(!($data = $cache->load(__CLASS__.md5($url)))){
		$data = file_get_contents($url);
		// $cache->save($data);
		// }
		return $data;
	}
	
	
	/**
	 * 
	 * Assembles a hint for "Datenschutzhinweise" - Hints on privacy
	 * @param string $value
	 * @return mixed
	 */
	private function makeConditionHint($value) {
		// ich habe die <a onclick=...>Datenschutzhinweise</a> für diese Zahlart gelesen
		$condition = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_condition');
		$conditionHint = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_condition_notice');
		$conditionLink = '<a href="javascript:void(0)" onclick=showDWH("' . $value . '")><b>' . $condition . '</b></a>';
		return str_replace('{{conditions}}', $conditionLink, $conditionHint);
	}
	
	
	/**
	 *
	 * Choose your payment, gives an overview of available payment options
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPostDispatchAccount(Enlight_Event_EventArgs $args) {
		$request = $args->getSubject()->Request();
		
		if($request->getControllerName() === 'account' && $request->getActionName() === 'payment') {
			// Payment action is to be chosen via HTML form in this case
			$view = $args->getSubject()->View();
			// if api key is not valid, deactivate payment by sofort so that no one can select
			// one of these payment method
			$apiKeyNotValid = !self::isApiKeyValid();   // negation
			if(!empty($view->sPaymentMeans)) {
				$view->sPaymentMeans = self::deactivatePaymentMeans($view->sPaymentMeans, $apiKeyNotValid);
			} elseif(!empty($view->sPayments)) {
				$view->sPayments = self::deactivatePaymentMeans($view->sPayments, $apiKeyNotValid);
			}
				
			self::sofortPaymentOverview($args, 'sofortpayment.tpl');
		}
	}
	
	
	/**
	 * 
	 * OnPostDispatchCheckout Event 
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPostDispatchCheckout(Enlight_Event_EventArgs $args) {
		$request = $args->getSubject()->Request();
		
		if($request->getControllerName() === 'checkout' && $request->getActionName() === 'confirm') {
			// Payment action is to be chosen via Javascript in this case
			$view = $args->getSubject()->View();
			// if api key not valid, deactivate payment by sofort so that no one can
			// select one of these payment method
			$apiKeyNotValid = !self::isApiKeyValid();	// negation
				
			if(!empty($view->sPaymentMeans)) {
				$view->sPaymentMeans = self::deactivatePaymentMeans($view->sPaymentMeans, $apiKeyNotValid);
			} elseif(!empty($view->sPayments)) {
				$view->sPayments = self::deactivatePaymentMeans($view->sPayments, $apiKeyNotValid);
			}
				
			self::sofortPaymentOverview($args, 'sofortcheckout.tpl');
		} else if ('checkout' === $request->getControllerName() && 'finish' === $request->getActionName()) {
			$view = $args->getSubject()->View();
			
			$view->addTemplateDir(dirname(__FILE__).'/Templates/Frontend/');
			$view->extendsTemplate('finish.tpl');
			$param = $args->getSubject()->Request()->getParams();
			// assign variables in view
			$view->transactionId = $param['transactionId'];
			$view->paymentMethod = $param['paymentMethod'];
			$view->paymentDescription = $param['paymentDescription'];
			$view->holder = $param['holder'];
			$view->accountNumber = $param['accountNumber'];
			$view->iban = $param['iban'];
			$view->bank_code = $param['bank_code'];
			$view->bic = $param['bic'];
			$view->amount = $param['amount'];
			$view->reason_1 = $param['reason_1'];
			$view->reason_2 = $param['reason_2'];
		}
	}
	
	
	/**
	 * 
	 * OnPostDispatchRegister Event
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPostDispatchRegister(Enlight_Event_EventArgs $args) {
		$request = $args->getSubject()->Request();
		if($request->getControllerName() === 'register' && $request->getActionName() === 'payment')
		{
			// Payment action is to be chosen via HTML form in this case
			$view = $args->getSubject()->View();
			// if api key not valid, deactivate payment by sofort so that no one can
			// select one of these payment methods
			$apiKeyNotValid = !self::isApiKeyValid();	// negation
				
			if(!empty($view->sPaymentMeans)) {
				$view->sPaymentMeans = self::deactivatePaymentMeans($view->sPaymentMeans, $apiKeyNotValid);
			} elseif(!empty($view->sPayments)) {
				$view->sPayments = self::deactivatePaymentMeans($view->sPayments, $apiKeyNotValid);
			}
			
			self::sofortPaymentOverview($args, 'sofortpayment.tpl');
		}
	}
	
	
	/**
	 * 
	 * OnSendMailForVorkasse Event
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onSendMailForVorkasse(Enlight_Event_EventArgs $args) {
		$subject = $args->getSubject();
		$mail = $args->getMail();
		
		$bodyText = $mail->getPlainBody();
		$get = $subject->sSYSTEM->_GET;
		
		$holder = $get['holder'];
		$accountNumber = $get['account_number'];
		$iban = $get['iban'];
		$bankCode = $get['bank_code'];
		$bic = $get['bic'];
		$amount = $get['amount'];
		$reason1 = $get['reason_1'];
		$reason2 = $get['reason_2'];
		$transactionId = $get['transactionId'];
		
		$holder = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_holder').': <b>'.$holder.'</b>';
		$accountNumber = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_account_number').': <b>'.$accountNumber.'</b>';
		$iban = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_iban').': <b>'.$iban.'</b>';
		$bankCode = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_bank_code').': <b>'.$bankCode.'</b>';
		$bic = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_bic').': <b>'.$bic.'</b>';
		$amount = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_amount').': <b>'.$amount.'</b>';
		$reason1 = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_reason_1').': <b>'.$reason1.'</b>';
		$reason2 = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_reason_2').': <b>'.$reason2.'</b>';
		$transactionId = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('sofort_multipay_transaction_id').': <b>'.$transactionId.'</b>';
		
		$template = '
		<p>'.$holder.'<p>
		<p>'.$accountNumber.'<p>
		<p>'.$iban.'<p>
		<p>'.$bankCode.'<p>
		<p>'.$bic.'<p>
		<p>'.$amount.'<p>
		<p>'.$reason1.'<p>
		<p>'.$reason2.'<p>
		<p>'.$transactionId.'<p>
		';
		
		if($subject->sUserData['additional']['payment']['name'] == 'vorkassebysofort_multipay') {
			$svReasonsHint = Shopware()->Snippets()->getSnippet('sofort_multipay_finish')->get('checkout.sv.reasons_hint');
			$bodyText = str_replace('###SV_REASON_HINT###', '<p><b>'.$svReasonsHint.'</b></p>', $bodyText);
			$bodyText = str_replace('###SV_ACCOUNT_DATA###', $template, $bodyText);
		} else {
			$bodyText = str_replace('###SV_REASON_HINT###', '', $bodyText);
			$bodyText = str_replace('###SV_ACCOUNT_DATA###', '', $bodyText);
		}
		
		$mail->setBodyText($bodyText);
		$mail->setBodyHtml($bodyText);
		$args->setMail($mail);
	}
	
	
	/**
	 *
	 * is api key valid?
	 * @return boolean
	 */
	public static function isApiKeyValid() {
		$apiKeyValid = Shopware()->Plugins()->Frontend()->PaymentSofort()->Config()->sofort_api_key_valid;
		$apiKeyValid = ($apiKeyValid == 1) ? true : false;
		return $apiKeyValid;
	}
	
	
	/**
	 * if $condition is true, unset all sofort payment methods
	 * @param array $paymentMeans
	 * @param boolean $condition
	 * @return array
	 */
	public static function deactivatePaymentMeans($paymentMeans, $condition) {
		if($condition === true) {
			foreach($paymentMeans as $key => $payment) {
				if(preg_match('/[a-z]*_multipay/', $payment['name'])) {
					unset($paymentMeans[$key]);
				}
			}
		}
		return $paymentMeans;
	}
	
	
	/**
	 * 
	 * Get the info text about beta products
	 */
	private function getInfoText() {
		$infoText = Shopware()->Snippets()->getSnippet('sofort_multipay_backend')->get('beta_products');
		$infoText = @file_get_contents($infoText);
		$matches = array();
		preg_match("/<\!-- content -->.*<\!-- \/content -->/s", $infoText, $matches);
		$infoText = $matches[0];
		if(!empty($infoText)) {
			return '<div style="color: #ff0000;">'.$infoText.'<br /></div>';
		}
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Shopware_Components_Plugin_Bootstrap::getVersion()
	 */
	public function getVersion(){
		return "1.0.1";
	}
	
	
	/**
	 *
	 * Display some information about this plugin
	 */
	public function getInfo() {
		
		$productLogoString = '';
		
		foreach($this->products as $product) {
			$productLogoString .= $product['logo'];
			$productLogoString .= '&nbsp;';
		}
		if(is_null($this->snippets)) {
			$this->snippets = Shopware()->Snippets();
		}
		
		$description = $this->getInfoText().'
			<h2>'.Shopware()->Snippets()->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_payment_modules').'</h2>
			<p>'.$this->snippets->getSnippet('sofort_multipay')->get('sofort_multipay_plugin_description').'</p>
		';
		
		return array(
			'version' => $this->getVersion(),
			'autor' => 'SOFORT AG',
			'copyright' => 'SOFORT AG, 2012',
			'label' => 'SOFORT Gateway',
			'source' => '',
			'description' => $description,
			'license' => '',
			'support' => 'https://www.payment-network.com/sue_de/integration/list/88',
			'link' => 'http://www.sofort.com',
			'changes' => '',
			'revision' => '$Revision: 2042 $'
		);
	}
}