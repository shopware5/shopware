<?php
/**
 * This class is  for retrieving information about transactions,
 * you can search by transaction-id or by date
 *
 * eg: $transactionDataObj = new SofortLib_TransactionData('yourapikey');
 *
 * $transactionDataObj->setTransaction('1234-456-789654-31321')->sendRequest();
 *
 * echo $transactionDataObj->getStatus();
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 17:15:47 +0100 (Fr, 23. Nov 2012) $
 * @version SofortLib 1.5.4  $Id: sofortLib_transaction_data.inc.php 5773 2012-11-23 16:15:47Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_TransactionData extends SofortLib_Abstract {
	
	protected $_parameters = array();
	
	protected $_response = array();
	
	protected $_xmlRootTag = 'transaction_request';
	
	private $_count = 0;
	
	
	/**
	 * 
	 * Constructor for SofortLib_TransactionData
	 * @param string $configKey
	 */
	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
		return $this;
	}
	
	
	/**
	 * use this function if you want to request
	 * detailed information about a single transaction
	 *
	 * @param String $arg
	 * @return SofortLib_TransactionData $this
	 */
	public function setTransaction($arg) {
		$this->_parameters['transaction'] = $arg;
		return $this;
	}
	
	
	/**
	 * use this function if you want to request
	 * detailed information about several transactions
	 * at once
	 *
	 * @param String $arg
	 * @return SofortLib_TransactionData $this
	 */
	public function addTransaction($arg) {
		if (is_array($arg)) {
			foreach($arg as $element) {
				$this->_parameters['transaction'][] = $element;
			}
		} else {
			$this->_parameters['transaction'][] = $arg;
		}
		
		return $this;
	}
	
	
	/**
	 * you can request all transactions of a certain time
	 * period
	 *
	 * use setNumber() to limit the results
	 *
	 * @param string $from date possible formats: 2011-01-25 or 2011-01-25T19:01:02+02:00
	 * @param string $to date possible formats: 2011-01-25 or 2011-01-25T19:01:02+02:00
	 * @return SofortLib_TransactionData $this
	 * @see setNumber()
	 */
	public function setTime($from, $to) {
		$this->_parameters['from_time'] = $from;
		$this->_parameters['to_time'] = $to;
		return $this;
	}
	
	
	/**
	 * you can limit the number of results
	 *
	 * @param int $number number of results [0-100]
	 * @param int $page result page
	 * @return SofortLib_TransactionData $this
	 * @see setTime()
	 */
	public function setNumber($number, $page = '1') {
		$this->_parameters['number'] = $number;
		$this->_parameters['page'] = $page;
		return $this;
	}
	
	/**
	 * returns the state of consumer_protection if set
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean
	 */
	public function getConsumerProtection($i = 0) {
		$paymentMethod = $this->getPaymentMethod($i);
		
		if (in_array($paymentMethod, array('su', 'sv'))) {
			if(isset($this->_response[$i][$paymentMethod]['consumer_protection']['@data'])) {
				return $this->_response[$i][$paymentMethod]['consumer_protection']['@data'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 * returns the InvoiceAddress
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return array
	 */
	public function getInvoiceAddress($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		$invoiceAddress = array(
			'firstname' => $this->_response[$i]['sr']['invoice_address']['firstname']['@data'],
			'lastname' => $this->_response[$i]['sr']['invoice_address']['lastname']['@data'],
			'name_additive' => $this->_response[$i]['sr']['invoice_address']['name_additive']['@data'],
			'street' => $this->_response[$i]['sr']['invoice_address']['street']['@data'],
			'street_number' => $this->_response[$i]['sr']['invoice_address']['street_number']['@data'],
			'street_additive' => $this->_response[$i]['sr']['invoice_address']['street_additive']['@data'],
			'zipcode' => $this->_response[$i]['sr']['invoice_address']['zipcode']['@data'],
			'city' => $this->_response[$i]['sr']['invoice_address']['city']['@data'],
			'country_code' => $this->_response[$i]['sr']['invoice_address']['country_code']['@data'],
			'salutation' => !empty($this->_response[$i]['sr']['invoice_address']['salutation']['@data']) ? $this->_response[$i]['sr']['invoice_address']['salutation']['@data'] : '',
			'company' => $this->_response[$i]['sr']['invoice_address']['company']['@data'],
		);
		
		return $invoiceAddress;
	}
	
	
	/**
	 * returns the ShippingAddress
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return array
	 */
	public function getShippingAddress($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		$shippingAddress = array(
			'firstname' => $this->_response[$i]['sr']['shipping_address']['firstname']['@data'],
			'lastname' => $this->_response[$i]['sr']['shipping_address']['lastname']['@data'],
			'name_additive' => $this->_response[$i]['sr']['shipping_address']['name_additive']['@data'],
			'street' => $this->_response[$i]['sr']['shipping_address']['street']['@data'],
			'street_number' => $this->_response[$i]['sr']['shipping_address']['street_number']['@data'],
			'street_additive' => $this->_response[$i]['sr']['shipping_address']['street_additive']['@data'],
			'zipcode' => $this->_response[$i]['sr']['shipping_address']['zipcode']['@data'],
			'city' => $this->_response[$i]['sr']['shipping_address']['city']['@data'],
			'country_code' => $this->_response[$i]['sr']['shipping_address']['country_code']['@data'],
			'salutation' => !empty($this->_response[$i]['sr']['shipping_address']['salutation']['@data']) ? $this->_response[$i]['sr']['shipping_address']['salutation']['@data'] : '',
			'company' => $this->_response[$i]['sr']['shipping_address']['company']['@data'],
		);
		
		return $shippingAddress;
	}
	
	
	/**
	 * returns the status of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string pending|received|loss|refunded
	 */
	public function getStatus($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['status']['@data'];
	}
	
	
	/**
	 * returns the detailed status description of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string message
	 */
	public function getStatusReason($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['status_reason']['@data'];
	}
	
	
	/**
	 * returns the time of the last status-change so you can check if sth. changed
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string time e.g. 2011-01-01T12:35:09+01:00 use strtotime() to convert it to unixtime
	 */
	public function getStatusModifiedTime($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['status_modified']['@data'];
	}
	
	
	/**
	 * returns the language code of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string language_code
	 */
	public function getLanguageCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
	
		return $this->_response[$i]['language_code']['@data'];
	}
	
	
	/**
	 * returns the total amount of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return double amount
	 */
	public function getAmount($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['amount']['@data'];
	}
	
	
	/**
	 *
	 * Getter for order number
	 * @param int $i
	 */
	public function getOrderNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['shop_order_number']['@data'];
	}
	
	
	/**
	 * refund, if a transaction was refundend. amount = amountRefunded if everything was refunded
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return double amount
	 */
	public function getAmountRefunded($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['amount_refunded']['@data'];
	}
	
	
	/**
	 *
	 * Getter for the amounts received
	 * @param int $i
	 */
	public function getAmountReceived($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sv']['received_amount']['@data'];
	}
	
	
	/**
	 * returns the currency of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string EUR|USD|GBP....
	 */
	public function getCurrency($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['currency_code']['@data'];
	}
	
	
	/**
	 * returns the payment method of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string su|sr|sl|sv|ls
	 */
	public function getPaymentMethod($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['payment_method']['@data'];
	}
	
	
	/**
	 * returns the transaction id of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string transaction id
	 */
	public function getTransaction($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['transaction']['@data'];
	}
	
	
	/**
	 *
	 * Returns an array containing all items of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @ return array transactions items
	 */
	public function getItems($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		$items = array();
		
		if (isset($this->_response[$i]['sr']['items']['item'][0])) {
			foreach ($this->_response[$i]['sr']['items']['item'] as $key => $item) {
				$items[$key]['item_id'] = $item['item_id']['@data'];
				$items[$key]['product_number'] = $item['product_number']['@data'];
				$items[$key]['product_type'] = $item['product_type']['@data'];
				$items[$key]['number_type'] = $item['number_type']['@data'];
				$items[$key]['title'] = $item['title']['@data'];
				$items[$key]['description'] = $item['description']['@data'];
				$items[$key]['quantity'] = $item['quantity']['@data'];
				$items[$key]['unit_price'] = $item['unit_price']['@data'];
				$items[$key]['tax'] = $item['tax']['@data'];
			}
		}
		
		return $items;
	}
	
	
	/**
	 *
	 * Returns an array containing reason of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @ return array transaction reason
	 */
	public function getReason($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		$reasons[] = $this->_response[$i]['reasons']['reason'][0]['@data'];
		$reasons[] = $this->_response[$i]['reasons']['reason'][1]['@data'];
		return $reasons;
	}
	
	
	/**
	 * returns the user variable of a transaction
	 * @param int $n number of the variable
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string the content of this variable
	 */
	public function getUserVariable($n, $i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['user_variables']['user_variable'][$n]['@data'];
	}
	
	
	/**
	 * returns the time of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string time e.g. 2011-01-01T12:35:09+01:00 use strtotime() to convert it to unixtime
	 */
	public function getTime($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['time']['@data'];
	}
	
	
	/**
	 * returns the project id of a transaction
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return int project id
	 */
	public function getProjectId($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['project_id']['@data'];
	}
	
	
	/**
	 * you can request the url to the pdf of a sr-invoice with this function
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string url to the pdf
	 */
	public function getInvoiceUrl($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['invoice_url']['@data'];
	}
	
	
	/**
	 * returns the status of an invoice
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string the status can be pending|received|reminder_1|reminder_2|reminder_3|encashment
	 */
	public function getInvoiceStatus($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['invoice_status']['@data'];
	}
	
	
	/**
	 * returns the status of an invoice
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string the status can be pending|received|reminder_1|reminder_2|reminder_3|encashment
	 */
	public function getInvoiceObjection($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['invoice_objection']['@data'];
	}
	
	
	/**
	 * checks if the transaction was a test
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return bool true|false
	 */
	public function isTest($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['test']['@data'];
	}
	
	
	/**
	 *
	 * check if the transaction was a sofortueberweisung
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean true|false
	 */
	public function isSofortueberweisung($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['payment_method']['@data'] == 'su';
	}
	
	
	/**
	 *
	 * check if the transaction was a sofortvorkasse
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean true|false
	 */
	public function isSofortvorkasse($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['payment_method']['@data'] == 'sv';
	}
	
	
	/**
	 *
	 * check if the transaction was a sofortlastschrift
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean true|false
	 */
	public function isSofortlastschrift($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['payment_method']['@data'] == 'sl';
	}
	
	
	/**
	 *
	 * check if the transaction was a lastschrift by sofort
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean true|false
	 */
	public function isLastschrift($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['payment_method']['@data'] == 'ls';
	}
	
	
	/**
	 *
	 * check if the transaction was a sofortrechnung
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean true|false
	 */
	public function isSofortrechnung($i = 0) {
		if($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['payment_method']['@data'] == 'sr';
	}
	
	
	/**
	 *
	 * check if status of transaction is received
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean true|false
	 */
	public function isReceived($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['status']['@data'] == 'received';
	}
	
	/**
	 *
	 * check if status of transaction is loss
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean true|false
	 */
	public function isLoss($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['status']['@data'] == 'loss';
	}
	
	
	/**
	 *
	 * check if status of transaction is pending
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean true|false
	 */
	public function isPending($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['status']['@data'] == 'pending';
	}
	
	
	/**
	 *
	 * check if status of transaction is refunded
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return boolean true|false
	 */
	public function isRefunded($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['status']['@data'] == 'refunded';
	}
	
	
	/**
	 * returns the holder of the receiving account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getRecipientHolder($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['recipient']['holder']['@data'];
	}
	
	
	/**
	 *
	 * returns the account number of the receiving account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getRecipientAccountNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['recipient']['account_number']['@data'];
	}
	
	
	/**
	 *
	 * returns the bank code of the receiving account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getRecipientBankCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['recipient']['bank_code']['@data'];
	}
	
	
	/**
	 *
	 * returns the country code of the receiving account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getRecipientCountryCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['recipient']['country_code']['@data'];
	}
	
	
	/**
	 *
	 * returns the bank name of the receiving account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getRecipientBankName($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['recipient']['bank_name']['@data'];
	}
	
	
	/**
	 *
	 * returns the BIC of the receiving account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getRecipientBic($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['recipient']['bic']['@data'];
	}
	
	
	/**
	 *
	 * returns the IBAN of the receiving account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getRecipientIban($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['recipient']['iban']['@data'];
	}
	
	
	/**
	 * returns the holder of the sending account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getSenderHolder($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sender']['holder']['@data'];
	}
	
	
	/**
	 *
	 * returns the account number of the sending account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getSenderAccountNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sender']['account_number']['@data'];
	}
	
	
	/**
	 *
	 * returns the bank code of the sending account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getSenderBankCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sender']['bank_code']['@data'];
	}
	
	
	/**
	 *
	 * returns the country code of the sending account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getSenderCountryCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sender']['country_code']['@data'];
	}
	
	
	/**
	 *
	 * returns the bank name of the sending account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getSenderBankName($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sender']['bank_name']['@data'];
	}
	
	
	/**
	 *
	 * returns the BIC of the sending account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getSenderBic($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sender']['bic']['@data'];
	}
	
	
	/**
	 *
	 * returns the IBAN of the sending account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string value
	 */
	public function getSenderIban($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sender']['iban']['@data'];
	}
	
	
	/**
	 * returns the reason the customer needs to use when paying for "Rechnung by sofort"
	 * @param int $n specify reason linenumber, can be 1 or 2; us 0 for an array with all reasons
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string|array reason
	 */
	public function getInvoiceReason($n = 0, $i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		if ($n == 1) {
			return $this->_response[$i]['sr']['reason_1']['@data'];
		}
		
		if ($n == 2) {
			return $this->_response[$i]['sr']['reason_2']['@data'];
		}
		
		return array($this->_response[$i]['sr']['reason_1']['@data'], $this->_response[$i]['sr']['reason_2']['@data']);
	}
	
	
	/**
	 * get debitor text (Forderungsabtretung)
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string
	 */
	public function getInvoiceDebitorText($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['debitor_text']['@data'];
	}
	
	
	/**
	 *
	 * date of the invoice
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string
	 */
	public function getInvoiceDate($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['invoice_date']['@data'];
	}
	
	
	/**
	 *
	 * due date of the invoice, only available for confirmed invoices
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string
	 */
	public function getInvoiceDueDate($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['due_date']['@data'];
	}
	
	
	/**
	 *
	 * Getter for invoice number
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string
	 */
	public function getInvoiceNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['invoice_number']['@data'];
	}
	
	
	/**
	 *
	 * invoice receiving bank account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string
	 */
	public function getInvoiceBankHolder($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['recipient_bank_account']['holder']['@data'];
	}
	
	
	/**
	 *
	 * invoice receiving bank account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string
	 */
	public function getInvoiceBankAccountNumber($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['recipient_bank_account']['account_number']['@data'];
	}
	
	
	/**
	 *
	 * invoice receiving bank account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string
	 */
	public function getInvoiceBankCode($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['recipient_bank_account']['bank_code']['@data'];
	}
	
	
	/**
	 *
	 * invoice receiving bank account
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string
	 */
	public function getInvoiceBankName($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
		
		return $this->_response[$i]['sr']['recipient_bank_account']['bank_name']['@data'];
	}
	
	
	/**
	 * get the invoice type
	 * @param int $i if you request multiple transactions at once you can set the number here
	 * @return string (OR or LS)
	 */
	public function getInvoiceType($i = 0) {
		if ($i < 0 || $i >= $this->_count) {
			return false;
		}
	
		return $this->_response[$i]['sr']['invoice_type']['@data'];
	}
	
	
	/**
	 *
	 * Getter for count
	 */
	public function getCount() {
		return $this->_count;
	}
	
	
	/**
	 * Parse the XML (override)
	 * (non-PHPdoc)
	 * @see SofortLib_Abstract::_parseXml()
	 */
	protected function _parseXml() {
		if (isset($this->_response['transactions']['transaction_details'])) {
			$this->_count = count($this->_response['transactions']['transaction_details']);
		} else {
			$this->_count = 0;
		}
		
		$transactions = array();
		
		if (isset($this->_response['transactions']) && is_array($this->_response['transactions'])) {
			foreach ($this->_response['transactions'] as $transaction) {
				if (!empty($transaction)) {
					if (isset($transaction['sa']['payments']['payment']) && !isset($transaction['sa']['payments']['payment'][0])) {
						$tmp = $transaction['sa']['payments']['payment'];
						unset($transaction['sa']['payments']['payment']);
						$transaction['sa']['payments']['payment'][] = $tmp;
						unset($tmp);
					}
					
					if (isset($transaction['sr']['items']['item']) && !isset($transaction['sr']['items']['item'][0])) {
						$tmp = $transaction['sr']['items']['item'];
						unset($transaction['sr']['items']['item']);
						$transaction['sr']['items']['item'][] = $tmp;
						unset($tmp);
					}
					
					$transactions[] = $transaction;
				}
			}
		}
		
		$this->_response = $transactions;
	}
}
?>