<?php
/**
 * This class is for confirming and changing statuses of invoices
 *
 * eg: $confirmObj = new SofortLib_ConfirmSr('yourapikey');
 *
 * $confirmObj->confirmInvoice('1234-456-789654-31321')->sendRequest();
 *
 * Copyright (c) 2012 SOFORT AG
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 17:15:47 +0100 (Fr, 23. Nov 2012) $
 * @version SofortLib 1.5.4  $Id: sofortLib_confirm_sr.inc.php 5773 2012-11-23 16:15:47Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_ConfirmSr extends SofortLib_Abstract {
	
	protected $_parameters = array();
	
	protected $_invoices = array();
	
	protected $_response = array();
	
	protected $_xmlRootTag = 'confirm_sr';
	
	protected $_apiVersion = '2.0';
	
	private $_file;
	
	
	/**
	 * create new confirm object
	 *
	 * @param String $apikey your API-key
	 */
	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
	}
	
	
	/**
	 * Set the transaction you want to confirm
	 * @param String $transaction Transaction Id
	 * @return SofortLib_ConfirmSr
	 */
	public function setTransaction($transaction, $invoice = 0) {
		if ($this->_apiVersion == 1) {
			$this->_parameters['transaction'] = $transaction;
		} else {
			$this->_parameters['invoice'][$invoice]['transaction'] = $transaction;
		}
		
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for invoice number
	 * @param String $invoiceNumber
	 * @param object $invoice
	 */
	public function setInvoiceNumber($invoiceNumber, $invoice = 0) {
		$this->setApiVersion('2.0');
		$this->_parameters['invoice'][$invoice]['invoice_number'] = $invoiceNumber;
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for costumer numer
	 * @param string $customerNumber
	 * @param int $invoice
	 */
	public function setCustomerNumber($customerNumber, $invoice = 0) {
		$this->setApiVersion('2.0');
		$this->_parameters['invoice'][$invoice]['customer_id'] = $customerNumber;
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for order number
	 * @param string $orderNumber
	 * @param unknown_type $invoice
	 */
	public function setOrderNumber($orderNumber, $invoice = 0) {
		$this->setApiVersion('2.0');
		$this->_parameters['invoice'][$invoice]['order_id'] = $orderNumber;
		return $this;
	}
	
	
	/**
	 * set a comment for refunds
	 * just useable with api version 1.0
	 * @see SofortLib_EditSr
	 * @deprecated
	 * @param string $arg
	 */
	public function setComment($comment) {
		$this->setApiVersion('1.0');
		$this->_parameters['comment'] = $comment;
		return $this;
	}
	
	
	/**
	 * add one item to the cart if you want to change the invoice
	 * just useable with api version 1.0
	 * @see SofortLib_EditSr
	 * @deprecated
	 * @param string $productNumber product number, EAN code, ISBN number or similar
	 * @param string $title description of this title
	 * @param double $unit_price gross price of one item
	 * @param int $productType product type number see manual
	 * @param string $description additional description of this item
	 * @param int $quantity default 1
	 * @param int $tax tax in percent, default 19
	 */
	public function addItem($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax) {
		$this->setApiVersion('1.0');
		$unitPrice = number_format($unitPrice, 2, '.', '');
		$tax = number_format($tax, 2, '.', '');
		$quantity = intval($quantity);
		$this->_parameters['items']['item'][] = array(
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
	 * TODO: implement removal of items
	 * @see SofortLib_EditSr
	 * @deprecated
	 * @param $productId
	 * @param $quantity
	 */
	public function removeItem($productId, $quantity = 0) {
		$this->setApiVersion('1.0');
		
		if (!isset($this->_parameters['items']['item'][$productId])) {
			return false;
		} elseif ($quantity = -1) {
			unset($this->_parameters['items']['item'][$productId]);
			return true;
		}
		
		//$this->_parameters['items']['item'][$productId]['quantity'] = $quantity;
		return true;
	}
	
	/**
	 * 
	 * just useable with api version 1.0
	 * @see SofortLib_EditSr
	 * @deprecated
	 * @param array $cartItems
	 */
	function updateCart($cartItems = array()) {
		$this->setApiVersion('1.0');
		
		if (empty($cartItems)) {
			$this->_parameters['items'] = array();
			return $this;
		}
		
		$i = 0;
		
		foreach ($cartItems as $cartItem) {
			$this->_parameters['items']['item'][$i]['item_id'] = $cartItem['itemId'];
			$this->_parameters['items']['item'][$i]['product_number'] = $cartItem['productNumber'];
			$this->_parameters['items']['item'][$i]['title'] = $cartItem['title'];
			$this->_parameters['items']['item'][$i]['description'] = $cartItem['description'];
			$this->_parameters['items']['item'][$i]['quantity'] = $cartItem['quantity'];
			$this->_parameters['items']['item'][$i]['unit_price'] = number_format($cartItem['unitPrice'], 2, '.', '') ;
			$this->_parameters['items']['item'][$i]['tax'] = $cartItem['tax'];
			$i++;
		}
		
		return $this;
	}
	
	
	/**
	 * cancel the invoice
	 * just useable with api version 1.0
	 * @see SofortLib_EditSr
	 * @deprecated
	 * @param string $transaction the transaction id
	 * @return SofortLib_ConfirmSr
	 */
	public function cancelInvoice($transaction = '') {
		$this->setApiVersion('1.0');
		
		if (empty($transaction) && array_key_exists('transaction', $this->_parameters)) {
			$transaction = $this->_parameters['transaction'];
		}
		
		if (!empty($transaction)) {
			$this->_parameters = NULL;
			$this->_parameters['transaction'] = $transaction;
			$this->_parameters['items'] = array();
		}
		
		return $this;
	}
	
	
	/**
	 * confirm the invoice
	 * @param string $transaction the transaction id
	 * @return SofortLib_ConfirmSr
	 */
	public function confirmInvoice($transaction = '') {
		if ($this->_apiVersion == 1) {
			if (empty($transaction) && array_key_exists('transaction', $this->_parameters)) {
				$transaction = $this->_parameters['transaction'];
			}
				
			if (!empty($transaction)) {
				$this->_parameters = NULL;
				$this->_parameters['transaction'] = $transaction;
			}
		} else {
			if (!empty($transaction)) {
				$this->_parameters['invoice'][0]['transaction'] = $transaction;
			}
		}
		
		return $this;
	}
	
	
	/**
	 * after you you changed/confirmed an invoice you
	 * can download the new invoice-pdf with this function
	 * @return string url
	 */
	public function getInvoiceUrl($i = 0) {
		return isset($this->_response['invoice'][$i]['download_url']['@data']) ? $this->_response['invoice'][$i]['download_url']['@data'] : '';
	}
	
	/**
	 * Parse the XML (override)
	 * (non-PHPdoc)
	 * @see SofortLib_Abstract::_parseXml()
	 */
	protected function _parseXml() {}
	
	
	/**
	 * Handle errors if occurred
	 * (non-PHPdoc)
	 * @see SofortLib::_handleErrors()
	 */
	protected function _handleErrors() {
		if ($this->_apiVersion == 1) {
			return parent::_handleErrors();
		}
		
		if (!isset($this->_response['invoices']['invoice'][0])) {
			$tmp = $this->_response['invoices']['invoice'];
			unset($this->_response['invoices']['invoice']);
			$this->_response['invoices']['invoice'][0] = $tmp;
		}
		
		foreach ($this->_response['invoices']['invoice'] as $response) {
			//handle errors
			if (isset($response['errors']['error'])) {
				if (!isset($response['errors']['error'][0])) {
					$tmp = $response['errors']['error'];
					unset($response['errors']['error']);
					$response['errors']['error'][0] = $tmp;
				}
				
				foreach ($response['errors']['error'] as $error) {
					$this->errors['sr'][] = $this->_getErrorBlock($error);
				}
			}
			
			//handle warnings
			if (isset($response['warnings']['warning'])) {
				if (!isset($response['warnings']['warning'][0])) {
					$tmp = $response['warnings']['warning'];
					unset($response['warnings']['warning']);
					$response['warnings']['warning'][0] = $tmp;
				}
			
				foreach ($response['warnings']['warning'] as $error) {
					$this->warnings['sr'][] = $this->_getErrorBlock($error);
				}
			}
		}
	}
}
?>