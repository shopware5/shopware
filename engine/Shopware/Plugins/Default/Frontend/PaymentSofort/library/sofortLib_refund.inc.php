<?php
/**
 * class for refund/rueckbuchung
 *
 * Copyright (c) 2012 SOFORT AG
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 17:26:25 +0100 (Fr, 23. Nov 2012) $
 * @version SofortLib 1.5.4  $Id: sofortLib_refund.inc.php 5776 2012-11-23 16:26:25Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_Refund extends SofortLib_Abstract {
	
	protected $_parameters = array();
	
	protected $_response = array();
	
	protected $_xmlRootTag = 'refunds';
	
	
	/**
	 * 
	 * Constructor for SofortLib_Refund
	 * @param string $configKey
	 */
	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('refundApiUrl') != '') ? getenv('refundApiUrl') : 'https://www.sofort.com/payment/refunds';
		parent::__construct($userId, $apiKey, $apiUrl);
	}
	
	
	/**
	 * send this message and get response
	 * @return array transactionid=>status
	 */
	public function sendRequest() {
		parent::sendRequest();
		return $this->getStatusArray();
	}
	
	
	/**
	 * add a new refund to this message
	 *
	 * @param string $transaction transaction id of transfer you want to refund
	 * @param float $amount amount of money to refund, less or equal to amount of original transfer
	 * @param string $comment comment that will be displayed in  admin-menu later
	 * @return SofortLib_Refund $this
	 */
	public function addRefund($transaction, $amount, $comment = '') {
		$this->_parameters['refund'][] = array(
			'transaction' => $transaction,
			'amount' => $amount,
			'comment' => $comment,
		);
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
	public function setSenderAccount($bankCode, $accountNumber, $holder = '') {
		$this->_parameters['sender'] = array(
			'holder' => $holder,
			'account_number' => $accountNumber,
			'bank_code' => $bankCode,
		);
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for title
	 * @param string $arg
	 */
	public function setTitle($arg) {
		$this->_parameters['title'] = $arg;
		return $this;
	}
	
	
	/**
	 * 
	 * Getter for transactions
	 * @param int $i
	 */
	public function getTransactionId($i = 0) {
		return $this->_response['refunds']['refund'][$i]['transaction']['@data'];
	}
	
	
	/**
	 * 
	 * Getter for amounts
	 * @param int $i
	 */
	public function getAmount($i = 0) {
		return $this->_response['refunds']['refund'][$i]['amount']['@data'];
	}
	
	
	/**
	 * 
	 * Getter for statuses
	 * @param int $i
	 */
	public function getStatus($i = 0) {
		return $this->_response['refunds']['refund'][$i]['status']['@data'];
	}
	
	
	/**
	 * 
	 * Getter for comments
	 * @param int $i
	 */
	public function getComment($i = 0) {
		return $this->_response['refunds']['refund'][$i]['comment']['@data'];
	}
	
	
	/**
	 * 
	 * Getter for refund's title
	 */
	public function getTitle() {
		return $this->_response['refunds']['title']['@data'];
	}
	
	
	/**
	 * 
	 * Getter for refund's errors
	 * @param int $i
	 */
	public function getRefundError($i = 0) {
		return parent::getError('all', $this->_response[$i]);
	}
	
	
	/**
	 * 
	 * Has an error occurred for refund
	 * @param int $i
	 */
	public function isRefundError($i = 0) {
		return $this->_response['refunds']['refund'][$i]['status']['@data'] == 'error';
	}
	
	
	/**
	 * 
	 * Getter for DTA (MT940)
	 */
	public function getDta() {
		return $this->_response['refunds']['dta']['@data'];
	}
	
	
	/**
	 * 
	 * Getter for response, as an array
	 */
	public function getAsArray() {
		return $this->_response;
	}
	
	
	/**
	 * 
	 * Getter for status array
	 * @deprecated
	 */
	public function getStatusArray() {
		$ret = array();
		
		foreach ($this->_response['refunds']['refund'] as $transaction) {
			$ret[$transaction['transaction']['@data']] = $transaction['status']['@data'];
		}
		
		return $ret;
	}
	
	
	/**
	 * Parse the XML (override)
	 * @see SofortLib_Abstract::_parseXml()
	 */
	protected function _parseXml() {}
	
	
	/**
	 * Handle errors occurred
	 * @see SofortLib::_handleErrors()
	 */
	protected function _handleErrors() {
		if (!isset($this->_response['refunds']['refund'][0])) {
			$tmp = $this->_response['refunds']['refund'];
			unset($this->_response['refunds']['refund']);
			$this->_response['refunds']['refund'][] = $tmp;
		}
		
		foreach ($this->_response['refunds']['refund'] as $response) {
			//handle errors
			if (isset($response['errors']['error'])) {
				if (!isset($response['errors']['error'][0])) {
					$tmp = $response['errors']['error'];
					unset($response['errors']['error']);
					$response['errors']['error'][0] = $tmp;
				}
				
				foreach ($response['errors']['error'] as $error) {
					$this->errors['global'][] = $this->_getErrorBlock($error);
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
					$this->warnings['global'][] = $this->_getErrorBlock($error);
				}
			}
		}
	}
}
?>