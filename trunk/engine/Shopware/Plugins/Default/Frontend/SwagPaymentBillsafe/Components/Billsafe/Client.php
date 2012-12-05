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
 * Shopware Billsafe Client
 * 
 * todo@all: Documentation
 */
class Shopware_Components_Billsafe_Client extends Zend_Soap_Client
{
	/**
     * The sandbox url.
     *
     * @var string
     */
	protected $apiUrlSandbox = 'https://sandbox-soap.billsafe.de/wsdl/V209';
	
	/**
     * The live url.
     *
     * @var string
     */
    protected $apiUrlLive = 'https://soap.billsafe.de/wsdl/V209';
    
    /**
     * The merchant id.
     *
     * @var string
     */
    protected $merchantId = null;
    
    /**
     * The merchant license.
     *
     * @var string
     */
    protected $merchantLicense = null;
    
    /**
     * The application signature.
     *
     * @var string
     */
    protected $applicationSignature = 'ace3d20e19218405d83e18aeb6279a08';
    
    /**
     * The application version.
     *
     * @var string
     */
    protected $applicationVersion = 'Shopware/4.0.0 PaymentBillsafe/2.0.0';
    
    /**
     * Constructor method
     * 
     * Expects a configuration parameter.
     *
     * @param Enlight_Config $config
     */
    public function __construct($config)
    {
        if($config->debug) {
            $wsdl = $this->apiUrlSandbox;
        } else {
            $wsdl = $this->apiUrlLive;
        }
        foreach (array('merchantId', 'merchantLicense') as $key) {
            if(isset($config->$key)) {
                $this->$key = $config->$key;
            }
        }
        parent::__construct($wsdl, array());
    }
    
    /**
     * The client encoding.
     * 
     * @var string
     */
    protected $_clientEncoding = 'UTF-8';
    
    /**
     * Sets the encryption for the client object.
     *
     * @param  string $encoding
     * @return Zend_Soap_Client
     * @throws Zend_Soap_Client_Exception with invalid encoding argument
     */
    public function setEncoding($encoding)
    {
        if (!is_string($encoding)) {
            require_once 'Zend/Soap/Client/Exception.php';
            throw new Zend_Soap_Client_Exception('Invalid encoding specified');
        }

        $this->_clientEncoding = $encoding;
        return $this;
    }

    /**
     * Performs pre processing of all arguments.
     *
     * @param array $arguments
     */
    protected function _preProcessArguments($arguments)
    {
    	$arguments[0] = array_merge(
			array(
				'merchant' => array(
					'id' => $this->merchantId,
					'license' => $this->merchantLicense,
				),
				'application' => array(
					'signature' => $this->applicationSignature,
					'version' => $this->applicationVersion
				),
			),
			(array) $arguments[0]
		);

        return $arguments;
    }
}
