<?php

/**
 * The base class for cancelling SofortDauerauftrag
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-09-05 14:27:56 +0200 (Mi, 05 Sep 2012) $
 * @version SofortLib 1.5.0  $Id: sofortLib_cancel_sa.inc.php 5301 2012-09-05 12:27:56Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */

class SofortLib_CancelSa extends SofortLib_Abstract {
	
	protected $_parameters;
	
	protected $_xmlRootTag = 'cancel_sa';
	
	protected $_response = array();
	
	private $_file;
	
	private $_cancelUrl = '';
	
	
	/**
	 * create new cancel object
	 *
	 * @param String $apikey your API-key
	 */
	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
	}
	
	
	/**
	 *
	 * remove SofortDauerauftrag
	 * @param String $transaction Transaction ID
	 * @return SofortLib_CancelSa
	 */
	public function removeSofortDauerauftrag($transaction) {
		if (empty($transaction) && array_key_exists('transaction', $this->_parameters)) {
			$transaction = $this->_parameters['transaction'];
		}
		
		if (!empty($transaction)) {
			$this->_parameters = NULL;
			$this->_parameters['transaction'] = $transaction;
		}
		
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
		$this->_parameters['success_url'] = $arg;
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
	 * Set the transaction you want to confirm/change
	 * @param String $arg Transaction Id
	 * @return SofortLib_CancelSa
	 */
	public function setTransaction($arg) {
		$this->_parameters['transaction'] = $arg;
		return $this;
	}
	
	
	public function getCancelUrl() {
		return $this->_cancelUrl;
	}
	
	
	protected function _parseXml() {
		$this->_cancelUrl = isset($this->_response['subscription']['cancel_url']['@data'])
			? $this->_response['subscription']['cancel_url']['@data']
			: '';
	}
}
?>