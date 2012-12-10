<?php
/**
 * Instance of this class handles the callback of SOFORT to notify about a status change, the classic way to do so
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 17:15:47 +0100 (Fr, 23. Nov 2012) $
 * @version SofortLib 1.5.4  $Id: sofortLib_classic_notification.inc.php 5773 2012-11-23 16:15:47Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_ClassicNotification {
	
	public $params = array();
	
	private $_password;
	
	private $_userId;
	
	private $_projectId;
	
	private $_hashFunction;

	private $_hashCheck = false;
	
	
	/**
	 *
	 * Constructor for SofortLib_ClassicNotification
	 * @param int $userId
	 * @param int $projectId
	 * @param string $password
	 * @param string $hashFunction
	 */
	public function __construct($userId, $projectId, $password, $hashFunction = 'sha1') {
		$this->_password = $password;
		$this->_userId = $userId;
		$this->_projectId = $projectId;
		$this->_hashFunction = strtolower($hashFunction);
	}
	
	
	/**
	 *
	 * Get the Notification details
	 * @param string $request (POST-Data)
	 */
	public function getNotification($request) {
		if (array_key_exists('international_transaction', $request)) {
			//standard notification
			$fields = array(
				'transaction', 'user_id', 'project_id',
				'sender_holder', 'sender_account_number', 'sender_bank_code', 'sender_bank_name', 'sender_bank_bic', 'sender_iban', 'sender_country_id',
				'recipient_holder', 'recipient_account_number', 'recipient_bank_code', 'recipient_bank_name', 'recipient_bank_bic', 'recipient_iban', 'recipient_country_id',
				'international_transaction', 'amount', 'currency_id', 'reason_1', 'reason_2', 'security_criteria',
				'user_variable_0', 'user_variable_1', 'user_variable_2', 'user_variable_3', 'user_variable_4', 'user_variable_5',
				'created',
			);
		} else {
			//ideal
			$fields = array(
				'transaction', 'user_id', 'project_id',
				'sender_holder', 'sender_account_number', 'sender_bank_name', 'sender_bank_bic', 'sender_iban', 'sender_country_id',
				'recipient_holder', 'recipient_account_number', 'recipient_bank_code', 'recipient_bank_name', 'recipient_bank_bic',	'recipient_iban', 'recipient_country_id',
				'amount', 'currency_id', 'reason_1', 'reason_2',
				'user_variable_0', 'user_variable_1', 'user_variable_2', 'user_variable_3', 'user_variable_4', 'user_variable_5',
				'created',
			);
		}
		
		// http-notification with status
		if (array_key_exists('status', $request) && !empty($request['status'])) {
			array_push($fields, 'status', 'status_modified');
		}
		
		$this->params = array();
		
		foreach ($fields as $key) {
			$this->params[$key] = $request[$key];
		}
		
		$this->params['project_password'] = $this->_password;
		$validationHash = $this->_getHashHexValue(implode('|', $this->params), $this->_hashFunction);
		$messageHash = $request['hash'];
		$this->_hashCheck = ($validationHash === $messageHash);
		return $this;
	}
	
	
	/**
	 *
	 * Check if error occurred
	 * @return boolean
	 */
	public function isError() {
		if (!$this->_hashCheck) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	 *
	 * Get error if occurred
	 */
	public function getError() {
		if (!$this->_hashCheck) {
			return 'hash-check failed';
		}
		
		return false;
	}
	
	
	/**
	 *
	 * Getter for transactionId
	 */
	public function getTransaction() {
		return $this->params['transaction'];
	}
	
	
	/**
	 *
	 * Getter for amount
	 * @return float
	 */
	public function getAmount() {
		return $this->params['amount'];
	}
	
	
	/**
	 * Getter for user variables
	 * @param int $i
	 */
	public function getUserVariable($i = 0) {
		return $this->params['user_variable_'.$i];
	}
	
	
	/**
	 *
	 * Getter for currency
	 * @return string
	 */
	public function getCurrency() {
		return $this->params['currency_id'];
	}
	
	
	/**
	 *
	 * Getter for time
	 * return timestamp
	 */
	public function getTime() {
		return $this->params['created'];
	}
	
	
	/**
	 *
	 * Getter for status
	 * @return string
	 */
	public function getStatus() {
		return $this->params['status'];
	}
	
	
	/**
	 *
	 * Getter for status reason
	 * @return strign
	 */
	public function getStatusReason() {
		switch ($this->getStatus()) {
			case 'received':
				return 'credited';
			case 'pending':
				return 'not_credited_yet';
			case 'loss':
				return 'loss';
		}
		
		return false;
	}
	
	
	/**
	 * Getter for Hash Hex Value
	 * @param string $data string to be hashed
	 * @return string the hash
	 */
	protected function _getHashHexValue($data, $hashFunction = 'sha1') {
		if ($hashFunction == 'sha1') {
			return sha1($data);
		}
		
		if ($hashFunction == 'md5') {
			return md5($data);
		}
		
		//mcrypt installed?
		if (function_exists('hash') && in_array($hashFunction, hash_algos())) {
			return hash($hashFunction, $data);
		}
		
		return false;
	}
}
?>