<?php

/*
  ##############################################################################
  # Plugin for Shopware
  # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  # @version $Id$
  # @copyright:   found in /lic/copyright.txt
  #
  ##############################################################################
 */
$originalReportLevel = error_reporting();
error_reporting(0);
include_once 'Controllers/Backend/BuiswPaymentPayone.php';


class Shopware_Plugins_Frontend_BuiswPaymentPayone_Bootstrap extends Shopware_Plugins_Frontend_Payment_Bootstrap {

    const NAME               = 'BuiswPaymentPayone';
    const LABEL              = 'PAYONE';
    const TABLE_CONFIG       = 's_bui_plg_payone_config';
    const TABLE_ASSIGNMENTS  = 's_bui_plg_payone_assigns';
    const TABLE_USERSETTINGS = 's_user_pay_payone';
    private $otherPayments   = array(
        array('text' => 'PayPal', 
              'key'  => 'paypal'), 
        array('text' => 'Lastschrift', 
              'key'  => 'lastschrift'), 
        array('text' => 'Rechnung', 
              'key'  => 'rechnung'), 
        array('text' => 'Vorkasse', 
              'key'  => 'vorkasse'), 
        array('text' => 'Nachnahme', 
              'key'  => 'nachnahme'),);

    private $directdebits = array (
        array (
                'text' => 'Sofort-&Uuml;berweisung',
                'key' => 'sofort',
                'value' => 'PNT'
        ),
        array (
                'text' => 'giropay',
                'key' => 'giropay',
                'value' => 'GPY'
        ),
        array (
                'text' => 'eps - Online-&Uuml;berweisung',
                'key' => 'eps',
                'value' => 'EPS'
        ),
        array (
                'text' => 'PostFinance E-Finance',
                'key' => 'post_e',
                'value' => 'PFF'
        ),
        array (
                'text' => 'PostFinance Card',
                'key' => 'post_c',
                'value' => 'PFC'
        ),
        array (
                'text' => 'iDeal',
                'key' => 'ideal',
                'value' => 'IDL'
        )
    );
    private $creditcards = array (
        array (
                'text' => 'Visa',
                'key' => 'visa',
                'value' => 'V'
        ),
        array (
                'text' => 'Mastercard',
                'key' => 'mastercard',
                'value' => 'M'
        ),
        array (
                'text' => 'American Express',
                'key' => 'amex',
                'value' => 'A'
        ),
        array (
                'text' => 'Diners Club',
                'key' => 'diners',
                'value' => 'D'
        ),
        array (
                'text' => 'JCB',
                'key' => 'jcb',
                'value' => 'J'
        ),
        array (
                'text' => 'Maestro International',
                'key' => 'maestro_int',
                'value' => 'O'
        ),
        array (
                'text' => 'Discover',
                'key' => 'discover',
                'value' => 'C'
        ),
        array (
                'text' => 'Carte Bleue',
                'key' => 'carte_bleue',
                'value' => 'B'
        )
    );

    static public function registerNamespace() {
        static $done = false;
        if (!$done) {
            $done = true;
            //Shopware()->Loader()->registerNamespace('Shopware', dirname(__FILE__) . '/');
            Shopware()->Loader()->registerNamespace('payone', dirname(__FILE__) . '/Components/');
        }
    }

	public function install() {
		self::registerNamespace();
//		if (!$this->assertVersionGreaterThen("3.5.5"))
//			throw new Enlight_Exception("This Plugin needs min shopware 3.5.5");
//
//		$depends = array ('Payment');
//
//		if (!$this->assertRequiredPluginsPresent($depends))
//			throw new Enlight_Exception("This plugin requires the followings plugins to be active: " . join(', ', $depends));
//
//		if (!function_exists('curl_init'))
//			throw new Enlight_Exception("This plugin requires the following php module to be active: curl");
//
//
		$this->createPayments();
		$this->createEvents();
		$this->createMenu();
		$this->createTables();
		$this->createAssigns();

		return parent::install();
	}

    /**
     * The onGetPluginController function is responsible to resolve the path to the pay one controller.
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetPluginController(Enlight_Event_EventArgs $args){

        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );
        return $this->Path(). 'Controllers/Backend/PayOne.php';
    }


	protected function createAssigns() {

		$shops_sql = "SELECT id FROM s_core_multilanguage";
		$shops = Shopware()->Db()->fetchAll($shops_sql);

		$groups_sql = "SELECT id FROM s_core_customergroups";
		$groups = Shopware()->Db()->fetchAll($groups_sql);

		$country_sql = 'SELECT id FROM s_core_countries WHERE countryiso="DE"';
		$country = Shopware()->Db()->fetchOne($country_sql);

		$payments = array ();
		$payments = array_merge($payments, $this->creditcards);
		$payments = array_merge($payments, $this->directdebits);
		$payments = array_merge($payments, $this->otherPayments);

		foreach ($payments as $payment) {
			foreach ($shops as $shop) {
				$sql = 'INSERT INTO s_bui_plg_payone_assigns (`key`, allowed_pk, allow_type) VALUES (?, ?, "s") on duplicate key update allow_type=allow_type';
				Shopware()->Db()->query($sql, array($payment['key'],$shop['id']));
//                $sql = 'INSERT INTO s_bui_plg_payone_assigns (`key`, allowed_pk, allow_type) VALUES ("' . $payment['key'] . '", ' . $shop['id'] . ', "s") on duplicate key update allow_type=allow_type';
//				Shopware()->Db()->exec($sql);
			}

			foreach ($groups as $group) {
				$sql = 'INSERT INTO s_bui_plg_payone_assigns (`key`, allowed_pk, allow_type) VALUES (?, ?, "g") on duplicate key update allow_type=allow_type';
				Shopware()->Db()->query($sql,array($payment['key'], $group['id']));
			}

			$sql = 'INSERT INTO s_bui_plg_payone_assigns (`key`, allowed_pk, allow_type) VALUES (?, ?, "c") on duplicate key update allow_type=allow_type';
			Shopware()->Db()->query($sql,array($payment['key'], $country));
		}
	}

	protected function findMenuEntry() {
        $parent = $this->Menu()->findOneBy('label', 'Zahlungen');
        
		return $parent;
	}

	protected function createMenu() 
    {
        $parent = $this->findMenuEntry();
        $select = Shopware()->Db()->select()->from('s_core_menu', array(new Zend_Db_Expr('max(position) as max'), new Zend_Db_Expr('min(position) as min')))
            ->where('parent=' . $parent->getId());
        $rows = Shopware()->Db()->fetchRow($select);
        
        $max = $min = 0;
        if ($rows) {
            $max = $rows['max'] + 1;
            $min = $rows['min'] - 1;
        }
    
        $item = $this->createMenuItem(array (
                'label' => self::LABEL,
                'onclick' => '',
                'class' => 'ico2 payoneicon',
                'active' => 1,
                'position' => $min,
                'parent' => $parent,
                'style' => 'background-position: 5px 5px;',
                'pluginID' => $this->getId()
        ));
        $this->Menu()->addItem($item);
    
        $parent = $item;
        $items[] = $this->createMenuItem(array (
                'label' => 'Konfiguration',
                'onclick' => 'openAction(\'BuiswPaymentPayone\' , \'Config\');',
                'class' => 'x-menu-item-icon  sprite-wrench_screwdriver',
                'active' => 1,
                'position' => 10,
                'parent' => $parent,
                'style' => 'background-position: 5px 5px;',
                'pluginID' => $this->getId()
        ));
    
//        $items[] = $this->createMenuItem(array (
//            'label' => 'Bestellungen',
//            'onclick' => 'openAction(\'BuiswPaymentPayone\' , \'Orders\');',
//            'class' => 'ico2 sticky_notes_pin',
//            'active' => 1,
//            'position' => 20,
//            'parent' => $parent,
//            'style' => 'background-position: 5px 5px;',
//            'pluginID' => $this->getId()
//        ));
        
        $items[] = $this->createMenuItem(array(
            'label' => 'Bestellungen',
            'class' => 'x-menu-item-icon sprite-sticky-notes-pin',
            'active' => 1,
            'position' => 10,
            'controller' => 'PayOne',
            'action' => 'Index',
            'parent' => $parent,
            'style' => 'background-position: 5px 5px;'
        ));
    
        $items[] = $this->createMenuItem(array (
                'label' => 'Protokolle',
                'onclick' => 'openAction(\'BuiswPaymentPayone\' , \'Logs\');',
                'class' => 'x-menu-item-icon  sprite-cards',
                'active' => 1,
                'position' => 30,
                'parent' => $parent,
                'style' => 'background-position: 5px 5px;',
                'pluginID' => $this->getId()
        ));
    
        $items[] = $this->createMenuItem(array (
                'label' => 'Transaktionen',
                'onclick' => 'openAction(\'BuiswPaymentPayone\' , \'Transactions\');',
                'class' => 'x-menu-item-icon sprite-cards',
                'active' => 1,
                'position' => 30,
                'parent' => $parent,
                'style' => 'background-position: 5px 5px;',
                'pluginID' => $this->getId()
                        ));
    
        foreach ($items as $item)
            $this->Menu()->addItem($item);
    
        $this->Menu()->save();
    
        // $parent = $items[0];
        $item = $this->createMenuItem(array (
                'label' => 'Informationen',
                'onclick' => 'openAction(\'BuiswPaymentPayone\' , \'Informations\');',
                'class' => 'ico2 payoneicon',
                'active' => 1,
                'position' => 30,
                'parent' => $parent,
                'style' => 'background-position: 5px 5px;',
                'pluginID' => $this->getId()
                        ));
    
        $this->Menu()->addItem($item);
    
        $this->Menu()->save();
	}

	protected function createTables() {

		$sql[] = 'CREATE TABLE IF NOT EXISTS ' . self::TABLE_CONFIG . '(
					`key` varchar(255) DEFAULT NULL,
					`val` varchar(255) DEFAULT NULL,
					UNIQUE KEY `key` (`key`)
			)';

		$sql[] = 'CREATE TABLE IF NOT EXISTS ' . self::TABLE_ASSIGNMENTS . '(
					`key` varchar(255) DEFAULT NULL,
					`allowed_pk` int(11) DEFAULT NULL,
					`allow_type` enum("c","s","g") DEFAULT NULL,
					UNIQUE KEY `key` (`key`,`allowed_pk`,`allow_type`)
			)';

		$sql[] = "CREATE TABLE IF NOT EXISTS " . self::TABLE_USERSETTINGS . " (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`userID` int(11) NOT NULL,
					`key` varchar(128) NOT NULL,
					`value` varchar(255) DEFAULT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `userID` (`userID`,`key`)
			)";

		$sql[] = "CREATE TABLE IF NOT EXISTS " . payone_ApiLogs::TABLE_NAME . "(
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`user_id` int(11) NOT NULL,
					`occoured` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					`api` enum('s','c') NOT NULL DEFAULT 's',
					`request` varchar(128) NOT NULL,
					`response` varchar(128) NOT NULL,
					`request_data` text,
					`response_data` text,
					PRIMARY KEY (`id`),
					KEY `user_id` (`user_id`),
					KEY `occoured` (`occoured`)
			)";

		$sql[] = "CREATE TABLE IF NOT EXISTS  " . payone_TransactionLogs::TABLE_NAME . "(
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`api_log_id` int(11) NOT NULL,
					`user_Id` int(11) NOT NULL,
					`order_number` int(11) NOT NULL DEFAULT '0',
					`occoured` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					`transaction_no` varchar(128) NOT NULL,
					`paymethod` varchar(128) NOT NULL,
					`customer_email` varchar(128) NOT NULL,
					`amount` decimal(15,2) DEFAULT NULL,
					`currency` char(3) NOT NULL,
					`status` varchar(128) NOT NULL,
					`data_array` text null,
					`mode` enum('t','l') NOT NULL,
					PRIMARY KEY (`id`),
					KEY `occoured` (`occoured`),
					KEY `user_Id` (`user_Id`),
					KEY `order_number` (`order_number`),
					KEY `transaction_no` (`transaction_no`),
					KEY `paymethod` (`paymethod`),
					KEY `customer_email` (`customer_email`),
					KEY `amount` (`amount`),
					KEY `currency` (`currency`),
					KEY `status` (`status`)
			)";

		$sql[] = "CREATE TABLE IF NOT EXISTS " . payone_CaptureAndRefund::TABLE_NAME . " (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`transaction_no` varchar(128) NOT NULL,
					`ammount` int(11) NOT NULL,
					`sequencenumber` int(11) NOT NULL,
					`api_log_id` int(11) NOT NULL,
					PRIMARY KEY (`id`),
					KEY `transaction_no` (`transaction_no`)
			)";

		$sql[] = "CREATE TABLE IF NOT EXISTS " . payone_BoniUsers::TABLE_NAME . " (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`user_id` int(11) NOT NULL,
					`type` enum('NO','IH','IA','IB') NOT NULL,
					`ampelwert` enum('G','Y','R') DEFAULT NULL,
					`infoscore` varchar(255) DEFAULT NULL,
					`date` date NOT NULL,
					PRIMARY KEY (`id`),
					KEY `date` (`date`)
			)";

		foreach ($sql as $s)
			Shopware()->Db()->exec($s);
	}

	private static $paymentId = null;

	protected static function getPaymentId() {
		if (!self::$paymentId) {
			$sql = 'select id from s_core_paymentmeans where name="' . self::NAME . '"';
			self::$paymentId = Shopware()->Db()->fetchOne($sql);
		}

		return self::$paymentId;
	}

	public function getVersion() {
		return '1.0.0';
	}

	/**
	 * Activate this plugin
	 *
	 * Sets the active flag in the payment row.
	 *
	 * @return bool
	 */
	public function enable() {
		$payment = $this->Payment();
        if($payment) {
		    $payment->active = 1;
		    $payment->save();
        }
//		$sql = 'update s_core_config set value=' . self::getPaymentId() . ' where name="sPAYMENTDEFAULT"';
//		Shopware()->Db()->exec($sql);

		return parent::enable();
	}

	/**
	 * Disable plugin method and sets the active flag in the payment row
	 *
	 * @return bool
	 */
	public function disable() {
		$payment = $this->Payment();

		if ($payment) {
			$payment->active = 0;
			$payment->save();
		}

		return parent::disable();
	}

	public function getInfo() {
		return include(dirname(__FILE__) . '/Meta.php');
	}

	public function uninstall() {
		$p = $this->Payment();

		if ($p) {
			$sql = 'delete from s_premium_dispatch_paymentmeans where paymentID=' . $p['id'];
			Shopware()->Db()->exec($sql);

			$sql = 'delete from s_core_paymentmeans where id=' . $p['id'];
			Shopware()->Db()->exec($sql);
		}

		$parent = $this->Menu()->findOneBy('label', self::LABEL);

		if ($parent->getId()) {
			$sql = 'delete from s_core_menu where parent=' . $parent->getId();
			Shopware()->Db()->exec($sql);

			$sql = 'delete from s_core_menu where id=' . $parent->getId();
			Shopware()->Db()->exec($sql);
		}

		// TODO zum testen auskommentiert - FÜR RELEASE EINKOMMETIEREN
		/*
		  $tables = array (self::TABLE_CONFIG, self::TABLE_ASSIGNMENTS, self::TABLE_USERSETTINGS, payoneApiLogs::TABLE_NAME, payoneTransactionLogs::TABLE_NAME, payoneBoniUsers::TABLE_NAME,
		  payoneCaptureAndRefund::TABLE_NAME);
		  foreach ($tables as $t) {
		  Shopware()->Db()->exec('drop table ' . $t);
		  }

		 */

		return parent::uninstall();
	}

	protected function createPayments() {
//		$paymentRow = Shopware()->Payments()->createRow(array (
//								'name' => self::NAME,
//								'description' => self::LABEL,
//								'class' => '../../../Shopware/Plugins/Default/Frontend/BuiswPaymentPayone/Controllers/Frontend/checkFields.php',
//								'additionaldescription' => '',
//								'action' => 'payment_' . self::NAME,
//								'template' => '../../../../../engine/Default/Plugins/Community/Frontend/BuiswPaymentPayone/Views/Frontend/payone.tpl',
//								'active' => 1,
//								'esdactive' => 1,
//								'pluginID' => $this->getId()
//						))->save();
        $paymentRow = Shopware()->Payments()->createRow(array (
								'name' => self::NAME,
								'description' => self::LABEL,
								'class' => '',
								'additionaldescription' => '',
								'action' => 'payment_' . self::NAME,
								'template' => 'payone.tpl',
								'active' => 1,
								'esdactive' => 1,
								'pluginID' => $this->getId()
						))->save();
	}

	protected function createEvents() {
		$event = $this->createEvent(
						'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Payment' . self::NAME, 'onGetControllerPathFrontendPayment'
		);
		$this->subscribeEvent($event);

		$event = $this->createEvent(
						'Enlight_Controller_Dispatcher_ControllerPath_Backend_' . self::NAME, 'onGetControllerPathBackend'
		);
		$this->subscribeEvent($event);

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PayOne',
            'onGetPluginController'
        );

		$hook = $this->createHook(
						'Shopware_Controllers_Frontend_Account', 'paymentAction', 'onpaymentAction', Enlight_Hook_HookHandler::TypeAfter, 0
		);
		$this->subscribeHook($hook);

		$hook = $this->createHook(
						'Shopware_Controllers_Frontend_Account', 'saveBillingAction', 'onsaveBillingAction', Enlight_Hook_HookHandler::TypeAfter, 0
		);
		$this->subscribeHook($hook);

		$event = $this->createEvent(
						'Enlight_Controller_Action_PostDispatch_Backend_Index', 'onPostDispatch'
		);
		$this->subscribeEvent($event);

		$event = $this->createEvent(
						'Enlight_Controller_Dispatcher_ControllerPath_Frontend_' . self::NAME, 'onGetControllerPathFrontend'
		);
		$this->subscribeEvent($event);

		$hook = $this->createHook(
						'sAdmin', 'sValidateStep3', 'onsValidateStep3', Enlight_Hook_HookHandler::TypeAfter, 0
		);
		$this->subscribeHook($hook);

		$event = $this->createEvent(
						'Shopware_Modules_Admin_GetPaymentMeanById_DataFilter', 'filterGetPaymentMeanById_DataFilter'
		);
		$this->subscribeEvent($event);

		$hook = $this->createHook(
						'sAdmin', 'sGetPaymentmean', 'onsGetPaymentmean', Enlight_Hook_HookHandler::TypeAfter, 0
		);
		$this->subscribeHook($hook);

		$hook = $this->createHook(
						'Shopware_Controllers_Frontend_Checkout', 'confirmAction', 'onpaymentAction', Enlight_Hook_HookHandler::TypeAfter, 0
		);
		$this->subscribeHook($hook);

		$hook = $this->createHook(
						'Shopware_Controllers_Frontend_Checkout', 'cartAction', 'oncartAction', Enlight_Hook_HookHandler::TypeAfter, 0
		);
		$this->subscribeHook($hook);

	}




	public function onsaveBillingAction() {
		$_SESSION['addresscheck'] = "CHANGE";
		unset($_SESSION['boni_proved']);
	}

	public function oncartAction(Enlight_Hook_HookArgs $args) {
		self::registerNamespace();
		$caller = $args->getSubject();
		$view = $caller->View();

		if ($view->sUserData->additional->user->id)
			return; // user eingeloggt

		$radioReplacement = '<input type="hidden" name="sPayment" value="' . self::getPaymentId() . '">';
		$view->extendsBlock('frontend_checkout_shipping_costs_payment', $radioReplacement);
	}

	// zum setzen der auf/abschlaege:
	public function onsGetPaymentmean(Enlight_Hook_HookArgs $args) {
		self::registerNamespace();
		$caller = $args->getSubject();
		$ret = $args->getReturn();

		if ($ret['id'] != self::getPaymentId())
			return;

		$usersettings = self::getUserSetting();
		$subpay = $usersettings['payonesubpay'];

		if ($subpay == 'creditcard' || $subpay == 'onlinepay')
			$key = 'invalid';
		else
			$key = $subpay;


		$conf = Shopware_Controllers_Backend_BuiswPaymentPayone::Config();
		$total = self::floatval($conf[$key . '_cost_total']);
		$percent = self::floatval($conf[$key . '_cost_percent']);
		$country = $conf[$key . '_cost_country'];

		foreach (split(';', $country) as $vals) {
			list ($c, $v) = split(':', $vals);

			$v = self::floatval($v);

			if ($c && $v !== null)
				$country_surcharge[$c] = $v;
		}

		if ($percent !== null)
			$ret['debit_percent'] = $percent;

		if ($total !== null)
			$ret['surcharge'] = $total;

		if ($country_surcharge)
			$ret['country_surcharge'] = $country_surcharge;

		$args->setReturn($ret);
	}

	protected static function floatval($v) {
		$v = trim($v);

		if (!$v)
			return null;

		$v = str_replace(',', '.', $v);

		if (!preg_match('/^-?(\d+)(\.?(\d+))?$/', $v))
			return null;

		return floatval($v);
	}

	static $subpaystranslations = array (
			'paypal' => array ('de' => 'PayPal', 'clearingtype' => 'wlt'),
			'onlinepay' => array ('de' => 'Online-&Uuml;berweisung', 'clearingtype' => 'sb'),
			'rechnung' => array ('de' => 'Rechnung', 'clearingtype' => 'rec'),
			'lastschrift' => array ('de' => 'Bankeinzug/Lastschrift', 'clearingtype' => 'elv'),
			'creditcard' => array ('de' => 'Kreditkarte', 'clearingtype' => 'cc'),
			'nachnahme' => array ('de' => 'Nachnahme', 'clearingtype' => 'cod'),
			'vorkasse' => array ('de' => 'Vorkasse', 'clearingtype' => 'vor'),
	);

	public static function filterGetPaymentMeanById_DataFilter(Enlight_Event_EventArgs $args) {
		$data = $args->getReturn();

		if ($data['id'] != self::getPaymentId())
			return $data;

		$subpay = self::getUserSetting('payonesubpay');

		if ($subpay)
			$subpay = self::$subpaystranslations[$subpay]['de'];

		$data['description'] = $subpay;

		return $data;
	}

	public static function onsValidateStep3(Enlight_Hook_HookArgs $args) {
		self::registerNamespace();
		$caller = $args->getSubject();
		$ret = $args->getReturn();
		if (is_array($ret) && $ret['paymentData']['id'] == self::getPaymentId()) {
			if (!array_key_exists('payonesubpay', $caller->sSYSTEM->_POST)) {
				$ret['checkPayment']['sErrorMessages'] = 'Bitte w&auml;hlen Sie eine Option';
				$ret['checkPayment']['sErrorFlag'] = array ('postbanksubpay');
				$args->setReturn($ret);
			} else {
				self::setUserSettings($caller, $caller->sSYSTEM->_POST['payonesubpay']);
			}
		}
	}

	protected static function setUserSettings($caller, $subpaymethod) {
		$post = $caller->sSYSTEM->_POST;
		$uid = intval($caller->sSYSTEM->_SESSION["sUserId"]);

		$sql = 'delete from ' . self::TABLE_USERSETTINGS . ' where `key`!= "payone_userid" and userID=' . $uid;
		Shopware()->Db()->exec($sql);

		$settings['payonesubpay'] = $subpaymethod;

		foreach ($post as $k => $v)
			if (preg_match('/^payonesubpay_/', $k))
				$settings[$k] = trim($v);


		$settings['payonesubpay_creditcard_checkdigit'] = "";

		if ($settings) {
			$sql = 'insert into ' . self::TABLE_USERSETTINGS . "(userID,`key`,value) values ($uid,?,?) on duplicate key update value=?";
			$p = Shopware()->Db()->prepare($sql);

			foreach ($settings as $k => $v)
				$p->execute(array ($k, $v, $v));
		}
	}

	static public function onGetControllerPathFrontend(Enlight_Event_EventArgs $args) {
		self::registerNamespace();
		return dirname(__FILE__) . '/Controllers/Frontend/BuiswPaymentPayone.php';
	}

	static function onPostDispatch(Enlight_Event_EventArgs $args) {
		self::registerNamespace();
		$response = $args->getSubject()->Response();

		if ($response->isException())
			return;

		/** @var $view Enlight_View_Default */
        $view = $args->getSubject()->View();
        
        if(!$view->hasTemplate()) {
            return ;
        }
        
		$view->addTemplateDir(dirname(__FILE__) . '/Views/Frontend/');
        /*
         * Own Icons are not possible yet. -JS
		$icon = "http://" . Shopware()->Config()->basepath . '/engine/Shopware/Plugins/Community/Frontend/BuiswPaymentPayone/images/payone_logo16x16.png';
		$style = '<style type="text/css">a.payoneicon { background: url(' . $icon . ') no-repeat 0px 0px transparent;}</style>';
		$view->extendsBlock('backend_index_css', $style, 'append');
        */
	}

	static function onGetControllerPathFrontendPayment(Enlight_Event_EventArgs $args) {
		self::registerNamespace();
		return dirname(__FILE__) . '/Controllers/Frontend/PaymentBuiswPaymentPayone.php';
	}

	public static function onGetControllerPathBackend(Enlight_Event_EventArgs $args) {
		self::registerNamespace();
		return dirname(__FILE__) . '/Controllers/Backend/BuiswPaymentPayone.php';
	}

	protected function checkCustomerAddress($request) {
        include_once 'Controllers/Frontend/PaymentBuiswPaymentPayone.php';
		$redirectURL = Shopware()->Front()->Router()->assemble(array (
				'controller' => 'account',
				'action' => 'billing',
				'appendSession' => true,
				'forceSecure' => true
						));

		$addressCheckType = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('addresscheck_type');

		if ($addressCheckType != "NO") {
			$user = Shopware()->System()->sMODULES['sAdmin']->sGetUserData();
            
            $languangeSql = 'SELECT `locale` FROM s_core_locales WHERE id = ' .(int) $user['additional']['user']['language'];
            $userLanguage = Shopware()->Db()->fetchOne($languangeSql);
            $tmpLang = explode('_', $userLanguage);
            $userLanguage = $tmpLang[0];
                
			$customerLang = $userLanguage;
			$user = $user['billingaddress'];

			$mode = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('bonitaets_mode');
			$applyNewAddress = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('applynewaddress');
			$redirectAddress = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('onaddresscheckerrorredirect');
			$params = Shopware_Controllers_Frontend_PaymentBuiswPaymentPayone::createFundamentalParams($mode);
			$countryBillingSQL = "SELECT countryiso FROM s_core_countries WHERE id=" . $user['countryID'];
			$countryBilling = Shopware()->Db()->fetchOne($countryBillingSQL);

			$params['request'] = "addresscheck";
			$params['addresschecktype'] = $addressCheckType;
			$params['firstname'] = $user['firstname'];
			$params['lastname'] = $user['lastname'];

			if ($user['company'])
				$params['company'] = $user['company'];

			$params['streetname'] = $user['street'];
			$params['streetnumber'] = $user['streetnumber'];
			$params['zip'] = $user['zipcode'];
			$params['city'] = $user['city'];
			$params['country'] = $countryBilling;
			$params['telephonenumber'] = $user['phone'];
			$params['language'] = $customerLang;

			ksort($params);

			$response = Shopware_Controllers_Frontend_PaymentBuiswPaymentPayone::curlCallAndApiLog($params, $err, $apilogid);

			if ($response['status'] == "VALID") {
				$_SESSION['addresscheck'] = "VALID";

				if ($response['secstatus'] == "10")
					return true;
				elseif ($response['secstatus'] == "20" && $applyNewAddress == "true") {
					$sql = 'UPDATE s_user_billingaddress SET street="' . $response['streetname'] . '", streetnumber="' . $response['streetnumber'] . '", zipcode="' . $response['zip'] . '", city="' . $response['city'] . '" WHERE userID="' . $user['id'] . '"';

					Shopware()->Db()->exec($sql);
				}
			} elseif ($response['status'] == "INVALID") {
				$_SESSION['addresscheck'] = "INVALID";

				if ($redirectAddress == "true") {
					header("LOCATION: " . $redirectURL);
					exit(0);
				} else
					return true;
			} elseif ($response['status'] == "ERROR") {
				$_SESSION['addresscheck'] = "ERROR";

				return true;
			} else
				return true;
		}
	}

	protected function checkConsumerScore($request) {
        include_once 'Controllers/Frontend/PaymentBuiswPaymentPayone.php';
		$return = new stdClass();
		$user = Shopware()->System()->sMODULES['sAdmin']->sGetUserData();
        $languangeSql = 'SELECT `locale` FROM s_core_locales WHERE id = ' .(int) $user['additional']['user']['language'];
        $userLanguage = Shopware()->Db()->fetchOne($languangeSql);
        $tmpLang = explode('_', $userLanguage);
        $userLanguage = $tmpLang[0];
        
		$customerLang = $userLanguage;
		$user = $user['billingaddress'];
		$user_id = $user['id'];

		$expireDays = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('bonitaets_lifetime');
		$boniType = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('bonitaets_type');
		$mode = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('bonitaets_mode');
		$defaultScoring = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('bonitaets_defaultindex');
		$minBasketValue = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('bonitaets_minbasketvalue');
		$basket = Shopware()->Modules()->Basket()->sGetBasket();
		$basketValue = $basket['AmountNumeric'];

		// if bonicheck deactivated or min basket value not reached, return false
		if ($boniType == "NO" || $basketValue < $minBasketValue) {
			$return->status = false;
			return $return;
		} else
			$return->status = true;

		// get loaded scoring key
		$scoringKey = ($boniType == 'IB') ? 'infoscore' : 'ampelwert';

		if (!$expireDays)
			$expireDays = 1;
		if (!$defaultScoring)
			$defaultScoring = 350;

		// select user scoring from db within the configured expire days
		$sql = 'SELECT ' . $scoringKey . ' FROM s_bui_plg_payone_boni_users WHERE user_id=? AND type=? AND DATE_SUB(CURDATE(),INTERVAL ?  DAY) <= date';

		$userScoring = Shopware()->Db()->fetchOne($sql,array($user_id, $boniType, $expireDays));

		// return stored user scoring
		if ($userScoring != "") {
			$return->$scoringKey = $userScoring;
			return $return;
		}

		$countryBillingSQL = "SELECT countryiso FROM s_core_countries WHERE id=" . $user['countryID'];
		$countryBilling = Shopware()->Db()->fetchOne($countryBillingSQL);

		$err = false;
		$apilogid = null;
		$params = Shopware_Controllers_Frontend_PaymentBuiswPaymentPayone::createFundamentalParams($mode);

		$params['request'] = "consumerscore";
		$params['addresschecktype'] = "NO";
		$params['consumerscoretype'] = $boniType;
		$params['firstname'] = $user['firstname'];
		$params['lastname'] = $user['lastname'];

		if ($user['company'])
			$params['company'] = $user['company'];

		$params['street'] = $user['street'] . " " . $user['streetnumber'];
		$params['zip'] = $user['zipcode'];
		$params['city'] = $user['city'];
		$params['country'] = $countryBilling;
		$params['telephonenumber'] = $user['phone'];
		$params['birthday'] = date("Ymd", strtotime($user['birthday']));
		$params['language'] = $customerLang;

		if ((int) $params['birthday'] < 0)
			unset($params['birthday']);

		ksort($params);

		$response = Shopware_Controllers_Frontend_PaymentBuiswPaymentPayone::curlCallAndApiLog($params, $err, $apilogid);


		$table = new payone_BoniUsers();

		$count = $table->count("where user_id=" . $user['id']);

		// if error occur or address not found, return default score
		if ($response['status'] == "ERROR" || $response['status'] == "INVALID") {
			$data = array ("infoscore" => $defaultScoring, "user_id" => $user['id'], "type" => $boniType, "date" => date("Y-m-d"));

			if ($count == 0)
				$table->insert($data);
			else
				$table->update($data, "user_id=" . $user['id']);

			$_SESSION['boni_proved']->infoscore = $defaultScoring;

			$return->infoscore = $defaultScoring;

			return $return;
		}

		if ($boniType == 'IB' && $response['score'] == "G")
			$response['scorevalue'] = 550;

		$data = array ("ampelwert" => $response['score'], "infoscore" => $response['scorevalue'], "user_id" => $user['id'], "type" => $boniType, "date" => date("Y-m-d"));

		if ($count == 0)
			$table->insert($data);
		else
			$table->update($data, "user_id=" . $user['id']);

		$return->ampelwert = $response['score'];
		$return->infoscore = $response['scorevalue'];
		$_SESSION['boni_proved'] = $return;

		return $return;
	}

    public static function onpaymentAction(Enlight_Event_EventArgs $args) {
		self::registerNamespace();
		$caller = $args->getSubject();
		$request = $args->getSubject()->Request();
        if ($request->sViewport == 'checkout' && ($request->sAction == 'confirm' || $request->sAction == 'index' || $request->success == 'payment' || $request->sAction == 'payment')) {
            // hier die extrabehandlung bei neuanmeldungen ...
            if ($caller->View()->sRegisterFinished) {
                $caller->View()->sFormData = array();
            } else {
                return;
            } // nothing to do here
        }
		if (!$caller->View()->sFormData)
			$caller->View()->sFormData = array ();

		if (isset($_SESSION['addresscheck']) && $_SESSION['addresscheck'] != "VALID")
			self::checkCustomerAddress($request);

		// boni check
		if ( !$_SESSION['boni_proved'])
			$cScore = self::checkConsumerScore($request);
		else
			$cScore = $_SESSION['boni_proved'];

		$allowedScores = array ("G" => array ("G", "Y", "R"), "Y" => array ("Y", "R"), "R" => array ("R"));

		$ret = $args->getReturn();


		$formData['payone_payId'] = self::getPaymentId();

		// paypal
		if (self::checkPaymentScore('paypal', $cScore, $allowedScores))
			$formData['paypal'] = true;

		// online pay
        foreach (Shopware_Controllers_Backend_BuiswPaymentPayone::$directdebits as $creditcard) {
            if (self::checkPaymentScore($creditcard['key'], $cScore, $allowedScores)) {
                $formData['onlinepay'][] = $creditcard;
            }
        }        

		$formData['eps_values'] = Shopware_Controllers_Backend_BuiswPaymentPayone::$eps_values;

		$formData['idl_values'] = Shopware_Controllers_Backend_BuiswPaymentPayone::$idl_values;

		// invoice
		if (self::checkPaymentScore("rechnung", $cScore, $allowedScores))
			$formData['rechnung'] = true;

		// direct debit
		if (self::checkPaymentScore("lastschrift", $cScore, $allowedScores))
			$formData['lastschrift'] = true;

		// credit card
		foreach (Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards as $creditcard)
			if (self::checkPaymentScore($creditcard['key'], $cScore, $allowedScores))
				$formData['creditcards'][] = $creditcard;

		for ($month = 1; $month <= 12; $month++)
			$formData['months'][str_pad($month, 2, '0', STR_PAD_LEFT)] = str_pad($month, 2, '0', STR_PAD_LEFT);

		for ($i = 0; $i <= 10; $i++)
			$formData['years'][(int) (date("Y") + $i) - 2000] = (int) date("Y") + $i;

		// cash on delivery
		if (self::checkPaymentScore('nachnahme', $cScore, $allowedScores))
			$formData['nachnahme'] = true;

		// prepayment
		if (self::checkPaymentScore("vorkasse", $cScore, $allowedScores))
			$formData['vorkasse'] = true;

		$caller->View()->sFormData += $formData;

		$caller->View()->sFormData += self::getUserSetting();
		$tmp = array ('payone_radio_classes' => ($request->sViewport == 'checkout' || ($request->sViewport == 'register' && $request->sAction == 'saveRegister') ? 'auto_submit' : ''));
		$caller->View()->sFormData += $tmp;

		if ($_GET['PAYONEERROR']) {
			$caller->View()->sErrorFlag = array (self::NAME);
			$caller->View()->sErrorMessages = 'Fehler: ' . $_GET['PAYONEERROR'];
		}

		$view = $caller->View();
		$view->addTemplateDir(dirname(__FILE__) . '/Views/');
        
		$radioReplacement = '<input type="hidden" name="register[payment]" value="' . self::getPaymentId() . '">';
		$pm = $view->sPaymentMeans ? $view->sPaymentMeans : $view->sPayments;

		$payonePayment = self::multi_array_key_search(self::getPaymentId(), $pm, 'id');
        // throws every other payment means out
		$view->sPaymentMeans = array ($payonePayment);

		if ($view->sPayments) {
			$view->sPayments = array ($payonePayment);
			$view->extendsBlock('frontend_checkout_payment_fieldset_input_radio', $radioReplacement);
		} else
			$view->extendsBlock('frontend_register_payment_fieldset_input_radio', $radioReplacement);
		return true;
	}

	protected function checkPaymentScore($key, $cScore, $allowedScores) {

		$res = ($cScore->status == false);
		$res |= in_array(Shopware_Controllers_Backend_BuiswPaymentPayone::Config($key . "_ampelwert"), $allowedScores[$cScore->ampelwert]);
		$res |= ($cScore->infoscore > Shopware_Controllers_Backend_BuiswPaymentPayone::Config($key . "_boniscore"));

		return (Shopware_Controllers_Backend_BuiswPaymentPayone::Config($key . "_active") == "on" && $res);
	}

	protected function multi_array_key_search($search, $array, $searchKey) {
		foreach ($array as $key => $array2)
			if ($array2[$searchKey] == $search)
				return $array2;
	}

	private static $userSettings = null;

	public static function getUserSetting($key = null) {
		if (self::$userSettings === null) {
			self::$userSettings = array ();
			$uid = Shopware()->Session()->sUserId;

			if (!$uid)
				return;
			$sql = 'select `key`,value from ' . self::TABLE_USERSETTINGS . ' where userID=?';
			$db = Shopware()->Db();
			$res = $db->fetchAll($sql,array($uid));

			foreach ($res as $r)
				self::$userSettings[$r['key']] = $r['value'];

			if (!array_key_exists('payonesubpay', self::$userSettings)) {
				self::$userSettings['payonesubpay'] = 'vorkasse';

				$sql = 'update s_user set paymentID=' . self::getPaymentId() . ' where id=?';
				Shopware()->Db()->query($sql,array($uid));

				$sql = 'insert into ' . self::TABLE_USERSETTINGS . '(userID,`key`,value) values (?,"payonesubpay", "vorkasse")';
				Shopware()->Db()->query($sql,array($uid));
			}
		}

		if ($key)
			return self::$userSettings[$key];

		return self::$userSettings;
	}

	protected function createStateDropDown($label, $group, $scope, $selected = null) {
		$sql = 'select id,description from s_core_states where `group`=? order by position,description';
		$db = Shopware()->Db();
		$res = $db->fetchAll($sql, array($group));

		$data = array ();
		$value = null;

		foreach ($res as $d) {
			if (!$selected)
				$selected = $d['id'];

			$descr = (string) utf8_encode($d['description']);

			if ($selected == $d['id'])
				$value = $descr;

			$data[] = array ((int) $d['id'], $descr . '|' . $d['id']);
		}

		if (!$value)
			$value = $data[0][1];

		$jdata = json_encode($data);

		return array ('label' => $label, 'value' => $selected, 'scope' => $scope, 'attributes' => array (
						'valueField' => 'myId', 'displayField' => 'displayText',
						'mode' => 'local',
						'triggerAction' => 'all',
						'store' => '
					new Ext.data.ArrayStore({
					id: 0,
					fields: [
						"myId",
						"displayText"
					],
					// Hier kommen die wichtigen Informationen rein
					data: ' . $jdata . '
				})'
						));
	}

	/**
	 * Fetches and returns PAYONE payment row instance.
	 *
	 * @return Shopware_Models_Payment
	 */
	protected function Payment() {
		return Shopware()->Payments()->fetchRow(array ('name=?' => self::NAME));
	}

}
error_reporting($originalReportLevel);
?>