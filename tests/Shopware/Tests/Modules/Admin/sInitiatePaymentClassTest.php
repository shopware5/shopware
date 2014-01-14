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
 */

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Modules_Admin_sInitiatePaymentClassTest extends Enlight_Components_Test_TestCase
{
    /**
     * Module instance
     *
     * @var sAdmin
     */
    protected $module;

    /**
     * Test set up method
     */
    protected function setUp()
    {
        parent::setUp();

        $this->module = Shopware()->Modules()->Admin();
    }

    public function testReturnType() {
        $payments = Shopware()->Models()->getRepository('Shopware\Models\Payment\Payment')->findAll();

        foreach ($payments as $payment) {
            $paymentClass = $this->module->sInitiatePaymentClass($this->module->sGetPaymentMeanById($payment->getId()));
            if (is_bool($paymentClass)) {
                $this->assertFalse($paymentClass);
            } else {
                $this->assertInstanceOf('ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod', $paymentClass);
                $this->paymentMethodClassTest($paymentClass);
            }
        }
    }

    protected function paymentMethodClassTest(ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod $basePaymentMethod) {
        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());

        $validationResult = $basePaymentMethod->validate(Shopware()->Front()->Request());
        $this->assertTrue(is_array($validationResult));
        if(count($validationResult)) {
            $this->assertArrayHasKey('sErrorFlag', $validationResult);
            $this->assertArrayHasKey('sErrorMessages', $validationResult);
        }
    }
}