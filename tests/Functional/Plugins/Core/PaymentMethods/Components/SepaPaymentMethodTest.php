<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

namespace Shopware\Tests\Plugins\Core\PaymentMethods;

use ShopwarePlugin\PaymentMethods\Components\SepaPaymentMethod;

class SepaPaymentMethodTest extends \Enlight_Components_Test_Plugin_TestCase
{
    /**
     * @var SepaPaymentMethod
     */
    protected static $sepaPaymentMethod;

    protected static $sepaStatus;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $helper = Shopware();
        $loader = $helper->Loader();

        $pluginDir = $helper->DocPath() . 'engine/Shopware/Plugins/Default/Core/PaymentMethods';

        $loader->registerNamespace(
            'ShopwarePlugin\\PaymentMethods\\Components',
            $pluginDir . '/Components/'
        );

        //SEPA needs to be active for this. Also, we need to save existing status to later restore it
        $sepaPaymentMean = Shopware()->Models()
            ->getRepository('\Shopware\Models\Payment\Payment')
            ->findOneByName('Sepa');

        self::$sepaStatus = $sepaPaymentMean->getActive();

        $sepaPaymentMean->setActive(true);
        Shopware()->Models()->flush($sepaPaymentMean);

        self::$sepaPaymentMethod = new SepaPaymentMethod();
    }

    public static function tearDownAfterClass(): void
    {
        Shopware()->Models()
            ->getRepository('\Shopware\Models\Payment\Payment')
            ->findOneByName('Sepa')
            ->setActive(self::$sepaStatus);

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
        $validationResult = self::$sepaPaymentMethod->validate([]);
        static::assertTrue(is_array($validationResult));
        if (count($validationResult)) {
            static::assertArrayHasKey('sErrorFlag', $validationResult);
            static::assertArrayHasKey('sErrorMessages', $validationResult);
            static::assertArrayHasKey('sSepaIban', $validationResult['sErrorFlag']);
            static::assertArrayHasKey('sSepaBic', $validationResult['sErrorFlag']);
            static::assertArrayHasKey('sSepaBankName', $validationResult['sErrorFlag']);
        }
    }

    public function testValidateFaultyIban()
    {
        $data = [
            'sSepaIban' => 'Some Invalid Iban',
            'sSepaBic' => 'Some Valid Bic',
            'sSepaBankName' => 'Some Valid Bank Name',
        ];

        $validationResult = self::$sepaPaymentMethod->validate($data);
        static::assertTrue(is_array($validationResult));
        if (count($validationResult)) {
            static::assertArrayHasKey('sErrorFlag', $validationResult);
            static::assertArrayHasKey('sErrorMessages', $validationResult);
            static::assertContains(Shopware()->Snippets()->getNamespace('frontend/plugins/payment/sepa')
                ->get('ErrorIBAN', 'Invalid IBAN'), $validationResult['sErrorMessages']);
            static::assertFalse(array_key_exists('sSepaBic', $validationResult['sErrorFlag']));
            static::assertFalse(array_key_exists('sSepaBankName', $validationResult['sErrorFlag']));
        }
    }

    public function testValidateCorrectData()
    {
        $data = [
            'sSepaIban' => 'AL47 2121 1009 0000 0002 3569 8741',
            'sSepaBic' => 'Some Valid Bic',
            'sSepaBankName' => 'Some Valid Bank Name',
        ];

        $validationResult = self::$sepaPaymentMethod->validate($data);
        static::assertTrue(is_array($validationResult));
        static::assertCount(0, $validationResult);
    }

    /**
     * Covers issue SW-7721
     */
    public function testCreatePaymentInstanceWithNoPaymentData()
    {
        $orderId = 57;
        $userId = 1;
        $paymentId = 6;
        Shopware()->Session()->sUserId = $userId;

        //for now, don't test email
        Shopware()->Config()->set('sepaSendEmail', false);

        self::$sepaPaymentMethod->createPaymentInstance($orderId, $userId, $paymentId);

        $paymentInstance = Shopware()->Models()
            ->getRepository('\Shopware\Models\Payment\PaymentInstance')
            ->findOneBy(['order' => $orderId, 'customer' => $userId, 'paymentMean' => $paymentId]);

        static::assertInstanceOf('Shopware\Models\Payment\PaymentInstance', $paymentInstance);
        static::assertInstanceOf('Shopware\Models\Order\Order', $paymentInstance->getOrder());
        static::assertEquals(57, $paymentInstance->getOrder()->getId());
        static::assertInstanceOf('Shopware\Models\Payment\Payment', $paymentInstance->getPaymentMean());
        static::assertEquals('sepa', $paymentInstance->getPaymentMean()->getName());

        static::assertNull($paymentInstance->getBankName());
        static::assertNull($paymentInstance->getBic());
        static::assertNull($paymentInstance->getIban());
        static::assertNull($paymentInstance->getFirstName());
        static::assertNull($paymentInstance->getLastName());
        static::assertNull($paymentInstance->getAddress());
        static::assertNull($paymentInstance->getZipCode());
        static::assertNull($paymentInstance->getCity());
        static::assertNotNull($paymentInstance->getAmount());

        Shopware()->Models()->remove($paymentInstance);
        Shopware()->Models()->flush($paymentInstance);
    }

    public function testSavePaymentDataInitialEmptyData()
    {
        self::$sepaPaymentMethod->savePaymentData(1, $this->Request());

        $lastPayment = self::$sepaPaymentMethod->getCurrentPaymentDataAsArray(1);
        static::assertEquals(null, $lastPayment['sSepaBankName']);
        static::assertEquals(null, $lastPayment['sSepaBic']);
        static::assertEquals(null, $lastPayment['sSepaIban']);
        static::assertEquals(false, $lastPayment['sSepaUseBillingData']);
    }

    /**
     * @depends testSavePaymentDataInitialEmptyData
     */
    public function testSavePaymentDataUpdatePrevious()
    {
        $this->Request()->setQuery([
            'sSepaIban' => 'AL47 2121 1009 0000 0002 3569 8741',
            'sSepaBic' => 'Some Valid Bic',
            'sSepaBankName' => 'Some Valid Bank Name',
            'sSepaUseBillingData' => 'true',
        ]);
        Shopware()->Front()->setRequest($this->Request());

        self::$sepaPaymentMethod->savePaymentData(1, $this->Request());

        $lastPayment = self::$sepaPaymentMethod->getCurrentPaymentDataAsArray(1);
        static::assertEquals('Some Valid Bank Name', $lastPayment['sSepaBankName']);
        static::assertEquals('Some Valid Bic', $lastPayment['sSepaBic']);
        static::assertEquals('AL47212110090000000235698741', $lastPayment['sSepaIban']);
        static::assertEquals(true, $lastPayment['sSepaUseBillingData']);
    }

    public function testCreatePaymentInstance()
    {
        $orderId = 57;
        $userId = 1;
        $paymentId = 6;
        Shopware()->Session()->sUserId = $userId;

        //for now, don't test email
        Shopware()->Config()->set('sepaSendEmail', false);

        self::$sepaPaymentMethod->createPaymentInstance($orderId, $userId, $paymentId);

        $paymentInstance = Shopware()->Models()
            ->getRepository('\Shopware\Models\Payment\PaymentInstance')
            ->findOneBy(['order' => $orderId, 'customer' => $userId, 'paymentMean' => $paymentId]);

        static::assertInstanceOf('Shopware\Models\Payment\PaymentInstance', $paymentInstance);
        static::assertInstanceOf('Shopware\Models\Order\Order', $paymentInstance->getOrder());
        static::assertEquals(57, $paymentInstance->getOrder()->getId());
        static::assertInstanceOf('Shopware\Models\Payment\Payment', $paymentInstance->getPaymentMean());
        static::assertEquals('sepa', $paymentInstance->getPaymentMean()->getName());

        static::assertEquals('Some Valid Bank Name', $paymentInstance->getBankName());
        static::assertEquals('Some Valid Bic', $paymentInstance->getBic());
        static::assertEquals('AL47212110090000000235698741', $paymentInstance->getIban());
        static::assertEquals('Max', $paymentInstance->getFirstName());
        static::assertEquals('Mustermann', $paymentInstance->getLastName());
        static::assertEquals('Musterstr. 55', $paymentInstance->getAddress());
        static::assertEquals('55555', $paymentInstance->getZipCode());
        static::assertEquals('Musterhausen', $paymentInstance->getCity());
        static::assertNotNull($paymentInstance->getAmount());
    }
}
