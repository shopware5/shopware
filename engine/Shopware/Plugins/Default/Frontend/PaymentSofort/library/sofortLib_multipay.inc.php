<?php
/**
 * Setup a multipay payment session
 * after the configuration of multiple payment methods you will receive
 * an url and a transaction id, your customer should be redirected to this
 * url you can use the transaction id for future reference of this payment
 *
 * example by usage:
 * $objMultipay = new SofortLib_Multipays('my-API-KEY');
 * $objMultipay->setSofortueberweisung(); 					//OR setSofortrechnung(), setSofortvorkasse() etc.
 * $objMultipay->set...($param);  							//set params for PNAG-API (watch API-documentation for needed params)
 * $objMultipay->add...($param);							//add params for PNAG-API (watch API-documentation for needed params)
 * $errorsAndWarnings = $objMultipay->validateRequest();	//send param against the PNAG-API without setting an order
 * ... make own validation of $errorsAndWarnings and if ok ...
 * $objMultipay->sendRequest();								//set the order at PNAG
 * $errorsAndWarnings =	$objMultipay->getErrors();			//should not occur, if validation was ok
 * ... make own validation of $errorsAndWarnings and if ok ...
 * ... finish order in the shopsystem
 *
 * Copyright (c) 2012 SOFORT AG
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 17:15:47 +0100 (Fr, 23. Nov 2012) $
 * @version SofortLib 1.5.4  $Id: sofortLib_multipay.inc.php 5773 2012-11-23 16:15:47Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_Multipay extends SofortLib_Abstract {
	
	protected $_parameters = array();
	
	protected $_response = array();
	
	protected $_xmlRootTag = 'multipay';
	
	private $_paymentMethods = array();
	
	private $_transactionId = '';
	
	private $_paymentUrl = '';
	
	
	/**
	 * create a new payment object
	 * @param string $apikey your API key
	 * @param int $projectId your project id
	 */
	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
		$this->_parameters['project_id'] = $projectId;
	}
	
	
	/**
	 * the language code will help in determing what language to
	 * use when displaying the payment form, other data like
	 * browser settings and ip will be used as well
	 *
	 * @param string $arg de|en|nl|fr ...
	 * @return SofortLib_Multipay
	 */
	public function setLanguageCode($arg) {
		$this->_parameters['language_code'] = $arg;
		return $this;
	}
	
	
	/**
	 * timeout how long this transaction configuration will be valid for
	 * this is the time between the generation of the payment url and
	 * the user completing the form, should be at least two to three minutes
	 * defaults to unlimited if not set
	 *
	 * @param int $arg timeout in seconds
	 * @return SofortLib_Multipay
	 */
	public function setTimeout($arg) {
		$this->_parameters['timeout'] = $arg;
		return $this;
	}
	
	
	/**
	 * set the email address of the customer
	 * this will be used for sofortvorkasse and sofortrechnung
	 *
	 * @param string $arg email address
	 * @return SofortLib_Multipay
	 */
	public function setEmailCustomer($arg) {
		$this->_parameters['email_customer'] = $arg;
		return $this;
	}
	
	
	/**
	 * set the phone number of the customer
	 *
	 * @param string $arg phone number
	 * @return SofortLib_Multipay
	 */
	public function setPhoneNumberCustomer($arg) {
		$this->_parameters['phone_customer'] = $arg;
		return $this;
	}
	
	
	/**
	 * add another variable this can be your internal order id or similar
	 *
	 * @param string $arg the contents of the variable
	 * @return SofortLib_Multipay
	 */
	public function addUserVariable($arg) {
		$this->_parameters['user_variables']['user_variable'][] = $arg;
		return $this;
	}
	
	
	/**
	 * set data of account
	 *
	 * @param string $bank_code bank code of bank
	 * @param string $account_number account number
	 * @param string $holder Name/Holder of this account
	 * @return SofortLib_Multipay $this
	 */
	public function setSenderAccount($bankCode, $accountNumber, $holder) {
		$this->_parameters['sender'] = array(
			'bank_code' => $bankCode,
			'account_number' => $accountNumber,
			'holder' => $holder,
		);
		return $this;
	}
	
	
	/**
	 * amount of this payment
	 *
	 * @param double $arg
	 * @param string $currency currency of this transaction, default EUR
	 * @return SofortLib_Multipay $this
	 */
	public function setAmount($arg, $currency = 'EUR') {
		$this->_parameters['amount'] = $arg;
		$this->_parameters['currency_code'] = $currency;
		return $this;
	}
	
	
	/**
	 * set the reason values of this transfer
	 *
	 * @param string $arg max 27 characters
	 * @param string $arg2 max 27 characters
	 * @return SofortLib_Multipay $this
	 */
	public function setReason($arg, $arg2 = '') {
		$arg = preg_replace('#[^a-zA-Z0-9+-\.,]#', ' ', $arg);
		$arg = substr($arg, 0, 27);
		$arg2 = preg_replace('#[^a-zA-Z0-9+-\.,]#', ' ', $arg2);
		$arg2 = substr($arg2, 0, 27);
		$this->_parameters['reasons']['reason'][0] = $arg;
		$this->_parameters['reasons']['reason'][1] = $arg2;
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for redirecting the success link automatically
	 * @param boolean $arg
	 */
	public function setSuccessLinkRedirect($arg) {
		$this->_parameters['success_link_redirect'] = $arg;
	}
	
	
	/**
	 * the customer will be redirected to this url after a successful
	 * transaction, this should be a page where a short confirmation is
	 * displayed
	 *
	 * @param string $arg the url after a successful transaction
	 * @return SofortLib_Multipay
	 */
	public function setSuccessUrl($successUrl, $redirect = true) {
		$this->_parameters['success_url'] = $successUrl;
		$this->setSuccessLinkRedirect($redirect);
		return $this;
	}
	
	
	/**
	 * the customer will be redirected to this url if he uses the
	 * abort link on the payment form, should redirect him back to
	 * his cart or to the payment selection page
	 *
	 * @param string $arg url for aborting the transaction
	 * @return SofortLib_Multipay
	 */
	public function setAbortUrl($arg) {
		$this->_parameters['abort_url'] = $arg;
		return $this;
	}
	
	
	/**
	 * if the customer takes too much time or if your timeout is set too short
	 * he will be redirected to this page
	 *
	 * @param string $arg url
	 * @return SofortLib_Multipay
	 */
	public function setTimeoutUrl($arg) {
		$this->_parameters['timeout_url'] = $arg;
		return $this;
	}
	
	
	/**
	 * set the url where you want notification about status changes
	 * being sent to. Use SofortLib_Notification and SofortLib_TransactionData
	 * to further process that notification
	 *
	 * @param string $arg url
	 * @return SofortLib_Multipay
	 */
	public function setNotificationUrl($arg) {
		$this->_parameters['notification_urls']['notification_url'] = array($arg);
		return $this;
	}
	
	
	/**
	 * you can set set multiple urls for receiving notifications
	 * this might be helpfull if you have several systems for processing
	 * an order (e.g. an ERP system)
	 *
	 * @param string $arg url
	 * @return SofortLib_Multipay
	 */
	public function addNotificationUrl($arg) {
		$this->_parameters['notification_urls']['notification_url'][] = $arg;
		return $this;
	}
	
	
	/**
	 * set the email address where you want notification about status changes
	 * being sent to.
	 *
	 * @param string $arg email address
	 * @return SofortLib_Multipay
	 */
	public function setNotificationEmail($arg) {
		$this->_parameters['notification_emails']['notification_email'] = array($arg);
		return $this;
	}
	
	
	/**
	 * you can set set multiple emails for receiving notifications
	 *
	 * @param string $arg email
	 * @return SofortLib_Multipay
	 */
	public function addNotificationEmail($arg) {
		$this->_parameters['notification_emails']['notification_email'][] = $arg;
		return $this;
	}
	
	
	/**
	 * set the version of this payment module
	 * this is helpfull so the support staff can easily
	 * find out if someone uses an outdated module
	 *
	 * @param string $arg version string of your module
	 * @return SofortLib_Multipay
	 */
	public function setVersion($arg) {
		$this->_parameters['interface_version'] = $arg;
		return $this;
	}
	
	
	/**
	 * add sofortueberweisung as payment method
	 * @param double $amount this amount only applies to this payment method
	 * @return SofortLib_Multipay $this
	 */
	public function setSofortueberweisung($amount = '') {
		$this->_paymentMethods[] = 'su';
		
		if (!array_key_exists('su', $this->_parameters) || !is_array($this->_parameters['su'])) {
			$this->_parameters['su'] = array();
		}
		
		if (!empty($amount)) {
			$this->_parameters['su']['amount'] = $amount;
		}
		
		return $this;
	}
	
	
	/**
	 * add sofortueberweisung as payment method
	 * adds customer protection
	 * @param double $amount this amount only applies to this payment method
	 * @return SofortLib_Multipay $this
	 */
	public function setSofortueberweisungCustomerprotection($customerProtection = true) {
		$this->_paymentMethods[] = 'su';
		
		if (!array_key_exists('su', $this->_parameters) || !is_array($this->_parameters['su'])) {
			$this->_parameters['su'] = array();
		}
		
		$this->_parameters['su']['customer_protection'] = $customerProtection ? 1 : 0;
		return $this;
	}
	
	
	/**
	 * add sofortlastschrift as payment method
	 * @param double $amount this amount only applies to this payment method
	 * @return SofortLib_Multipay $this
	 */
	public function setSofortlastschrift($amount = '') {
		$this->_paymentMethods[] = 'sl';
		
		if (!array_key_exists('sl', $this->_parameters) || !is_array($this->_parameters['sl'])) {
			$this->_parameters['sl'] = array();
		}
		
		if (!empty($amount)) {
			$this->_parameters['sl']['amount'] = $amount;
		}
		
		return $this;
	}
	
	
	/**
	 * set the address of the customer for address validation,
	 * this should be the invoice address of the customer
	 *
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $street
	 * @param string $streetNumber
	 * @param string $zipcode
	 * @param string $city
	 * @param int $salutation [2|3] 2=Mr. 3=Mrs.
	 * @param string $country country code, only DE allowed at the moment
	 * @return SofortLib_Multipay $this
	 */
	public function setSofortlastschriftAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE') {
		$this->_parameters['sl']['invoice_address']['salutation'] = $salutation;
		$this->_parameters['sl']['invoice_address']['firstname'] = $firstname;
		$this->_parameters['sl']['invoice_address']['lastname'] = $lastname;
		$this->_parameters['sl']['invoice_address']['street'] = $street;
		$this->_parameters['sl']['invoice_address']['street_number'] = $streetNumber;
		$this->_parameters['sl']['invoice_address']['zipcode'] = $zipcode;
		$this->_parameters['sl']['invoice_address']['city'] = $city;
		$this->_parameters['sl']['invoice_address']['country_code'] = $country;
		return $this;
	}
	
	
	/**
	 * add lastschrift as payment method
	 * @param double $amount this amount only applies to this payment method
	 * @return SofortLib_Multipay $this
	 */
	public function setLastschrift($amount = '') {
		$this->_paymentMethods[] = 'ls';
		
		if (!array_key_exists('ls', $this->_parameters) || !is_array($this->_parameters['ls'])) {
			$this->_parameters['ls'] = array();
		}
		
		if (!empty($amount)) {
			$this->_parameters['ls']['amount'] = $amount;
		}
		
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for base checks disabled of Lastschrift
	 */
	public function setLastschriftBaseCheckDisabled() {
		$this->_parameters['ls']['base_check_disabled'] = 1;
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for extende checks disabled of Lastschrift
	 */
	public function setLastschriftExtendedCheckDisabled() {
		$this->_parameters['ls']['extended_check_disabled'] = 1;
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for mobile checks disabled of Lastschrift
	 */
	public function setLastschriftMobileCheckDisabled() {
		$this->_parameters['ls']['mobile_check_disabled'] = 1;
		return $this;
	}
	
	
	/**
	 * set the address of the customer for address validation,
	 * this should be the invoice address of the customer
	 *
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $street
	 * @param string $streetNumber
	 * @param string $zipcode
	 * @param string $city
	 * @param int $salutation [2|3] 2=Mr. 3=Mrs.
	 * @param string $country country code, only DE allowed at the moment
	 *
	 * @return SofortLib_Multipay object
	 */
	public function setLastschriftAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE') {
		$this->_parameters['ls']['invoice_address']['salutation'] = $salutation;
		$this->_parameters['ls']['invoice_address']['firstname'] = $firstname;
		$this->_parameters['ls']['invoice_address']['lastname'] = $lastname;
		$this->_parameters['ls']['invoice_address']['street'] = $street;
		$this->_parameters['ls']['invoice_address']['street_number'] = $streetNumber;
		$this->_parameters['ls']['invoice_address']['zipcode'] = $zipcode;
		$this->_parameters['ls']['invoice_address']['city'] = $city;
		$this->_parameters['ls']['invoice_address']['country_code'] = $country;
		return $this;
	}
	
	
	/**
	 * add sofortrechnung as payment method
	 * if you use this payment method you have to provide
	 * the customer address and cart as well
	 * the total amount of this payment method will
	 * be determined by the total of the cart
	 *
	 * @return SofortLib_Multipay object
	 */
	public function setSofortrechnung() {
		$this->_paymentMethods[] = 'sr';
		
		if (!array_key_exists('sr', $this->_parameters) || !is_array($this->_parameters['sr'])) {
			$this->_parameters['sr'] = array();
		}
		
		return $this;
	}
	
	
	/**
	 * add sofortvorkasse as payment method
	 * @param double $amount this amount only applies to this payment method
	 *
	 * @return SofortLib_Multipay objet
	 */
	public function setSofortvorkasse($amount = '') {
		$this->_paymentMethods[] = 'sv';
		
		if (!array_key_exists('sv', $this->_parameters) || !is_array($this->_parameters['sv'])) {
			$this->_parameters['sv'] = array();
		}
		
		if (!empty($amount)) {
			$this->_parameters['sv']['amount'] = $amount;
		}
		
		return $this;
	}
	
	
	/**
	 * add sofortvorkasse as payment method
	 * adds customer protection
	 * @param double $amount this amount only applies to this payment method
	 * @return SofortLib_Multipay $this
	 */
	public function setSofortvorkasseCustomerprotection($customerProtection = true) {
		$this->_paymentMethods[] = 'sv';
		
		if (!array_key_exists('sv', $this->_parameters) || !is_array($this->_parameters['sv'])) {
			$this->_parameters['sv'] = array();
		}
		
		$this->_parameters['sv']['customer_protection'] = $customerProtection ? 1 : 0;
		return $this;
	}
	
	
	/**
	 * set the customer id which will appear on top of the invoice
	 * @param int $arg
	 * @return SofortLib_Multipay $this
	 */
	public function setSofortrechnungCustomerId($arg) {
		$this->_parameters['sr']['customer_id'] = $arg;
		return $this;
	}
	
	
	/**
	 * set the order id which will appear on top of the invoice
	 * @param int $arg
	 * @return SofortLib_Multipay $this
	 */
	public function setSofortrechnungOrderId($arg) {
		$this->_parameters['sr']['order_id'] = $arg;
		return $this;
	}
	
	
	/**
	 * set debitor vat number for invoice
	 * @param string $vatNumber
	 * @return SofortLib_Multipay $this
	 */
	public function setDebitorVatNumber($vatNumber) {
		$this->_parameters['sr']['debitor_vat_number'] = $vatNumber;
		return $this;
	}
	
	
	/**
	 * set the invoice address of the customer
	 *
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $street
	 * @param string $streetNumber
	 * @param string $zipcode
	 * @param string $city
	 * @param int $salutation [2|3] 2 = Mr. 3 = Mrs.
	 * @param string $country country code, only DE allowed at the moment
	 * @return SofortLib_Multipay $this
	 */
	public function setSofortrechnungInvoiceAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '', $companyName = '') {
		$this->_parameters['sr']['invoice_address']['salutation'] = $salutation;
		$this->_parameters['sr']['invoice_address']['firstname'] = $firstname;
		$this->_parameters['sr']['invoice_address']['lastname'] = $lastname;
		$this->_parameters['sr']['invoice_address']['street'] = $street;
		$this->_parameters['sr']['invoice_address']['street_number'] = $streetNumber;
		$this->_parameters['sr']['invoice_address']['zipcode'] = $zipcode;
		$this->_parameters['sr']['invoice_address']['city'] = $city;
		$this->_parameters['sr']['invoice_address']['country_code'] = $country;
		$this->_parameters['sr']['invoice_address']['name_additive'] = $nameAdditive;
		$this->_parameters['sr']['invoice_address']['street_additive'] = $streetAdditive;
		$this->_parameters['sr']['invoice_address']['company'] = $companyName;
		return $this;
	}
	
	
	/**
	 * set the shipping address of the customer
	 *
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $street
	 * @param string $streetNumber
	 * @param string $zipcode
	 * @param string $city
	 * @param int $salutation [2|3] 1 = Mr. 2 = Mrs.
	 * @param string $country country code, only DE allowed at the moment
	 * @return SofortLib_Multipay $this
	 */
	public function setSofortrechnungShippingAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '', $companyName = '') {
		$this->_parameters['sr']['shipping_address']['salutation'] = $salutation;
		$this->_parameters['sr']['shipping_address']['firstname'] = $firstname;
		$this->_parameters['sr']['shipping_address']['lastname'] = $lastname;
		$this->_parameters['sr']['shipping_address']['street'] = $street;
		$this->_parameters['sr']['shipping_address']['street_number'] = $streetNumber;
		$this->_parameters['sr']['shipping_address']['zipcode'] = $zipcode;
		$this->_parameters['sr']['shipping_address']['city'] = $city;
		$this->_parameters['sr']['shipping_address']['country_code'] = $country;
		$this->_parameters['sr']['shipping_address']['name_additive'] = $nameAdditive;
		$this->_parameters['sr']['shipping_address']['street_additive'] = $streetAdditive;
		$this->_parameters['sr']['shipping_address']['company'] = $companyName;
		return $this;
	}
	
	
	/**
	 * add one item to the cart
	 *
	 * @param int $itemId unique item id
	 * @param string $productNumber product number, EAN code, ISBN number or similar
	 * @param string $title description of this title
	 * @param double $unit_price gross price of one item
	 * @param int $productType product type number see manual (0=other, 1=shipping, ...)
	 * @param string $description additional description of this item
	 * @param int $quantity default 1
	 * @param int $tax tax in percent, default 19
	 */
	public function addSofortrechnungItem($itemId, $productNumber, $title, $unitPrice, $productType = 0, $description = '', $quantity = 1, $tax = 19) {
		$unitPrice = number_format($unitPrice, 2, '.', '');
		$tax = number_format($tax, 2, '.', '');
		$quantity = intval($quantity);
		
		if (empty($title)) {
			$this->setError('Title must not be empty. Title: '.$title.', Productnumber: '.$productNumber.', Unitprice: '.$unitPrice.', Quantity: '.$quantity.', Description: '.$description);
		}
		
		$this->_parameters['sr']['items']['item'][] = array(
			'item_id' => $itemId,
			'product_number' => $productNumber,
			'product_type' => $productType,
			'title' => $title,
			'description' => $description,
			'quantity' => $quantity,
			'unit_price' => $unitPrice,
			'tax' => $tax,
		);
	}
	
	
	/**
	 * 
	 * Setter for commenting Rechnung by sofort
	 * @param string $comment
	 */
	public function setSofortrechungComment($comment) {
		$this->_parameters['sr']['items']['comment'] = $comment;
	}
	
	
	/**
	 * Remove one item from cart
	 * @param $itemId
	 * @return boolean
	 */
	public function removeSofortrechnungItem($itemId) {
		$i = 0;
		
		foreach ($this->_parameters['sr']['items'] as $item) {
			if (isset($item['item_id']) && $item['item_id'] == $itemId) {
				unset($this->_parameters['sr']['items']['item'][$i]);
				return true;
			}
			
			$i++;
		}
		
		return false;
	}
	
	
	/**
	 * Update one item in cart
	 * @param $itemId
	 * @param $quantity
	 * @param $unit_price
	 * @return boolean
	 */
	public function updateSofortrechnungItem($itemId, $quantity, $unitPrice) {
		$i = 0;
		
		foreach ($this->_parameters['sr']['items'] as $item) {
			if (isset($item[$i]['item_id']) && $item[$i]['item_id'] == $itemId) {
				$this->_parameters['sr']['items']['item'][$i]['quantity'] = $quantity;
				$this->_parameters['sr']['items']['item'][$i]['unit_price'] = $unitPrice;
				return true;
			}
			
			$i++;
		}
		
		return false;
	}
	
	
	/**
	 * 
	 * Getter for invoice's item
	 * @param int $itemId
	 */
	public function getSofortrechnungItemAmount($itemId) {
		$i = 0;
		
		foreach ($this->_parameters['sr']['items'] as $item) {
			if (isset($item['item_id']) && $item['item_id'] == $itemId) {
				return $this->_parameters['sr']['items']['item'][$i]['quantity'] * $this->_parameters['sr']['items']['item'][$i]['unit_price'];
			}
			
			$i++;
		}
	}
	
	
	/**
	 * 
	 * Setter for invoice's time for payment
	 * @param string $arg
	 */
	public function setSofortrechnungTimeForPayment($arg) {
		$this->_parameters['sr']['time_for_payment'] = $arg;
		return $this;
	}
	
	
	/**
	 * makes a request against the pnag-API and returns all API-Fault/Warnings
	 * it doesnt result in an order at pnag!
	 * @return bool
	 */
	public function sendValidationRequest() {
		$this->_validateOnly = true;
		$this->sendRequest();
		return isset($this->_response['validation']['status']['@data']) ? true : false;
	}
	
	
	/**
	 * 
	 * Getter for invoice's item
	 * @param int $itemId
	 */
	public function getSofortrechnungItem($itemId) {
		return $this->_parameters['sr']['items'][$itemId];
	}
	
	
	/**
	 * 
	 * Getter for all invoice's items
	 */
	public function getSofortrechnungItems() {
		return $this->_parameters['sr']['items'];
	}
	
	
	/**
	 * after configuration and sending this request
	 * you can use this function to redirect the customer
	 * to the payment form
	 *
	 * @return string url of payment form
	 */
	public function getPaymentUrl() {
		$this->_paymentUrl = isset($this->_response['new_transaction']['payment_url']['@data'])
			? $this->_response['new_transaction']['payment_url']['@data']
			: false;
		return $this->_paymentUrl;
	}
	
	
	/**
	 * 
	 * Getter for payment method
	 * @param int $i
	 */
	public function getPaymentMethod($i = 0) {
		if ($i < 0 || $i >= count($this->_paymentMethods)) {
			return false;
		}
		
		return $this->_paymentMethods[$i];
	}
	
	
	/**
	 * 
	 * Is sofortüberweisung
	 */
	public function isSofortueberweisung() {
		return array_key_exists('su', $this->_parameters);
	}
	
	
	/**
	 * 
	 * Is vorkasse by sofort
	 */
	public function isSofortvorkasse() {
		return array_key_exists('sv', $this->_parameters);
	}
	
	
	/**
	 * 
	 * Check if it is a sofortlastschrift
	 */
	public function isSofortlastschrift() {
		return array_key_exists('sl', $this->_parameters);
	}
	
	
	/**
	 * 
	 * Is lastschrift by sofort
	 */
	public function isLastschrift() {
		return array_key_exists('ls', $this->_parameters);
	}
	
	
	/**
	 * 
	 * Is rechnung by sofort
	 */
	public function isSofortrechnung() {
		return array_key_exists('sr', $this->_parameters);
	}
	
	
	/**
	 * 
	 * Check if consumer protection / customer protection enabled
	 * @param string $product
	 */
	public function isConsumerProtection($product) {
		if (in_array($product, array('su', 'sv'))) {
			if(isset($this->_parameters[$product]['customer_protection'])) {
				return $this->_parameters[$product]['customer_protection'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 * 
	 * Check if debit pay check disabled
	 * @param string $product
	 * @param boolean $check
	 */
	public function isDebitpayCheckDisabled($product, $check) {
		if (in_array($product, array('ls', 'sl')) && in_array($check, array('base_check_disabled', 'extended_check_disabled', 'mobile_check_disabled'))) {
			return $this->_parameters[$product][$check];
		} else {
			return false;
		}
	}
	
	
	/**
	 * use this id to track the transaction
	 *
	 * @return string transaction id
	 */
	public function getTransactionId() {
		return $this->_transactionId;
	}
	
	
	/**
	 * Parse the XML (override)
	 * (non-PHPdoc)
	 * @see SofortLib_Abstract::_parseXml()
	 */
	protected function _parseXml() {
		$this->_transactionId = isset($this->_response['new_transaction']['transaction']['@data'])
			? $this->_response['new_transaction']['transaction']['@data']
			: false;
	}
}
?>