<?php
require_once(dirname(__FILE__).'/../../library/sofortLib.php');
require_once(dirname(__FILE__).'/../Helper/Helper.php');
/**
 *
 * Controller for handling payments by sofort multipay
 *
 * $Date: 2012-07-23 13:03:20 +0200 (Mon, 23 Jul 2012) $
 * @version sofort 1.0  $Id: Sofort.php 4873 2012-07-23 11:03:20Z dehn $
 * @author SOFORT AG http://www.sofort.com (f.dehn@sofort.com)
 * @package Shopware 4, sofort.com
 *
 */
class Shopware_Controllers_Frontend_Sofort extends Shopware_Controllers_Frontend_Payment {

	/**
	 * reference to sofortLib
	 * @var sofortLib
	 */
	private $SofortLib_Multipay = null;
	
	private $Shopware = null;
	
	private $PnagInvoice = null;
	
	/**
	 * Language Snippets
	 * @var Object
	 */
	private $Snippets = null;
	
	private $testMode = false;
	
	/**
	 * Version
	 * @var string
	 */
	private $version = 'sofort_shopware4_1.0';
	
	/**
	 * a random unique id used as "token" for payments
	 * @var string
	 */
	private $uniqueId = '';
	
	/**
	 * User's configuration key issued by Payment Network
	 * @var string
	 */
	private $configKey = '';
	
	/**
	 *
	 * Global error codes
	 * @var array
	 */
	private $errorCodes = array();
	
	/**
	 *
	 * payment reason the user set in settings
	 * @var string
	 */
	private $paymentReason = '';
	
	private $paymentMethodString = '';
	
	private $paymentMethod = array();
	
	private $bankAccount = array();
	
	private $dateFormat = 'd.m.Y H:i:s';
	
	private $ShopwareUpdateHelper = null;
	
	
	/**
	 *
	 * Init this script
	 */
	public function init() {
		if(!$this->Shopware) {
			$this->setShopware(Shopware());
		}
		
		$this->ShopwareUpdateHelper = new ShopwareUpdateHelper($this->Shopware);
		$this->Snippets = $this->Shopware->Snippets();
		$this->View()->addTemplateDir(dirname(__FILE__).'/../../Templates');
		
		if(empty($this->configKey)) {
			$this->configKey = $this->Config()->sofort_api_key;
		}
		
		$this->SofortLib_Multipay = new SofortLib_Multipay($this->configKey);
		$this->SofortLib_Multipay->setVersion($this->version);
		$secret = $this->Config()->sofort_paymentsecret;
		$this->uniqueId = md5($secret.'|'.mt_rand());
		$this->Config()->sofort_payment_reason;
	}
	
	
	/**
	 * 
	 * Getter for Invoice
	 */
	public function getInvoice() {
		return $this->PnagInvoice;
	}
	
	
	/**
	 * 
	 * Set the test mode
	 * @param boolean $flag
	 */
	public function setTestMode($flag) {
		$this->testMode = $flag;
	}
	
	
	/**
	 * 
	 * Getter for test mode
	 */
	public function isInTestMode() {
		return $this->testMode;
	}
	
	
	/**
	 * 
	 * Setter for Shopware Object
	 * @param object $instance
	 */
	public function setShopware(Shopware $instance) {
		$this->Shopware = $instance;
	}
	
	
	/**
	 * 
	 * Setter for config key
	 * @param string $string
	 */
	public function setConfigKey($string) {
		$this->configKey = $string;
	}
	
	
	/**
	 * 
	 * Set the payment method
	 * @param array $method
	 */
	public function setPaymentMethod($method = array('name' => 'sofortrechnung_multipay')) {
		$this->paymentMethod = $method;
	}
	
	
	/**
	 * 
	 * Set the bank account
	 * @param array $account
	 */
	public function setBankAccount($account = array()) {
		$this->bankAccount = $account;
	}
	
	
	/**
	 * 
	 * Get the SofortLib object
	 */
	public function getSofortObject() {
		return $this->SofortLib_Multipay;
	}
	
	
	/**
	 *
	 * Index action
	 * @throws Exception
	 */
	public function indexAction() {
		$Object = null;
		
		switch ($this->getPaymentShortName()) {
			case 'sofortrechnung_multipay':
				$Object = $this->setSofortrechnung();
				break;
			case 'vorkassebysofort_multipay':
				$Object = $this->setVorkassebysofort();
				break;
			case 'sofortueberweisung_multipay':
				$Object = $this->setSofortueberweisung();
				break;
			case 'sofortlastschrift_multipay':
				$Object = $this->setSofortlastschrift();
				break;
			case 'lastschriftbysofort_multipay':
				$Object = $this->setLastschriftbysofort();
				break;
			default:
				throw new Exception('Error while processing payment. Not logged in?', 404);
				break;
		}
		
		return $Object;
	}
	
	
	/**
	 * @return bool|Shopware_Controllers_Frontend_PaymentSofort
	 */
	private function setSofortrechnung() {
		$this->paymentMethodString = 'sr';
		$basket = $this->getBasket();
		$user = $this->getUser();
		$billingAddress = $user['billingaddress'];
		$shippingAddress = $user['shippingaddress'];
		$billingLastname = $billingAddress['lastname'];
		$shippingLastname = $shippingAddress['lastname'];
		
		if($billingAddress['salutation'] === 'mr' ) {
			$billingSalutation = 2;
		} elseif($billingAddress['salutation'] === 'ms' ) {
			$billingSalutation = 3;
		}
		
		if($shippingAddress['salutation'] === 'mr' ) {
			$shippingSalutation = 2;
		} elseif($shippingAddress['salutation'] === 'ms' ) {
			$shippingSalutation = 3;
		}
		
		$countryInvoice = $user['additional']['country']['countryiso'];
		$countryShipping = (isset($user['additional']['countryShipping']['countryiso'])) ? $user['additional']['countryShipping']['countryiso'] : $countryInvoice;
		$this->PnagInvoice = new PnagInvoice($this->configKey, '');
		$this->PnagInvoice->setVersion($this->version);
		$this->PnagInvoice->setCustomerId($billingAddress['id']);
		$company = (isset($billingAddress['company'])) ? $billingAddress['company'] : '';
		$department = (!empty($company)) ? $billingAddress['department'] : '';
		$this->PnagInvoice->addInvoiceAddress($billingAddress['firstname'], $billingLastname, $billingAddress['street'], $billingAddress['streetnumber'], $billingAddress['zipcode'], $billingAddress['city'], $billingSalutation, $countryInvoice, $department, '',$company);
		$company = (isset($shippingAddress['company'])) ? $shippingAddress['company'] : '';
		$this->PnagInvoice->addShippingAddress($shippingAddress['firstname'], $shippingLastname, $shippingAddress['street'],$shippingAddress['streetnumber'], $shippingAddress['zipcode'], $shippingAddress['city'], $shippingSalutation, $countryShipping, $company);
		$this->PnagInvoice->setEmailCustomer($user['additional']['user']['email']);
		
		foreach ($basket['content'] as $article) {
			$surchargeNumber        = $this->getSurchargeNumber();
			$discountNumber         = $this->getDiscountNumber();
			$paymentDiscountNumber  = $this->getPaymentDiscountNumber();
			$paymentSurchargeNumber = $this->getPaymentSurchargeNumber();
			$productType            = 0;
			
			if($article['ordernumber'] == $surchargeNumber) {
				$article['articleID'] = 'swsurcharge';
				$productType = 2;
			} elseif($article['ordernumber'] == $discountNumber) {
				$article['articleID'] = 'swdiscount';
				$productType = 2;
			} elseif($article['ordernumber'] == $paymentDiscountNumber) {
				$article['articleID'] = 'swpayment';
				$productType = 2;
			} elseif($article['ordernumber'] == $paymentSurchargeNumber) {
				$article['articleID'] = 'swpaymentabs';
				$productType = 2;
			}
			
			// Get the real name
			$realArticleName = $this->getRealArticleName($article, array('Gutschein'));
			
			// notice the html encoded article names!
			if (strlen($realArticleName) != strlen($article['articlename'])) {
				$realArticleDescription = trim(substr(html_entity_decode($article['articlename']), strlen($realArticleName)));
			} else {
				$realArticleDescription = '';
			}
			
			// add "normal" articles to invoice
			$this->PnagInvoice->addItemToInvoice($article['articleID'].'|'.$article['ordernumber'], $article['ordernumber'], $realArticleName, $article['priceNumeric'], $productType, $realArticleDescription, $article['quantity'], $article['tax_rate']);
		}
		
		if ($basket['sShippingcosts'] > 0) {
			$shippingSnippet = $this->Snippets->getSnippet("sofort_multipay_checkout")->get("shipping_costs");
			$shippingSnippetShort = 'shpmntvk';
			// get dispatcher ID
			$dispatchId = (!empty($this->Shopware->Session()->sDispatch)) ? $this->Shopware->Session()->sDispatch : 0;
			// add shipping costs to invoice
			$this->PnagInvoice->addItemToInvoice($shippingSnippetShort.'|'.$shippingSnippetShort, $shippingSnippetShort, $shippingSnippet, $basket['sShippingcosts'], 1, $this->getDispatcherName($dispatchId), 1, $basket['sShippingcostsTax']);
		}
		
		$this->PnagInvoice->setSuccessUrl($this->makeSuccessUrl());
		$this->PnagInvoice->setTimeoutUrl($this->makeTimeoutUrl());
		$this->PnagInvoice->setAbortUrl($this->makeAbortUrl());
		$this->PnagInvoice->setNotificationUrl($this->makeNotificationUrl());
		
		$this->placeOrder();
		$this->PnagInvoice->setReason($this->Config()->paymentReason, $this->Config()->paymentReason2);
		//$this->PnagInvoice->setOrderId($orderId);
		$this->PnagInvoice->checkout();
		
		if ($this->isInTestMode()) {
			return $this;
		}
		
		if ($this->PnagInvoice->isError()) {
			$errorMsg = $this->PnagInvoice->getError();
			$allErrors = $this->PnagInvoice->getErrors();
			$i = 0;
			$errorCodes = array();
			
			// make some error message like errors[]=8054,8033
			foreach($allErrors as $error) {
				foreach($error as $key => $value) {
					if($key == 'code') {
						array_push($errorCodes, $value);
					}
				}
				
				$i++;
			}
			
			$this->redirect($this->makeAbortUrl($errorMsg, $errorCodes));
		} else {
			$this->setOrderTransaction($this->PnagInvoice->getTransactionId(), $this->uniqueId);
			$this->redirect($this->PnagInvoice->getPaymentUrl());
			return true;
		}
	}
	
	
	/**
	 * 
	 * initiate Vorkasse payment
	 */
	private function setVorkassebysofort() {
		$this->paymentMethodString = 'sv';
		$account = empty($this->bankAccount) ? $this->getBankAccount() : $this->bankAccount;
		// get customer protection
		$svCustomerProtection = $this->Config()->vorkassebysofort_customer_protection;
		
		if ($svCustomerProtection == 'on' || $svCustomerProtection == 1) {
			$this->SofortLib_Multipay->setSofortvorkasseCustomerprotection();
		} else {
			$this->SofortLib_Multipay->setSofortvorkasse();
		}
		
		$user = $this->getUser();
		$this->SofortLib_Multipay->setAmount($this->getAmount());
		$this->SofortLib_Multipay->setEmailCustomer($user['additional']['user']['email']);
		$this->SofortLib_Multipay->setSuccessUrl($this->makeSuccessUrl());
		$this->SofortLib_Multipay->setTimeoutUrl($this->makeTimeoutUrl());
		$this->SofortLib_Multipay->setAbortUrl($this->makeAbortUrl());
		$this->SofortLib_Multipay->setNotificationUrl($this->makeNotificationUrl());
		$orderId = $this->placeOrder();
		$this->SofortLib_Multipay->setReason($this->Config()->paymentReason, $this->Config()->paymentReason2);
		
		// if this is called a 'test transaction', add a sender account
		if(getenv('test_sv') == true) {
			$this->SofortLib_Multipay->setSenderAccount('00000', '12345', 'Tester Testaccount');
		}
		
		$this->SofortLib_Multipay->sendRequest();
		
		if($this->isInTestMode()) {
			return $this;
		}
		
		if($this->SofortLib_Multipay->isError()) {
			$errorMsg = $this->SofortLib_Multipay->getError();
			$allErrors = $this->SofortLib_Multipay->getErrors();
			$errorCodes = array();
			$i = 0;
			
			// make some error message like errors[]=8054,8033
			foreach ($allErrors as $error) {
				foreach($error as $key => $value) {
					if ($key == 'code') {
						array_push($errorCodes, $value);
					}
				}
				
				$i++;
			}
			
			$this->redirect($this->makeAbortUrl($errorMsg, $errorCodes));
		} else {
			$this->setOrderTransaction($this->SofortLib_Multipay->getTransactionId(), $this->uniqueId);
			$this->redirect($this->SofortLib_Multipay->getPaymentUrl());
			return true;
		}
	}
	
	
	/**
	 * 
	 * Initiate Sofortueberweisung payment
	 */
	private function setSofortueberweisung() {
		$this->paymentMethodString = 'su';
		$basket = $this->getBasket();
		$suCustomerProtection = $this->Config()->sofort_ueberweisung_customer_protection;
		
		if ($suCustomerProtection == 1) {
			$this->SofortLib_Multipay->setSofortueberweisungCustomerprotection();
		} else {
			$this->SofortLib_Multipay->setSofortueberweisungCustomerprotection(false);
		}
		
		$cartIdString = $this->Snippets->getSnippet("sofort_multipay_checkout")->get("cart_id");
		$this->SofortLib_Multipay->setAmount($this->getAmount());
		$this->SofortLib_Multipay->setSuccessUrl($this->makeSuccessUrl());
		$this->SofortLib_Multipay->setTimeoutUrl($this->makeTimeoutUrl());
		$this->SofortLib_Multipay->setAbortUrl($this->makeAbortUrl());
		$this->SofortLib_Multipay->setNotificationUrl($this->makeNotificationUrl());
		$orderId = $this->placeOrder();
		// Hier wird ggf. die Ordernumber benötigt um die in die Banküberweisung mit einzubauen
		// Damit der Shopbetreiber den Vorgang einfacher zu einem Einkauf zuordnen kann
		// Jetzt wird die Temporäre Order ID zurückgegeben.
		$this->SofortLib_Multipay->setReason($this->Config()->paymentReason, $this->Config()->paymentReason2);
		$this->SofortLib_Multipay->sendRequest();
		
		if($this->isInTestMode()) {
			return $this;
		}
		
		if($this->SofortLib_Multipay->isError()) {
			$errorMsg = $this->SofortLib_Multipay->getError();
			$allErrors = $this->SofortLib_Multipay->getErrors();
			$i = 0;
			$errorCodes = array();
			
			// make some error message like errors[]=8054,8033
			foreach ($allErrors as $error) {
				foreach ($error as $key => $value) {
					if ($key == 'code') {
						array_push($errorCodes, $value);
					}
				}
				
				$i++;
			}
			
			$this->redirect($this->makeAbortUrl($errorMsg, $errorCodes));
		} else {
			//$orderNumber = $this->saveOrder($this->SofortLib_Multipay->getTransactionId(), $this->uniqueId, 17);
			$this->setOrderTransaction($this->SofortLib_Multipay->getTransactionId(), $this->uniqueId);
			$this->redirect($this->SofortLib_Multipay->getPaymentUrl());
			return true;
		}
	}
	
	
	/**
	 *
	 * Lastschrift is assembled here
	 */
	private function setSofortlastschrift() {
		$this->paymentMethodString = 'sl';
		$account = empty($this->bankAccount) ? $this->getBankAccount() : $this->bankAccount;
		$basket = $this->getBasket();
		$this->SofortLib_Multipay->setSofortlastschrift();
		$cartIdString = $this->Snippets->getSnippet("sofort_multipay_checkout")->get("cart_id");
		$this->SofortLib_Multipay->setAmount($this->getAmount());
		$user = $this->getUser();
		$user = $user['billingaddress'];
		$this->SofortLib_Multipay->setSuccessUrl($this->makeSuccessUrl());
		$this->SofortLib_Multipay->setTimeoutUrl($this->makeTimeoutUrl());
		$this->SofortLib_Multipay->setAbortUrl($this->makeAbortUrl());
		$this->SofortLib_Multipay->setNotificationUrl($this->makeNotificationUrl());
		$salutation = 2;
		
		if ($user['salutation'] == 'ms') {
			$salutation = 3;
		}
		
		$countryInvoice = (isset($user['additional']['country']['countryiso'])) ? $user['additional']['country']['countryiso'] : '';
		$this->SofortLib_Multipay->setSofortlastschriftAddress($user['firstname'], $user['lastname'], $user['street'],$user['streetnumber'], $user['zipcode'], $user['city'], $salutation, $countryInvoice);
		$orderId = $this->placeOrder();
		$this->SofortLib_Multipay->setReason($this->Config()->paymentReason, $this->Config()->paymentReason2);
		$this->SofortLib_Multipay->sendRequest();
		
		if ($this->isInTestMode()) {
			return $this;
		}
		
		if ($this->SofortLib_Multipay->isError()) {
			$errorMsg = $this->SofortLib_Multipay->getError();
			$allErrors = $this->SofortLib_Multipay->getErrors();
			$errorCodes = array();
			$i = 0;
			
			// make some error message like errors[]=8054,8033
			foreach ($allErrors as $error) {
				foreach ($error as $key => $value) {
					if ($key == 'code') {
						array_push($errorCodes, $value);
					}
				}
				
				$i++;
			}
			
			$this->redirect($this->makeAbortUrl($errorMsg, $errorCodes));
		} else {
			$this->setOrderTransaction($this->SofortLib_Multipay->getTransactionId(), $this->uniqueId);
			$this->redirect($this->SofortLib_Multipay->getPaymentUrl());
			return true;
		}
	}
	
	
	/**
	 *
	 * Lastschrift by sofort is assembled here
	 */
	private function setLastschriftbysofort() {
		$this->paymentMethodString = 'ls';
		$account = empty($this->bankAccount) ? $this->getBankAccount() : $this->bankAccount;
		$basket = $this->getBasket();
		$cartIdString = $this->Snippets->getSnippet("sofort_multipay_checkout")->get("cart_id");
		
		$this->SofortLib_Multipay->setLastschrift();
		$this->SofortLib_Multipay->setSenderAccount($account['ls_bank_code'], $account['ls_account_number'], utf8_encode($account['ls_holder']));
		$this->SofortLib_Multipay->setAmount($this->getAmount());
		$this->SofortLib_Multipay->setSuccessUrl($this->makeSuccessUrl());
		$this->SofortLib_Multipay->setTimeoutUrl($this->makeTimeoutUrl());
		$this->SofortLib_Multipay->setAbortUrl($this->makeAbortUrl());
		$this->SofortLib_Multipay->setNotificationUrl($this->makeNotificationUrl());
		
		$user = $this->getUser();
		$user = $user['billingaddress'];
		$salutation = 2;
		
		if($user['salutation'] == 'ms') {
			$salutation = 3;
		}
		
		$countryInvoice = (isset($user['additional']['country']['countryiso'])) ? $user['additional']['country']['countryiso'] : '';
		$this->SofortLib_Multipay->setLastschriftAddress($user['firstname'], $user['lastname'], $user['street'], $user['streetnumber'], $user['zipcode'], $user['city'], $salutation, $countryInvoice);
		$orderId = $this->placeOrder();
		$this->SofortLib_Multipay->setReason($this->Config()->paymentReason, $this->Config()->paymentReason2);
		$this->SofortLib_Multipay->sendRequest();
		
		if ($this->isInTestMode()) {
			return $this;
		}
		
		if ($this->SofortLib_Multipay->isError()) {
			$errorMsg = $this->SofortLib_Multipay->getError();
			$allErrors = $this->SofortLib_Multipay->getErrors();
			$errorCodes = array();
			$i = 0;
			
			// make some error message like errors[]=8054,8033
			foreach ($allErrors as $error) {
				foreach ($error as $key => $value) {
					if ($key == 'code') {
						array_push($errorCodes, $value);
					}
				}
				
				$i++;
			}
			
			$this->redirect($this->makeAbortUrl($errorMsg, $errorCodes));
		} else {
			$this->setOrderTransaction($this->SofortLib_Multipay->getTransactionId(), $this->uniqueId);
			$this->redirect($this->SofortLib_Multipay->getPaymentUrl());
			return true;
		}
	}
	
	
	/**
	 * 
	 * Places an order with the associated payment method
	 */
	private function placeOrder() {
		if(empty($this->paymentMethod)) {
			$this->paymentMethod = $this->ShopwareUpdateHelper->getPaymentDetails($this->getPaymentShortName());
		}
		
		// save in own table
		$sql = 'INSERT INTO `sofort_orders` (`paymentMethod`, `paymentDescription`, `transactionId`, `secret`, `paymentStatus`)
		VALUES (?, ?, "empty", ?, "pending");';
		$fields = array(
				$this->paymentMethod['name'],
				$this->paymentMethod['description'],
				$this->uniqueId,
		);
		$this->Shopware->Db()->query($sql, $fields);
		
		if ($this->PnagInvoice) {
			$this->PnagInvoice->refreshTransactionData();
		}
		
		$comment = '';
		
		switch($this->paymentMethod['name']) {
			case 'sofortueberweisung_multipay' :
				$comment = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_su_pending');
				break;
			case 'vorkassebysofort_multipay' :
				$comment = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sv_pending');
				break;
			case 'sofortrechnung_multipay' :
				$comment = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sr_pending');
				break;
			case 'sofortlastschrift_multipay' :
				$comment = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_sl_pending');
				break;
			case 'lastschriftbysofort_multipay' :
				$comment = $this->Snippets->getSnippet('sofort_multipay_bootstrap')->get('sofort_multipay_ls_pending');
				break;
			default: die('something strange occured here');
		}
		
		$basket = $this->getBasket();
		$user = $this->getUser();
		$userComment = (isset($_SESSION['Shopware']['sOrderVariables'])) ? $_SESSION['Shopware']['sOrderVariables']->getArrayCopy() : '';
		
		if(empty($userComment['sComment'])) {
			$userComment['sComment'] = '';
		}
		
		return substr($this->uniqueId, 20);
	}
	
	
	/**
	 * 
	 * Get the real article name (some html entities might be included)
	 * @param array $article
	 * @param array $excludes
	 */
	private function getRealArticleName($article, $excludes = array()) {
		if(in_array($article['articlename'] , $excludes)) {
			return $article['articlename'];
		}
		$realArticleName = $this->getArticleName($article['articleID']);
		
		// only empty, if article is shipping costs or sth. like this
		if(empty($realArticleName)) {
			$realArticleName = $article['articlename'];
		}
		
		$realArticleName = html_entity_decode($realArticleName);
		return $realArticleName;
	}
	
	
	/**
	 * 
	 * Getter for voucher description
	 * @param string $voucherId
	 */
	private function getVoucherDescription($voucherId) {
		$sql = 'SELECT description FROM s_emarketing_vouchers WHERE id = ?';
		return $this->Shopware->Db()->fetchOne($sql, array($voucherId));
	}
	
	
	/**
	 * 
	 * Getter for article name
	 * @param int $articleId
	 */
	private function getArticleName($articleId) {
		$sql = 'SELECT name FROM s_articles WHERE id = ?';
		return $this->Shopware->Db()->fetchOne($sql, array($articleId));
	}
	
	
	private function getDispatcherName($dispatchId) {
		if($dispatchId == 0) return '';
		$sql = 'SELECT name FROM s_premium_dispatch WHERE id = ?';
		return $this->Shopware->Db()->fetchOne($sql, array($dispatchId));
	}
	
	
	/**
	 * 
	 * Getter for surcharge number
	 */
	private function getSurchargeNumber() {
		return Shopware()->Config()->surchargenumber;
	}
	
	
	/**
	 * 
	 * Getter for discount number
	 */
	private function getDiscountNumber() {
		return Shopware()->Config()->discountNumber;
	}
	
	
	/**
	 * 
	 * Getter for Payment Discount number
	 */
	private function getPaymentDiscountNumber() {
		return Shopware()->Config()->paymentSurchageNumber;
	}
	
	
	/**
	 * 
	 * Getter for Payment Surcharge number
	 */
	private function getPaymentSurchargeNumber() {
		return Shopware()->Config()->paymentSurchargeAbsoluteNumber;
	}
	
	
	/**
	 * 
	 * Setter for order's transaction id
	 * @param string $transactionId
	 * @param string $uniqueId
	 */
	private function setOrderTransaction($transactionId, $uniqueId) {
		$sql = 'UPDATE `sofort_orders` SET transactionId = ? WHERE secret = ?';
		$fields = array(
				$transactionId,
				$uniqueId
		);
		$this->Shopware->Db()->query($sql, $fields);
		return true;
	}
	
	
	/**
	 *
	 * Assembles the success URL
	 * @return URL
	 */
	private function makeSuccessUrl() {
		$config = array(
				'action' => 'end',
				'transactionId' => '-TRANSACTION-',
				'unique' => $this->uniqueId,
				'appendSession' => true,
				'forceSecure' => true
		);
		return $this->Front()->Router()->assemble($config);
	}
	
	
	/**
	 *
	 * Assembles the success URL
	 * @return URL
	 */
	private function makeTimeoutUrl() {
		$config = array(
				'action' => 'timeout',
				'transactionId' => '-TRANSACTION-',
				'unique' => $this->uniqueId,
				'appendSession' => true,
				'forceSecure' => true
		);
		return $this->Front()->Router()->assemble($config);
	}
	
	
	/**
	 *
	 * Assembles the abort URL
	 * should direct to controller "checkout"...?
	 * @return URL
	 */
	private function makeAbortUrl($message = '', $errorCodes = array()) {
		$config = array(
				'action' => 'cancel',
				'unique' => $this->uniqueId,
				'transactionId' => '-TRANSACTION-',
				'appendSession' => true,
				'forceSecure' => true,
		);
		
		if ($message != '') {
			$config['message'] = $message;
		}
		
		if (is_array($errorCodes)) {
			$config['errors'] = $errorCodes;
		}
		
		$abortUrl = $this->Front()->Router()->assemble($config);
		return $abortUrl;
	}
	
	
	/**
	 * Assembles the notification URL for notifications coming from sofort.com
	 * @return URL
	 */
	private function makeNotificationUrl() {
		$config = array(
				'controller' => 'sofort_notification',
				'appendSession' => true,
				'transactionId' => '-TRANSACTION-',
				'unique' => $this->uniqueId,
		);
		$notificationUrl = $this->Front()->Router()->assemble($config);
		return $notificationUrl.'&transactionId=-TRANSACTION-';
	}
	
	
	/**
	 *
	 * Fetches the payment secret from DB
	 * @param unknown_type $transactionId
	 */
	private function getPayment($transactionId) {
		$sql = 'SELECT secret FROM `sofort_orders` WHERE `transactionId` = ?';
		$fields = array(
				$transactionId,
		);
		return $this->Shopware->Db()->fetchOne($sql, $fields);
	}
	
	
	/**
	 * 
	 * Setter for the payment status
	 * @param string $transactionId
	 * @param int $status
	 * @throws Exception
	 */
	private function setPaymentStatus($transactionId, $status) {
		$sql = 'UPDATE `sofort_orders` SET paymentStatus = ? WHERE `transactionId` = ?';
		$fields = array(
				$status,
				$transactionId,
		);
		Shopware()->Db()->query($sql, $fields);
		return true;
	}
	
	
	/**
	 *
	 * Fetch bank account for sofortlastschrift
	 * @param Enlight_Event_EventArgs $args
	 * @return Array
	 */
	private function getBankAccount() {
		$userData = $this->getUser();
		$userId = $userData['billingaddress']['id'];
		$sql = 'SELECT `ls_account_number`, `ls_bank_code`, `ls_holder` FROM `sofort_user_settings` WHERE userID = ?';
		$fields = array(
				$userId,
		);
		$bankAccount = $this->Shopware->Db()->fetchAll($sql, $fields);
		
		if (!empty($bankAccount)) {
			return $bankAccount[0];
		}
		
		return array();
	}
	
	
	/**
	 * 
	 * Getter for order's id
	 * @param string $transactionId
	 */
	private function getOrderId($transactionId) {
		$sql = 'SELECT `ordernumber` FROM `s_order` WHERE `transactionID` = ?';
		$fields = array(
				$transactionId,
		);
		$orderId = $this->Shopware->DB()->fetchOne($sql, $fields);
		return $orderId;
	}
	
	
	/**
	 * 
	 * Getter for order's temporary id
	 * @param string $transactionId
	 */
	private function getOrderTemporaryId($transactionId) {
		$sql = 'SELECT `temporaryID` FROM `s_order` WHERE `transactionID` = ?';
		$fields = array(
				$transactionId,
		);
		$temporaryId = $this->Shopware->DB()->fetchOne($sql, $fields);
		return $temporaryId;
	}
	
	
	/**
	 * 
	 * Getter for pending state
	 */
	private function getPendingState() {
		return $this->Config()->sofort_pending_state;
	}
	
	
	/**
	 * 
	 * init order and timeline tables
	 */
	private function initOrderTablesAndTimeLine() {
		$paymentDetails = $this->ShopwareUpdateHelper->getPaymentDetails($this->paymentMethod);
		$transactionId = $this->Request()->getParam('transactionId');
		$orderId = $this->getOrderId($transactionId);
		$basket = $this->getBasket();
		$this->ShopwareUpdateHelper->initOrderTablesAndTimeLine($transactionId, $orderId, $this->paymentMethod, $basket);
	}
	
	
	/**
	 * 
	 * Set the status to canceled (table sofort_status)
	 * @param string $transactionId
	 */
	private function setSofortStatusCanceled($transactionId) {
		$sql = 'INSERT INTO sofort_status
		(sofort_product_id, status_id, status, status_reason, invoice_status, invoice_objection)
		VALUES ((SELECT id FROM `sofort_products` WHERE transactionId = ?), "", "canceled", "canceled", "canceled", "")
		';
		$fields = array(
				$transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
		$time = date($this->dateFormat, time());
		$cancelString = $this->Snippets->getSnippet('sofort_multipay_cancel')->get('order_cancel');
		$cancelString = $time.' '.$cancelString;
		$sql = 'UPDATE `sofort_products` SET `comment` = ?, `date_modified` = NOW() WHERE `transactionId` = ?';
		$fields = array(
				$cancelString,
				$transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * Set the order's comment according to the message sent by notification
	 * @param string $transactionId
	 * @param string $comment
	 * @param boolean $overwrite overwrite the old comments, set the current one as new
	 */
	private function setOrderComment($transactionId, $comment, $overwrite = false) {
		$oldComment = ''; $newComment = '';
		
		if (!$overwrite) { // if false, old comment is not being overwritten
			$sql = 'SELECT `comment` FROM `s_order` WHERE `transactionID` = ?';
			$fields = array(
					$transactionId,
			);
			$oldComment = $this->Shopware->Db()->FetchOne($sql, $fields);
		}
		
		$actDate = date($this->dateFormat, time());
		// add new comment at the end of the old one
		$newComment = (empty($oldComment)) ? $actDate.' - '.$comment: $oldComment.'<br />'.$actDate.' - '.$comment;
		$newComment = str_replace('{{paymentMethodStr}}', $this->paymentMethodString, $newComment);
		$newComment = str_replace('{{tId}}', '', $newComment);
		$newComment = str_replace('{{time}}', '', $newComment);
		
		// update the order comment
		$sql = 'UPDATE `s_order` SET `comment` = ? WHERE `transactionID` = ?';
		$fields = array(
				$newComment,
				$transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
	}
	
	
	/**
	 * 
	 * Does an order exist
	 * @param boolean $transactionId
	 */
	private function orderExists($transactionId) {
		$sql = 'SELECT order_id FROM `sofort_products` WHERE transactionId = ? AND order_id != 0';
		$fields = array(
				$transactionId,
		);
		return $this->Shopware->Db()->fetchOne($sql, $fields);
	}
	
	
	/**
	 * If payment has been canceled whether if the user requested to or if some errors occured
	 */
	public function cancelAction() {
		$this->paymentMethod = $this->ShopwareUpdateHelper->getPaymentDetails($this->getPaymentShortName());
		$this->View()->loadTemplate('Frontend/payment_cancel.tpl');
		$message = $this->Request()->getParam('message');
		$transactionId = $this->Request()->getParam('transactionId');
		$this->uniqueId = $this->Request()->getParam('unique');
		$parameters = $this->Request()->getParams();
		$errorString = $this->getErrorMessagesFromUrlParameters($parameters);
		
		// set the canceled order's status to >canceled<
		if (!empty($transactionId) && !empty($this->uniqueId) && empty($this->errorCodes)) {
			$sql = 'SELECT COUNT(id) FROM `sofort_orders` WHERE `transactionId` = ? AND `secret` = ?';
			$fields = array(
				$transactionId,
				$this->uniqueId,
			);
			$authorized = $this->Shopware->Db()->fetchOne($sql, $fields);
			
			// decide, whether this function call is authorized or not...
			if ($authorized) {
				$this->initOrderTablesAndTimeLine();
				$this->setOrderCanceled($transactionId);
				$this->setSofortStatusCanceled($transactionId);
			}
		}
		
		// remove last comma if existing
		$errorString = substr($errorString, 0, -2);
		$this->View()->errorString = $errorString;
		$this->View()->sofortPaymentMethod = $this->ShopwareUpdateHelper->getPaymentDetails($this->paymentMethod);
		$this->View()->message = $message;
		$this->View()->sAmountWithTax = 10;
		$this->View()->errorCodes = $this->errorCodes;
		
		$this->View()->sBasket = $this->getBasket();
		$checkoutUrl = $this->Front()->Router()->assemble(array(
			'controller' => 'checkout',
			'action' => 'cart'
		));
		
		$this->View()->checkoutUrl = $checkoutUrl;
		return;
	}
	
	
	/**
	 * Parse GET parameters and set global error codes accordingly
	 * @param array $parameters
	 * @return string
	 */
	private function getErrorMessagesFromUrlParameters($parameters) {
		$errorString = '';
		
		foreach ($parameters as $key => $value) {
			// fetch all errors from request (errors[1], errors[2], ...)
			if (preg_match('/errors\[\d\]/', $key)) {
				
				$error = '';
				if (strpos($value, '.') !== false) {
					$position = strpos($value, '.');
					$error = substr($value, 0, $position);
					array_push($this->errorCodes, $error);
				} else {
					array_push($this->errorCodes, $value);
				}
				
				$errorString .= $value.', ';
			}
		}
		
		// every error has to be handled, but don't care about duplicates
		$this->errorCodes = array_unique($this->errorCodes);
		return $errorString;
	}
	
	
	/**
	 *
	 * Finish payment and save order
	 */
	public function endAction() {
		$this->View()->loadTemplate('Frontend/finish.tpl');
		$basket = $this->getBasket();
		$this->View()->sBasket = $this->getBasket();
		$this->View()->sShippingcosts = $basket['sShippingcosts'];
		$this->View()->sAmount = $basket['sAmount'];
		
		$this->paymentMethod = $this->ShopwareUpdateHelper->getPaymentDetails($this->getPaymentShortName());
		$this->View()->sofortPaymentDetails = $this->paymentMethod;
		
		$transactionId = '';
		$transactionId = $this->Request()->getParam('transactionId');
		$this->View()->transactionId = $transactionId;
		$this->uniqueId = $this->Request()->getParam('unique');
		//
		if ($this->uniqueId != $this->getPayment($transactionId)) {
			$this->redirect($this->makeAbortUrl($this->Snippets->getSnippet("sofort_multipay_checkout")->get("order_error")));
		} else {
			// multiple execution must not update on and on...
			if($orderId = $this->orderExists($transactionId)) {
				unset($this->Shopware->Session()->sOrderVariables->sBasket);
				return $orderId;
			}
			
			$sofortPendingState = $this->getPendingState();
			// save order
			$orderNumber = $this->saveOrder($transactionId, $this->uniqueId, $sofortPendingState);
			// setzt den Paymentstatus in den Tabellen von Sofort
			$this->setPaymentStatus($transactionId, $sofortPendingState);	// set the payment status
			$temporaryId = $this->getOrderTemporaryId($transactionId);
			// Speichert den Bestellstatus in Shopware
			$this->savePaymentStatus($transactionId, $temporaryId, $sofortPendingState);	// save the payment status, set to pending
			// Speichert in den Sofort Tabellen die Artikel serialisiert ab
			$this->initOrderTablesAndTimeLine();
			
			// show payment details to the customer for Vorkasse by sofort
			$redirectParams = array();
			
			if ($this->paymentMethod['name'] === 'vorkassebysofort_multipay') {
				$redirectParams['holder'] = urldecode($this->Request()->getParam('holder'));
				$redirectParams['accountNumber'] = $this->Request()->getParam('account_number');
				$redirectParams['iban'] = $this->Request()->getParam('iban');
				$redirectParams['bank_code'] = $this->Request()->getParam('bank_code');
				$redirectParams['bic'] = $this->Request()->getParam('bic');
				$redirectParams['amount'] = $this->Request()->getParam('amount');
				$redirectParams['reason_1'] = urldecode($this->Request()->getParam('reason_1'));
				$redirectParams['reason_2'] = $this->Snippets->getSnippet("sofort_multipay_bootstrap")->get("ordernumber").': '.$this->getOrderNumberByTransactionId($transactionId);
			}
			
			$redirectParams['controller'] = 'checkout';
			$redirectParams['action'] = 'finish';
			$redirectParams['transactionId'] = $transactionId;
			$redirectParams['paymentMethod'] = $this->paymentMethod['name'];
			$redirectParams['paymentDescription'] = $this->paymentMethod['description'];
			$redirectParams['sUniqueID'] = $this->uniqueId;
			
			/**
			 * Update order number 
			 */
			if($this->paymentMethod['name'] === 'sofortrechnung_multipay') {
				$this->PnagInvoice = new PnagInvoice($this->configKey);
				$this->PnagInvoice->updateOrderNumber($transactionId, $orderNumber);
			}
			
			$this->redirect($redirectParams);
			return;
		}
		
		return $this->forward('error');
	}
	
	
	/**
	 * 
	 * Get the order number
	 * @param string $transactionId
	 */
	public function getOrderNumberByTransactionId($transactionId) {
		$sql = 'SELECT ordernumber FROM s_order WHERE transactionID = ?';
		return Shopware()->Db()->fetchOne($sql, array($transactionId));
	}
	
	
	/**
	 * 
	 * Controller's action for timeout while doing payment
	 */
	public function timeoutAction() {
		$this->View()->sBasket = $this->getBasket();
		$this->paymentMethod = $this->ShopwareUpdateHelper->getPaymentDetails($this->getPaymentShortName());
		
		
		$this->View()->loadTemplate('Frontend/payment_timeout.tpl');
		$this->View()->sofortPaymentMethod = $this->paymentMethod;
		$transactionId = $this->Request()->getParam('transactionId');
		$this->uniqueId = $this->Request()->getParam('unique');
		$checkoutUrl = $this->Front()->Router()->assemble(array(
				'controller' => 'checkout',
				'action' => 'cart'
		));
		$this->View()->checkoutUrl = $checkoutUrl;
		
		// set the canceled order's status to >canceled<
		if (!empty($transactionId) && !empty($this->uniqueId)) {
			$sql = 'SELECT COUNT(id) FROM `sofort_orders` WHERE `transactionId` = ? AND `secret` = ?';
			$authorized = $this->Shopware->Db()->fetchOne($sql,array($transactionId, $this->uniqueId));
			// decide, whether this function call is authorized or not...
			if ($authorized) {
				$this->initOrderTablesAndTimeLine();
				$this->setOrderCanceled($transactionId);
				$this->setSofortStatusCanceled($transactionId);
			}
		}
	}
	
	
	/**
	 * 
	 * Set the order to canceled
	 * @param string $transactionId
	 */
	private function setOrderCanceled($transactionId)
	{
		$sofortCanceledState = $this->Config()->sofort_canceled_state;
		$sql = 'UPDATE `s_order` SET `status` = 4 WHERE `transactionID` = ?';
		$fields = array(
				$transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
		// set the sofort_orders's status to >canceled<
		$sql = 'UPDATE `sofort_orders` SET `paymentStatus` = "canceled" WHERE `transactionID` = ?';
		$fields = array(
				$transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
		// Unset the secret/token
		$sql = 'UPDATE `sofort_orders` SET `secret` = "" WHERE `transactionID` = ?';
		$fields = array(
				$transactionId,
		);
		$this->Shopware->Db()->query($sql, $fields);
		$this->setOrderComment($transactionId, $this->Snippets->getSnippet('sofort_multipay_cancel')->get('order_cancel'));
	}
	
	
	/**
	 * 
	 * Controller's error action
	 */
	public function errorAction() {
		$this->View()->sBasket = $this->getBasket();
		$this->View()->sofortPaymentMethod = $this->$this->ShopwareUpdateHelper->getPaymentDetails($this->paymentMethod);
		$this->View()->loadTemplate('Frontend/payment_error.tpl');
	}
	
	
	/**
	 * Returns the payment plugin config data.
	 *
	 * @return Shopware_Models_Plugin_Config
	 */
	public function Config()
	{
		return Shopware()->Plugins()->Frontend()->PaymentSofort()->Config();
	}
	
	
	/**
	 * 
	 * Override the toString method
	 */
	public function __toString() {
		$string = 'payment method: '.$this->paymentMethod."\n";
		$string .= 'payment method string: '.$this->paymentMethodString."\n";
		return $string;
	}
}