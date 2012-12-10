<?php
require_once 'sofortLib_classic_notification.inc.php';
/**
 * Setup a sofortueberweisung.de session using the classic api
 * after the configuration of the configuration you will receive
 * an url and a transaction id, your customer should be redirected to this url
 *
 *
 * Called by the sofortLib.php/sofortLib_ideal_classic.php etc.
 * $sofort->new SofortLib_SofortueberweisungClassic( $userid, $projectid, $password [, $hashfunction='sha1'] );
 * $sofort->set...(); //set params for Hashcalculation
 * $sofort->set...(); //set more params for Hashcalculation
 * $sofort->getPaymentUrl();
 * Notice: sometimes getPaymentUrl() must be overwritten by calling class because of changed hash-params
 *
 *
 * Copyright (c) 2012 SOFORT AG
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 17:15:47 +0100 (Fr, 23. Nov 2012) $
 * @version SofortLib 1.5.4  $Id: sofortLib_sofortueberweisung_classic.php 5773 2012-11-23 16:15:47Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_SofortueberweisungClassic {
	
	public $params = array();
	
	protected $_password;
	
	protected $_userId;
	
	protected $_projectId;
	
	protected $_hashFunction;
	
	protected $_paymentUrl = 'https://www.sofort.com/payment/start';
	
	protected $_hashFields = array(
		'user_id',
		'project_id',
		'sender_holder',
		'sender_account_number',
		'sender_bank_code',
		'sender_country_id',
		'amount','currency_id',
		'reason_1','reason_2',
		'user_variable_0',
		'user_variable_1',
		'user_variable_2',
		'user_variable_3',
		'user_variable_4',
		'user_variable_5',
	);
	
	
	/**
	 * 
	 * Constructor for SofortLib_SofortueberweisungClassic
	 * @param int $userId
	 * @param int $projectId
	 * @param string $password
	 * @param string $hashFunction
	 * @param string $paymentUrl
	 */
	public function __construct($userId, $projectId, $password, $hashFunction = 'sha1', $paymentUrl = null) {
		$this->_password = $password;
		$this->_userId = $this->params['user_id'] = $userId;
		$this->_projectId = $this->params['project_id'] = $projectId;
		$this->_hashFunction = strtolower($hashFunction);
		$this->params['encoding'] = 'UTF-8';
		if ($paymentUrl) $this->_paymentUrl = $paymentUrl;
		$this->_paymentUrl = $this->_getPaymentDomain();
	}
	
	
	/**
	 * 
	 * Setter for amount
	 * @param float $arg
	 * @param string $currency
	 */
	public function setAmount($arg, $currency = 'EUR') {
		$this->params['amount'] = $arg;
		$this->params['currency_id'] = $currency;
	}
	
	
	/**
	 * 
	 * Setter for sender and holder
	 * @param unknown_type $senderHolder
	 */
	public function setSenderHolder($senderHolder) {
		$this->params['sender_holder'] = $senderHolder;
	}
	
	
	/**
	 * 
	 * Setter for sender's account number
	 * @param string $senderAccountNumber
	 */
	public function setSenderAccountNumber($senderAccountNumber) {
		$this->params['sender_account_number'] = $senderAccountNumber;
	}
	
	
	/**
	 *
	 * Set the reason (Verwendungszweck) for sending money
	 * @param string $arg
	 * @param string $arg2
	 */
	public function setReason($arg, $arg2 = '') {
		$this->params['reason_1'] = preg_replace('#[^a-zA-Z0-9+-\.,]#', ' ', $arg);
		$this->params['reason_2'] = preg_replace('#[^a-zA-Z0-9+-\.,]#', ' ', $arg2);
		return $this;
	}
	
	
	/**
	 * 
	 * User variables can be added here
	 * @param string $arg
	 */
	public function addUserVariable($arg) {
		$i = 0;
		
		while ($i < 6) {
			if (array_key_exists('user_variable_'.$i, $this->params)) {
				$i++;
			} else {
				break;
			}
		}
		
		$this->params['user_variable_'.$i] = $arg;
		return $this;
	}
	
	
	/**
	 * the customer will be redirected to this url after a successful
	 * transaction, this should be a page where a short confirmation is
	 * displayed
	 *
	 * @param string $arg the url after a successful transaction
	 * @return SofortLib_Multipay
	 */
	public function setSuccessUrl($arg) {
		$this->params['user_variable_3'] = $arg;
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
		$this->params['user_variable_4'] = $arg;
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
		$this->params['user_variable_5'] = $arg;
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for interface version
	 * @param string $arg
	 */
	public function setVersion($arg) {
		$this->params['interface_version'] = $arg;
		return $this;
	}
	
	
	/**
	 * 
	 * Getter for payment URL
	 */
	public function getPaymentUrl() {
		//fields required for hash
		$hashFields = $this->_hashFields;
		//build parameter-string for hashing
		$hashString = '';
		
		foreach ($hashFields as $value) {
			if (array_key_exists($value, $this->params)) {
				$hashString .= $this->params[$value];
			}
			
			$hashString .= '|';
		}
		
		$hashString .= $this->_password;
		//calculate hash
		$hash = $this->getHashHexValue($hashString, $this->_hashFunction);
		$this->params['hash'] = $hash;
		//create parameter string
		$paramString = '';
		
		foreach ($this->params as $key => $value) {
			$paramString .= $key.'='.urlencode($value).'&';
		}
		
		$paramString = substr($paramString, 0, -1); //remove last &
		return $this->_paymentUrl.'?'.$paramString;
	}
	
	
	/**
	 * 
	 * Has an error occurred
	 */
	public function isError() {
		return false;
	}
	
	
	/**
	 * 
	 * Getter for error occurred
	 */
	public function getError() {
		return false;
	}
	
	
	/**
	 * Get the hash value
	 * @param string $data string to be hashed
	 * @return string the hash
	 */
	public function getHashHexValue($data, $hashFunction = 'sha1') {
		if($hashFunction == 'sha1') {
			return sha1($data);
		}
		
		if($hashFunction == 'md5') {
			return md5($data);
		}
			
		//mcrypt installed?
		if (function_exists('hash') && in_array($hashFunction, hash_algos())) {
			return hash($hashFunction, $data);
		}
		
		return false;
	}
	
	
	/**
	 * 
	 * Generate a password
	 * @param int [optional] $length length of return value, default 24
	 * @return string
	 */
	public static function generatePassword($length = 24) {
		$password = '';
		
		//we generate about 5-34 random characters [A-Za-z0-9] in every loop
		do {
			$randomBytes = '';
			$strong = false;
			
			if (function_exists('openssl_random_pseudo_bytes')) { //php >= 5.3
				$randomBytes = openssl_random_pseudo_bytes(32, $strong);//get 256bit
			}
			
			if (!$strong) { //fallback
				$randomBytes = pack('I*', mt_rand()); //get 32bit (pseudo-random)
			}
			
			//convert bytes to base64 and remove special chars
			$password .= preg_replace('#[^A-Za-z0-9]#', '', base64_encode($randomBytes));
		} while (strlen($password) < $length);
		
		return substr($password, 0, $length);
	}
	
	
	/**
	 * checks wich hash algorithms are supported by the server
	 * and returns the best one
	 *
	 * @return sha512|sha256|sha1|md5|empty string
	 */
	public function getSupportedHashAlgorithm() {
		$algorithms = $this->getSupportedHashAlgorithms();
		
		if(is_array($algorithms)) {
			return $algorithms[0];
		} else {
			return ''; //no hash function found
		}
	}
	
	
	/**
	 * checks wich hash algorithms are supported by the server
	 *
	 * @return array with all supported algorithms, preferred as first one (index 0)
	 */
	public function getSupportedHashAlgorithms() {
		$algorithms = array();
		
		if (function_exists('hash') && in_array('sha512', hash_algos())) {
			$algorithms[] = 'sha512';
		}
		
		if(function_exists('hash') && in_array('sha256', hash_algos())) {
			$algorithms[] = 'sha256';
		}
		
		if(function_exists('sha1'))	{ //deprecated
			$algorithms[] = 'sha1';
		}
		
		if(function_exists('md5')) { //deprecated
			$algorithms[] = 'md5';
		}
		
		return $algorithms;
	}
	
	
	/**
	 * 
	 * Getter for payment domain
	 */
	protected function _getPaymentDomain() {
		return (getenv('sofortPaymentUrl') != '') ? getenv('sofortPaymentUrl') : $this->_paymentUrl;
	}
}
?>