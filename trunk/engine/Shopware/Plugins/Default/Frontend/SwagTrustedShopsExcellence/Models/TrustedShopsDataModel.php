<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * This Model capsulates the whole data from and to the Trusted Shops Soap Service
 *
 * @copyright Copyright (c) 2011, Shopware AG
 * @author m.schmaeing
 * @author $Author$
 * @package Shopware
 * @subpackage Controllers_Frontend
 * @creation_date 16.11.11 10:24
 * @version $Id$
 */
class TrustedShopsDataModel
{
	const TS_QA_SERVER = "qa.trustedshops.de";
	const TS_PROTECTION_QA_SERVER = "protection-qa.trustedshops.com";
	const TS_PROTECTION_SERVER = "protection.trustedshops.com";
	const TS_SERVER = "www.trustedshops.de";
	const TS_WSDL = "/ts/services/TsProtection?wsdl";
	const TS_RATING_WSDL = "/ts/services/TsRating?wsdl";
	const TS_PROTECTION_WSDL = "/ts/protectionservices/ApplicationRequestService?wsdl";

	/**
	 * this is the global plugin configuration
	 * @var array
	 */
	private $pluginConfig = array();

	/**
	 * class constructor to get access to the config and set soap settings
	 */
	public function __construct()
	{
		ini_set("soap.wsdl_cache_enabled", 1);
		//access to the plugin configuration
		$this->pluginConfig = Shopware()->Plugins()->Frontend()->SwagTrustedShopsExcellence()->Config();
	}

	/**
	 * Get the Protection Item for the Shopware System
	 * These Item can be bought by the user to protect the order
	 * @return array | protection item response
	 */
	public function getProtectionItems()
	{
		return $this->sendSoapRequest('getProtectionItems');
	}

	/**
	 * Checks the Trusted Shops Certificate against the trusted shops id given in the plugin config
	 * @return array | checkCertificate enumerations
	 */
	public function checkCertificate()
	{
		return $this->sendSoapRequest("checkCertificate");
	}

	/**
	 * checks the right login against the Trusted Shops Service
	 *
	 * @param $checkLoginData
	 * @return array | soap response
	 */
	public function checkLogin($checkLoginData)
	{
		return $this->sendSoapRequest("checkLogin",$checkLoginData);
	}

	/**
	 * this will send a request to Trusted Shops to protect the order.
	 *
	 * @param \sOrder $orderData
	 * @param int $tsProductId
	 * @return array | soap response
	 */
	public function sendBuyerProtectionRequest(sOrder $orderData, int $tsProductId)
	{
		$soapData = $this->prepareDataForSoapRequest($orderData, $tsProductId);
		return $this->sendSoapRequest("requestForProtectionV2", $soapData);
	}

	/**
	 * this will send a request to Trusted Shops to check the status of the order
	 * 
	 * @param array $tsSoapParameter
	 * @return array | soap response
	 */
	public function getRequestState(Array $tsSoapParameter)
	{
		return $this->sendSoapRequest("getRequestState", $tsSoapParameter);
	}
	/**
	 * this will send a request to Trusted Shops to update the trusted shop rating image
	 * @param $updateImageData
	 * @return array | soap response
	 */
	public function updateRatingWidgetState($updateImageData)
	{
		return $this->sendSoapRequest("updateRatingWidgetState", $updateImageData);
	}

	/**
	 * This function sends the buyer protection request for the confirmed order.
	 *
	 * @param \sOrder $order
	 * @param int $tsProductId
	 * @return array | data for the soap call
	 */
	private function prepareDataForSoapRequest(sOrder $order, int $tsProductId)
	{
		$user = $order->sUserData;
		$basket = $order->sBasketData;
		$userID = $user["billingaddress"]["customernumber"];
		$currency = Shopware()->Currency();
		$payment = $user["additional"]["payment"]["name"];
		$payment = Shopware()->Plugins()->Frontend()->SwagTrustedShopsExcellence()->getTsPaymentCode($payment);
		$orderNumber = $order->sOrderNumber;
		$orderDate = date("Y-m-d", time()) . "T" . date("H:i:s", time());
		$shopSystemVersion = "Shopware/" . Shopware()->Config()->Version . " ? TS v0.1";
		$decimalAmount = (double)$basket["AmountNumeric"];
		$tsId = $this->pluginConfig->tsEID;

		return array($tsId, $tsProductId, $decimalAmount, strtoupper($currency->getShortName()),
					 strtoupper($payment), $user["additional"]["user"]["email"], $userID, $orderNumber,
					 $orderDate, $shopSystemVersion, $this->pluginConfig->tsWebServiceUser,
					 $this->pluginConfig->tsWebServicePassword);
	}

	/**
	 * Helper function which implements the general soap request to trusted shops
	 *
	 * @param \String $soapFunction
	 * @param array $soapData
	 * @internal param $requestURLSuffix
	 * @return string or int | soap resonse data
	 */
	private function sendSoapRequest(String $soapFunction, Array $soapData = null)
	{
		define("SOAP_ERROR", -1);

		# TS-ID received by Trusted Shops
		$tsId = $this->pluginConfig->tsEID;
		if(empty($soapData)) {
			$soapData = array($tsId);
		}

		if($soapFunction == "requestForProtectionV2" || $soapFunction == "getRequestState" ) {
			$ts_url = ($this->pluginConfig->testSystemActive) ? self::TS_PROTECTION_QA_SERVER : self::TS_PROTECTION_SERVER;
			//special url for protection request on save order
			$wsdlUrl = "https://" . $ts_url . self::TS_PROTECTION_WSDL;
		}
		elseif ($soapFunction == "updateRatingWidgetState") {
			$ts_url = ($this->pluginConfig->testSystemActive) ? self::TS_QA_SERVER : self::TS_SERVER;
			$wsdlUrl = "https://" . $ts_url . self::TS_RATING_WSDL;
		}
		else {
			$ts_url = ($this->pluginConfig->testSystemActive) ? self::TS_QA_SERVER : self::TS_SERVER;
			$wsdlUrl = "https://" . $ts_url . self::TS_WSDL;
		}
		
		try {
			$client = new Zend_Soap_Client($wsdlUrl);
			$returnValue = $client->__call($soapFunction, $soapData);
		}
		catch(SoapFault $fault) {
			$returnValue = $fault->faultcode . " " . $fault->faultstring;
		}
		return $returnValue;
	}
}