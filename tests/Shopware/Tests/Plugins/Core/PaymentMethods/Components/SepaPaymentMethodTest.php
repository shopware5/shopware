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

class Shopware_Tests_Plugins_Core_PaymentMethods_SepaPaymentMethod extends Enlight_Components_Test_Plugin_TestCase
{
    protected $sepaPaymentMethod;

    public function setUp()
    {
        parent::setUp();

        $helper = \TestHelper::Instance();
        $loader = $helper->Loader();

        $pluginDir = $helper->DocPath() . 'engine/Shopware/Plugins/Default/Core/PaymentMethods';

        $loader->registerNamespace(
            'ShopwarePlugin\\PaymentMethods\\Components',
            $pluginDir . '/Components/'
        );

        //As by default SEPA is disabled, we need to activate it
        $sepaPaymentMean = Shopware()->Models()->getRepository('\Shopware\Models\Payment\Payment')
            ->findOneByName('Sepa')->setActive(true);
        Shopware()->Models()->persist($sepaPaymentMean);
        Shopware()->Models()->flush();

        $this->sepaPaymentMethod = new \ShopwarePlugin\PaymentMethods\Components\SepaPaymentMethod();
    }

    public static function tearDownAfterClass() {
        $sepaPaymentMean = Shopware()->Models()->getRepository('\Shopware\Models\Payment\Payment')
            ->findOneByName('Sepa')->setActive(false);
        Shopware()->Models()->persist($sepaPaymentMean);

        $paymentData = Shopware()->Models()->getRepository('\Shopware\Models\Customer\PaymentData')
            ->findAll();
        foreach($paymentData as $payment) {
            Shopware()->Models()->remove($payment);
        }

        $paymentInstances = Shopware()->Models()->getRepository('\Shopware\Models\Payment\PaymentInstance')
            ->findAll();
        foreach($paymentInstances as $paymentInstance) {
            Shopware()->Models()->remove($paymentInstance);
        }

        Shopware()->Models()->flush();
        parent::tearDownAfterClass();
    }

    public function testValidateEmptyGet() {
        $this->Request()->setMethod('GET');

        Shopware()->Front()->setRequest($this->Request());

        $validationResult = $this->sepaPaymentMethod->validate();
        $this->assertTrue(is_array($validationResult));
        if(count($validationResult)) {
            $this->assertArrayHasKey('sErrorFlag', $validationResult);
            $this->assertArrayHasKey('sErrorMessages', $validationResult);
            $this->assertArrayHasKey("sSepaIban", $validationResult['sErrorFlag']);
            $this->assertArrayHasKey("sSepaBic", $validationResult['sErrorFlag']);
            $this->assertArrayHasKey("sSepaBankName", $validationResult['sErrorFlag']);
        }
    }

    public function testValidateFaultyIban() {
        $this->Request()->setMethod('POST');
        $this->Request()->setQuery(array(
            "sSepaIban" => "Some Invalid Iban",
            "sSepaBic" => "Some Valid Bic",
            "sSepaBankName" => "Some Valid Bank Name"
        ));

        Shopware()->Front()->setRequest($this->Request());

        $validationResult = $this->sepaPaymentMethod->validate();
        $this->assertTrue(is_array($validationResult));
        if(count($validationResult)) {
            $this->assertArrayHasKey('sErrorFlag', $validationResult);
            $this->assertArrayHasKey('sErrorMessages', $validationResult);
            $this->assertContains(Shopware()->Snippets()->getNamespace('engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa')
                ->get('ErrorIBAN', 'Invalid IBAN'), $validationResult['sErrorMessages']);
            $this->assertFalse(array_key_exists("sSepaBic", $validationResult['sErrorFlag']));
            $this->assertFalse(array_key_exists("sSepaBankName", $validationResult['sErrorFlag']));
        }
    }

    public function testValidateCorrectData() {
        $this->Request()->setMethod('POST');
        $this->Request()->setQuery(array(
            "sSepaIban" => "AL47 2121 1009 0000 0002 3569 8741",
            "sSepaBic" => "Some Valid Bic",
            "sSepaBankName" => "Some Valid Bank Name"
        ));

        Shopware()->Front()->setRequest($this->Request());

        $validationResult = $this->sepaPaymentMethod->validate();
        $this->assertTrue(is_array($validationResult));
        $this->assertCount(0, $validationResult);
    }

    public function testSavePaymentDataInitialEmptyData() {
        Shopware()->Session()->sUserId = 1;

        Shopware()->Front()->setRequest($this->Request());

        $this->sepaPaymentMethod->savePaymentData();

        $lastPayment = $this->sepaPaymentMethod->getCurrentPaymentData();
        $this->assertInstanceOf('Shopware\Models\Customer\PaymentData', $lastPayment);
        $this->assertEquals('sepa', $lastPayment->getPaymentMean()->getName());
        $this->assertEquals(1, $lastPayment->getCustomer()->getId());
        $this->assertEquals(null, $lastPayment->getBankName());
        $this->assertEquals(null, $lastPayment->getBic());
        $this->assertEquals(null, $lastPayment->getIban());
        $this->assertEquals(false, $lastPayment->getUseBillingData());
    }

    public function testSavePaymentDataUpdatePrevious() {
        Shopware()->Session()->sUserId = 1;

        $this->Request()->setQuery(array(
            "sSepaIban" => "AL47 2121 1009 0000 0002 3569 8741",
            "sSepaBic" => "Some Valid Bic",
            "sSepaBankName" => "Some Valid Bank Name",
            "sSepaUseBillingData" => "true"
        ));
        Shopware()->Front()->setRequest($this->Request());

        $this->sepaPaymentMethod->savePaymentData();

        $lastPayment = $this->sepaPaymentMethod->getCurrentPaymentData();
        $this->assertInstanceOf('Shopware\Models\Customer\PaymentData', $lastPayment);
        $this->assertEquals('sepa', $lastPayment->getPaymentMean()->getName());
        $this->assertEquals(1, $lastPayment->getCustomer()->getId());
        $this->assertEquals("Some Valid Bank Name", $lastPayment->getBankName());
        $this->assertEquals("Some Valid Bic", $lastPayment->getBic());
        $this->assertEquals("AL47212110090000000235698741", $lastPayment->getIban());
        $this->assertEquals(true, $lastPayment->getUseBillingData());
    }

    public function testCreatePaymentInstance() {
        $orderId = 57;
        $userId = 1;
        $paymentId = 6;
        Shopware()->Session()->sUserId = $userId;

        //for now, don't test email
        Shopware()->Config()->set('sepaSendEmail', false);

        $paymentInstance = $this->sepaPaymentMethod->createPaymentInstance($orderId, $userId, $paymentId);

        $this->assertInstanceOf('Shopware\Models\Payment\PaymentInstance', $paymentInstance);
        $this->assertInstanceOf('Shopware\Models\Order\Order', $paymentInstance->getOrder());
        $this->assertEquals(57, $paymentInstance->getOrder()->getId());
        $this->assertInstanceOf('Shopware\Models\Payment\Payment', $paymentInstance->getPaymentMean());
        $this->assertEquals('sepa', $paymentInstance->getPaymentMean()->getName());

        $this->assertEquals("Some Valid Bank Name", $paymentInstance->getBankName());
        $this->assertEquals("Some Valid Bic", $paymentInstance->getBic());
        $this->assertEquals("AL47212110090000000235698741", $paymentInstance->getIban());
        $this->assertEquals("Max", $paymentInstance->getFirstName());
        $this->assertEquals("Mustermann", $paymentInstance->getLastName());
        $this->assertEquals("Musterstr. 55", $paymentInstance->getAddress());
        $this->assertEquals("55555", $paymentInstance->getZipCode());
        $this->assertEquals("Musterhausen", $paymentInstance->getCity());
    }
}