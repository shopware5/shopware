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
class Shopware_Components_Ipayment_Client extends Zend_Soap_Client
{
    protected $accountData = array();

    /**
     * Constructor method
     * 
     * Expects a configuration parameter.
     *
     * @param Enlight_Config $config
     */
    public function __construct($config)
    {
        $wsdl = 'https://ipayment.de/service/3.0/?wsdl';
        if($config->get('ipaymentSandbox')) {
            $this->accountData = array(
                'accountId' => '99999',
                'trxuserId' => '99998',
                'trxpassword' => '0',
                'adminactionpassword' => '5cfgRT34xsdedtFLdfHxj7tfwx24fe',
            );
        } else {
            $this->accountData = array(
                'accountId' => $config->get('ipaymentAccountId'),
                'trxuserId' => $config->get('ipaymentAppId'),
                'trxpassword' => $config->get('ipaymentAppPassword'),
                'adminactionpassword' => $config->get('ipaymentAdminPassword'),
            );
        }
        parent::__construct($wsdl, array(
            'useragent' => 'Shopware ' . Shopware::VERSION
        ));
    }

    /**
     * Performs pre processing of all arguments.
     *
     * @param array $arguments
     */
    protected function _preProcessArguments($arguments)
    {
    	$arguments = array_merge(
            array($this->accountData),
            $arguments
		);
        return $arguments;
    }
}
