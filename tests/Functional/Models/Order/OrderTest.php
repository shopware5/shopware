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

namespace Shopware\Tests\Functional\Models\Order;

use DateTime;
use Enlight_Components_Test_TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Order\History;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Repository;
use Shopware\Models\Order\Status;
use Shopware\Models\Partner\Partner;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Shop\Shop;

class OrderTest extends Enlight_Components_Test_TestCase
{
    protected ModelManager $em;

    protected Repository $repo;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->getRepository(Order::class);

        Shopware()->Container()->set('auth', new ZendAuthMock());
    }

    public function testUpdateOrderHistory(): void
    {
        $order = $this->createOrder();

        $previousPaymentStatus = $order->getPaymentStatus();
        $previousOrderStatus = $order->getOrderStatus();

        $this->orderIsSaved($order);

        $history = $this->thenRetrieveHistoryOf($order);
        static::assertCount(0, $history);

        $paymentStatusInProgress = $this->em->getReference(Status::class, 1);
        static::assertNotNull($paymentStatusInProgress);
        $orderStatusReserved = $this->em->getReference(Status::class, 18);
        static::assertNotNull($orderStatusReserved);

        $order->setPaymentStatus($paymentStatusInProgress);
        $order->setOrderStatus($orderStatusReserved);
        $this->em->flush($order);

        $history = $this->em->getRepository(History::class)->findBy(['order' => $order->getId()]);

        static::assertCount(1, $history);

        static::assertSame($paymentStatusInProgress, $history[0]->getPaymentStatus());
        static::assertSame($previousPaymentStatus, $history[0]->getPreviousPaymentStatus());

        static::assertSame($orderStatusReserved, $history[0]->getOrderStatus());
        static::assertSame($previousOrderStatus, $history[0]->getPreviousOrderStatus());
    }

    public function testSaveMoreThan255CharactersAsTrackingCode(): void
    {
        $order = $this->createOrder();
        $this->orderIsSaved($order);

        $trackingCode = 'trackingCodeWithMoreThan255Characters_1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';

        $order->setTrackingCode($trackingCode);
        $this->em->flush($order);
        $this->em->refresh($order);

        static::assertSame($trackingCode, $order->getTrackingCode());
    }

    public function createOrder(): Order
    {
        $paymentStatusOpen = $this->em->getReference(Status::class, 17);
        $orderStatusOpen = $this->em->getReference(Status::class, 0);
        $paymentDebit = $this->em->getReference(Payment::class, 2);
        $dispatchDefault = $this->em->getReference(Dispatch::class, 9);
        $defaultShop = $this->em->getReference(Shop::class, 1);

        $partner = new Partner();
        $partner->setCompany('Dummy');
        $partner->setIdCode('Dummy');
        $partner->setDate(new DateTime());
        $partner->setContact('Dummy');
        $partner->setStreet('Dummy');
        $partner->setZipCode('Dummy');
        $partner->setCity('Dummy');
        $partner->setPhone('Dummy');
        $partner->setFax('Dummy');
        $partner->setCountryName('Dummy');
        $partner->setEmail('Dummy');
        $partner->setWeb('Dummy');
        $partner->setProfile('Dummy');

        $this->em->persist($partner);

        $order = new Order();
        $order->setNumber('abc');
        $order->setPaymentStatus($paymentStatusOpen);
        $order->setOrderStatus($orderStatusOpen);
        $order->setPayment($paymentDebit);
        $order->setDispatch($dispatchDefault);
        $order->setPartner($partner);
        $order->setShop($defaultShop);
        $order->setInvoiceAmount(5);
        $order->setInvoiceAmountNet(5);
        $order->setInvoiceShipping(5);
        $order->setInvoiceShippingNet(5);
        $order->setTransactionId('5');
        $order->setComment('Dummy');
        $order->setCustomerComment('Dummy');
        $order->setInternalComment('Dummy');
        $order->setNet(true);
        $order->setTaxFree(false);
        $order->setTemporaryId('5');
        $order->setReferer('Dummy');
        $order->setTrackingCode('Dummy');
        $order->setLanguageIso('Dummy');
        $order->setCurrency('EUR');
        $order->setCurrencyFactor(5);
        $order->setRemoteAddress('127.0.0.1');

        return $order;
    }

    private function orderIsSaved(Order $order): void
    {
        $this->em->persist($order);
        $this->em->flush($order);
    }

    /**
     * @return History[]
     */
    private function thenRetrieveHistoryOf(Order $order): array
    {
        return $this->em->getRepository(History::class)->findBy(['order' => $order->getId()]);
    }
}

class ZendAuthMock
{
    /**
     * @return null
     */
    public function getIdentity()
    {
        return null;
    }
}
