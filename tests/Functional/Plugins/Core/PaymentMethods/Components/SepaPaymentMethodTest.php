<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Plugins\Core\PaymentMethods\Components;

use Enlight_Components_Test_Plugin_TestCase;
use Shopware\Models\Customer\PaymentData;
use Shopware\Models\Order\Order;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Payment\PaymentInstance;
use ShopwarePlugin\PaymentMethods\Components\SepaPaymentMethod;

class SepaPaymentMethodTest extends Enlight_Components_Test_Plugin_TestCase
{
    protected static SepaPaymentMethod $sepaPaymentMethod;

    protected static bool $sepaStatus;

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

        // SEPA needs to be active for this. Also, we need to save existing status to later restore it
        $sepaPaymentMean = Shopware()->Models()->getRepository(Payment::class)->findOneBy(['name' => 'Sepa']);
        static::assertNotNull($sepaPaymentMean);

        self::$sepaStatus = $sepaPaymentMean->getActive();

        $sepaPaymentMean->setActive(true);
        Shopware()->Models()->flush($sepaPaymentMean);

        self::$sepaPaymentMethod = new SepaPaymentMethod();
    }

    public static function tearDownAfterClass(): void
    {
        $sepaPaymentMean = Shopware()->Models()
            ->getRepository(Payment::class)
            ->findOneBy(['name' => 'Sepa']);
        static::assertNotNull($sepaPaymentMean);
        $sepaPaymentMean->setActive(self::$sepaStatus);

        $paymentData = Shopware()->Models()
            ->getRepository(PaymentData::class)
            ->findAll();
        foreach ($paymentData as $payment) {
            Shopware()->Models()->remove($payment);
        }

        $paymentInstances = Shopware()->Models()
            ->getRepository(PaymentInstance::class)
            ->findAll();
        foreach ($paymentInstances as $paymentInstance) {
            Shopware()->Models()->remove($paymentInstance);
        }

        Shopware()->Models()->flush();
        parent::tearDownAfterClass();
    }

    public function testValidateEmptyGet(): void
    {
        $validationResult = self::$sepaPaymentMethod->validate([]);
        static::assertIsArray($validationResult);
        if (\count($validationResult)) {
            static::assertArrayHasKey('sErrorFlag', $validationResult);
            static::assertArrayHasKey('sErrorMessages', $validationResult);
            static::assertArrayHasKey('sSepaIban', $validationResult['sErrorFlag']);
            static::assertArrayHasKey('sSepaBic', $validationResult['sErrorFlag']);
            static::assertArrayHasKey('sSepaBankName', $validationResult['sErrorFlag']);
        }
    }

    public function testValidateFaultyIban(): void
    {
        $data = [
            'sSepaIban' => 'Some Invalid Iban',
            'sSepaBic' => 'Some Valid Bic',
            'sSepaBankName' => 'Some Valid Bank Name',
        ];

        $validationResult = self::$sepaPaymentMethod->validate($data);
        static::assertIsArray($validationResult);
        if (\count($validationResult)) {
            static::assertArrayHasKey('sErrorFlag', $validationResult);
            static::assertArrayHasKey('sErrorMessages', $validationResult);
            static::assertContains(Shopware()->Snippets()->getNamespace('frontend/plugins/payment/sepa')
                ->get('ErrorIBAN', 'Invalid IBAN'), $validationResult['sErrorMessages']);
            static::assertArrayNotHasKey('sSepaBic', $validationResult['sErrorFlag']);
            static::assertArrayNotHasKey('sSepaBankName', $validationResult['sErrorFlag']);
        }
    }

    public function testValidateCorrectData(): void
    {
        $data = [
            'sSepaIban' => 'AL47 2121 1009 0000 0002 3569 8741',
            'sSepaBic' => 'Some Valid Bic',
            'sSepaBankName' => 'Some Valid Bank Name',
        ];

        $validationResult = self::$sepaPaymentMethod->validate($data);
        static::assertIsArray($validationResult);
        static::assertCount(0, $validationResult);
    }

    /**
     * Covers issue SW-7721
     */
    public function testCreatePaymentInstanceWithNoPaymentData(): void
    {
        $orderId = 57;
        $userId = 1;
        $paymentId = 6;
        Shopware()->Session()->set('sUserId', $userId);

        // for now, don't test email
        Shopware()->Config()->offsetSet('sepaSendEmail', false);

        self::$sepaPaymentMethod->createPaymentInstance($orderId, $userId, $paymentId);

        $paymentInstance = Shopware()->Models()
            ->getRepository(PaymentInstance::class)
            ->findOneBy(['order' => $orderId, 'customer' => $userId, 'paymentMean' => $paymentId]);

        static::assertInstanceOf(PaymentInstance::class, $paymentInstance);
        static::assertInstanceOf(Order::class, $paymentInstance->getOrder());
        static::assertSame(57, $paymentInstance->getOrder()->getId());
        static::assertInstanceOf(Payment::class, $paymentInstance->getPaymentMean());
        static::assertSame('sepa', $paymentInstance->getPaymentMean()->getName());

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

    public function testSavePaymentDataInitialEmptyData(): void
    {
        self::$sepaPaymentMethod->savePaymentData(1, $this->Request());

        $lastPayment = self::$sepaPaymentMethod->getCurrentPaymentDataAsArray(1);
        static::assertIsArray($lastPayment);
        static::assertNull($lastPayment['sSepaBankName']);
        static::assertNull($lastPayment['sSepaBic']);
        static::assertSame('', $lastPayment['sSepaIban']);
        static::assertFalse($lastPayment['sSepaUseBillingData']);
    }

    /**
     * @depends testSavePaymentDataInitialEmptyData
     */
    public function testSavePaymentDataUpdatePrevious(): void
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
        static::assertIsArray($lastPayment);
        static::assertSame('Some Valid Bank Name', $lastPayment['sSepaBankName']);
        static::assertSame('Some Valid Bic', $lastPayment['sSepaBic']);
        static::assertSame('AL47212110090000000235698741', $lastPayment['sSepaIban']);
        static::assertTrue($lastPayment['sSepaUseBillingData']);
    }

    public function testCreatePaymentInstance(): void
    {
        $orderId = 57;
        $userId = 1;
        $paymentId = 6;
        Shopware()->Session()->set('sUserId', $userId);

        // for now, don't test email
        Shopware()->Config()->offsetSet('sepaSendEmail', false);

        self::$sepaPaymentMethod->createPaymentInstance($orderId, $userId, $paymentId);

        $paymentInstance = Shopware()->Models()
            ->getRepository(PaymentInstance::class)
            ->findOneBy(['order' => $orderId, 'customer' => $userId, 'paymentMean' => $paymentId]);

        static::assertInstanceOf(PaymentInstance::class, $paymentInstance);
        static::assertInstanceOf(Order::class, $paymentInstance->getOrder());
        static::assertSame(57, $paymentInstance->getOrder()->getId());
        static::assertInstanceOf(Payment::class, $paymentInstance->getPaymentMean());
        static::assertSame('sepa', $paymentInstance->getPaymentMean()->getName());

        static::assertSame('Some Valid Bank Name', $paymentInstance->getBankName());
        static::assertSame('Some Valid Bic', $paymentInstance->getBic());
        static::assertSame('AL47212110090000000235698741', $paymentInstance->getIban());
        static::assertSame('Max', $paymentInstance->getFirstName());
        static::assertSame('Mustermann', $paymentInstance->getLastName());
        static::assertSame('Musterstr. 55', $paymentInstance->getAddress());
        static::assertSame('55555', $paymentInstance->getZipCode());
        static::assertSame('Musterhausen', $paymentInstance->getCity());
        static::assertNotNull($paymentInstance->getAmount());
    }
}
