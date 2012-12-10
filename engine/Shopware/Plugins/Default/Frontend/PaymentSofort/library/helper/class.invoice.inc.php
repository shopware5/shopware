<?php
/**
 * Copyright (c) 2012 SOFORT AG
 *
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 17:15:47 +0100 (Fr, 23. Nov 2012) $
 * @version $Id: class.invoice.inc.php 5773 2012-11-23 16:15:47Z dehn $
 * @package sofortLib
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */

/**
 * Abstraction of an invoice
 * Helper class to ease usage of "Rechnung by sofort"
 * Encapsulates Multipay, TransactionData and ConfirmSr to handle everything there is about "Rechnung by sofort"
 * @see SofortLib_Multipay
 * @see SofortLib_TransactionData
 * @see SofortLib_ConfirmSr
 */

class PnagInvoice extends PnagAbstractDocument {
	
	const PENDING_CONFIRM_INVOICE = 4195329;
	const LOSS_CANCELED = 4194824;
	const LOSS_CONFIRMATION_PERIOD_EXPIRED = 4196360;
	
	const PENDING_NOT_CREDITED_YET_PENDING = 32785;
	const PENDING_NOT_CREDITED_YET_REMINDER_1 = 65553;
	const PENDING_NOT_CREDITED_YET_REMINDER_2 = 131089;
	const PENDING_NOT_CREDITED_YET_REMINDER_3 = 262161;
	const PENDING_NOT_CREDITED_YET_DELCREDERE = 524305;
	
	const RECEIVED_CREDITED_PENDING = 33026;
	const RECEIVED_CREDITED_REMINDER_1 = 65794;
	const RECEIVED_CREDITED_REMINDER_2 = 131330;
	const RECEIVED_CREDITED_REMINDER_3 = 262402;
	const RECEIVED_CREDITED_DELCREDERE = 524546;
	
	/* im normalfall nicht möglich */
	const REFUNDED_REFUNDED_PENDING = 32836;
	const REFUNDED_REFUNDED_RECEIVED = 2097220;
	const REFUNDED_REFUNDED_REMINDER_1 = 65604;
	const REFUNDED_REFUNDED_REMINDER_2 = 131140;
	const REFUNDED_REFUNDED_REMINDER_3 = 262212;
	const REFUNDED_REFUNDED_DELCREDERE = 524356;
	/* im normalfall nicht möglich */
	
	const REFUNDED_REFUNDED_REFUNDED = 1048644;
	const PENDING_NOT_CREDITED_YET_RECEIVED = 2097169;
	const RECEIVED_CREDITED_RECEIVED = 2097410;
	
	
	/**
	 *
	 * Multipay-Object to handle API calls
	 * @var object
	 * @private
	 */
	public $SofortLib_Multipay = null;
	
	/**
	 * Object TransactionData to handle information about transactions
	 * @var object
	 * @private
	 */
	public $SofortLib_TransactionData = null;
	
	/**
	 * Object Confirm_SR to handle sofortrechnung/rechnung by sofort items
	 * Handling of Sofortrechung
	 * @var object
	 * @private
	 */
	public $ConfirmSr = null;
	
	public $EditSr = null;
	
	public $CancelSr = null;
	
	protected $_items = array();
	
	/**
	 *
	 * Some kind of a bitmask to represent every possible state of Rechnung by sofort
	 * Every combination must be unique to represent a unique state
	 * @var array
	 * @private
	 */
	private $_statusMask = array(
		'status'=>
			array(
				'pending' => 1,
				'received' => 2,
				'refunded' => 4,
				'loss' => 8,
			),
		'status_reason' =>
			array(
				'not_credited_yet' => 16,
				'not_credited' => 32,
				'refunded' => 64,
				'compensation' => 128,
				'credited' => 256,
				'canceled' => 512,
				'confirm_invoice' => 1024,
				'confirmation_period_expired' => 2048,
				'wait_for_money' => 4096,
				'reversed' => 8192,
				'rejected' => 16384,
			),
		'invoice_status' =>
			array(
				'pending' => 32768,
				'reminder_1' => 65536,
				'reminder_2' => 131072,
				'reminder_3' => 262144,
				'delcredere' => 524288,
				'refunded' => 1048576,
				'received' => 2097152,
				'empty' => 4194304,
		)
	);
	
	/**
	 *
	 * @see $statusMask
	 * @var string
	 * @private
	 */
	private $_status = '';
	
	/**
	 *
	 * @see $statusMask
	 * @var string
	 * @private
	 */
	private $_status_reason = '';
	
	/**
	 *
	 * @see $statusMask
	 * @var string
	 */
	private $_invoice_status = '';
	
	/**
	 *
	 * Invoice's objection (Einrede)
	 * @var string
	 */
	private $_invoice_objection = '';
	
	/**
	 *
	 * language code
	 * @var string
	 */
	private $_language_code = '';
	
	/**
	 *
	 * transaction id
	 * @var string
	 * @private
	 */
	private $_transactionId = '';
	
	/**
	 * api key given in project setup in payment network backend
	 * @var string
	 * @private
	 */
	private $_configKey = '';
	
	/**
	 *
	 * api url
	 * @var string
	 * @private
	 */
	private $_apiUrl = '';
	
	/**
	 * time
	 * @var string
	 * @private
	 */
	private $_time = '';
	
	/**
	 * payment method
	 * @var string
	 * @private
	 */
	private $_payment_method = '';
	
	/**
	 * The resulting url to the invoice (PDF)
	 * @var string
	 * @private
	 */
	private $_invoiceUrl = '';
	
	
	/**
	 * Constructor for PnagInvoice
	 * @param string $apiKey
	 * @param string $transactionId
	 * @param string $apiUrl
	 */
	public function __construct($configKey, $transactionId = '') {
		$this->_transactionId = $transactionId;
		$this->_configKey = $configKey;
		$this->_apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		$this->SofortLib_Multipay = new SofortLib_Multipay($this->_configKey, $this->_apiUrl);
		
		if ($transactionId != '') {
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
			$this->ConfirmSr = $this->_setupConfirmSr();
		}
		
		return $this;
	}
	
	
	/**
	 * Getter for a class constant
	 * @param int $id
	 * @return string
	 */
	public function getConstantById($id) {
		$Object = new ReflectionClass(__CLASS__);
		$constants = array_flip($Object->getConstants());
		return (array_key_exists($id, $constants)) ? $constants[$id] : 0;
	}
	
	
	/**
	 *
	 * Getter for a class constant
	 * @param string $name
	 * @return int
	 */
	public function getConstantByName($name) {
		$Object = new ReflectionClass(__CLASS__);
		$constants = $Object->getConstants();
		return (array_key_exists($name, $constants)) ? $constants[$name] : 0;
	}
	
	
	/**
	 *
	 * Set the bitmask to a specific state
	 * @param string $status
	 * @param string $status_reason
	 * @param string $invoice_status
	 * @return string pending - confirm_invoice - empty -> 4195329
	 */
	public function setBitmask($status, $statusReason, $invoiceStatus) {
		$this->_status = $status;
		$this->_status_reason = $statusReason;
		$this->_invoice_status = $invoiceStatus;
		$string = $this->_status.' - '.$this->_status_reason.' - '.$this->_invoice_status;
		return $string.' -> '.$this->_calcInvoiceStatusCode()."\n";
	}
	
	
	/**
	 *
	 * Set the state
	 * An optional callback can be registered
	 * @param int $state
	 * @param function $callback
	 */
	public function setState($state, $callback = '') {
		$this->_state = $state;
		
		if ($callback != '') {
			call_user_func($callback);
		}
		
		return $this;
	}
	
	
	/**
	 * Getter for the current state
	 * @return int $this->state
	 */
	public function getState() {
		return $this->_state;
	}
	
	
	/**
	 * Setter for transactionId
	 * @param $transactionId
	 * @public
	 */
	public function setTransactionId($transactionId) {
		$this->_transactionId = $transactionId;
		$this->SofortLib_TransactionData = $this->_setupTransactionData();
		$this->ConfirmSr = $this->_setupConfirmSr();
		return $this;
	}
	
	
	/**
	 * Construct the SofortLib_TransactionData object
	 * Collect every order's item and set it accordingly
	 * TransactionData is used encapsulated in this class to retrieve information about the order's details
	 * @return object SofortLib_TransactionData
	 * @private
	 */
	private function _setupTransactionData() {
		$SofortLib_TransactionData = new SofortLib_TransactionData($this->_configKey, $this->_apiUrl);
		$SofortLib_TransactionData->setTransaction($this->_transactionId);
		$SofortLib_TransactionData->sendRequest();
		
		if (!$SofortLib_TransactionData->getCount()) {
			return false;
		}
		
		$this->setStatus($SofortLib_TransactionData->getStatus());
		$this->setStatusReason($SofortLib_TransactionData->getStatusReason());
		$this->setStatusOfInvoice($SofortLib_TransactionData->getInvoiceStatus());
		$this->setInvoiceObjection($SofortLib_TransactionData->getInvoiceObjection());
		$this->setLanguageCode($SofortLib_TransactionData->getLanguageCode());
		$this->setTransaction($this->getTransactionId());
		$this->setTime($SofortLib_TransactionData->getTime());
		$this->setPaymentMethod($SofortLib_TransactionData->getPaymentMethod());
		$this->setInvoiceUrl($SofortLib_TransactionData->getInvoiceUrl());
		$this->setAmount($SofortLib_TransactionData->getAmount());
		$this->setAmountRefunded($SofortLib_TransactionData->getAmountRefunded());
		$itemArray = $SofortLib_TransactionData->getItems();
		
		// should there be any items, fetch them accordingly
		$this->_items = array();
		
		if (is_array($itemArray) && !empty($itemArray)) {
			foreach ($itemArray as $item) {
				$this->setItem($item['item_id'], $item['product_number'], $item['product_type'], $item['title'], $item['description'], $item['quantity'], $item['unit_price'], $item['tax']);
				$this->_amount += ($item['unit_price'] * $item['quantity']);
			}
		}
		/*
		 * set the state according to the state given by transaction information (status, status_reason, invoice_status)
		 * @see $statusMask
		 */
		$this->setState($this->_calcInvoiceStatusCode());
		return $SofortLib_TransactionData;
	}
	
	
	/**
	 * 
	 * Setter for SofortLib_Multipay
	 * @param object $SofortLib_Multipay
	 */
	public function setSofortLibMultipay($SofortLib_Multipay) {
		$this->SofortLib_Multipay = $SofortLib_Multipay;
	}
	
	
	/**
	 * 
	 * Setter for SofortLib_TransactionData
	 * @param object $SofortLib_TransactionData
	 */
	public function setSofortLibTransactionData($SofortLib_TransactionData) {
		$this->SofortLib_TransactionData = $SofortLib_TransactionData;
	}
	
	
	/**
	 * 
	 * Setter for SofortLib_EditSr
	 * @param object $SofortLib_EditSr
	 */
	public function setSofortLibEditSr($SofortLib_EditSr) {
		$this->EditSr = $SofortLib_EditSr;
	}
	
	
	/**
	 * 
	 * Setter for SofortLib_CancelSr
	 * @param object $SofortLib_CancelSr
	 */
	public function setSofortLibCancelSr($SofortLib_CancelSr) {
		$this->CancelSr = $SofortLib_CancelSr;
	}
	
	
	/**
	 * Initialize SofortLib_ConfirmSR
	 * @private
	 * @return Object SofortLib_ConfirmSr
	 */
	private function _setupConfirmSr() {
		$SofortLib_ConfirmSr = new SofortLib_ConfirmSr($this->_configKey);
		$SofortLib_ConfirmSr->setTransaction($this->_transactionId);
		return $SofortLib_ConfirmSr;
	}
	
	
	/**
	 * 
	 * Setup EditSr object
	 */
	private function _setupEditSr() {
		$SofortLib_EditSr = new SofortLib_EditSr($this->_configKey);
		$SofortLib_EditSr->setTransaction($this->_transactionId);
		return $SofortLib_EditSr;
	}
	
	
	/**
	 * 
	 * Setup CancelSr object
	 */
	private function _setupCancelSr() {
		$SofortLib_CancelSr = new SofortLib_CancelSr($this->_configKey);
		$SofortLib_CancelSr->setTransaction($this->_transactionId);
		return $SofortLib_CancelSr;
	}
	
	
	/**
	 * Refreshes the TransactionData with the data directly from the pnag-server
	 * @return boolean
	 */
	public function refreshTransactionData() {
		$this->SofortLib_TransactionData = $this->_setupTransactionData();
		return true;
	}
	
	
	/**
	 * Wrapper function for cancelling this invoice via SofortLib_Multipay (SofortLib)
	 * @return Ambigious boolean/Array
	 * @todo fix returned value array, empty array
	 * @public
	 */
	public function cancelInvoice($transactionId = '', $creditNoteNumber = '') {
		if ($transactionId != '' || $transactionId = $this->getTransactionId()) {
			$this->_transactionId = $transactionId;
			$this->CancelSr = $this->_setupCancelSr();
		}
		
		if ($this->CancelSr != null) {
			unset($this->_items);
			$this->CancelSr->cancelInvoice();
			$this->CancelSr->setComment('Vollstorno');
			$creditNoteNumber && $this->CancelSr->setCreditNoteNumber($creditNoteNumber);
			$this->CancelSr->sendRequest();
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
			return $this->getErrors();
		}
		
		return false;
	}
	
	
	/**
	 * Wrapper function for confirming this invoice via SofortLib_Multipay (SofortLib)
	 * @param $transactionId - optional parameter for confirming a transaction on the fly
	 * @param $invoiceNumer - optional parameter for own invoice number
	 * @param $customerNumber - optional parameter for own customer number
	 * @param $orderNumber - optional parameter for own order number
	 * @return Ambigious boolean/Array
	 * @todo fix returned value array, empty array
	 * @public
	 */
	public function confirmInvoice($transactionId = '', $invoiceNumber = '', $customerNumber = '', $orderNumber = '') {
		if ($transactionId != '' || $transactionId = $this->getTransactionId()) {
			$this->_transactionId = $transactionId;
			$this->ConfirmSr = $this->_setupConfirmSr();
		}
		
		if ($this->ConfirmSr != null) {
			$this->ConfirmSr->confirmInvoice();
			$invoiceNumber && $this->ConfirmSr->setInvoiceNumber($invoiceNumber);
			$customerNumber && $this->ConfirmSr->setCustomerNumber($customerNumber);
			$orderNumber && $this->ConfirmSr->setOrderNumber($orderNumber);
			$this->ConfirmSr->setApiVersion('2.0');
			$this->ConfirmSr->sendRequest();
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
			return $this->getErrors();
		}
		
		return false;
	}
	
	
	/**
	 * Wrapper function for removing an article via SofortLib_Multipay (SofortLib)
	 * @param $transactionId string
	 * @param $PnagArticels array
	 * @param $comment int
	 * @public
	 * return array
	 */
	public function updateInvoice($transactionId, $items, $comment, $invoiceNumber = '', $customerNumber = '', $orderNumber = '') {
		if ($transactionId != '' || $transactionId = $this->getTransactionId()) {
			$this->_transactionId = $transactionId;
			$this->EditSr = $this->_setupEditSr();
		}
		
		if ($this->EditSr != null) {
			$this->EditSr->setComment($comment);
			$invoiceNumber && $this->EditSr->setInvoiceNumber($invoiceNumber);
			$customerNumber && $this->EditSr->setCustomerNumber($customerNumber);
			$orderNumber && $this->EditSr->setOrderNumber($orderNumber);
			$this->EditSr->updateCart($items);
			$this->EditSr->sendRequest();
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
			return $this->getErrors();
		}
		
		return false;
	}
	
	/**
	 * Wrapper function for updating order number after order hast been placed
	 * @param string $transactionId
	 * @param string $orderNumber
	 */
	public function updateOrderNumber($transactionId, $orderNumber) {
		if ($transactionId != '' || $transactionId = $this->getTransactionId()) {
			$this->_transactionId = $transactionId;
			$this->EditSr = $this->_setupEditSr();
		}
	
		if ($this->EditSr != null) {
			$orderNumber && $this->EditSr->setOrderNumber($orderNumber);
			$this->EditSr->sendRequest();
		}
	}
	
	
	/* ########################## WRAPPER FUNCTIONS MULTIPAY ########################## */
	/**
	 * Wrapper for SofortLib_Multipay::addSofortrechnungItem
	 * @see SofortLib_Multipay
	 * @public
	 * @param $itemId
	 * @param $productNumber
	 * @param $title
	 * @param $unit_price - float precision 2 @see SofortLib_Multipay api
	 * @param $productType
	 * @param $description
	 * @param $quantity - int
	 * @param $tax
	 */
	public function addItemToInvoice($itemId, $productNumber, $title, $unitPrice, $productType = 0, $description = '', $quantity = 1, $tax = 19) {
		$unitPrice = round($unitPrice, 2);	// round all prices to two decimals
		$this->SofortLib_Multipay->addSofortrechnungItem($itemId, $productNumber, $title, $unitPrice, $productType, $description, $quantity, $tax);
		$this->setItem($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax);
		$this->_amount += ($quantity * $unitPrice);
		$this->setAmount($this->_amount, $this->_currency);
	}
	
	
	/**
	 * Remove an item from the invoice
	 * @public
	 * @param $itemId
	 * @return boolean
	 */
	public function removeItemfromInvoice($itemId) {
		$return = false;
		$i = 0;
		
		foreach ($this->_items as $item) {
			if ($item->itemId == $itemId) {
				// TODO: remove item
				//unset($this->_items[$i]);
				$this->setAmount($this->getAmount() - $this->getItemAmount($itemId));
				$return = $this->SofortLib_Multipay->removeSofortrechnungItem($itemId);
			}
			
			$i++;
		}
		
		return $return;
	}
	
	
	/**
	 * 
	 * Update an invoice's item
	 * @param string $itemId
	 * @param int $quantity
	 * @param float $unitPrice
	 */
	public function updateInvoiceItem($itemId, $quantity, $unitPrice) {
		$return = false;
		foreach ($this->_items as $item) {
			if ($item->itemId == $itemId) {
				$oldPrice = $item->unitPrice * $item->quantity;
				$item->uniPrice = $unitPrice;
				$item->quantity = $quantity;
				$newPrice = $unitPrice * $quantity;
				$this->setAmount($this->getAmount() - $oldPrice + $newPrice);
				$return = $this->SofortLib_Multipay->updateSofortrechnungItem($itemId, $quantity, $unitPrice);
			}
		}
		
		return $return;
	}
	
	
	/**
	 * 
	 * Getter for an invoice's amount
	 * @param string $itemId
	 */
	public function getItemAmount($itemId) {
		return $this->SofortLib_Multipay->getSofortrechnungItemAmount($itemId);
	}
	
	
	/**
	 * Wrapper for SofortLib_Multipay::setSofortrechnungShippingAddress
	 * @see SofortLib_Multipay
	 * @public
	 * @param $firstname
	 * @param $lastname
	 * @param $street
	 * @param $streetNumber
	 * @param $zipcode
	 * @param $city
	 * @param $salutation
	 * @param $country (optional, default: DE)
	 * @param $nameAdditive (optional)
	 * @param $streetAdditive (optional)
	 * @param $companyName (optional)
	 */
	public function addShippingAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '', $companyName = '') {
		$this->SofortLib_Multipay->setSofortrechnungShippingAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country, $nameAdditive, $streetAdditive, $companyName);
	}
	
	
	/**
	 * Wrapper for SofortLib_Multipay::setSofortrechnungShippingAddress
	 * @see SofortLib_Multipay
	 * @public
	 * @param $firstname
	 * @param $lastname
	 * @param $street
	 * @param $streetNumber
	 * @param $zipcode
	 * @param $city
	 * @param $salutation
	 * @param $country (optional, default: DE)
	 * @param $nameAdditive (optional)
	 * @param $streetAdditive (optional)
	 * @deprecated
	 */
	public function addShippingAddresss($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '') {
		$this->SofortLib_Multipay->setSofortrechnungShippingAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country, $nameAdditive, $streetAdditive);
	}
	
	
	/**
	 * Wrapper for SofortLib_Multipay::setSofortrechnungInvoiceAddress
	 * @see SofortLib_Multipay
	 * @public
	 * @param $firstname
	 * @param $lastname
	 * @param $street
	 * @param $streetNumber
	 * @param $zipcode
	 * @param $city
	 * @param $salutation
	 * @param $country (optional, default: DE)
	 * @param $nameAdditive (optional)
	 * @param $streetAdditive (optional)
	 * @param $companyName (optional)
	 */
	public function addInvoiceAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country = 'DE', $nameAdditive = '', $streetAdditive = '', $companyName = '') {
		$this->SofortLib_Multipay->setSofortrechnungInvoiceAddress($firstname, $lastname, $street, $streetNumber, $zipcode, $city, $salutation, $country, $nameAdditive, $streetAdditive, $companyName);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setSofortrechnungOrderId
	 * @see SofortLib_Multipay
	 * @public
	 * @param $arg
	 */
	public function setOrderId($arg) {
		$this->SofortLib_Multipay->setSofortrechnungOrderId($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setSofortrechnungCustomerId
	 * @public
	 * @param $arg
	 */
	public function setCustomerId($arg) {
		$this->SofortLib_Multipay->setSofortrechnungCustomerId($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setPhoneNumberCustomer
	 * @public
	 * @param $arg
	 */
	public function setPhoneNumberCustomer($arg) {
		$this->SofortLib_Multipay->setPhoneNumberCustomer($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setEmailCustomer
	 * @public
	 * @param $arg
	 */
	public function setEmailCustomer($arg) {
		$this->SofortLib_Multipay->setEmailCustomer($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::addUserVariable
	 * @public
	 * @param $arg
	 */
	public function addUserVariable($arg) {
		$this->SofortLib_Multipay->addUserVariable($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setNotificationUrl
	 * @public
	 * @param $arg
	 */
	public function setNotificationUrl($arg) {
		$this->SofortLib_Multipay->setNotificationUrl($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setAbortUrl
	 * @public
	 * @param $arg
	 */
	public function setAbortUrl($arg) {
		$this->SofortLib_Multipay->setAbortUrl($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setSuccessUrl
	 * @public
	 * @param $arg
	 */
	public function setSuccessUrl($arg) {
		$this->SofortLib_Multipay->setSuccessUrl($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setTimeoutUrl
	 * @public
	 * @param $arg
	 */
	public function setTimeoutUrl($arg) {
		$this->SofortLib_Multipay->setTimeoutUrl($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setTimeout
	 * @public
	 * @param $arg
	 */
	public function setTimeout($arg) {
		$this->SofortLib_Multipay->setTimeout($arg);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setReason
	 * @public
	 * @param $reason1 string
	 * @param $reason2 string
	 */
	public function setReason($reason1, $reason2 = '') {
		$this->SofortLib_Multipay->setReason($reason1, $reason2);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setAmount
	 * @public
	 * @param $arg float
	 * @param $currency string
	 */
	public function setAmount($arg, $currency = 'EUR') {
		$this->SofortLib_Multipay->setAmount($arg, $currency);
	}
	
	
	/**
	 * current total amount of the given order-articles
	 * @return float - sum (price, total) of all articles
	 */
	public function getAmount() {
		if (isset($this->SofortLib_TransactionData) && $this->SofortLib_TransactionData instanceof  SofortLib_TransactionData) {
			$amount = $this->SofortLib_TransactionData->getAmount();
		} else {
			$amount = $this->_amount;
		}
		
		if ($amount != 0.00) {
			return $amount;
		} elseif (isset($this->_amount) && $this->_amount != 0.00) {
			return $this->_amount;	// TODO: check
		}
		
		return 0.0;
	}
	
	
	/**
	 * 
	 * Setter for amount refunded
	 * @param amount $arg
	 */
	public function setAmountRefunded($arg) {
		$this->_amountRefunded = $arg;
	}
	
	
	/**
	 * 
	 * Getter for amount refunded
	 */
	public function getAmountRefunded() {
		return $this->_amountRefunded;
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setSofortrechnung
	 * @public
	 */
	public function setSofortrechnung() {
		$this->SofortLib_Multipay->setSofortrechnung();
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::setDebitorVatNumber
	 * @public
	 */
	public function setDebitorVatNumber($vatNumber) {
		$this->SofortLib_Multipay->setDebitorVatNumber($vatNumber);
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::getPaymentUrl
	 * @public
	 * @return url string
	 */
	public function getPaymentUrl() {
		return $this->SofortLib_Multipay->getPaymentUrl();
	}
	
	
	/**
	 * 
	 * Getter for invoice's number
	 */
	public function getInvoiceNumber() {
		return $this->SofortLib_TransactionData->getInvoiceNumber();
	}
	
	
	/**
	 * 
	 * Getter for customer's number
	 */
	public function getCustomerNumber() {
		return $this->SofortLib_TransactionData->getCustomerNumber();
	}
	
	
	/**
	 * 
	 * Getter for order's number
	 */
	public function getOrderNumber() {
		if ($this->SofortLib_TransactionData instanceof SofortLib_TransactionData) {
			return $this->SofortLib_TransactionData->getOrderNumber();
		}
		return false;
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::geInvoiceType
	 * @public
	 * @return (OR or LS)
	 */
	public function getInvoiceTye() {
		return $this->SofortLib_TransactionData->getInvoiceType();
	}
	
	
	/**
	 * Wrapper function for SofortLib_Multipay::getPaymentUrl
	 * @public
	 * @return url string
	 */
	public function getTransactionId() {
		if ($this->SofortLib_Multipay instanceof SofortLib_Multipay && $transactionId = $this->SofortLib_Multipay->getTransactionId()) {
			return $transactionId;
		} elseif ($this->SofortLib_TransactionData instanceof SofortLib_TransactionData && $transactionId = $this->SofortLib_TransactionData->getTransaction()) {
			return $transactionId;
		} else {
			return $this->_transactionId;
		}
	}
	
	
	/**
	 * Validate your parameters against API
	 * @return array - any validationerrors and -warnings
	 * @public
	 */
	/*
	public function validateRequest() {
		$errorsAndWarnings = $this->SofortLib_Multipay->validateRequest('sr');
		return $errorsAndWarnings;
	}
	*/
	
	
	/**
	 * send the order to pnag (-> buy your products)
	 * @return empty array if ok ELSE array with errors and/or warnings
	 * @public
	 */
	public function checkout() {
		$this->SofortLib_Multipay->sendRequest();
		$this->_transactionId = $this->SofortLib_Multipay->getTransactionId();	// set the resulting transaction id
		$this->SofortLib_TransactionData = $this->_setupTransactionData();
		
		$errors = array();
		
		if ($this->isError()) {
			$errors = $this->getErrors();
		}
		
		$warnings = array();
		
		if ($this->isWarning()) {
			$warnings = $this->getWarnings();
		}
		
		if (!empty($errors) && !empty($warnings)) {
			return array(); //no errors or warnings found
		} else {
			$returnArray = array();
			$returnArray['errors'] = $errors;
			$returnArray['warnings'] = $warnings;
			return $returnArray;
		}
	}
	
	
	/**
	 * 
	 * Getter for information about transaction
	 */
	public function getTransactionInfo() {
		if (is_a($this->SofortLib_TransactionData, 'SofortLib')) {
			$this->SofortLib_TransactionData->setTransaction($this->transactionId);
			$this->sendRequest();
			return $this->SofortLib_TransactionData;
		} else {
			$this->SofortLib_TransactionData = $this->_setupTransactionData();
		}
		
		return array();
	}
	/* ########################## WRAPPER FUNCTIONS MULTIPAY ########################## */
	
	
	/**
	 * Output the resulting invoice as pdf, if possible
	 * Function uses file_get_contents, if allow_url_fopen is allowed in php.ini (might be disabled on shared hosting)
	 * As a fallback, downloading via cURL, when module cURL is available
	 * If neither file_get_contents nor cURL is available for downloading, a connection via socket is used to download.
	 * @public
	 * @return boolean
	 */
	public function getInvoice() {
		$errorCode = $this->getHttpResponseCode($this->_invoiceUrl);
		
		if (!in_array($errorCode, array('200', '301', '302'))) {
			return false;
		}
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="invoice.pdf"');
		echo $this->handleDownload($this->getInvoiceDownloadMethod());
	}
	
	
	/**
	 * 
	 * Handle download of invoice
	 * @param string $method
	 */
	public function handleDownload($method = 'socket') {
		switch ($method) {
			case 'file_get_contents':
				return file_get_contents($this->_invoiceUrl);
				break;
			case 'curl':
				return $this->handleCurlDownload();
				break;
			default:
				return $this->handleSocketDownload();
				break;
		}
	}
	
	
	/**
	 * 
	 * Getter for invoice's download method
	 */
	public function getInvoiceDownloadMethod() {
		if (ini_get('allow_url_fopen')) {
			$method = 'file_get_contents';
		} elseif (function_exists('curl_init')) {
			$method = 'curl';
		} else {
			$method = 'socket';
		}
		return $method;
	}
	
	
	/**
	 * 
	 * Handle download via cURL
	 */
	private function handleCurlDownload() {
		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $this->_invoiceUrl);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		return $buffer;
	}
	
	
	/**
	 * 
	 * Handle download via Socket
	 */
	private function handleSocketDownload() {
		$uri = parse_url($this->_invoiceUrl);
		$host = $uri['host'];
		$path = $uri['path'];
		$handle = $this->openSocket($host);
		$header = $this->makeHeader('GET', $path, $host);
		fwrite($handle, $header);
		$buffer = null;
		
		while (!feof($handle)) {
			$buffer .= fgets($handle, 8192);
		}
		
		fclose($handle);
		return $buffer;
	}
	
	
	/**
	 * 
	 * Getter for HTTP Response code
	 */
	public function getHttpResponseCode() {
		$uri = parse_url($this->_invoiceUrl);
		$host = $uri['host'];
		$path = $uri['path'];
		$handle = $this->openSocket($host);
		$header = $this->makeHeader('HEAD', $path, $host);
		fwrite($handle, $header);
		$buffer = null;
		
		while(!feof($handle)) {
			$buffer .= fgets($handle, 16);
		}
		
		fclose($handle);
		$httpCode = substr($buffer, 9, 3);
		return (int)$httpCode;
	}
	
	
	/**
	 * 
	 * Open up a socket
	 * @param string $host
	 */
	private function openSocket($host) {
		if (!$fp = fsockopen('ssl://'.$host, 443, $errno, $errstr, 15)) {
			return false;
		}
		
		return $fp;
	}
	
	
	/**
	 * 
	 * Make HTTP header for communication
	 * @param string $action
	 * @param string $path
	 * @param string $host
	 */
	private function makeHeader($action, $path, $host) {
		$header = $action." ".$path." HTTP/1.1\r\n";
		$header .= 'Host: '.$host."\r\n";
		$header .= "User-Agent: SOFORTLib \r\n";
		return $header .= "Connection: Close\r\n\r\n";
	}
	
	
	/**
	 * Setter for invoiceUrl
	 * @public
	 * @param $invoiceUrl
	 * @return object
	 */
	public function setInvoiceUrl($invoiceUrl) {
		$this->_invoiceUrl = $invoiceUrl;
		return $this;
	}
	
	
	/**
	 * Getter for retrieving the invoice's url
	 * @public
	 * @return url string
	 */
	public function getInvoiceUrl() {
		return $this->_invoiceUrl;
	}
	
	
	/**
	 * Setter for status
	 * @public
	 * @param $status
	 * @return object
	 */
	public function setStatus($status) {
		$this->_status = $status;
		return $this;
	}
	
	
	/**
	 * Setter for status_reason
	 * @param $status_reason
	 * @return object
	 */
	public function setStatusReason($statusReason) {
		$this->_status_reason = $statusReason;
		return $this;
	}
	
	
	/**
	 *
	 * Setter for invoice_status
	 * @param string $invoice_status | may be emtpy
	 * @return object
	 */
	public function setStatusOfInvoice($invoiceStatus = '') {
		$this->_invoice_status = !empty($invoiceStatus) ? $invoiceStatus : 'empty';
		return $this;
	}
	
	
	/**
	 *
	 * Setter for language_code
	 * @param string $language_code | fallback en
	 * @return object
	 */
	public function setLanguageCode($languageCode = '') {
		$this->_language_code = !empty($languageCode) ? $languageCode : 'en';
		return $this;
	}
	
	
	/**
	 * Setter for transaction
	 * @param $transaction
	 * @return object
	 */
	public function setTransaction($transaction) {
		$this->_transaction = $transaction;
		return $this;
	}
	
	
	/**
	 * Setter for variable time
	 * @param $time
	 * @public
	 * return object
	 */
	public function setTime($time) {
		$this->_time = $time;
		return $this;
	}
	
	
	/**
	 * Setter for interface version
	 * Wrapper for class Multipay to set version according to shop module and it's interface version
	 * e.g. 'pn_xtc_5.0.0'
	 * @param $arg string
	 */
	public function setVersion($arg) {
		$this->SofortLib_Multipay->setVersion($arg);
	}
	
	
	/**
	 * Setter for payment_method
	 * @param $paymentMethod
	 * @return object
	 */
	public function setPaymentMethod($paymentMethod) {
		$this->_payment_method = $paymentMethod;
		return $this;
	}
	
	
	/**
	 * Sets the reason for objecting this invoice
	 * @param string $invoiceObjection (40-50 chars max.)
	 * @return object
	 */
	public function setInvoiceObjection($invoiceObjection) {
		$this->_invoice_objection = $invoiceObjection;
		return $this;
	}
	
	
	/**
	 * Sets the invoice status
	 * @public
	 * @param string $invoiceStatus
	 * @return object
	 */
	public function setInvoiceStatus($invoiceStatus) {
		$this->_invoice_status = $invoiceStatus;
		return $this;
	}
	
	
	/**
	 * Returns the reason for objecting this invoice
	 * @public
	 * @return string
	 */
	public function getInvoiceObjection() {
		return $this->_invoice_objection;
	}
	
	
	/**
	 * Instead of calculated status, this method returns the invoice's staus (string)
	 * @public
	 * @return string
	 */
	public function getStatusOfInvoice() {
		return $this->_invoice_status;
	}
	
	
	/**
	 * Uses the statusMask to "calculate" the current invoice's payment status
	 * @public
	 * @see Invoice::_calcInvoiceStatusCode
	 * @return int
	 */
	public function getInvoiceStatus() {
		return $this->_calcInvoiceStatusCode();
	}
	
	
	/**
	 *
	 * Calculate the current invoice's payment status using bitwise OR
	 * @return int
	 * @private
	 */
	private function _calcInvoiceStatusCode() {
		return $this->_statusMask['status'][$this->_status]
			| $this->_statusMask['status_reason'][$this->_status_reason]
			| $this->_statusMask['invoice_status'][$this->_invoice_status];
	}
	
	
	/**
	 * Getter for payment_method
	 * @public
	 * @return string
	 */
	public function getPaymentMethod() {
		return $this->_payment_method;
	}
	
	
	/**
	 * Getter for status_reason
	 * @public
	 * @return string
	 */
	public function getStatusReason() {
		return $this->_status_reason;
	}
	
	
	/**
	 * Getter for status
	 * @public
	 * @return string
	 */
	public function getStatus() {
		return $this->_status;
	}
	
	
	/**
	 * Getter for language code
	 * @public
	 * @return string
	 */
	public function getLanguageCode() {
		return $this->_language_code;
	}
	
	
	/**
	 * Getter for items
	 * @public
	 * @return array
	 */
	public function getItems() {
		return $this->_items;
	}
	
	
	/**
	 * Setter for invoice items, takes an array of PnagArticle objects
	 * @param array $items
	 */
	public function setItems($items) {
		$this->_items = $items;
	}
	
	
	/**
	 * return TransactionData, the invoice is working with
	 * NOTICE: if status changed (removeArticle, InvoiceConfirmed etc.) it returns always the FRESH TransactionData from pnag-server
	 * @return object
	 * @see $this->refreshTransactionData();
	 */
	public function getTransactionData() {
		if ($this->SofortLib_TransactionData) {
			return $this->SofortLib_TransactionData;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Check, if errors occured
	 * @public
	 * @return boolean
	 */
	public function isError() {
		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isError('sr')) {
				return true;
			}
		}
		
		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isError('sr')) {
				return true;
			}
		}
		
		if ($this->EditSr) {
			if ($this->EditSr->isError('sr')) {
				return true;
			}
		}
		
		if ($this->CancelSr) {
			if ($this->CancelSr->isError('sr')) {
				return true;
			}
		}
		
		
		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isError('sr')) {
				return true;
			}
		}
		
		return false;
	}
	
	
	/**
	 * Check, if warnings occured
	 * @public
	 * @return boolean
	 */
	public function isWarning() {
		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isWarning('sr')) {
				return true;
			}
		}
		
		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isWarning('sr')) {
				return true;
			}
		}
		
		if ($this->EditSr) {
			if ($this->EditSr->isWarning('sr')) {
				return true;
			}
		}
		
		if ($this->CancelSr) {
			if ($this->CancelSr->isWarning('sr')) {
				return true;
			}
		}
		
		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isWarning('sr')) {
				return true;
			}
		}
		
		return false;
	}
	
	
	/**
	 * returns one error (as String!)
	 */
	public function getError() {
		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isError('sr')) {
				return $this->SofortLib_Multipay->getError('sr');
			}
		}
		
		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isError('sr')) {
				return $this->ConfirmSr->getError('sr');
			}
		}
		
		if ($this->EditSr) {
			if ($this->EditSr->isError('sr')) {
				return $this->EditSr->getError('sr');
			}
		}
		
		if ($this->CancelSr) {
			if ($this->CancelSr->isError('sr')) {
				return $this->CancelSr->getError('sr');
			}
		}
		
		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isError('sr')) {
				return $this->SofortLib_TransactionData->getError('sr');
			}
		}
		
		return '';
	}
	
	
	/**
	 * collect all errors and returns them
	 * @return array - all errors
	 * @public
	 */
	public function getErrors() {
		$allErrors = array();
		
		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isError('sr')) {
				$allErrors = array_merge($this->SofortLib_Multipay->getErrors('sr'), $allErrors);
			}
		}
		
		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isError('sr')) {
				$allErrors = array_merge($this->ConfirmSr->getErrors('sr'), $allErrors);
			}
		}
		
		if ($this->EditSr) {
			if ($this->EditSr->isError('sr')) {
				$allErrors = array_merge($this->EditSr->getErrors('sr'), $allErrors);
			}
		}
		
		if ($this->CancelSr) {
			if ($this->CancelSr->isError('sr')) {
				$allErrors = array_merge($this->CancelSr->getErrors('sr'), $allErrors);
			}
		}
		
		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isError('sr')) {
				$allErrors = array_merge($this->SofortLib_TransactionData->getErrors('sr'), $allErrors);
			}
		}
		
		return $allErrors;
	}
	
	
	/**
	 * 
	 * Ouputs errors in a more convenient array to let users easily iterate
	 * @param int $detailLevel
	 * @public
	 */
	public function getErrorCodes($detailLevel = 0) {
		$errors = $this->getErrors();
		
		if (empty($errors)) return array();
		
		$errorCodes = array();
		
		foreach($errors as $error) {
			
			if ($detailLevel === 0) {
				array_push($errorCodes, $error['code']);
			} elseif ($detailLevel === 1) {
				array_push($errorCodes, array(
										'code' => $error['code'],
										'message' => $error['message'],
										'field' => $error['field'],
				));
			}
			
		}
		
		return $errorCodes;
	}
	
	
	/**
	 * 
	 * collects all warnings and returns them
	 * @return array
	 * @public
	 */
	public function getWarnings() {
		$allWarnings = array();
		
		if ($this->SofortLib_Multipay) {
			if ($this->SofortLib_Multipay->isWarning('sr')) {
				$allWarnings = array_merge($this->SofortLib_Multipay->getWarnings('sr'), $allWarnings);
			}
		}
		
		if ($this->ConfirmSr) {
			if ($this->ConfirmSr->isWarning('sr')) {
				$allWarnings = array_merge($this->ConfirmSr->getWarnings('sr'), $allWarnings);
			}
		}
		
		if ($this->EditSr) {
			if ($this->EditSr->isWarning('sr')) {
				$allErrors = array_merge($this->EditSr->getWarnings('sr'), $allErrors);
			}
		}
		
		if ($this->CancelSr) {
			if ($this->CancelSr->isWarning('sr')) {
				$allErrors = array_merge($this->CancelSr->getWarnings('sr'), $allErrors);
			}
		}
		
		if ($this->SofortLib_TransactionData) {
			if ($this->SofortLib_TransactionData->isWarning('sr')) {
				$allWarnings = array_merge($this->SofortLib_TransactionData->getWarnings('sr'), $allWarnings);
			}
		}
		
		return $allWarnings;
	}
	
	
	/**
	 * Enabling logging for all encapsed SofortLib components
	 * @public
	 * @return boolean
	 */
	public function enableLog() {
		(is_a($this->SofortLib_Multipay, 'SofortLib')) ? $this->SofortLib_Multipay->setLogEnabled() : '';
		(is_a($this->SofortLib_TransactionData, 'SofortLib')) ? $this->SofortLib_TransactionData->setLogEnabled() : '';
		(is_a($this->ConfirmSr, 'SofortLib')) ? $this->ConfirmSr->setLogEnabled() : '';
		return true;
	}
	
	
	/**
	 * Disable logging for all encapsed SofortLib components
	 * @public
	 * @return boolean
	 */
	public function disableLog() {
		(is_a($this->SofortLib_Multipay, 'SofortLib')) ? $this->SofortLib_Multipay->setLogDisabled() : '';
		(is_a($this->SofortLib_TransactionData, 'SofortLib')) ? $this->SofortLib_TransactionData->setLogDisabled() : '';
		(is_a($this->ConfirmSr, 'SofortLib')) ? $this->ConfirmSr->setLogDisabled() : '';
		return true;
	}
	
	
	/**
	 * Log the given String into log.txt
	 * Notice: logging must be enabled -> use enableLog();
	 * @param string $msg - Message to log
	 * @return bool - true=logged ELSE false=logging failed
	 * @public
	 */
	public function log($message){
		if (is_a($this->SofortLib_Multipay, 'SofortLib')) {
			$this->SofortLib_Multipay->log($message);
			return true;
		} elseif (is_a($this->SofortLib_TransactionData, 'SofortLib')) {
			$this->SofortLib_TransactionData->log($message);
			return true;
		} elseif (is_a($this->ConfirmSr, 'SofortLib')) {
			$this->ConfirmSr->log($message);
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Log the given String into error_log.txt
	 * Notice: logging must be enabled -> use enableLog();
	 * @param string $msg - Message to log
	 * @return bool - true=logged ELSE false=logging failed
	 * @public
	 */
	public function logError($message){
		if (is_a($this->SofortLib_Multipay, 'SofortLib')) {
			$this->SofortLib_Multipay->logError($message);
			return true;
		} elseif (is_a($this->SofortLib_TransactionData, 'SofortLib')) {
			$this->SofortLib_TransactionData->logError($message);
			return true;
		} elseif (is_a($this->ConfirmSr, 'SofortLib')) {
			$this->ConfirmSr->logError($message);
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Log the given String into warning_log.txt
	 * @param string $msg - Message to log
	 * @return bool - true=logged ELSE false=logging failed
	 * @public
	 */
	public function logWarning($message){
		if (is_a($this->SofortLib_Multipay, 'SofortLib')) {
			$this->SofortLib_Multipay->logWarning($message);
			return true;
		} elseif (is_a($this->SofortLib_TransactionData, 'SofortLib')) {
			$this->SofortLib_TransactionData->logWarning($message);
			return true;
		} elseif (is_a($this->ConfirmSr, 'SofortLib')) {
			$this->ConfirmSr->logWarning($message);
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * 
	 * Override toString
	 */
	public function __toString() {
		$string = '<pre>';
		$string .= print_r($this, 1);
		$string .= '</pre>';
		return $string;
	}
}
?>