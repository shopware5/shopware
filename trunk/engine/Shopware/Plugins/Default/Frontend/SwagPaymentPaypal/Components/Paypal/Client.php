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
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Paypal Client
 *
 * @method array setExpressCheckout(array $params)
 * @method array getExpressCheckoutDetails(array $params)
 * @method array doExpressCheckoutPayment(array $params)
 * @method array doReferenceTransaction(array $params)
 * @method array getTransactionDetails(array $params)
 * @method array getBalance(array $params = array())
 * @method array getPalDetails(array $params = array())
 * @method array TransactionSearch(array $params)
 * @method array RefundTransaction(array $params)
 * @method array doReAuthorization(array $params)
 * @method array doAuthorization(array $params)
 * @method array doCapture(array $params)
 * @method array doVoid(array $params)
 */
class Shopware_Components_Paypal_Client extends Zend_Http_Client
{
	/**
     * The sandbox url.
     *
     * @var string
     */
	const URL_SANDBOX = 'https://api-3t.sandbox.paypal.com/nvp';
	
	/**
     * The live url.
     *
     * @var string
     */
    const URL_LIVE = 'https://api-3t.paypal.com/nvp';
    
    /**
     * @var string
     */
    protected $apiUsername;
    
    /**
     * @var string
     */
    protected $apiPassword;
    
    /**
     * @var string
     */
    protected $apiSignature;
    
    /**
     * @var string
     */
    protected $apiVersion;
    
    /**
     * Constructor method
     * 
     * Expects a configuration parameter.
     *
     * @param Enlight_Config $config
     */
    public function __construct($config)
    {
        if(!empty($config->paypalSandbox)) {
            $url = self::URL_SANDBOX;
        } else {
            $url = self::URL_LIVE;
        }
        $this->apiUsername = $config->get('paypalUsername');
        $this->apiPassword = $config->get('paypalPassword');
        $this->apiSignature = $config->get('paypalSignature');
        $this->apiVersion = $config->get('paypalVersion');
        parent::__construct($url, array(
            'useragent' => 'Shopware/' . Shopware()->Config()->Version,
            'timeout' => 5,
        ));
        if (extension_loaded('curl')) {
            $adapter = new Zend_Http_Client_Adapter_Curl();
            $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
            $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
            $this->setAdapter($adapter);
        }
    }

    /**
     * @param $name
     * @param array $args
     * @return array|bool
     */
    public function __call($name, $args = array())
    {
        $name = ucfirst($name);
        $this->resetParameters();
        $this->setParameterGet(array(
            'METHOD' => $name,
            'VERSION' => $this->apiVersion,
            'PWD' => $this->apiPassword,
            'USER' => $this->apiUsername,
            'SIGNATURE' => $this->apiSignature
        ));
        if(!empty($args[0])) {
            $this->setParameterGet($args[0]);
        }
        try {
            $response = $this->request('GET');
        } catch (Exception $e) {
            return false;
        }

        $body = $response->getBody();
        $params = array();
        parse_str($body, $params);
        return $params;
    }
}
