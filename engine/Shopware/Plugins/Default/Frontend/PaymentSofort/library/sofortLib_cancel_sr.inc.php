<?php
/**
 * This class is for confirming and changing statuses of invoices
 *
 * eg: $confirmObj = new SofortLib_CancelSr('yourapikey');
 *
 * $confirmObj->confirmInvoice('1234-456-789654-31321')->sendRequest();
 *
 * Copyright (c) 2012 SOFORT AG
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-05-21 16:53:26 +0200 (Mo, 21 Mai 2012) $
 * @version SofortLib 1.5.4  $Id: sofortLib_cancel_sr.inc.php 4191 2012-05-21 14:53:26Z niehoff $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_CancelSr extends SofortLib_Abstract {
	
	protected $_apiVersion = '1.0';
	
	protected $_parameters = array();
	
	protected $_response = array();
	
	protected $_xmlRootTag = 'cancel_sr';
	
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
	 * Set the transaction you want to confirm/change
	 * @param String $transaction Transaction Id
	 * @return SofortLib_ConfirmSr
	 */
	public function setTransaction($transaction, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['transaction'] = $transaction;
		return $this;
	}
	
	
	/**
	 * set a comment for refunds
	 * @param string $arg
	 */
	public function setComment($comment, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['comment'] = $comment;
		return $this;
	}
	
	
	/**
	 * 
	 * Setter for credit note number
	 * @param string $creditNoteNumber
	 * @param int $invoice
	 */
	public function setCreditNoteNumber($creditNoteNumber, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['credit_note_number'] = $creditNoteNumber;
		return $this;
	}
	
	
	/**
	 * cancel the invoice
	 * @param string $transaction the transaction id
	 * @return SofortLib_ConfirmSr
	 */
	public function cancelInvoice($transaction = '', $invoice = 0) {
		if (empty($transaction) && array_key_exists('transaction', $this->_parameters)) {
			$transaction = $this->_parameters['invoice'][$invoice]['transaction'];
		}
		
		if (!empty($transaction)) {
			$this->_parameters = NULL;
			$this->_parameters['invoice'][$invoice]['transaction'] = $transaction;
			$this->_parameters['invoice'][$invoice]['items'] = array();
		}
		
		return $this;
	}
	
	
	/**
	 * after you you changed/confirmed an invoice you
	 * can download the new invoice-pdf with this function
	 * @return string url
	 */
	public function getInvoiceUrl() {
		return $this->_file;
	}
	
	
	/**
	 * Parse the XML (override)
	 * (non-PHPdoc)
	 * @see SofortLib_Abstract::_parseXml()
	 */
	protected function _parseXml() {
		$this->_file = isset($this->_response['invoice']['download_url']['@data']) ? $this->_response['invoice']['download_url']['@data'] : '';
	}
	
	
	/**
	 * Error handling (override)
	 * (non-PHPdoc)
	 * @see SofortLib::_handleErrors()
	 */
	protected function _handleErrors() {
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