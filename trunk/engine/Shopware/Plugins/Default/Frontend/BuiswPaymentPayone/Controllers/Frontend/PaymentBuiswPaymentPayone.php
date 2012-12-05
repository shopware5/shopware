<?php

/*
  ##############################################################################
  # Plugin for Shopware
  # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  # @version $Id$
  # @copyright:   found in /lic/copyright.txt
	# @author ak
  #
  ##############################################################################
 */

class Shopware_Controllers_Frontend_PaymentBuiswPaymentPayone extends Shopware_Controllers_Frontend_Payment {

	protected $config;
	protected $errorurl;

	public function __construct(Enlight_Controller_Request_Request $request, Enlight_Controller_Response_Response $response) {
		$this->config = Shopware()->Plugins()->Frontend()->BuiswPaymentPayone()->Config();
        
		$this->errorurl = $this->Front()->Router()->assemble(array(
				'action' => 'payment',
				'sTarget' => 'checkout',
				'sViewport' => 'account',
				'appendSession' => true,
				'forceSecure' => true
						));

		parent::__construct($request, $response);
	}

	// http://portal1.jii-services.com/shopware/shopware.php/sViewport,payment_BuiswPaymentPayone/sAction,transactionNotify
	public function transactionNotifyAction() {

		echo 'TSOK';
		if (! $this->Request()->getParam('key')) {
			exit(0);
		}
		$config = Shopware_Controllers_Backend_BuiswPaymentPayone::Config();
		if ($this->Request()->getParam('key') != md5 ($config['portal_key'])) {
			exit(0);
		}
		$txid = $this->Request()->getParam('txid');
		$sql = 'select * from ' . payone_TransactionLogs::TABLE_NAME  .
						" where transaction_no=?";
		$row = Shopware()->Db()->fetchRow($sql, array($txid));
		if (! $row) {
			exit (0);
		}

		$data_array = $row['data_array'] ? unserialize ($row['data_array']) : array();

		setlocale (LC_ALL, 'de_DE');
        $datum = strftime("%a., %e. %B %Y - %H:%M:%S", time());

		$new_status = $this->Request()->getParam('txaction');
		$orderNumber = $row['order_number'];
		Shopware()->Session()->sUserId = $row['user_id'];

		if($this->Request()->getParam('email'))
			$email = $this->Request()->getParam('email');
		else
			$email = Shopware()->System()->sMODULES['sAdmin']->sGetUserMailById();

	  //function logTransaction ($apiLogId, $transactionNumber, $paymethod, $custEmail, $amount, $currency, $status, $mode, $orderNumber = 0, $data_array = null) {
        $tid = payone_ApiLogger::logTransaction (0, $this->Request()->getParam('txid'), $this->Request()->getParam('clearingtype'), $email, $this->Request()->getParam('price'), $this->Request()->getParam('currency'), $this->Request()->getParam('txaction'),
					$this->Request()->getParam('mode'), $orderNumber, $this->Request()->getParams());

		// FAKE-Eintrag (REDIRECT) wieder entfernen
		$sql = 'DELETE FROM ' . payone_TransactionLogs::TABLE_NAME . ' WHERE `status`="REDIRECT"';
		Shopware()->Db()->exec($sql);

		$txactionKey = 'paystatus_' . $new_status;
		$new_status  = $config[$txactionKey] ? $config[$txactionKey] : '0|0';
		$new_status = explode('|', $new_status);
		$new_status = (int) $new_status[count($new_status) - 1];

		/* so wärs sauber .... aber wie soll ich an die $paymentUniqueId == temporaryID  hier im callback kommen ???
		$this->savePaymentStatus($transactionId, $paymentUniqueId, $paymentStatusId);
		 *
		 */
		if (! $orderNumber) {
			exit(0);
		}
		$sql = 'select id from s_order where ordernumber=?';
		$id = Shopware()->Db()->fetchOne($sql, array($orderNumber));
		if (! $id) {
			exit (0);
		}
		$sql = 'update s_order set status=0 where id=?';
		Shopware()->Db()->query($sql,array($id));

		$order = Shopware()->Modules()->Order();
        $order->setPaymentStatus($id, $new_status);
		exit(0);

	}
	public function endAction() {
		$txid = Shopware()->Session()->txid;
		// aussage kuchel:
		// hier wurde definitiv vorher synchron die transactionNotifyAction() aufgerufen.
		// also existiert auch der 'neue' status.
		$sql = 'select status from ' . payone_TransactionLogs::TABLE_NAME . ' where transaction_no=?';
		$status = Shopware()->Db()->fetchOne ($sql, array($txid));


		$user = Shopware()->System()->sMODULES['sAdmin']->sGetUserData();
		$paystatus = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('paystatus_' . $status);
		$paystatus = explode('|', $paystatus);
		$paystatus = (int) $paystatus[count($paystatus) - 1];

		$reference = Shopware()->Session()->reference;
		$transactionLogId = Shopware()->Session()->transactionLogId;

		$orderNumber = $this->saveOrder($txid, $reference, $paystatus);
		if ($transactionLogId) {
			payone_ApiLogger::assignOrderNumberToTransactionLog($transactionLogId, $orderNumber);
		}
		return $this->forward('finish', 'checkout', null, array()); // 'sUniqueID' => $hash));
	}

	/**
	 *
	 * @param array $params
	 * @param bool $err
	 * @return array
	 */
	static public function curlCallAndApiLog ($params, & $err, & $apilogid) {
		$ch = curl_init('https://api.pay1.de/post-gateway/');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 45);

		$result = curl_exec($ch);
		$err = false;
		if (curl_error($ch)) {
			$err = true; // technische probleme!!!
			$response[] = 'errormessage=' . curl_errno($ch) . ': ' . curl_error($ch);
		} else {
			$resp = explode("\n", $result);
			foreach ($resp as $r) {
				list ($key, $val) = explode('=', $r, 2);
				if ($key) {
					$response[$key] = $val;
				}
			}
		}
		$apilogid = payone_ApiLogger::logAPI(payone_ApiLogger::SERVER_API, $params['request'], $response['status'], $params, $response);
		curl_close($ch);
		return $response;
	}

	public function indexAction() {
		$user = Shopware()->System()->sMODULES['sAdmin']->sGetUserData();

		if (! $user['billingaddress']) {
			$this->redirect('shopware.php');
			return;
		}
		$userID = $user['billingaddress']['userID'];

		$params = $this->createBaseParams();
		$params = $this->createOptionalParams($params);

		$err = false;
		$apilogid = null;
        
        //error wird als referenz übergeben und kann somit ausserhalb dieser methode veraendert werden!!!
		$response = self::curlCallAndApiLog($params, $err, $apilogid);
		$status = $response['status'];
		if ($status == 'APPROVED' || $status == 'REDIRECT') {
			$transactionLogId =
			payone_ApiLogger::logTransaction($apilogid, $response['txid'], $params['clearingtype'], $params['email'], $params['amount'] / 100, $params['currency'], $status, $params['mode']);
		}

		if ($err) {
			//$this->forward (payment-select
			$url = $this->errorurl . '&PAYONEERROR=' . $response[0];
			$this->redirect($url);
			return;
		}

		if ($payoneuid = $response['userid']) {
			$sql = 'INSERT INTO ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_USERSETTINGS . '(userID, `key`, value) VALUES(?, "payone_userid", ?)';
			$sql .= 'on duplicate key update value=?';
			$payone_userID = Shopware()->Db()->query($sql,array($userID, $payoneuid ,$payoneuid));
		}
		switch ($status) {
			case 'APPROVED':

				$paystatus = $paystatusOriginal = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('paystatus_approved');
				$paystatus = explode('|', $paystatus);
				$paystatus = (int) $paystatus[count($paystatus) - 1];
				$orderNumber = $this->saveOrder($response['txid'], $params['reference'], $paystatus);

//				$sql = 'update s_order set status=-1 where ordernumber="' . $orderNumber . '"';
//				Shopware()->Db()->exec($sql);
				
				return $this->forward('finish', 'checkout', null, array('sUniqueID' => $params['reference'])); // 'sUniqueID' => $hash));
			case 'REDIRECT':
				Shopware()->Session()->txid = $response['txid'];
				Shopware()->Session()->reference = $params['reference'];
				Shopware()->Session()->transactionLogId = $transactionLogId;
				// session-vars for endAction
				$this->redirect($response['redirecturl']);
				return;
			case 'ERROR';
				$url = $this->errorurl . '&PAYONEERROR=' . $response['customermessage'];
				$this->redirect($url);
				return;
			default:
			throw new Exception("payone status == $status wasn't mentioned in documentation");
		}
	}

	protected function search_multi_array($search, $array) {
		foreach ($array as $key => $array2) {
			if (in_array($search, $array2))
				return $key;
		}
	}

	public static function createFundamentalParams($mode) {
		$params['mid'] = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('merchant_id');
		$params['aid'] = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('sub_account_id');
		$params['portalid'] = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('portal_id');
		$key = md5(Shopware_Controllers_Backend_BuiswPaymentPayone::Config('portal_key'));
		$params['key'] = $key;
		$params['mode'] = $mode;
		$params['solution_name'] = 'bui';
		$params['solution_version'] = Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::getVersion();
		$params['integrator_name'] = 'shopware';
		$params['integrator_version'] = Shopware()->Config()->Version;

		return $params;
	}

	protected function createBaseParams() {
		$user = Shopware()->System()->sMODULES['sAdmin']->sGetUserData();
		$usersettings = Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::getUserSetting();
        
		switch ($usersettings['payonesubpay']) {
			case "creditcard":
				$key = $this->search_multi_array($usersettings['payonesubpay_creditcard_card'], Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards);
				$subpay = Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards[$key]['key'];
				break;
			case "onlinepay":
				$key = $this->search_multi_array($usersettings['payonesubpay_creditcard_card'], Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards);
				$subpay = Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards[$key]['key'];
				break;
			default:
				$subpay = $usersettings['payonesubpay'];
				break;
		}

		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$clientIP = $_SERVER['REMOTE_ADDR'];
		else
			$clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];

		$countryBillingSQL = "SELECT countryiso FROM s_core_countries WHERE id=?";
		$countryBilling = Shopware()->Db()->fetchOne($countryBillingSQL, array($user['billingaddress']['countryID']));
        
		$countryShippingSQL = "SELECT countryiso FROM s_core_countries WHERE id=?" ;
		$countryShipping = Shopware()->Db()->fetchOne($countryShippingSQL, array($user['shippingaddress']['countryID']));

        $languangeSql = 'SELECT `locale` FROM s_core_locales WHERE id = ?' ;
        $userLanguage = Shopware()->Db()->fetchOne($languangeSql,array((int) $user['additional']['user']['language']));
        $tmpLang = explode('_', $userLanguage);
        $userLanguage = $tmpLang[0];
        
		$params = self::createFundamentalParams(Shopware_Controllers_Backend_BuiswPaymentPayone::Config($subpay . '_mode'));
        
		$params['clearingtype'] = Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::$subpaystranslations[$usersettings['payonesubpay']]['clearingtype'];
		$params['reference'] = substr($this->createPaymentUniqueId(), 0, 20);
		$params['amount'] = (int) ($this->getAmount() * 100);
		$params['currency'] = $this->getCurrencyShortName();

		//$params['param'] = 'mein parameter';
		// billing address
		$params['customerid'] = $user['billingaddress']['customernumber'];
		$params['firstname'] = $user['billingaddress']['firstname'];
		$params['lastname'] = $user['billingaddress']['lastname'];
		if ($user['billingaddress']['company']) {
			$params['company'] = $user['billingaddress']['company'];
			$params['salutation'] = "Firma";
		} else {
			if ($user['billingaddress']['salutation'] == "mr")
				$params['salutation'] = "Herr";
			else
				$params['salutation'] = "Frau";
		}
		$params['street'] = $user['billingaddress']['street'] . " " . $user['billingaddress']['streetnumber'];
		$params['zip'] = $user['billingaddress']['zipcode'];
		$params['city'] = $user['billingaddress']['city'];
		$params['country'] = $countryBilling;
		$params['email'] = $user['additional']['user']['email'];
		$params['telephonenumber'] = $user['billingaddress']['phone'];
		if (strpos ($user['billingaddress']['birthday'],'0000') === false) {
			$params['birthday'] = date("Ymd", strtotime($user['billingaddress']['birthday']));
		}
		$params['language'] = $userLanguage;
		$params['ip'] = $clientIP;

		// deliery address
		$params['shipping_firstname'] = $user['shippingaddress']['firstname'];
		$params['shipping_lastname'] = $user['shippingaddress']['lastname'];
		if ($user['shippingaddress']['company'])
			$params['shipping_company'] = $user['shippingaddress']['company'];
		$params['shipping_street'] = $user['shippingaddress']['street'] . " " . $user['shippingaddress']['streetnumber'];
		$params['shipping_zip'] = $user['shippingaddress']['zipcode'];
		$params['shipping_city'] = $user['shippingaddress']['city'];
		$params['shipping_country'] = $countryShipping;

		$authMode = $this->getAuthMode($subpay);
		$params['request'] = $authMode;

		ksort($params);

		return $params;
	}

	protected function createOptionalParams($params) {
		$usersettings = Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::getUserSetting();
		$subpay = $usersettings['payonesubpay'];
		$user = Shopware()->System()->sMODULES['sAdmin']->sGetUserData();

		$successurl = $this->Front()->Router()->assemble(array(
				'action' => 'end',
				'appendSession' => true,
				'forceSecure' => true
						));



		$cancelurl = $this->errorurl;

		switch ($subpay) {
			case 'lastschrift':
				$params['bankcountry'] = $user['additional']['country']['countryiso'];
				$params['bankaccount'] = $usersettings['payonesubpay_directdebit_accountnumber'];
				$params['bankcode'] = $usersettings['payonesubpay_directdebit_bankcode'];
				$params['bankaccountholder'] = $usersettings['payonesubpay_directdebit_depositor'];
				break;
			case 'onlinepay':
				$params['onlinebanktransfertype'] = $usersettings['payonesubpay_onlinepay_provider'];
				$params['bankcountry'] = $user['additional']['country']['countryiso'];
				$params['bankaccount'] = $usersettings['payonesubpay_onlinepay_accountnumber'];
				$params['bankcode'] = $usersettings['payonesubpay_onlinepay_bankcode'];

				if($usersettings['payonesubpay_onlinepay_provider'] == "EPS")
					$params['bankgrouptype'] = $usersettings['payonesubpay_onlinepay_bankgroup_eps'];
				elseif($usersettings['payonesubpay_onlinepay_provider'] == "IDL")
					$params['bankgrouptype'] = $usersettings['payonesubpay_onlinepay_bankgroup_idl'];

				$params['successurl'] = $successurl;
				$params['errorurl'] = $this->errorurl;
				$params['backurl'] = $cancelurl;
				break;
			case 'paypal':
				$params['wallettype'] = 'PPE';
				$params['successurl'] = $successurl;
				$params['errorurl'] = $this->errorurl;
				$params['backurl'] = $cancelurl;
				break;
			case 'creditcard':
				// es wird ausschliesslich die pseudocardpan übergeben!

				$params['cardtype'] = $usersettings['payonesubpay_creditcard_card'];
				$params['cardholder'] = $usersettings['payonesubpay_creditcard_depositor'];
				$params['pseudocardpan'] = $usersettings['payonesubpay_creditcard_pseudonumber'];
				$params['successurl'] = $successurl;
				$params['errorurl'] = $this->errorurl;
				$params['cancelurl'] = $cancelurl;
				break;
			case 'nachnahme':
				$params['shippingprovider'] = 'DHL';
				break;
		}

		return $params;
	}

	protected function getAuthMode($subpay) {
		switch ($subpay) {
			case 'creditcard':
				$key = $this->search_multi_array($usersettings['payonesubpay_creditcard_card'], Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards);
				$subpay = Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards[$key]['key'];
				break;
			case 'onlinepay':
				$key = $this->search_multi_array($usersettings['payonesubpay_creditcard_card'], Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards);
				$subpay = Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards[$key]['key'];
				break;
		}
		$m = Shopware_Controllers_Backend_BuiswPaymentPayone::Config($subpay . '_authmethod');

		return ($m == 'auth') ? 'authorization' : 'preauthorization';
	}

}
?>