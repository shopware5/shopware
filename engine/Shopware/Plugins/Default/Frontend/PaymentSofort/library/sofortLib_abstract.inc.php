<?php
/// \cond
/**
 * interface for SOFORT XML-API
 *
 * this class implements basic http authentication and a xml-parser
 * for parsing response messages
 *
 * requires libcurl and openssl
 *
 * Copyright (c) 2012 SOFORT AG
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 11:34:40 +0100 (Fr, 23. Nov 2012) $
 * @version SofortLib 1.5.4  $Id: sofortLib_abstract.inc.php 5748 2012-11-23 10:34:40Z Niehoff $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 * @internal
 *
 */
class SofortLib_Abstract extends SofortLib {
	
	protected $_validateOnly = false;
	
	protected $_apiVersion = '1.0';
	
	
	/**
	 * Override this callback to set the response in the right context
	 *
	 * @protected
	 */
	protected function _parseXml() {
		trigger_error('Missing implementation of parseXml()', E_USER_NOTICE);
	}
	
	
	/**
	 * send this message and get response
	 * save all warnings - errors are only saved if no payment-url is send from pnag
	 *
	 * @return SofortLib_TransactionData $this
	 */
	public function sendRequest() {
		$requestData[$this->_xmlRootTag] = $this->_parameters;
		$requestData = $this->_prepareRootTag($requestData);
		$xmlRequest = ArrayToXml::render($requestData);
		$this->_log($xmlRequest, ' XmlRequest -> ');
		$xmlResponse = $this->_sendMessage($xmlRequest);
		
		try {
			$this->_response = XmlToArray::render($xmlResponse);
		} catch (Exception $e) {
			$this->_response = array('errors' => array('error' => array('code' => array('@data' => '0999'), 'message' => array('@data' => $e->getMessage()))));
		}
		
		$this->_log($xmlResponse, ' XmlResponse <- ');
		$this->_handleErrors();
		$this->_parseXml();
		return $this;
	}
	
	
	/**
	 * 
	 * Log XML with message
	 * @param string $xml
	 * @param string $message
	 */
	protected function _log($xml, $message) {
		$this->log(get_class($this).$message.$xml);
	}
	
	
	/**
	 * 
	 * prepare the root tag
	 * @param array $requestData
	 */
	private function _prepareRootTag($requestData) {
		if ($this->_apiVersion) {
			$requestData[$this->_xmlRootTag]['@attributes']['version'] = $this->_apiVersion;
		}
		
		if ($this->_validateOnly) {
			$requestData[$this->_xmlRootTag]['@attributes']['validate_only'] = 'yes';
		}
		
		return $requestData;
	}
}
/// \endcond
?>