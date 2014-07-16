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

class Shopware_Tests_Plugins_Core_PaymentMethods_DebitPaymentMethod extends Enlight_Components_Test_Plugin_TestCase
{
    protected static $debitPaymentMethod;

    protected static $debitStatus;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $helper = \TestHelper::Instance();
        $loader = $helper->Loader();

        $pluginDir = $helper->DocPath() . 'engine/Shopware/Plugins/Default/Core/PaymentMethods';

        $loader->registerNamespace(
            'ShopwarePlugin\\PaymentMethods\\Components',
            $pluginDir . '/Components/'
        );

        //Debit needs to be active for this. Also, we need to save existing status to later restore it
        $debitPaymentMean = Shopware()->Models()
            ->getRepository('\Shopware\Models\Payment\Payment')
            ->findOneByName('debit');

        self::$debitStatus = $debitPaymentMean->getActive();

        $debitPaymentMean->setActive(true);
        Shopware()->Models()->flush($debitPaymentMean);

        self::$debitPaymentMethod = new \ShopwarePlugin\PaymentMethods\Components\DebitPaymentMethod();
    }

    public static function tearDownAfterClass()
    {
        Shopware()->Models()
            ->getRepository('\Shopware\Models\Payment\Payment')
            ->findOneByName('debit')
            ->setActive(self::$debitStatus);

        $paymentData = Shopware()->Models()
            ->getRepository('\Shopware\Models\Customer\PaymentData')
            ->findAll();
        foreach ($paymentData as $payment) {
            Shopware()->Models()->remove($payment);
        }

        $paymentInstances = Shopware()->Models()
            ->getRepository('\Shopware\Models\Payment\PaymentInstance')
            ->findAll();
        foreach ($paymentInstances as $paymentInstance) {
            Shopware()->Models()->remove($paymentInstance);
        }

        Shopware()->Models()->flush();
        parent::tearDownAfterClass();
    }

    public function testValidateEmptyGet()
    {
        $this->Request()->setMethod('GET');

        $validationResult = self::$debitPaymentMethod->validate($this->Request());
        $this->assertTrue(is_array($validationResult));
        $this->assertCount(2, $validationResult);
        $this->assertArrayHasKey('sErrorFlag', $validationResult);
        $this->assertArrayHasKey('sErrorMessages', $validationResult);
        $this->assertArrayHasKey("sDebitAccount", $validationResult['sErrorFlag']);
        $this->assertArrayHasKey("sDebitBankcode", $validationResult['sErrorFlag']);
        $this->assertArrayHasKey("sDebitBankName", $validationResult['sErrorFlag']);
    }

    public function testValidateCorrectData()
    {
        $this->Request()->setMethod('POST');
        $this->Request()->setQuery(array(
            "sDebitAccount" => "AL47 2121 1009 0000 0002 3569 8741",
            "sDebitBankHolder" => "Some Account Holder Name",
            "sDebitBankcode" => "Some Bank Code",
            "sDebitBankName" => "Some Bank Name"
        ));

        $validationResult = self::$debitPaymentMethod->validate($this->Request());
        $this->assertTrue($validationResult);
    }

    public function testCreatePaymentInstanceWithNoPaymentData()
    {
        $orderId = 57;
        $userId = 1;
        $paymentId = 2;
        Shopware()->Session()->sUserId = $userId;

        self::$debitPaymentMethod->createPaymentInstance($orderId, $userId, $paymentId);

        $paymentInstance = Shopware()->Models()
            ->getRepository('\Shopware\Models\Payment\PaymentInstance')
            ->findOneBy(array('order' => $orderId, 'customer' => $userId, 'paymentMean' => $paymentId));

        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Customer\Billing')->
            getUserBillingQuery($userId)->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $this->assertInstanceOf('Shopware\Models\Payment\PaymentInstance', $paymentInstance);
        $this->assertInstanceOf('Shopware\Models\Order\Order', $paymentInstance->getOrder());
        $this->assertEquals(57, $paymentInstance->getOrder()->getId());
        $this->assertInstanceOf('Shopware\Models\Payment\Payment', $paymentInstance->getPaymentMean());
        $this->assertEquals('debit', $paymentInstance->getPaymentMean()->getName());

        $this->assertNotEmpty($paymentInstance->getAccountNumber());
        $this->assertNotEmpty($paymentInstance->getAccountHolder());
        $this->assertNotEmpty($paymentInstance->getBankName());
        $this->assertNotEmpty($paymentInstance->getBankCode());
        $this->assertNull($paymentInstance->getBic());
        $this->assertNull($paymentInstance->getIban());
        $this->assertEquals($addressData['firstName'], $paymentInstance->getFirstName());
        $this->assertEquals($addressData['lastName'], $paymentInstance->getLastName());
        $this->assertEquals($addressData['street'] . ' ' . $addressData['streetNumber'], $paymentInstance->getAddress());
        $this->assertEquals($addressData['zipCode'], $paymentInstance->getZipCode());
        $this->assertEquals($addressData['city'], $paymentInstance->getCity());
        $this->assertNotNull($paymentInstance->getAmount());

        Shopware()->Models()->remove($paymentInstance);
        Shopware()->Models()->flush($paymentInstance);
    }

    public function testSavePaymentDataInitialEmptyData()
    {
        self::$debitPaymentMethod->savePaymentData(1, $this->Request());

        $lastPayment = self::$debitPaymentMethod->getCurrentPaymentDataAsArray(1, $this->Request());
        $this->assertEquals(null, $lastPayment['sSepaBankName']);
        $this->assertEquals(null, $lastPayment['sSepaBic']);
        $this->assertEquals(null, $lastPayment['sSepaIban']);
        $this->assertEquals(false, $lastPayment['sSepaUseBillingData']);
    }

    /**
     * @depends testSavePaymentDataInitialEmptyData
     */
    public function testSavePaymentDataUpdatePrevious()
    {
        $this->Request()->setQuery(array(
            "sDebitAccount" => "AL47AL47AL47 AL47AL47AL47",
            "sDebitBankHolder" => "Another Account Holder Name",
            "sDebitBankcode" => "Another Bank Code",
            "sDebitBankName" => "Another Bank Name"
        ));
        Shopware()->Front()->setRequest($this->Request());

        self::$debitPaymentMethod->savePaymentData(1, $this->Request());

        $lastPayment = self::$debitPaymentMethod->getCurrentPaymentDataAsArray(1, $this->Request());
        $this->assertEquals("AL47AL47AL47 AL47AL47AL47", $lastPayment['sDebitAccount']);
        $this->assertEquals("Another Bank Code", $lastPayment['sDebitBankcode']);
        $this->assertEquals("Another Bank Name", $lastPayment['sDebitBankName']);
        $this->assertEquals("Another Account Holder Name", $lastPayment['sDebitBankHolder']);
    }

    public function testCreatePaymentInstance()
    {
        $orderId = 57;
        $userId = 1;
        $paymentId = 2;
        Shopware()->Session()->sUserId = $userId;

        self::$debitPaymentMethod->createPaymentInstance($orderId, $userId, $paymentId);

        $paymentInstance = Shopware()->Models()
            ->getRepository('\Shopware\Models\Payment\PaymentInstance')
            ->findOneBy(array('order' => $orderId, 'customer' => $userId, 'paymentMean' => $paymentId));

        $this->assertInstanceOf('Shopware\Models\Payment\PaymentInstance', $paymentInstance);
        $this->assertInstanceOf('Shopware\Models\Order\Order', $paymentInstance->getOrder());
        $this->assertEquals(57, $paymentInstance->getOrder()->getId());
        $this->assertInstanceOf('Shopware\Models\Payment\Payment', $paymentInstance->getPaymentMean());
        $this->assertEquals('debit', $paymentInstance->getPaymentMean()->getName());

        $this->assertEquals("AL47AL47AL47 AL47AL47AL47", $paymentInstance->getAccountNumber());
        $this->assertEquals("Another Account Holder Name", $paymentInstance->getAccountHolder());
        $this->assertEquals("Another Bank Name", $paymentInstance->getBankName());
        $this->assertEquals("Another Bank Code", $paymentInstance->getBankCode());
        $this->assertNull($paymentInstance->getBic());
        $this->assertNull($paymentInstance->getIban());
        $this->assertEquals("Max", $paymentInstance->getFirstName());
        $this->assertEquals("Mustermann", $paymentInstance->getLastName());
        $this->assertEquals("Musterstr. 55", $paymentInstance->getAddress());
        $this->assertEquals("55555", $paymentInstance->getZipCode());
        $this->assertEquals("Musterhausen", $paymentInstance->getCity());
        $this->assertNotNull($paymentInstance->getAmount());
    }
}
